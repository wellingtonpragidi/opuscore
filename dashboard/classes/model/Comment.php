<?php
/**
 * Gerencia as operacoes de comentarios no sistema,
 * incluindo selecao com filtros e paginacao, atualizacao, exclusao,
 * e controle de status de aprovacao.
 * 
 * @package Dashboard/Model
 */
class Comment {

    private $conn;

    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }

    /**
     * Seleciona comentarios do banco de dados com base em diversos filtros.
     *
     * - Se o parametro 'q' estiver presente na URL, filtra por nome de usuario (LIKE).
     * - Se o parametro 'id' estiver presente, seleciona um comentario especifico pelo ID.
     * - Caso contrario, lista comentarios paginados, ordenados por ID em ordem decrescente.
     *
     * Os resultados incluem dados do comentario, do usuario relacionado e um possivel anexo de midia.
     */
    public function select(): array {
        $pagination = new Pagination( Count::comments(), per_page('comments') );
        $offset = $pagination->offset();

        $list = [];
        # Caso 1: Busca por termo de consulta ('q') no nome do usuario.
        if( URL::has("q") ) {
            # Prepara a consulta SQL com placeholders de interrogacao (?) para os parametros.
            $cmd = $this->conn->prepare("
                SELECT c.ID, c.related, c.type, c.email, c.content, c.created, u.name, u.username, m.attachment
                FROM comments AS c
                JOIN users AS u ON u.email = c.email
                LEFT JOIN medias AS m ON u.name = m.related_title AND m.related_type = 'user'
                WHERE u.name LIKE ? ORDER BY c.ID DESC LIMIT ?, ?
            ");
            $q = '%' . URL::GET('q') . '%';
            $cmd->execute([ $q, $offset, per_page('comments') ]);
        }
        # Caso 2: Busca por ID de comentario especifico.
        elseif( URL::has("id") ) {
            # Prepara a consulta SQL para buscar um comentario por ID.
            $cmd = $this->conn->prepare("
                SELECT c.ID, c.related, c.type, c.email, c.content, c.created, u.name, u.username, m.attachment
                FROM comments AS c
                JOIN users AS u ON u.email = c.email
                LEFT JOIN medias AS m ON u.name = m.related_title AND m.related_type = 'user'
                WHERE c.ID = ?
            ");
            $cmd->execute([ URL::int("id") ]);
        }
        # Caso 3: Listagem paginada de todos os comentarios.
        else {
            $cmd = $this->conn->prepare("
                SELECT c.ID, c.related, c.type, c.email, c.content, c.created, u.name, u.username, m.attachment
                FROM comments AS c
                JOIN users AS u ON u.email = c.email
                LEFT JOIN medias AS m ON u.name = m.related_title AND m.related_type = 'user'
                ORDER BY c.ID DESC LIMIT ?, ?
            ");
            $cmd->execute([ $offset, per_page('comments') ]);
        }
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) :
            $bind = new Assign;
            $bind->ID         = $row["ID"];
            $bind->type       = $row["type"];
            $bind->related    = $row["related"];
            $bind->email      = $row["email"];
            $bind->content    = $row["content"];
            $bind->date       = $row["created"]; # created eh usado como nome de coluna.
            $bind->name       = $row["name"];
            $bind->username   = $row["username"];
            $bind->attachment = json_decode( $row["attachment"] ?? '' );
            $list[] = $bind;
        endwhile;
        return $list;
    }

    /**
     * Atualiza o conteudo de um comentario existente no banco de dados.
     */
    public function update( Assign $bind ): int {
        $cmd = $this->conn->prepare("UPDATE comments SET content = ? WHERE type = ? AND ID = ?");
        $cmd->execute([ $bind->content, $bind->type, $bind->ID ]);
        return $cmd->rowCount();
    }

    /**
     * Exclui um comentario do banco de dados com base no ID fornecido via POST.
     * Se a exclusao for bem-sucedida, exibe um preloader e redireciona com um alerta de sucesso.
     * Caso contrario, exibe um alerta de erro.
     * @return void
     */
    public function delete( int $id ): bool {
        # Sanitiza o ID recebido via POST para garantir a seguranca da consulta.
        $cmd = $this->conn->prepare("DELETE FROM comments WHERE ID = ?");
        $cmd->execute([ $id ]);
        return (int) $cmd->fetchColumn() === 1;
    }

    /**
     * Verifica e retorna o status de aprovacao de um comentario ou a contagem de comentarios pendentes.
     *
     * - Se um `$show` (ID do comentario) for fornecido, retorna o status de aprovacao (1 para aprovado, 0 para pendente)
     * desse comentario especifico.
     * - Se nenhum `$show` for fornecido, retorna a quantidade total de comentarios pendentes (aqueles com 'approved' = 0).
     *
     * @param int|string $show Opcional. O ID do comentario para verificar o status de aprovacao individual.
     * Pode ser int ou string dependendo da origem, sera usado diretamente na query.
     * @return int O status de aprovacao (0 ou 1) do comentario especificado, ou a contagem de comentarios pendentes.
     */
    public function approved( $show = '' ): ?int {
        if( $show ) {
            $cmd = $this->conn->prepare("SELECT approved FROM comments WHERE ID = ?");
            $cmd->execute([ $show ]);
            return $cmd->fetchColumn(); # Retorna o valor da coluna 'approved'
        }
        else {
            # Conta todos os comentarios que estao com status de pendente (approved = 0).
            $cmd = $this->conn->prepare("SELECT approved FROM comments WHERE approved = 0");
            $cmd->execute();
            return $cmd->rowCount();
        }
    }

    /**
     * Atualiza o status de aprovacao de um comentario para "aprovado" (1).
     * Esta operacao eh disparada por uma requisicao POST contendo um valor para 'approve' (o ID do comentario).
     */
    public function approvedupdated( int $id ): bool {
        # Verifica se a requisicao eh do tipo POST e se o parametro 'approve' esta presente.
        $cmd = $this->conn->prepare("UPDATE comments SET approved = ? WHERE ID = ?");
        $cmd->execute([ 1, Ensure::int($id) ]);
        return (int) $cmd->fetchColumn() === 1;
        
    }

}