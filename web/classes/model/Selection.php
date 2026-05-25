<?php
declare( strict_types = 1 );
/**
 * Responsavel por realizar consultas ao banco de dados para listagem de posts, 
 * busca, categorias e exibicao de paginas unicas, com suporte a paginacao 
 * tradicional e paginacao assincrona (load more).
 * 
 * A classe tambem permite a ordenacao via filtros e adaptacao conforme o 
 * contexto da URL (base, categoria, busca ou pagina unica).
 * 
 * Propriedades:
 * - $conn: instancia de conexao PDO com o banco de dados.
 * - $pagination: controle de paginacao tradicional.
 * - $page: instancia de Page para manipulacao de tipos de paginas.
 * - $limit: limite de registros retornados (usado na paginacao assincrona).
 * - $offset: deslocamento de registros (usado na paginacao assincrona).
 * 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Output\Seek
 * @subpackage \Model
 */

class Selection extends Model {

    private Router $router;
    private Post $post;
    private Category $category;


    # limite de registros para feed_async ou paginacao, dependendo do estado de is_feed_async
    # deslocamento para feed_async
    private int $limit, $offset;


    public function __construct( 
        Router $router, Pagination $pagination, Post $post, Category $category ) {
        
        $this->router   = $router;
        $this->post     = $post;
        $this->category = $category;

        # para paginacao assincrona:
        if( is_feed_async() ) {
            $this->offset = INPUT::has('offset') ? INPUT::int('offset') : 0;
        }
        else {
            $this->offset = $pagination->post_paginate();
        }

        $this->limit  = INPUT::has('limit') ? INPUT::int('limit') : posts_per_page();
    }


    /**
     * Define qual metodo sera chamado de acordo com o contexto
     * 
     * Verifica se ha contexto na query string (?context=...) ou detecta 
     * automaticamente pelo path da URL. 
     * 
     * Retorna resultado da consulta: posts, categorias, busca ou pagina unica.
     * 
     * @return mixed ??
     */
    public function resolve(): ?SeekPreparer {
        switch( URL::GET('context') ) {
            case posts_base():
                return $this->posts();
            break;
            case category_base():
                return $this->categories();
            break;
            case 'search':
                return $this->query();
            break;
        }

        # is_home_or_query() ???
        if( is_query() ) {
            return $this->query();
        }
        else if( is_home() || is_posts() ) {
            return $this->posts();
        }
        else if( is_category() ) {
            return $this->categories();
        }
        else if( is_post() ) {
            return $this->single();
        }

        return null;
    }


    private const string SELECT = "
        p.ID, 
        p.title, 
        p.author, 
        p.content, 
        p.summary, 
        p.created, 
        p.updated, 
        p.slug, 
        p.segment, 
        m.attachment
    "; 


    /**
     * Consulta e retorna lista de posts
     * Aplica ordenacao e paginacao (tradicional ou feed_async).
     */
    private function posts(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();

        $cmd = $this->conn->prepare("
            SELECT " . self::SELECT . "
            FROM posts AS p LEFT JOIN medias AS m 
            ON m.related_type = 'post' AND m.related_id = p.ID 
            WHERE p.status = 1 
            $ORDER_BY LIMIT ?, ?
        ");

        if( is_feed_async() ) {
            $cmd->execute([ $this->offset, $this->limit ]);
        } 
        else {
            $cmd->execute([ $this->offset, posts_per_page() ]);
        }

        $rows = $cmd->fetchAll();
        return ! empty($rows)
            ? new SeekPreparer($rows)
            : null;
    }
    

    /**
     * Consulta e retorna resultados de busca
     * 
     * Realiza busca no titulo e conteudo, com ordenacao e paginacao.
     * 
     * @return ?SeekPreparer
     */
    private function query(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();
        $cmd = $this->conn->prepare("
            SELECT " . self::SELECT . "
            FROM posts p LEFT JOIN medias m 
            ON m.related_type = 'post' AND m.related_id = p.ID 
            WHERE (p.title LIKE ? OR p.summary LIKE ?) 
            AND p.status = 1 
            $ORDER_BY LIMIT ?, ?
        ");

        $q = URL::GET('q');

        if( strlen($q) < 3 ) {
            return null;
        }

        $q = '%' . $q . '%';

        if( is_feed_async() ) {
            $cmd->execute([ $q, $q, $this->offset, $this->limit ]);
        } 
        else {
            $cmd->execute([ $q, $q, $this->offset, posts_per_page() ]);
        }

        $rows = $cmd->fetchAll();
        return empty($rows)
            ? null 
            : new SeekPreparer($rows);
    }

    /**
     * Consulta e retorna lista de posts por categoria
     * 
     * Utiliza slug da categoria para realizar filtro, com ordenacao e paginacao.
     * 
     * @return ?SeekPreparer
     */
    private function categories(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();

        $cmd = $this->conn->prepare("
            SELECT " . self::SELECT . " 
            FROM posts AS p LEFT JOIN medias AS m 
            ON m.related_type = 'post' AND m.related_id = p.ID 
            JOIN relations AS r ON p.ID = r.type_id 
            JOIN categories AS c ON c.ID = r.category_id 
            WHERE c.segment = ? AND c.type = 'post' AND r.type = 'post' AND p.status = 1 
            $ORDER_BY LIMIT ?, ?
        ");

        if( is_feed_async() ) {
            $cmd->execute([ 
                URL::GET('segment'),
                $this->offset,
                $this->limit,
            ]);
        } 
        else {
            $cmd->execute([ 
                $this->category->segment(),
                $this->offset, 
                posts_per_page() 
            ]);
        }

        $rows = $cmd->fetchAll();
        return ! empty($rows)
            ? new SeekPreparer($rows)
            : null;
    }

    /**
     * Consulta e retorna pagina unica
     * 
     * Baseado no tipo e slug presente na URL.
     * 
     * @return ?SeekPreparer
     */
    private function single(): ?SeekPreparer {
        $cmd = $this->conn->prepare("
            SELECT " . self::SELECT . " 
            FROM posts AS p LEFT JOIN medias AS m 
            ON m.related_type = 'post' AND m.related_id = p.ID 
            WHERE p.segment = ? AND p.status = ?
        ");

        $cmd->execute([ URL::pathname(), 1 ]);

        $rows = $cmd->fetchAll();

        return ! empty($rows)
            ? new SeekPreparer($rows)
            : null;
    }

    /**
     * Define clausula ORDER BY ...
     * Opcao de gancho tipo filtro registrado no Hook 'orderby'
     * 
     * @return string | padrao = ORDER BY p.created DESC
     */
    private function ORDER_BY(): string {
        # padrao 
        $orderby = "ORDER BY p.created DESC";

        $order = [
            'ID DESC'      => 'p.ID DESC',
            'ID ASC'       => 'p.ID ASC',
            'updated DESC' => 'p.updated DESC',
            'updated ASC'  => 'p.updated ASC',
            'title DESC'   => 'p.title DESC',
            'title ASC'    => 'p.title ASC',
            'created ASC'  => 'p.created ASC',
            'random'       => 'RAND()'
        ];

        # inicia possivel valor externo para orderby, vindo por GET ou gancho 
        $value = '';
        # async → direto da request
        if( is_feed_async() ) {
            $value = URL::GET('orderby');
        }
        # sync → via hook
        else if( Hook::has_filter('orderby') ) {
            $value = Hook::call_filter('orderby', null);
        }

        if( ! empty($value) ) {
            if( ! isset($order[$value]) ) {
                throw new OpusException(
                    "O valor: <code>{$order[$value]}</code> do filtro <code>'orderby'</code> é inválido", "error"
                );
            }
            
            $orderby = "ORDER BY {$order[$value]}";
        }

        return $orderby;
    }
    /**
     * Exibe lista de posts, sem contexto, apenas por tipo
     * 
     * Metodo utilitario para exibir lista de posts de um determinado tipo.
     * 
     * @param array $args parametros opcionais
     * @return array
     */
    public function display( $args = [] ): array {
        $TYPE  = isset( $args['type'] ) ? $args['type'] : 'post';
        $cmd = $this->conn->prepare("
            SELECT " . self::SELECT . " 
            FROM posts AS p LEFT JOIN medias AS m 
            ON m.related_type = 'post' AND m.related_id = p.ID 
            WHERE p.status = 1 
            ORDER BY p.ID DESC LIMIT ?, ?
        ");
        $cmd->execute([ $TYPE, $this->pagination->post_paginate(), posts_per_page() ]);
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID         = $row["ID"];
            $bind->title      = $row["title"];
            $bind->author     = $row["author"];
            $bind->content    = $row["content"];
            $bind->summary    = $row["summary"];
            $bind->date       = $row["created"];
            $bind->update     = $row["updated"];
            $bind->slug       = $row["slug"];
            $bind->segment    = $row["segment"];
            $bind->attachment = json_decode($row["attachment"] ?? '');
            $bind->URL        = URL::root($bind->segment ?? '');

            $list[] = $bind;
        }

        return $list;
    }
}
