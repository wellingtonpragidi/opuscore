<?php
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\User
 */

class Access extends Model {

    private array $cache = [];



    # -- INSERT --------------------------------------------------------------- C

    /**
     * registra usuario depois de verificar se nao existe registro com o mesmo e-mail
     * apos registro precisa ativar a conta e definir senha
     */
    public function register( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO users (name, username, email, created, token, nonce) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $cmd->execute([
            $bind->name,
            $bind->username,
            $bind->email,
            $bind->created,
            $bind->token,
            $bind->nonce
        ]);

        $bind->LastID = (int) $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }



    # -- SELECT --------------------------------------------------------------- R

    /**
     * verifica existencia de email que:
     * - antecede o registro
     * - para fazer login 
     * - recuperar de senha
     */
    public function verify_email( string $email ): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
        $cmd->execute([ $email ]);

        return (bool) $cmd->fetchColumn();
    }


    # Consulta campo da coluna $column com base no email
    public function field_email( string $email ): ?Assign {

        $cmd = $this->conn->prepare("
            SELECT ID, email, name, pswd, token, status FROM users WHERE email = ? LIMIT 1
        ");

        $cmd->execute([ $email ]);

        $row = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];

        return new Assign( $row );
    }


    # Consulta campo da coluna $column com base em token e ID
    public function field_activate( string $token, int $id ): ?Assign {
        $cmd = $this->conn->prepare("
            SELECT ID, name, token, status FROM users WHERE token = ? AND ID = ? LIMIT 1
        ");

        $cmd->execute([ $token, $id ]);

        $row = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];

        return new Assign( $row );
    }



    # -- UPDATE --------------------------------------------------------------- U

    /** 
     * insere username apos registro 
     * o username padrao eh o "nome" totalmente higienizado + o ID do registro
     * isso garante que nao haja usernames repetidos
     * username eh inserido apos registro pois so assim e possivel obter LastID
     */
    public function update_username( Assign $bind ): bool {
        $cmd = $this->conn->prepare("SELECT username FROM users WHERE ID = ? LIMIT 1");
        $cmd->execute([ $bind->ID ]);
        $current = $cmd->fetch( PDO::FETCH_ASSOC );
        if( ! $current ) {
            return false;
        }
        
        $hasChange = $current['username'] !== $bind->username;

        $cmd = $this->conn->prepare("UPDATE users SET username = ? WHERE ID = ?");
        $cmd->execute([ $bind->username, $bind->ID ]);

        return $hasChange ? true : false;
    }


    # atualiza dados na ativacao de conta 
    public function update_activate( Assign $bind ) {
        $columns = ['pswd', 'token', 'status'];

        return parent::updater( 'users', $columns, $bind );
    }


    # atualiza dados no reset de senha 
    public function update_reset( Assign $bind ): bool {
        $columns = ['pswd', 'token', 'updated', 'status'];

        return parent::updater( 'users', $columns, $bind );
    }

    public function update_token( Assign $bind ): bool {
        return parent::updater( 'users', ['token'], $bind );
    }
    public function update_nonce( Assign $bind ): bool {
        return parent::updater( 'users', ['nonce'], $bind );
    }


}