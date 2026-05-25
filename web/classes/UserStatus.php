<?php
declare( strict_types = 1 );
/**
 * Verifica e acessa o estado atual do usuario logado
 * 
 * Atua com base na sessao ativa `$_SESSION`
 *   sem lidar com autenticacao direta ou banco alem de leitura.
 * 
 * Nao altera dados no banco, apenas verifica status e retorna informacoes
 *   com base no ID e token salvos na sessao.
 *
 * @link https://opuscore.dev/classe-userstatus
 *
 * funcionalidades:
 * ───────────────────────────────────────────────────────
 * Sessao
 * - session_id()      → Retorna o ID atual salvo na sessao
 * - session_token()   → Retorna o token atual salvo na sessao
 * 
 * Verificação
 * - is_logged()       → Verifica se o usuario está autenticado com base em ID, token e status
 * 
 * Dados do usuario autenticado
 * - logged_id()         → ID validado no banco
 * - logged_token()      → Token validado
 * - logged_status()     → Status (1 = ativo)
 * - logged_name()       → Nome do usuário
 * - logged_username()   → Nome de usuário
 * - logged_email()      → E-mail
 * - logged_registered() → Data de registro
 * - logged_updated()    → Última atualização
 * 
 * Interno
 * - logged($column)     → Consulta um campo específico direto no banco com base na sessão
 * ───────────────────────────────────────────────────────
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\User\Status
 */
class UserStatus {

    private PDO $conn;

    # dados de sessao 
    private ?int $session_id = null;
    private ?string $session_token = null;

    # estado de autenticacao
    private ?int $logged_id = null;
    private ?string $logged_token = null;

    private bool $is_valid = false;

    # cache de estado do objeto
    private ?array $data = null;

    # manter 'url' sempre como ultimo item do array
    private const array LOGGED_COLUMNS = [
        'ID', 'name', 'username', 'email', 'created', 'updated', 'content', 'url'
    ]; 

    public function __construct( PDO $conn ) {
        $this->conn = $conn;

        $this->sessions();
        $this->validate();
    }

    public function is_logged(): bool {
        return $this->is_valid;
    }

    # retorna o ID da session somente se is_valid for verdadeiro 
    public function logged_id(): ?int {
        return $this->logged_id;
    }


    # ----------------------------------------
    # Acessores para campos do usuario logado
    # 

    public function logged( string $column ): string|int|null {
        $columns = self::LOGGED_COLUMNS;

        if( ! in_array($column, $columns, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'logged', 'UserStatus') 
            );
        }

        if( ! $this->is_valid ) {
            return null;
        }

        if( $this->data === null ) {
            array_pop( $columns ); # remove 'url' do array LOGGED_COLUMNS

            $columns = implode( ', ', $columns );

            $cmd = $this->conn->prepare("
                SELECT $columns FROM users WHERE ID = ? AND token = ? LIMIT 1
            ");

            $cmd->execute([ $this->logged_id, $this->logged_token ]);

            $this->data = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        if( $column === 'url' ) {
            $segment = user_base() . '/' . $this->data['username'];
            return URL::root( $segment );
        }

        return $this->data[$column] ?? null;
    }


    private function sessions(): void {
        if( isset($_SESSION['user_id']) ) { 
            $this->session_id = Ensure::int($_SESSION['user_id']); 
        }
        if( isset($_SESSION['user_token']) ) { 
            $this->session_token = Ensure::abstr($_SESSION['user_token']); 
        }
    }

    private function validate(): void {
        if( empty($this->session_id) || empty($this->session_token) ) {
            return;
        }
        if( ! is_int($this->session_id) || ! is_string($this->session_token) ) {
            return;
        }

        $cmd = $this->conn->prepare("
            SELECT ID, token, status FROM users WHERE ID = ? AND token = ? LIMIT 1
        ");
        $cmd->execute([ $this->session_id, $this->session_token ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row === false ) {
            return;
        }

        $has_fields = isset( $row['ID'], $row['token'], $row['status'] );

        $this->is_valid = $has_fields && (int) $row['status'] === 1;

        if( $this->is_valid === true ) {
            $this->logged_id    = $this->session_id;
            $this->logged_token = $this->session_token;
        } 
        else {
            $this->logged_id    = null;
            $this->logged_token = null;
        }
    }

}