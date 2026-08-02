<?php
declare( strict_types = 1 );
/**
 * Responsavel por realizar consultas ao banco de dados para listagem de articles, 
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
    private Article $article;
    private Category $category;


    # limite de registros para feed_async ou paginacao, dependendo do estado de is_feed_async
    # deslocamento para feed_async
    private int $limit, $offset;


    private string $SELECT = "
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



    public function __construct( 
        Router $router, Pagination $pagination, Article $article, Category $category ) {
        
        $this->router   = $router;
        $this->article  = $article;
        $this->category = $category;

        # para paginacao assincrona:
        if( is_feed_async() ) {
            $this->offset = INPUT::has('offset') ? INPUT::int('offset') : 0;
        }
        else {
            $this->offset = $pagination->article_paginate();
        }

        $this->limit  = INPUT::has('limit') ? INPUT::int('limit') : articles_per_page();
    }


    # Define qual metodo de consulta sera chamado de acordo com o contexto
    public function resolve(): ?SeekPreparer {

        $src = URL::GET('src');

        return match( true ) {

            $src === articles_base() => $this->articles(),

            $src === category_base() => $this->categories(),

            $src === 'search' => $this->query(),

            is_query() => $this->query(),

            is_home() || is_articles() => $this->articles(),

            is_category() => $this->categories(),

            is_article() => $this->single(),

            default => null
        };
    }


    /**
     * Consulta e retorna lista de articles
     * Aplica ordenacao e paginacao (tradicional ou feed_async).
     */
    private function articles(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();

        $cmd = $this->conn->prepare("
            SELECT $this->SELECT
            FROM articles p 

            LEFT JOIN medias m 
                ON m.related_type = ? AND m.related_id = p.ID 

            WHERE p.status = ? 
            
            $ORDER_BY LIMIT ?, ?
        ");

        if( is_feed_async() ) {
            $cmd->execute([ 'article', 1, $this->offset, $this->limit ]);
        } 
        else {
            $cmd->execute([ 'article', 1, $this->offset, articles_per_page() ]);
        }

        $rows = $cmd->fetchAll();

        return empty($rows) ? null : new SeekPreparer($rows);
    }
    

    private function query(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();
        $cmd = $this->conn->prepare("
            SELECT $this->SELECT
            FROM articles p 

            LEFT JOIN medias m 
                ON m.related_type = ? AND m.related_id = p.ID 

            WHERE ( p.title LIKE ? OR p.summary LIKE ? ) 
                AND p.status = ? 

            $ORDER_BY LIMIT ?, ?
        ");

        $qv = URL::GET('q');

        if( strlen($qv) < 3 ) {
            return null;
        }

        $q = '%' . $qv . '%';

        if( is_feed_async() ) {
            $cmd->execute([ 'article', $q, $q, 1, $this->offset, $this->limit ]);
        } 
        else {
            $cmd->execute([ 'article', $q, $q, 1, $this->offset, articles_per_page() ]);
        }

        $rows = $cmd->fetchAll();

        return empty($rows) ? null : new SeekPreparer($rows);
    }


    private function categories(): ?SeekPreparer {
        $ORDER_BY = $this->ORDER_BY();

        $cmd = $this->conn->prepare("
            SELECT $this->SELECT 
            FROM articles p 

            LEFT JOIN medias m 
                ON m.related_type = 'article' AND m.related_id = p.ID 

            JOIN relations r 
                ON p.ID = r.type_id 
            
            JOIN categories c 
                ON c.ID = r.category_id 

            WHERE c.segment = ? AND c.type = ? 
                AND r.type   = ? 
                AND p.status = ? 

            $ORDER_BY LIMIT ?, ?
        ");

        if( is_feed_async() ) {
            $cmd->execute([ 
                URL::GET('cat'), # segment da URL
                'article', 
                'article', 
                1, 
                $this->offset,
                $this->limit,
            ]);
        } 
        else {
            $cmd->execute([ 
                $this->category->segment(),
                'article', 
                'article', 
                1, 
                $this->offset, 
                articles_per_page() 
            ]);
        }

        $rows = $cmd->fetchAll();

        return empty($rows) ? null : new SeekPreparer($rows);
    }


    private function single(): ?SeekPreparer {
        $cmd = $this->conn->prepare("
            SELECT $this->SELECT 
            FROM articles p 

            LEFT JOIN medias m 
                ON m.related_type = ? AND m.related_id = p.ID 

            WHERE p.segment = ? AND p.status = ?
        ");

        $cmd->execute([ 'article', URL::pathname(), 1 ]);

        $rows = $cmd->fetchAll();

        return empty($rows) ? null : new SeekPreparer($rows);
    }


    /**
     * Define clausula ORDER BY ...
     * Opcao de gancho tipo filtro registrado no Hook 'orderby'
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
     * Exibe lista de articles, sem contexto, apenas por tipo
     * 
     * Metodo utilitario para exibir lista de articles de um determinado tipo.
     * 
     * @param array $args parametros opcionais
     * @return array
     */
    public function display( $args = [] ): array {
        $TYPE  = isset( $args['type'] ) ? $args['type'] : 'article';
        $cmd = $this->conn->prepare("
            SELECT $this->SELECT 
            FROM articles AS p LEFT JOIN medias AS m 
            ON m.related_type = 'article' AND m.related_id = p.ID 
            WHERE p.status = 1 
            ORDER BY p.ID DESC LIMIT ?, ?
        ");
        $cmd->execute([ $TYPE, $this->pagination->article_paginate(), articles_per_page() ]);
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID         = $row["ID"];
            $bind->title      = $row["title"];
            $bind->author     = $row["author"];
            $bind->content    = $row["content"];
            $bind->summary    = $row["summary"];
            $bind->created    = $row["created"];
            $bind->updated    = $row["updated"];
            $bind->slug       = $row["slug"];
            $bind->segment    = $row["segment"];
            $bind->attachment = json_decode($row["attachment"] ?? '');
            $bind->URL        = URL::root($bind->segment ?? '');

            $list[] = $bind;
        }

        return $list;
    }
}
