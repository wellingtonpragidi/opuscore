<?php
declare( strict_types = 1 );
/**
 *  Controlar fluxo de autenticacao do usuario:
 * • registro
 * • login
 * • verificacao de credenciais (ativacao)
 * • redefinicao de senha
 * 
 * @link https://opuscore.dev/classe-userauth
 *
 *  funcionalidades:
 * Registro
 * - register()          → Insere novo usuário com nome e e-mail
 *
 * Verificação de login
 * - verify_email()      → Checa se e-mail ja existe
 *
 * Atualizacoes
 * - update_username()   → Adiciona username após criação
 * - activation_update() → Ativa conta e define senha
 * - reset_update()      → Atualiza senha via recuperação
 * - token_update()      → Atualiza token do usuário
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\User\Auth
 */

class UserAuth extends Model {

    private array $cache = [];

    /**
     * registra usuario depois de verificar se nao existe registro com o mesmo e-mail
     * apos registro precisa ativar a conta e definir senha
     */
	public function register( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO 
            users (name, username, email, created, token, nonce) 
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
        $bind->LastID = $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    /** 
     * insere username apos registro 
     * o username padrao eh o "nome" totalmente higienizado + o ID do registro
     * isso garante que nao haja usernames repetidos
     * username eh inserido apos registro pois so assim e possivel obter LastID
     */
    public function update_username( string $username, int $id ): bool {
        $cmd = $this->conn->prepare("SELECT username FROM users WHERE ID = ?");
        $cmd->execute([ $id ]);
        $current = $cmd->fetch( PDO::FETCH_ASSOC );
        if( ! $current ) {
            return false;
        }
        $noChange = $current['username'] == $username;

        $cmd = $this->conn->prepare("UPDATE users SET username = ? WHERE ID = ?");
        $cmd->execute([ $username, $id ]);

        if( $noChange ) { 
            return false; 
        }

        return true;
    }


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


    # atualiza dados na ativacao de conta 
    public function activation_update( Assign $bind ) {
        $columns = ['pswd', 'token', 'status'];

        return parent::updater( 'users', $columns, $bind );
    }


    # atualiza dados no reset de senha 
    public function reset_update( Assign $bind ): bool {
        $columns = ['pswd', 'token', 'updated', 'status'];

        return parent::updater( 'users', $columns, $bind );
    }


    # atualiza token ao fazer logout 
    public function token_update( Assign $bind ): bool {

        return parent::updater( 'users', ['token'], $bind );
    }


    # Consulta campo da coluna $column com base no email
    public function fields_by_email( string $email ): ?Assign {

        $cmd = $this->conn->prepare("
            SELECT ID, email, name, pswd, token, status FROM users WHERE email = ? LIMIT 1
        ");

        $cmd->execute([ $email ]);

        $row = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];

        return new Assign( $row );
    }


    # Consulta campo da coluna $column com base em token e ID
    public function fields_by_activation( string $token, int $id ): ?Assign {
        $cmd = $this->conn->prepare("
            SELECT ID, name, token, status FROM users WHERE token = ? AND ID = ? LIMIT 1
        ");

        $cmd->execute([ $token, $id ]);

        $row = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];

        return new Assign( $row );
    }

}