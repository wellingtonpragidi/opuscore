<?php
declare( strict_types = 1 );
/**
 * Classe para gerenciar administradores no sistema.
 * Trata autenticacao de sessao, acesso a dados de administradores (logados ou especificos),
 * e operacoes CRUD basicas para administradores.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System/Access
 */
class Admin {

    private PDO $conn;

    /**
     * Token de sessao do administrador logado.
     */
    private ?string $session_token;

    /**
     * ID do administrador logado na sessao.
     */
    private ?int $session_id;

    /**
     * Armazena a URL de redirecionamento (sanitizada) ou null se invalida.
     * Esta URL eh usada apos o login bem-sucedido.
     */
    private ?string $session_redirect;

    /**
     * ID do administrador validado e logado.
     */
    private ?int $logged_id;

    /**
     * Token do administrador validado e logado.
     */
    private ?string $logged_token;

    /**
     * Armazena o resultado da validacao das credenciais da sessao contra o banco de dados.
     * Retorna `true` se:
     * - O ID e token da sessao existirem no banco;
     * - O status do admin for ativo (1).
     */
    private bool $is_valid = false;

    /**
     * Construtor da classe Admin.
     * Inicializa a conexao com o banco de dados e tenta validar as credenciais da sessao.
     */
    public function __construct( PDO $conn ) {
        $this->conn = $conn;

        # Tenta obter ID, token e URL de redirecionamento da sessao.
        $this->session_id = $_SESSION["admin_id"] ?? null;
        $this->session_token = $_SESSION["admin_token"] ?? null;
        $this->session_redirect = $this->validate_redirect_url($_SESSION["admin_redirect"] ?? null);

        # Se ID e token da sessao existirem, tenta validar as credenciais no banco de dados.
        if( ! empty($this->session_id) && ! empty($this->session_token) ) {
            # Sanitiza os dados da sessao para uso seguro em consultas SQL.
            $this->logged_id    = Ensure::int($this->session_id);
            $this->logged_token = $this->session_token;

            # Prepara e executa a consulta para verificar as credenciais do admin.
            $cmd = $conn->prepare("
                SELECT ID, token, status FROM admins WHERE ID = ? AND token = ?
            ");
            $cmd->execute([$this->logged_id, $this->logged_token]);
            $row = $cmd->fetch( PDO::FETCH_ASSOC );

            # Define o estado de validade da sessao.
            $this->is_valid = (
                $row !== false # Verifica se encontrou alguma linha.
                && (int) $row['ID'] === $this->logged_id # ID deve corresponder.
                && (string) $row['token'] === $this->logged_token # Token deve corresponder.
                && (int) $row['status'] === 1 # Admin deve estar ativo.
            );
        }
    }

    # Verifica se ha um administrador logado e com sessao valida
    public function logged_in(): bool {
        return $this->is_valid;
    }

    # Verifica se existe uma URL de redirecionamento valida para a sessao atual.
    public function has_redirect(): bool {
        return $this->session_redirect !== null;
    }

    # Retorna a URL de redirecionamento sanitizada.
    public function redirect(): ?string {
        return $this->session_redirect;
    }

    /**
     * Valida e higieniza uma URL de redirecionamento.
     * Garante que a URL seja uma string valida e esteja dentro do dominio do painel administrativo.
     */
    private function validate_redirect_url( ?string $url ): ?string {
        $sanitized = Ensure::URL( $url ); 

        # Retorna null se a URL sanitizada estiver vazia OU
        # se a URL nao comecar com a URL do painel (seguranca para evitar redirecionamentos externos)
        if( empty($sanitized) || ! str_starts_with($sanitized, dash_url()) ) {
            return null;
        }

        # Se a URL sanitizada for valida e nao for a URL base do painel (para evitar loop).
        return ( $sanitized !== dash_url() ) ? $sanitized : null;
    }

    /**
     * Seleciona informacoes de administradores do banco de dados.
     * Se um ID for fornecido via parametro GET 'id', retorna apenas esse administrador.
     * Caso contrario, retorna uma lista de todos os administradores.
     */
    public function select(): array {
        $list = [];
        if( URL::param(1) === 'admin' && ! URL::has('id') ) {
            header( 'Location: ' . dash_url('404') );
            exit;
        }
        if( URL::has('id') ) {
            $cmd = $this->conn->prepare("
                SELECT ID, name, email, created, role, status FROM admins WHERE ID = ?
            ");
            $cmd->execute([ URL::int('id') ]);
        }
        else {
            $cmd = $this->conn->prepare("
                SELECT ID, name, email, created, role, status FROM admins
            ");
            $cmd->execute();
        }
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) :
            $bind = new Assign;
            $bind->ID         = $row["ID"];
            $bind->name       = $row["name"];
            $bind->email      = $row["email"];
            $bind->created = $row["created"];
            $bind->role       = $row["role"];
            $bind->status     = $row["status"];
            $list[] = $bind;
        endwhile;

        return $list;
    }

    # Atualiza os dados de um administrador existente no banco de dados.
    public function update( Assign $bind ): int {
        $cmd = $this->conn->prepare("UPDATE admins SET role = ?, name = ? WHERE ID = ?");
        $cmd->execute([
            $bind->role,
            $bind->name,
            $bind->ID
        ]);
        return $cmd->rowCount();
    }

    /**
     * Para excluir administrador na tabela admins do banco de dados
     */
    public function delete( int $id ): bool {
        $cmd = $this->conn->prepare("DELETE FROM admins WHERE ID = ?");
        $cmd->execute([ $id ]);
        
        return $cmd->rowCount() > 0;
    }

    /**
     * Recupera uma informacao especifica do administrador atualmente logado.
     * Este metodo eh privado e serve como base para os metodos publicos `logged_*`
     */
    private function logged( string $column ): mixed {
        # Garante que o ID e token da sessao sejam usados.
        $cmd = $this->conn->prepare("SELECT $column FROM admins WHERE ID = ? AND token = ?");
        $cmd->execute([ $this->session_id, $this->session_token ]);
        return $cmd->fetchColumn();
    }

    # Retorna o ID do administrador logado.
    public function logged_id(): int {
        return Ensure::int( $this->logged('ID') );
    }

    # Retorna o nome do administrador logado.
    public function logged_name(): string {
        return (string) $this->logged('name');
    }

    # Retorna o email do administrador logado.
    public function logged_email(): string {
        return (string) $this->logged('email');
    }

   # Retorna a data de registro do administrador logado.
    public function logged_registered(): string { # Adicionado tipo de retorno string
        return (string) $this->logged('created');
    }

    # Retorna o nivel de acesso (role) do administrador logado.
    public function logged_role(): int {
        return Ensure::int( $this->logged('role') );
    }

    # Retorna o token de sessao do administrador logado.
    public function logged_token(): string {
        return (string) $this->logged('token');
    }

    # Retorna o status (ativo/inativo) do administrador logado.
    public function logged_status(): int {
        return Ensure::int( $this->logged('status') );
    }

    /**
     * Recupera uma informacao especifica de um administrador baseado no ID 
     * fornecido via URL (GET 'id')
     */
    public function current( string $column ): mixed {
        # Usa URL::int para garantir que o ID seja um inteiro e seguro.
        $admin_id = URL::int('id');
        $cmd = $this->conn->prepare("SELECT $column FROM admins WHERE ID = ?");
        $cmd->execute([ $admin_id ]);
        return $cmd->fetchColumn();
    }

    # Retorna o nivel de acesso (role) do administrador cujo ID esta na URL.
    public function role(): int {
        return (int) $this->current('role');
    }

    /**
     * Atualiza o token de sessao de um administrador.
     * Geralmente usado durante o processo de logout ou invalidacao de sessao.
     */
    public function update_token( string $generate_token ): bool {
        # O ID do administrador eh obtido da sessao atual, garantindo que o token seja atualizado
        # para o admin logado.
        $cmd = $this->conn->prepare("UPDATE admins SET token = ? WHERE ID = ?");
        $cmd->execute([ $generate_token, $this->session_id ]);

        return $cmd->rowCount() > 0;
    }

}