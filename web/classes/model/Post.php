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

class Post extends Model {


    private array $cache = [];

    /**
     * selecao com opcoes para tipo de pagina, limit de posts e ordem de exibicao
     * (esse loop nao inclui paginacao)
     * @param $args [ orderby, order e limit ]
     */
    public function select( $args = [] ): array {
        $ORDER_BY_LIMIT = $this->ORDER_BY_LIMIT( $args );
        $list = [];
        $cmd = $this->conn->prepare("
            SELECT p.ID, p.title, p.author, p.content, p.summary, p.slug, m.attachment 
            FROM posts AS p LEFT JOIN medias AS m 
            ON m.related_id = p.ID AND m.related_type = 'post' 
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
        $LIMIT = isset( $args['limit'] ) ? $args['limit'] : posts_per_page();

        return "ORDER BY $COLUMN $SORT LIMIT $LIMIT";
    }


    # usado para exibir posts recentes em sidebars */
    public function recents( int $limit ): array {
        $cmd = $this->conn->prepare("
            SELECT p.title, p.segment, p.created, m.attachment FROM posts p 
            LEFT JOIN medias m ON m.related_id = p.ID AND m.related_type = 'post' 
            WHERE p.segment != ? AND p.status = ? 
            ORDER BY p.ID DESC LIMIT $limit
        ");
        $cmd->execute([ URL::pathname(), 1 ]);
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            $bind->title      = $row['title'];
            $bind->created    = $row['created'];
            $bind->URL        = site_url( $row['segment'] );
            $bind->attachment = json_decode( $row['attachment'] ?? '' );
            $list[] = $bind;
        }

        return $list;
    }

    /**
     * Retorna a quantidade de posts por contexto: todos, categoria e pesquisa
     * E tambem adicionado texto junto ao total no singular plural e nenhum resultado
     */
    public function show_record( array $args = [] ): string {
        $singular   = $args['singular'] ?? 'publicação';
        $plural     = $args['plural'] ?? 'publicações';
        $no_results = $args['no_results'] ?? 'Nenhuma publicação';

        $count = 0;
        if( is_query() ) {
            $cmd = $this->conn->prepare("
                SELECT COUNT(*) FROM posts WHERE (title LIKE ? OR content LIKE ?) AND status = 1
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
                    SELECT COUNT(*) FROM posts AS p  
                    JOIN relations AS r ON p.ID = r.type_id 
                    JOIN categories AS c ON c.ID = r.category_id 
                    WHERE c.segment = ? AND r.type = 'post' AND c.type = 'post' AND p.status = 1 
                ");
                $cmd->execute([ URL::segment() ]);
            }
            else {
                $cmd = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE status = 1");
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
     * Retorna posts relacionados respeitando hierarquia de categorias
     *
     * Fluxo do metodo:
     *
     * 0. Obtem ID do post atual a partir do slug para ser usado nas consultas seguintes
     *
     * 1. Descobre a categoria's ligada ao post
     *
     * 2. Monta cadeia de categorias, percorre a hierarquia subindo do filho ate a raiz
     *    Exemplo: [21, 10, 5]
     *
     * 3. Consulta posts relacionados, buscando posts nas categorias da cadeia e exclui o post atual
     *    Usa ORDER BY FIELD() para priorizar categorias mais proximas
     * -  FIELD() define prioridade manual de ordenacao
     *    Primeiro mesma categoria, depois categorias pai, depois categorias mais acima e sucessivamente
     *
     * @see https://opuscore.dev/function-posts_relateds
     */
    public function relateds( int $limit = 4 ): array {

        # 0. consulta simples para evitar abstracoes extras
        $cmd = $this->conn->prepare("SELECT ID FROM posts WHERE segment = ? LIMIT 1");
        $cmd->execute([ URL::pathname() ]);
        $post_id = (int) ($cmd->fetchColumn() ?: 0);

        # 1.
        $cmd = $this->conn->prepare("
            SELECT c.ID, c.parent
            FROM categories c
            JOIN relations r ON r.category_id = c.ID
            WHERE r.type_id = ? AND r.type = ? 
            LIMIT 1
        ");
        $cmd->execute([ $post_id, 'post' ]);
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
            FROM posts p

            JOIN relations r ON r.type_id = p.ID

            JOIN categories c ON c.ID = r.category_id

            LEFT JOIN medias m ON m.related_id = p.ID AND m.related_type = 'post'

            WHERE c.ID IN ($IDs)
            AND p.ID != ? AND p.status = 1

            ORDER BY FIELD(c.ID, $IDs), p.ID DESC
            LIMIT $limit
        ");

        $cmd->execute([ $post_id ]);

        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {

            $bind = new Assign;

            $bind->title      = $row['title'];
            $bind->URL        = URL::root( $row['segment'] ?? '' );
            $bind->attachment = json_decode($row['attachment'] ?? '');

            $list[] = $bind;
        }

        return $list;
    }


    /**
     * metodo usado na classe / Pagination 
     * */
    public function total_records(): int {
        switch( URL::param(0) ) :
            case '': case posts_base():
                if( isset($_GET['q']) ) {
                    $cmd = $this->conn->prepare("
                        SELECT COUNT(*) FROM posts 
                        WHERE status = 1 AND (title LIKE ? OR summary LIKE ?)
                    ");
                    $q = '%' . URL::GET('q') . '%';
                    $cmd->execute([ $q, $q ]);
                }
                else {
                    $cmd = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE status = 1");
                    $cmd->execute();
                }
            break;
            case category_base():
                $cmd = $this->conn->prepare("
                    SELECT COUNT(*) FROM posts AS p 
                    JOIN relations AS r ON p.ID = r.type_id 
                    JOIN categories AS c ON c.ID = r.category_id 
                    WHERE c.segment = ?  AND c.type = 'post' AND r.type = 'post' 
                ");
                $cmd->execute([ URL::segment() ]);
            break;
        endswitch;

        return (int) $cmd->fetchColumn();
    }


  
    private function field( string $column ): string|int|null {
        $segment = URL::pathname();

        $key = 'post:' . $segment;

        if( ! array_key_exists($key, $this->cache) ) {
            $cmd = $this->conn->prepare("
                SELECT * FROM posts WHERE segment = ? AND status = ? LIMIT 1
            ");

            $cmd->execute([ $segment, 1 ]);

            $this->cache[$key] = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        return $this->cache[$key][$column] ?? null;
    }

    /**
     * Ao usar os metodos abaixo sempre de preferencia para o conjunto de funcoes e metodos Seek
     * 
     */
    public function id(): int {
        return (int) ($this->field('ID') ?? 0);
    }

    public function title(): string {
        return $this->field('title');
    }

    public function title_attr(): string {
        $title = $this->field('title') ?? '';
        return Ensure::attr($title);
    }

    public function content(): string {
        return $this->field('content') ?? '';
    }

    public function slug(): string {
        return $this->field('slug') ?? '';
    }

    public function segment(): string {
        return $this->field('segment') ?? '';
    }

    public function author(): string {
        return $this->field('author') ?? '';
    }

    public function status(): int {
        return (int) ($this->field('status') ?? 0);
    }

    public function created(): string {
        return $this->field('created') ?? '';
    }
    public function updated(): string {
        return $this->field('updated') ?? '';
    }

    public function meta_description(): string {
        $summary = $this->field('summary');

        if( ! $summary ) {
            $content = $this->field('content');

            if( $content === null ) {
                return '';
            }

            return text_summary_attr($content);
            return text_summary_attr($content);
        }

        return Ensure::attr( $summary );
    }

}