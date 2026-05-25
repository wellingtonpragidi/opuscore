<?php
/**
 * @package System/Model
 **/
class User {

    private $conn;

    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }

	public function select() {
        $pagination = new Pagination( Count::users(), per_page('users') );

        $list = [];
		if( URL::has("q") ) {
            $cmd = $this->conn->prepare("
	    		SELECT u.ID, u.name, u.username, u.email, u.created, u.status, m.attachment 
	    		FROM users AS u LEFT JOIN medias AS m 
                ON u.ID = m.related_id AND m.related_type = 'user' 
                WHERE (u.name LIKE ? OR u.email LIKE ?) ORDER BY u.ID DESC LIMIT ?, ?
	    	");
            $q = '%' . URL::GET('q') . '%';
            $cmd->execute([ 
                $q, $q, $pagination->offset(), per_page('users') 
            ]);
        }
        elseif( URL::has("id") ) {
        	$cmd = $this->conn->prepare("
	    		SELECT u.ID, u.name, u.username, u.email, u.created, u.status, m.attachment 
                FROM users AS u LEFT JOIN medias AS m 
                ON u.ID = m.related_id AND m.related_type = 'user' 
                WHERE ID = ?
	    	");
            $cmd->execute([ URL::int("id") ]);
        }
        else {
            $cmd = $this->conn->prepare("
	    		SELECT u.ID, u.name, u.username, u.email, u.created, u.status, m.attachment 
                FROM users AS u LEFT JOIN medias AS m 
                ON u.ID = m.related_id AND m.related_type = 'user' 
                ORDER BY ID DESC LIMIT ?, ?
	    	");
            $cmd->execute([ $pagination->offset(), per_page('users') ]);
        }
        while($row = $cmd->fetch(PDO::FETCH_ASSOC)) :
        	$bind = new Assign;
        	$bind->ID         = $row["ID"];
        	$bind->name       = $row["name"];
        	$bind->username   = $row["username"];
        	$bind->email      = $row["email"];
        	$bind->created    = $row["created"];
        	$bind->status     = $row["status"];
            $bind->attachment = json_decode( $row["attachment"] ?? '' );
        	$list[] = $bind;
        endwhile;
        return $list;
	}

	public function delete() {
        INPUT::method_request();
        if( ! INPUT::is('action', 'target_id') ) {
            return;
        }
        $cmd = $this->conn->prepare("DELETE FROM users WHERE ID = ?");
        $cmd->execute([ INPUT::int("target_id") ]);
        if( $cmd->rowCount() > 0 ) {
        	alert_time( 'success', 'Usuário excluído.', 2500 );
            redirect( dash_url('users'), 4200 );
        }
        else {
            alert('warning', 'O usuário não foi excluído.');
        }
        /**
         * @todo
         * adicionar unlink()
         * 
         * */
    }

    public function approved( $show = '' ) {
        if( $show ) {
            $cmd = $this->conn->prepare("SELECT approved FROM users WHERE ID = ?");
            $cmd->execute([ $show ]);
            return $cmd->fetchColumn();
        }
        else {
            $cmd = $this->conn->prepare("SELECT approved FROM users WHERE approved = 0");
            $cmd->execute();
            return $cmd->rowCount();
        }
    }

    public function approvedupdated(): void {
        INPUT::method_request();
        if( ! INPUT::is('action', 'approve') ) {
            return;
        }

        $cmd = $this->conn->prepare("UPDATE users SET approved = ? WHERE ID = ?");
        $cmd->execute([ 1, INPUT::int('user_id') ]);
        if( (int) $cmd->fetchColumn() > 0 ) {
            alert_time('success', 'Usuário aprovado', 4500, 1000);
            alert_redirect('success dnone', '', URL::current());
        } 
        else {
            alert_time('warning', 'Nenhuma linha afetada');
        }
    }

    public function column( $column, $id = 0 ): string|int|null {
        $cmd = $this->conn->prepare("SELECT $column FROM users WHERE ID = ?");
        $id = ( $id != 0 ) ? $id : URL::int('id');
        $cmd->execute([ $id ]);

        return $cmd->fetchColumn();
    }

    public function username( $id = 0 ) {
        return $this->column('username', $id);
    }

    public function URL( $id = 0 ) {
        return site_url( user_base() .'/'. $this->column('username', $id) );
    }

}