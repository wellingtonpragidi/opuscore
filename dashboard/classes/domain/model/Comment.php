<?php
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System\Model\Interaction
 */
class Comment extends Model {

    /**
     * SELECT (read)
     * 1. Listagem por Busca - query - search (LIKE)
     * 2. Single - update (ID)
     * 3. Listagem de tudo 
     */
    public function select(): array {
        $pagination = new Pagination( Count::comments(), per_page('comments') );
        $offset = $pagination->offset();

        $pattern = '
            SELECT 
                c.ID, 
                c.content, 
                c.created, 
                c.approved, 
                u.name, 
                u.username, 
                u.email, 
                p.title, 
                p.segment, 
                m.attachment 
            FROM comments c

            JOIN users u 
                ON c.user_id = u.ID 

            JOIN articles p 
                ON c.related_id = p.ID 
                AND c.type = ? 

            LEFT JOIN medias m 
                ON m.related_type = ? 
                AND c.user_id = m.related_id 
        ';

        if( URL::has('q') ) {
            $cmd = $this->conn->prepare("
                $pattern WHERE u.name LIKE ? ORDER BY c.ID DESC LIMIT ?, ?
            ");

            $q = '%' . URL::GET('q') . '%';
            $params = [ 'article', 'user', $q, $offset, per_page('comments') ];
        }

        else if( URL::has('id') ) {
            $cmd = $this->conn->prepare("$pattern WHERE c.ID = ?");

            $params = [ 'article', 'user', URL::int('id') ];
        }

        else {
            $cmd = $this->conn->prepare("$pattern ORDER BY c.ID DESC LIMIT ?, ?");

            $params = [ 'article', 'user', $offset, per_page('comments') ];
        }

        $cmd->execute( $params );
        
        $list = [];

        $site_url = URL::root();
        $dash_url = URL::root('dashboard');
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            # comments
            $bind->ID         = $row['ID'];
            $bind->content    = $row['content'];
            $bind->created    = $row['created'];
            $bind->URL        = $dash_url . '/comments/update/?id=' . $bind->ID;
            # users
            $bind->name       = $row['name'];
            $bind->email      = $row['email'];
            $bind->username   = $row['username'];
            # articles
            $bind->title      = $row['title'];
            $bind->segment    = $row['segment'];
            # medias
            $bind->attachment = Ensure::object($row['attachment']);

            $list[] = $bind;
        }

        return $list;
    }


    public function update( Assign $bind ): bool {
        if( ! parent::hasChanged('comments', ['content'], $bind) ) {
            return false;
        }

        $cmd = $this->conn->prepare("
            UPDATE comments
            SET content = ?
            WHERE ID = ? AND type = ?
        ");

        $cmd->execute([
            $bind->content,
            $bind->ID,
            $bind->type
        ]);

        # ainda esta diferente?
        if( parent::hasChanged('comments', ['content'], $bind) ) {
            return false;
        }

        return true;
    }


    public function update_approved( Assign $bind ): bool {

        return parent::updater( 'comments', ['approved'], $bind );
    }



    public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM comments WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    

    public function approved( object $show ): bool {
        $cmd = $this->conn->prepare("SELECT approved FROM comments WHERE ID = ? LIMIT 1");
        $cmd->execute([ $show->ID ]);

        return (bool) $cmd->fetchColumn();
    }

    # retorna numero de comentarios a serem aprovados (para notificacoes)
    public function notify_approved(): int {
        $cmd = $this->conn->prepare("SELECT COUNT(*) FROM comments WHERE approved = 0 LIMIT 1");
        $cmd->execute();
        
        return (int) $cmd->fetchColumn();
    }

}