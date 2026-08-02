<?php
declare( strict_types = 1 );
/**
 * consultas e operacoes CRUD para admins
 * 
 * colunas:
 * 'ID', 'name', 'email', 'pswd' 'created', 'role', 'token', 'nonce', 'status'
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System/Access
 */
class Admin extends Model {

    private static array $cache = [];

    # ---------------------------------
    # permitidas em consultas ( so para validacoes, nao use implode em SELECT com esse )
    public const ALLOWED_COLUMNS = [
        'ID', 'name', 'email', 'created', 'role', 'status', 'pswd' 
    ]; 


    public function __construct() {}


    public function select( ?int $id = null ): array|Assign {
        if( $id /* update */ ) {
            $cmd = $this->conn->prepare("
                SELECT ID, name, email, created, role, status 
                FROM admins WHERE ID = ? LIMIT 1
            ");

            $cmd->execute([ $id ]);
        }
        else {
            $cmd = $this->conn->prepare("
                SELECT ID, name, email, created, role, status 
                FROM admins
            ");

            $cmd->execute();
        }

        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID      = (int) $row['ID'];
            $bind->name    = $row['name'];
            $bind->email   = $row['email'];
            $bind->created = $row['created'];
            $bind->role    = (int) $row['role'];
            $bind->status  = (int) $row['status'];

            $list[] = $bind;
        }

        return $list;
    }


    public function target( string $column ): string|int|null {
        $admin_id = URL::int('id');

        # checa se rota eh update antes, assim evita verificar in_array sem necessidade
        if( $admin_id === null ) {
            return null;
        }

        if( ! in_array($column, self::ALLOWED_COLUMNS, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'target', 'Admin') 
            );
        }
        if( array_key_exists($admin_id, self::$cache) === false ) {

            $cmd = $this->conn->prepare("
                SELECT ID, name, email, created, role, status 
                FROM admins WHERE ID = ? LIMIT 1
            ");

            $cmd->execute([ $admin_id ]);

            self::$cache[$admin_id] = $cmd->fetch( PDO::FETCH_ASSOC ) ?: [];
        }

        # retorna valor campo da coluna ou nulo de uma vez sem frescura de var $row antes
        return self::$cache[$admin_id][$column] ?? null;
    }



    public function register( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO admins " . parent::sql_into_values( 
                'name, email, pswd, created, role, token, nonce, status', 8 
            )
        );

        $cmd->execute([
            $bind->name,
            $bind->email,
            $bind->pswd,
            $bind->created,
            $bind->role,
            $bind->token,
            $bind->nonce,
            $bind->status
        ]);

        $bind->LastID = (int) $this->conn->lastInsertId();

        return $cmd->rowCount() > 0;
    }


    public function exists( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            SELECT 1 FROM admins 
            WHERE email = ? AND ID != ? LIMIT 1
        ");

        $cmd->execute([ $bind->email, $bind->ID ]);

        return (bool) $cmd->fetchColumn();
    }


    public function update_name( Assign $bind ): bool {
        return parent::updater( 'admins', ['name'], $bind );
    }

    public function update_email( Assign $bind ): bool {
        return parent::updater( 'admins', ['email'], $bind );
    }

    public function update_status( Assign $bind ): bool {
        return parent::updater( 'admins', ['status'], $bind );
    }

    public function update_role( Assign $bind ): bool {
        return parent::updater( 'admins', ['role'], $bind );
    }


    public function update_token( Assign $bind ): bool {
        return parent::updater( 'admins', ['token'], $bind );
    }
    public function update_nonce( Assign $bind ): bool {
        return parent::updater( 'admins', ['nonce'], $bind );
    }
    public function update_tokens( Assign $bind ): bool {
        return parent::updater( 'admins', ['token', 'nonce'], $bind );
    }

    public function update_pswd( Assign $bind ): bool {
        return parent::updater( 'admins', ['pswd'], $bind );
    }


    /**
     * Para excluir administrador na tabela admins do banco de dados
     */
    public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM admins WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);
        
        return $cmd->rowCount() > 0;
    }



    # translate role's
    public function role( Assign|int $role ): string {
        $show = is_int($role) ? $role : $role->role;

        return match($show) {
            1 => 'Master',
            2 => 'Gerenciador',
            3 => 'Produtor',
            4 => 'Articulista',
            5 => 'Moderador',
            default => ''
        };
    }


    # translate status
    public function status( Assign $show ): string {
        return match($show->status) {
            0, false => 'Pendente',
            1, true  => 'Confirmado',
            default  => ''
        };
    }

}