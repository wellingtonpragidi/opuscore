<?php
declare( strict_types = 1 );
/**
 * Verifica e acessa o estado atual do administrador logado
 * 
 * Atua com base na sessao ativa `$_SESSION` e autenticacao direta com banco de dados
 * 
 * Nao altera dados no banco, apenas verifica status e retorna informacoes
 *   com base no ID e token salvos na sessao
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package System\Admin
 */

class Auth {

    protected PDO $conn;

    # dados de sessao 
    private ?int $session_id = null;
    private ?string $session_token = null;

    private ?string $session_redirect = null;

    # estado de autenticacao
    private ?int $logged_id = null;
    private ?string $logged_token = null;

    private bool $is_valid = false;

    # cache de estado do objeto
    private ?array $data = null;


    public function __construct( PDO $conn ) {
        $this->conn = $conn;
        $this->sessions();
        $this->validate();
    }

    public function is_logged(): bool {
        return $this->is_valid;
    }


    public function id(): ?int {
        return $this->logged_id;
    }

    public function token(): ?string {
        return $this->logged_token;
    }


    # ----------------------------------------
    # Acessor para campos do administrador logado
    #
    public function logged( string $column ): string|int|null {
        $columns = Admin::ALLOWED_COLUMNS;

        if( $column !== 'url' && ! in_array($column, $columns, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'logged', 'Auth') 
            );
        }

        if( ! $this->is_valid ) {
            return null;
        }

        if( $this->data === null ) {
            $columns = implode( ', ', Admin::ALLOWED_COLUMNS );

            $cmd = $this->conn->prepare("
                SELECT $columns FROM admins WHERE ID = ? AND token = ? LIMIT 1
            ");

            $cmd->execute([ $this->logged_id, $this->logged_token ]);

            $this->data = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        # Retorno do campo virtual 'url'
        if( $column === 'url' ) {
            return URL::root("dashboard/admins/?id={$this->data['ID']}");
        }

        if( in_array($column, ['ID', 'role', 'status'], true) ) {
            return (int) $this->data[$column] ?? 0;
        }

        return (string) $this->data[$column] ?? '';
    }


    # Verifica se o admin logado possui a funcao 'superior (1)'
    public function is_master(): bool {
        return $this->is_valid && $this->logged('role') === 1;
    }

    # Verifica se o admin logado eh um admin 'master' e esta em sua propria view
    public function is_self_master(): bool {
        return $this->is_master() && $this->logged_id === URL::int('id');
    }

    /**
     * Verifica se o documento/rota eh de um admin que possui a funcao master
     * ***
     * 
     * Esse metodo nao verifica se o admin logado esta em sua propria view
     * Verifica se a view eh de um master, 
     *     independente de qual admin esteja no caminho (inclusive ele mesmo)
     */
    public function is_from_master(): bool {
        if( ! $this->is_valid ) {
            return false;
        }

        $cmd = $this->conn->prepare("
            SELECT 1 FROM admins WHERE role = ? AND ID = ? LIMIT 1
        ");

        $cmd->execute([ 1, URL::int('id') ]);

        return (bool) $cmd->fetchColumn();
    }


    # Verifica se o admin logado possui a funcao 'Master (1) ou Gerenciador (2)'
    public function is_any_manager(): bool {
        $role = $this->logged('role');

        return $this->is_valid && ($role === 1 || $role === 2);
    }


    # Verifica se o admin logado possui a funcao de 'Gerenciador (2)'
    public function is_manager(): bool {
        return $this->is_valid && $this->logged('role') === 2;
    }
    public function is_from_manager(): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM admins WHERE role = ? AND ID = ? LIMIT 1");
        $cmd->execute([ 2, URL::int('id') ]);

        return $this->is_valid && (bool) $cmd->fetchColumn();
    }


    public function is_staff(): bool {
        return $this->is_valid && $this->logged('role') >= 3;
    }


    # Verifica se o admin logado eh um is_manager() ou se eh o dono da conta
    public function is_authorized(): bool {
        $is_self = $this->logged_id === URL::int('id');

        return $this->is_valid && ($this->is_any_manager() || $is_self);
    }


    # Verifica se o admin logado esta em sua propria rota/documento`
    public function is_self(): bool {
        return $this->is_valid && $this->logged_id === URL::int('id');
    }




    # retorna a URL de redirecionamento caso tenha sido armazenada na sessao
    public function session_redirect(): ?string {
        return $this->session_redirect;
    }

    public function set_session_redirect( ?string $url ): void {
        $url = $this->validate_redirect_url($url);

        if( $url === null ) {
            unset( $_SESSION['admin_redirect'] );
            return;
        }

        $_SESSION['admin_redirect'] = $url;
        $this->session_redirect = $url;
    }


    public function session_destroy( string $key ): void {
        unset( $_SESSION[$key] );
    }

    public function session_append( string $key, string|int $data ): void {
        $_SESSION[$key] = $data;
    }


    private function sessions(): void {
        if( isset($_SESSION['admin_id']) ) { 
            $this->session_id = Ensure::int($_SESSION['admin_id']); 
        }

        if( isset($_SESSION['admin_token']) ) { 
            $this->session_token = Ensure::str($_SESSION['admin_token']); 
        }

        if( isset($_SESSION['admin_redirect']) ) {
            $this->session_redirect = $this->validate_redirect_url(
                $_SESSION['admin_redirect']
            );
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
            SELECT ID, token, status FROM admins WHERE ID = ? AND token = ? LIMIT 1
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

    /**
     * Garante que a string de redirecionamento seja uma URL valida 
     * e que esteja dentro do dominio com diretorio do painel administrativo
     * 
     * Se esse metodo retornar null, ao fazer login o admin vai para a pagina inicial do painel
     */
    private function validate_redirect_url( ?string $url ): ?string {
        if( empty($url) ) {
            return null;
        }

        # chegou ate aqui, a url de entrada nao esta vazia, entao higieniza
        $redirect_url = Ensure::URL( $url ); 

        if( Ensure::sameHost($redirect_url) === false ) {
            return null;
        }

        if( Ensure::https($redirect_url) === false ) {
            return null;
        }

        $dashboard_url = URL::root('dashboard');

        # Garante que a URL aponta para dentro do diretorio do painel
        if( ! str_starts_with($redirect_url, $dashboard_url) ) {
            return null;
        }

        # Se URL for qualquer rota de access: nao precisa da URL para redirecionamento
        $pathname = parse_url($redirect_url, PHP_URL_PATH) ?? '';
        if( str_contains($pathname, 'dashboard/access') ) {
            return null;
        }

        # Se a URL for exatamente a home do painel: nao precisa da URL para redirecionamento
        if( $redirect_url === $dashboard_url ) {
            return null;
        }


        return $redirect_url;
    }

}
