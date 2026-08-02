<?php
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Interaction
 * 
 * ID, type, related_id, user_id, parent, content, created, updated, approved
 */

class Comment extends Model {

    public function select( int $articleID ): array {
        $cmd = $this->conn->prepare("
            SELECT 
                c.ID,
                c.content,
                c.created,
                u.name,
                u.username,
                m.attachment

            FROM comments c 

            JOIN users u
                ON c.user_id = u.ID

            LEFT JOIN medias m
                ON m.related_type = ? 
               AND m.related_id = c.user_id 

            WHERE c.type = ?
              AND c.related_id = ?
              AND c.parent = ?

            ORDER BY c.created DESC
        ");

        $cmd->execute([ 'user', 'article', $articleID, 0 ]);

        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID         = $row['ID'];
            $bind->content    = $row['content'];
            $bind->created    = $row['created'];
            $bind->name       = $row['name'];
            $bind->username   = $row['username'];
            $bind->attachment = Ensure::object($row['attachment']);

            $list[] = $bind;
        }

        return $list;
    }


    public function insert( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO comments(type, related_id, user_id, content, created)
            VALUES(?, ?, ?, ?, ?)
        ");

        $cmd->execute([
            $bind->type,
            $bind->related->ID,
            $bind->related->user_id,
            $bind->content,
            $bind->date->created
        ]);

        $bind->LastID = (int) $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    public function reply(): array {
        return [];
    }



    # Conta o numero de comentarios para o article relacionado
    public function count( Article $article, string $no_comments = 'Nenhum comentário' ): string {
        $cmd = $this->conn->prepare("
            SELECT COUNT(*) FROM comments WHERE related_id = ? LIMIT 1
        ");

        $cmd->execute([ $article->target()->ID ]);

        $count = (int) $cmd->fetchColumn();

        if( $count === 0 ) {
            
            return $no_comments;
        }
        
        $write = $count === 1 ? ' comentário' : ' comentários';

        return $count . $write;
    }




    # Conta o numero de comentarios feitos por um usuario especifico
    public function user_count(): string {
        $cmd = $this->conn->prepare("
            SELECT c.ID, u.name 
                FROM comments c

            JOIN users u 
                ON c.user_id = u.ID

            WHERE u.ID = ? 

            ORDER BY c.created DESC
        ");
        
        $cmd->execute([ User::id() ]);

        $count = (int) $cmd->fetchColumn();

        if( $count === 0 ) {

            return 'Nenhum comentário';
        }
        
        $write = $count === 1 ? ' comentário' : ' comentários';

        return $count . $write;
    }

    # Seleciona comentarios feitos por um usuario especifico
    public function user_commented_on(): array {
        $cmd = $this->conn->prepare("
            SELECT c.ID, c.created, p.title, p.segment 
                FROM comments c

            JOIN users u 
                ON c.user_id = u.ID

            JOIN articles p 
                ON c.related_id = p.ID

            WHERE u.ID = ? AND c.type = ? AND c.approved = ? 

            ORDER BY c.created DESC
        ");

        $cmd->execute([ User::id(), 'article', 1 ]);

        $list = [];

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID      = $row['ID'];
            $bind->created = $row['created'];
            $bind->title   = $row['title'];
            $bind->URL     = URL::root($row['segment'] . '/#comment-' . $bind->ID);

            $list[] = $bind;
        }

        return $list;
    }
}