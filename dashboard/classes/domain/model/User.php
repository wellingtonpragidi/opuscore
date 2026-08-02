<?php
declare( strict_types = 1 );
/**
 * ID, name, username, email, pswd, created, updated, content, token, nonce, status, approved
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @link https://opuscore.dev/classes/user
 * 
 * @package System\Model\Interaction
 */

class User extends Model {

    private static array $cache = [];

    private ?Assign $target = null;


    public function __construct() {}

	public function select() {
        $pagination = new Pagination( Count::users(), per_page('users') );

        $list = [];

        $pattern = "
            SELECT 
                u.ID, 
                u.name, 
                u.username, 
                u.email, 
                u.created, 
                u.updated, 
                u.status, 
                u.approved, 

                m.attachment 

            FROM users u 

            LEFT JOIN medias m 
                ON u.ID = m.related_id AND m.related_type = ? 
        ";

		if( URL::has("q") ) {
            $cmd = $this->conn->prepare("
	    		$pattern 
                WHERE (u.name LIKE ? OR u.email LIKE ?) ORDER BY u.ID DESC LIMIT ?, ?
	    	");
            
            $q = '%' . URL::GET('q') . '%';
            $params = [ 'user', $q, $q, $pagination->offset(), per_page('users') ];
        }

        else if( URL::has("id") ) {
        	$cmd = $this->conn->prepare("$pattern WHERE ID = ?");

            $params = [ 'user', URL::int('id') ];
        }

        else {
            $cmd = $this->conn->prepare("$pattern ORDER BY ID DESC LIMIT ?, ?");

            $params = [ 'user', $pagination->offset(), per_page('users') ];
        }

        $cmd->execute( $params );

        while($row = $cmd->fetch(PDO::FETCH_ASSOC)) {
        	$bind = new Assign;

        	$bind->ID         = $row['ID'];
        	$bind->name       = $row['name'];
        	$bind->username   = $row['username'];
        	$bind->email      = $row['email'];
        	$bind->created    = $row['created'];
            $bind->updated    = $row['updated'];
        	$bind->status     = $row['status'];
            $bind->approved   = $row['approved'];
            $bind->attachment = Ensure::object($row['attachment']);

        	$list[] = $bind;
        }

        return $list;
	}


    # apenas referencia
    # ainda nao existe um users/update/?id=X no ambiente do dashbd
    public function update( Assign $bind ): bool {
        $set = ['name, username, email, pswd, created, updated, content, token, nonce, status, approved'];

        return parent::updater( 'users', $set, $bind );
    }



    public function update_approved( Assign $bind ): bool {

        return parent::updater( 'users', ['approved'], $bind );
    }



	public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM users WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }



    public function approved( object $show ): bool {
        $cmd = $this->conn->prepare("SELECT approved FROM users WHERE ID = ? LIMIT 1");
        $cmd->execute([ $show->ID ]);

        return (bool) $cmd->fetchColumn();
    }


    # retorna numero de usuarios a serem aprovados (para notificacoes)
    public function notify_approved(): int {
        $cmd = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE approved = 0 LIMIT 1");
        $cmd->execute();
        
        return (int) $cmd->fetchColumn();
    }



    public function target(): Assign {
        if( $this->target !== null ) {
            return $this->target;
        }

        $id = URL::int('id');

        $cmd = $this->conn->prepare("SELECT * FROM users WHERE ID = ?");
        $cmd->execute([ $id ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC ) ?: [];

        return $this->target = new Assign($row);
    }

    # public static function field_byID

    public static function profile_url( int $id ): ?string {
        if( isset(self::$cache['profile_url']) ) {
            return self::$cache['profile_url'];
        }

        parent::init();

        $cmd = self::$db->prepare("SELECT username FROM users WHERE ID = ? LIMIT 1");
        $cmd->execute([ $id ]);

        $username = parent::fetchColumn($cmd);

        self::$cache['profile_url'] = URL::root() . user_base() . '/' . $username;


        return self::$cache['profile_url'];
    } 

}