<?php
declare( strict_types = 1 );
/**
 * Verifica e acessa o estado atual do usuario logado
 * 
 * Atua com base na sessao ativa `$_SESSION`
 *  e autenticacao direta com banco de dados
 * 
 * Nao altera dados no banco, apenas verifica status e retorna informacoes
 *   com base no ID e token salvos na sessao.
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\User
 * @subpackage 1 \Admin
 * @subpackage 2 \Model
 */

class Auth {

    private PDO $conn;

    /**
     * dados para usuarios:
     */
    # sessoes:
    private ?int    $session_id = null;
    private ?string $session_token = null;

    private ?string $session_redirect = null;

    # estado de autenticacao:
    private ?int    $logged_id = null;
    private ?string $logged_token = null;
    # validacao da autenticacao
    private bool $is_valid = false;


    # cache de estado do objeto - para consultas do usuario logado
    private ?array $logged = null;



    /**
     * dados para admins:
     */
    # sessoes do admin
    private ?int    $admin_session_id = null;
    private ?string $admin_session_token = null;

    # estado de autenticacao do admin
    private ?int    $admin_logged_id = null;
    private ?string $admin_logged_token = null;

    private bool $is_admin_valid = false;


    public function __construct( PDO $conn ) {
        $this->conn = $conn;

        $this->sessions();
        $this->validate();
        
        # admin valida direto por is_admin_valid()
    }

    public function is_logged(): bool {
        return $this->is_valid;
    }

    public function id(): ?int {
        return $this->logged_id;
    }


    /**
     * Acessor para campos do usuario logado
     * 
     * Esse metodo retorna stdClass em vez de Assign porque:
     * Assign exige que sempre seja criada uma nova instancia (estado isolado) 
     * E esse metodo tambem eh chamado em loops como listagem de comentarios
     * O que aumentaria o custo desnecessariamente
     */
    public function logged(): stdClass {
        if( ! $this->is_valid ) {
            return new stdClass;
        }

        if( $this->logged !== null ) {
            return (object) $this->logged;
        }

        $cmd = $this->conn->prepare("
            SELECT ID, name, username, email, created, updated, content, status, pswd 
            FROM users 
            WHERE ID = ? AND token = ? 
            LIMIT 1
        ");

        $cmd->execute([ $this->logged_id, $this->logged_token ]);

        $this->logged = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];


        return (object) $this->logged;
    }


    public function URL(): string {
        if( $this->logged()->username === null ) {
            return '';
        }

        $pathname = user_base() . '/' . $this->logged()->username;

        return URL::root( $pathname );
    }



    /**
     * Verifica se o documento de visualizacao atual pertence a um determinado usuario valido
     * 
     * Util para monitoramento de contexto no Output 
     * Ex: (
     *     contar visitantes no perfil, 
     *     disparar notificacoes de quem visualizou o perfil 
     *     ate mesmo validar se o visitante e o dono da conta para liberar edicao de outras maneiras alem de is_self()
     * )
     */
    public function is_from(): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
        $cmd->execute([ URL::param(1) ]);

        return $this->is_valid && (bool) $cmd->fetchColumn();
    }


    # Verifica se o usuario logado esta em sua propria rota / view
    public function is_self(): bool {
        return $this->is_valid && $this->logged_id === User::id();
    }


    private function sessions(): void {
        if( isset($_SESSION['user_id']) ) { 
            $this->session_id = Ensure::int($_SESSION['user_id']); 
        }

        if( isset($_SESSION['user_token']) ) { 
            $this->session_token = Ensure::str($_SESSION['user_token']); 
        }


        if( isset($_SESSION['admin_id']) ) { 
            $this->admin_session_id = Ensure::int($_SESSION['admin_id']); 
        }

        if( isset($_SESSION['admin_token']) ) { 
            $this->admin_session_token = Ensure::str($_SESSION['admin_token']); 
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



    /**
     * Garante que a string de redirecionamento seja uma URL valida 
     * e que esteja dentro do dominio do site
     * 
     * Se esse metodo retornar null, ao fazer login o usuario vai para a pagina inicial do site
     */
    public function validate_redirect_url( ?string $url ): ?string {
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

        $base_url = URL::root();

        $login_url = $base_url . 'access?action=login';


        $redir = parse_url( $redirect_url, PHP_URL_PATH );
        $login = parse_url( $login_url, PHP_URL_PATH );

        # Se URL for uma rota de access nao precisa de redirecionamento
        if( $redir === $login ) {
            return null;
        }

        # Se a URL for a mesma que home nao precisa da URL para redirecionamento
        if( $redirect_url === $base_url ) {
            return null;
        }

        return $redirect_url;
    }



    public function is_admin_valid(): bool {
        if( empty($this->admin_session_id) || empty($this->admin_session_token) ) {
            return false;
        }
        if( ! is_int($this->admin_session_id) || ! is_string($this->admin_session_token) ) {
            return false;
        }

        $cmd = $this->conn->prepare("
            SELECT ID, token, status FROM admins WHERE ID = ? AND token = ? LIMIT 1
        ");
        $cmd->execute([ $this->admin_session_id, $this->admin_session_token ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC );


        # ID e token ja foram validados pelo WHERE da consulta
        if( $row !== false && (int) $row['status'] === 1 ) {
            $this->admin_logged_id    = $this->admin_session_id;
            $this->admin_logged_token = $this->admin_session_token;

            return true;
        } 

        $this->admin_logged_id    = null;
        $this->admin_logged_token = null;

        return false;
    }

}