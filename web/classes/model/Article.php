<?php 
declare( strict_types = 1 );
/**
 * ...
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Model
 */

class Article extends Model {

    private ?Assign $target = null;

    /**
     * selecao com opcoes para tipo de pagina, limit de articles e ordem de exibicao
     * (esse loop nao inclui paginacao)
     * @param $args [ orderby, order e limit ]
     */
    public function select( $args = [] ): array {
        $ORDER_BY_LIMIT = $this->ORDER_BY_LIMIT( $args );
        $list = [];
        $cmd = $this->conn->prepare("
            SELECT p.ID, p.title, p.author, p.content, p.summary, p.slug, m.attachment 
            FROM articles AS p LEFT JOIN medias AS m 
            ON m.related_id = p.ID AND m.related_type = 'article' 
            WHERE p.status = 1 $ORDER_BY_LIMIT
        ");

        $cmd->execute();

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            $bind->ID         = $row["ID"];
            $bind->title      = $row["title"];
            $bind->author     = $row["author"];
            $bind->content    = $row["content"];
            $bind->summary    = $row["summary"];
            $bind->slug       = $row["slug"];
            $bind->attachment = json_decode( $row["attachment"] ?? '' );
            $bind->URL        = site_url( $row["slug"] );
            $list[] = $bind;
        }
        return $list;
    }

    private function ORDER_BY_LIMIT( $args = [] ): string {
        $COLUMN = '';
        if( isset($args['order']) ) {
            switch( $args['order'] ) {
                case 'ID':
                    $COLUMN = 'p.ID';
                break;
                case 'title':
                    $COLUMN = 'p.title';
                break;
                case 'date':
                    $COLUMN = 'p.created';
                break;
                case 'update':
                    $COLUMN = 'p.updated';
                break;
                default:
                    $COLUMN = 'p.ID';
                break;
            }
        }
       
        $SORT  = isset( $args['sort'] ) ? $args['sort'] : 'DESC';
        $LIMIT = isset( $args['limit'] ) ? $args['limit'] : articles_per_page();

        return "ORDER BY $COLUMN $SORT LIMIT $LIMIT";
    }


    # usado para exibir articles recentes em sidebars */
    public function recents( int $limit ): array {
        $cmd = $this->conn->prepare("
            SELECT 
                p.title, p.segment, p.created, m.attachment 
            FROM articles p 

            LEFT JOIN medias m 
                ON m.related_id = p.ID AND m.related_type = ? 

            WHERE p.segment != ? AND p.status = ? 

            ORDER BY p.ID DESC 
            LIMIT $limit
        ");
        $cmd->execute([ URL::pathname(), 1, 'article' ]);
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            $bind->title      = $row['title'];
            $bind->created    = $row['created'];
            $bind->URL        = URL::root( $row['segment'] );
            $bind->attachment = json_decode( $row['attachment'] ?? '' );
            $list[] = $bind;
        }

        return $list;
    }

    /**
     * Retorna a quantidade de articles por contexto: todos, categoria e pesquisa
     * E tambem adicionado texto junto ao total no singular plural e nenhum resultado
     */
    public function show_record( array $args = [] ): string {
        $singular   = $args['singular'] ?? 'publicação';
        $plural     = $args['plural'] ?? 'publicações';
        $no_results = $args['no_results'] ?? 'Nenhuma publicação';

        $count = 0;
        if( is_query() ) {
            $cmd = $this->conn->prepare("
                SELECT COUNT(*) FROM articles WHERE (title LIKE ? OR summary LIKE ?) AND status = 1
            ");
            $q = '%' . URL::GET('q') . '%';
            $cmd->execute([ $q, $q ]);

            $count += (int) $cmd->fetchColumn();
            if( $count === 1 ) {
                $result = $count . ' resultado encontrado';
            }
            elseif( $count > 1 ) {
                $result = $count . ' resultados encontrados';
            }
            else {
                $result = 'Nenhum resultado encontrado';
            }

            return $result;
        }
        else {

            if( is_category() ) {
                $cmd = $this->conn->prepare("
                    SELECT COUNT(*) FROM articles AS p  
                    JOIN relations AS r ON p.ID = r.type_id 
                    JOIN categories AS c ON c.ID = r.category_id 
                    WHERE c.segment = ? AND r.type = 'article' AND c.type = 'article' AND p.status = 1 
                ");
                $cmd->execute([ URL::segment() ]);
            }
            else {
                $cmd = $this->conn->prepare("SELECT COUNT(*) FROM articles WHERE status = 1");
                $cmd->execute();
            }

            $count += (int) $cmd->fetchColumn();
            if( $count === 1 ) {
                $result = "$count $singular";
            }
            elseif( $count > 1 ) {
                $result = "$count $plural";
            }
            else {
                $result = $no_results;
            }

            return $result;
        }
    }

    /**
     * Retorna articles relacionados respeitando hierarquia de categorias
     *
     * Fluxo do metodo:
     *
     * 0. Obtem ID do article atual a partir do slug para ser usado nas consultas seguintes
     *
     * 1. Descobre a categoria's ligada ao article
     *
     * 2. Monta cadeia de categorias, percorre a hierarquia subindo do filho ate a raiz
     *    Exemplo: [21, 10, 5]
     *
     * 3. Consulta articles relacionados, buscando articles nas categorias da cadeia e exclui o article atual
     *    Usa ORDER BY FIELD() para priorizar categorias mais proximas
     * -  FIELD() define prioridade manual de ordenacao
     *    Primeiro mesma categoria, depois categorias pai, depois categorias mais acima e sucessivamente
     *
     * @see https://opuscore.dev/function-articles_relateds
     */
    public function relateds( int $limit = 4 ): array {

        # 0. consulta simples para evitar abstracoes extras
        $cmd = $this->conn->prepare("SELECT ID FROM articles WHERE segment = ? LIMIT 1");
        $cmd->execute([ URL::pathname() ]);
        $article_id = (int) ($cmd->fetchColumn() ?: 0);

        # 1.
        $cmd = $this->conn->prepare("
            SELECT c.ID, c.parent
            FROM categories c
            JOIN relations r ON r.category_id = c.ID
            WHERE r.type_id = ? AND r.type = ? 
            LIMIT 1
        ");
        $cmd->execute([ $article_id, 'article' ]);
        $cat = $cmd->fetch( PDO::FETCH_ASSOC );

        if( ! $cat ) {
            return [];
        }

        # 2. montar cadeia de categoria, percorre filho -> pai -> raiz
        $chain = [];
        $current = $cat['ID'];

        while( $current ) {
            # adiciona categoria atual na cadeia
            $chain[] = $current;

            # consulta o pai da categoria
            $cmd = $this->conn->prepare("
                SELECT parent
                FROM categories
                WHERE ID = ?
            ");
            $cmd->execute([ $current ]);

            $parent = (int) ($cmd->fetchColumn() ?: 0);

            # se nao houver pai chegamos na raiz
            if( ! $parent ) {
                break;
            }

            $current = $parent; # continua subindo na hierarquia
            # exemplo de cadeia final: [21, 10, 5]
        }

        $IDs = implode( ',', $chain );

        # 3. consulta principal de relacionados
        $cmd = $this->conn->prepare("
            SELECT DISTINCT p.ID, p.title, p.segment, m.attachment
            FROM articles p

            JOIN relations r ON r.type_id = p.ID

            JOIN categories c ON c.ID = r.category_id

            LEFT JOIN medias m ON m.related_id = p.ID AND m.related_type = 'article'

            WHERE c.ID IN ($IDs)
            AND p.ID != ? AND p.status = 1

            ORDER BY FIELD(c.ID, $IDs), p.ID DESC
            LIMIT $limit
        ");

        $cmd->execute([ $article_id ]);

        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {

            $bind = new Assign;

            $bind->title      = $row['title'];
            $bind->URL        = URL::root($row['segment'] ?? '');
            $bind->attachment = Ensure::object($row['attachment']);

            $list[] = $bind;
        }

        return $list;
    }


    /**
     * metodo usado na classe / Pagination 
     * */
    public function total_records(): int {
        $base = URL::param(0);

        if( $base === articles_base() || $base === '' ) {
            if( URL::has('q') ) {
                $cmd = $this->conn->prepare("
                    SELECT COUNT(*) FROM articles 
                    WHERE (title LIKE ? OR summary LIKE ?) 
                    AND status = ?
                ");
                $q = '%' . URL::GET('q') . '%';
                $cmd->execute([ $q, $q, 1 ]);
            }
            else {
                $cmd = $this->conn->prepare("
                    SELECT COUNT(*) FROM articles WHERE status = ?
                ");
                $cmd->execute([ 1 ]);
            }
        }
        else if( $base === category_base() ) {
            $cmd = $this->conn->prepare("
                SELECT COUNT(*) FROM articles AS p 
                JOIN relations AS r ON p.ID = r.type_id 
                JOIN categories AS c ON c.ID = r.category_id 
                WHERE c.segment = ?  AND c.type = ? AND r.type = ? 
            ");
            $cmd->execute([ URL::segment(), 'article', 'article' ]);
        }

        return isset($cmd) ? (int) $cmd->fetchColumn() : 0;
    }



    public function target(): Assign {
        if( $this->target !== null ) {
            return $this->target;
        }

        $segment = URL::pathname();

        $cmd = $this->conn->prepare("
            SELECT * FROM articles WHERE segment = ? AND status = ? LIMIT 1
        ");

        $cmd->execute([ $segment, 1 ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC ) ?: [];


        return $this->target = new Assign($row);
    }


    public function meta_description(): string {
        $target = $this->target();

        $summary = $target->summary;

        if( ! $summary ) {

            $content = $target->content;

            if( $content === null ) {
                return '';
            }

            return text_summary_attr($content);
        }

        return Ensure::attr( $summary );
    }

    /**
     * consulta o ID do article para uso em controladores assincrono
     * utiliza o valor da query string segment utilizada na URL em `xhr.open()` 
     */
    public function async_id(): ?int {

        $segment = URL::GET('segment');

        if( empty($segment) ) {

            return null;
        }

        $cmd = $this->conn->prepare("
            SELECT ID FROM articles WHERE segment = ? AND status = ? LIMIT 1
        ");

        $cmd->execute([ $segment, 1 ]);

        $id = $cmd->fetchColumn();

        
        return ($id === false) ? null : (int) $id;
    }

}