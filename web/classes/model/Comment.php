<?php



/**
 * 
 * 
 * @todo ESTRUTURA DO BANCO DE DADOS TODA ALTERADA
 * ESSA CLASSE REQUER ATUALIZAÇÃO COMPLETA
 * 
 * - ESTANCIAR CLASSE POST PASSADO POR ARGUMENTO NO CONTROLADOR IRA AJUDAR...
 * 
 * 
 * 
 */














declare( strict_types = 1 );
/**
 * 
 * Gerencia as operacoes relacionadas a comentarios no sistema,
 * incluindo insercao, selecao, contagem e gerenciamento de respostas.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Model
 */

class Comment {

    /**
     * @var PDO $conn Instancia da conexao com o banco de dados.
     */
    private PDO $conn;

    /**
     * Construtor da classe Comment.
     *
     * @param PDO $conn Instancia da conexao PDO.
     */
    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }

    /**
     * Insere um novo comentario no banco de dados.
     *
     * @param Assign $bind Objeto Assign contendo os dados do comentario:
     * - type (string): Tipo do comentario (ex: 'post').
     * - slug (string): Slug do item relacionado (post, pagina, etc.).
     * - email (string): Email do autor do comentario.
     * - parent (int): ID do comentario pai (0 para comentarios de nivel superior).
     * - content (string): Conteudo do comentario.
     * - date (string): Data e hora do comentario.
     * @return int O numero de linhas afetadas pela insercao.
     */
    public function insert( Assign $bind ): int {
        $sql = $this->conn->prepare("
            INSERT INTO comments(type, related, email, parent, content, created)
            VALUES(?, ?, ?, ?, ?, ?)
        ");
        $sql->execute([
            $bind->type,
            $bind->slug,
            $bind->email,
            $bind->parent,
            $bind->content,
            $bind->date
        ]);
        $bind->LastID = $this->conn->lastInsertId();
        return $sql->rowCount();
    }

    /**
     * Seleciona comentarios de nivel superior (parent = 0) para um post relacionado.
     * Os comentarios sao ordenados por date em ordem decrescente.
     *
     * @return array<object> Uma lista de objetos `Assign` representando os comentarios.
     */
    public function select(): array {
        $sql = $this->conn->prepare("
            SELECT c.ID, c.parent, c.content, c.created, u.name, u.username, m.attachment
            FROM comments AS c
            JOIN users AS u ON c.email = u.email
            JOIN pages AS p ON c.related = p.slug
            LEFT JOIN medias AS m ON u.name = m.related_title AND m.related_type = ? 
            WHERE c.type = ? AND c.related_id = ? AND c.parent = 0
            ORDER BY c.created DESC
        ");
        $sql->execute([ 'user', 'post', POST_ID ]);
        return $this->read( $sql );
    }

    /**
     * Seleciona respostas (replies) para um comentario especifico.
     *
     * @param int $id O ID do comentario pai.
     * @return array<object> Uma lista de objetos `Assign` representando as respostas.
     */
    public function reply( int $id ): array {
        $sql = $this->conn->prepare("
            SELECT ID, user, parent, content, date, user_picture, user_username
            FROM comments
            JOIN users ON email = user_email
            JOIN posts ON related = post_slug
            WHERE related = ? AND parent = ? ORDER BY ID DESC
        ");
        $sql->execute([ Ensure::string( URL::param(1) ), $id ]);
        return $this->read( $sql );
    }

    /**
     * Processa o resultado de uma consulta PDO e o formata em uma lista de objetos Assign.
     *
     * @param PDOStatement $sql O objeto PDOStatement apos a execucao da consulta.
     * @return array<object> Uma lista de objetos `Assign` com os dados do comentario.
     */
    private function read( PDOStatement $sql ): array {
        $list = [];
        while( $row = $sql->fetch(PDO::FETCH_ASSOC) ) :
            $bind = new Assign();
            $bind->ID         = $row["ID"];
            $bind->parent     = $row["parent"];
            $bind->content    = $row["content"];
            $bind->date       = $row["created"];
            $bind->name       = $row["name"];
            $bind->username   = $row["username"];
            $bind->attachment = json_decode( $row["attachment"] ?? '' );
            $list[] = $bind;
        endwhile;
        return $list;
    }

    /**
     * Conta o numero de comentarios para o item relacionado na URL.
     * Retorna a contagem formatada como string (ex: '1 comentario', '5 comentarios')
     * ou uma string padrao se nao houver comentarios.
     *
     * @param string $no_comments String a ser retornada se nao houver comentarios (padrao: '').
     * @return string O texto formatado com a contagem de comentarios.
     *
     * @todo adicionar mensagem padrao para parametro $no_comments = '' em vez de vazio ?
     */
    public function count( string $no_comments = '' ): string {
        $count = 0;
        $sql = $this->conn->prepare("SELECT ID FROM comments WHERE related = ? ORDER BY ID DESC");
        $sql->execute([ URL::param(0) ]);
        $count += $sql->rowCount();
        if( $count == 1 ) {
            return $count .' comentário';
        }
        elseif( $count > 1 ) {
            return $count .' comentários';
        }
        else {
            return $no_comments;
        }
    }

    /**
     * Conta o numero de comentarios feitos por um usuario especifico.
     * Imprime a contagem formatada como string (ex: '1 comentario', '5 comentarios', 'Nenhum comentario').
     */
    public function user_count(): string {
        $sql = $this->conn->prepare("
            SELECT c.ID, u.name FROM comments AS c
            JOIN users AS u ON c.email = u.email
            WHERE u.username = ? ORDER BY ID DESC
        ");
        $sql->execute([ URL::param(1) ]);
        $count = $sql->rowCount();
        if( $count === 0 ) {
            return 'Nenhum comentário';
        }
        
        $comments = $count === 1 ? ' comentário' : ' comentários';
        return $count . $comments;
    }

    /**
     * Seleciona comentarios feitos por um usuario especifico.
     * Retorna uma lista de objetos Assign contendo detalhes do comentario e do post relacionado.
     *
     * @return array<object> Uma lista de objetos `Assign` representando os comentarios.
     */
    public function where(): array {
        $list = [];
        $sql = $this->conn->prepare("
            SELECT c.ID, c.created, p.title, p.slug FROM comments AS c
            JOIN users AS u ON c.email = u.email
            JOIN pages AS p ON c.related = p.slug
            WHERE u.username = ? ORDER BY c.ID DESC
        ");
        $sql->execute([ URL::param(1) ]);
        while($row = $sql->fetch(PDO::FETCH_ASSOC)) :
            $bind = new Assign();
            $bind->ID    = $row["ID"];
            $bind->date  = $row["created"];
            $bind->title = $row["title"];
            $bind->URL   = site_url( $row["slug"].'/#comment-'.$bind->ID );
            $list[] = $bind;
        endwhile;
        return $list;
    }
}