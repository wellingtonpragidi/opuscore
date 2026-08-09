<?php 
declare( strict_types = 1 );
/**
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Model
 */

class Page extends Model {

    private const PAGE_COLUMNS = [
        'ID', 'title', 'content', 'summary', 'parent', 'lastmod', 'slug', 'segment', 'template', 'status'
    ];

    public function id(): int {
        return (int) ($this->field('ID') ?? 0);
    }

    public function parent(): int {
        return (int) ($this->field('parent') ?? 0);
    }

    public function title(): string {
        return $this->field('title');
    }

    public function title_attr(): string {
        return Ensure::attr( $this->title() );
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

    public function template(): string {
        return $this->field('template') ?? 'page.php';
    }

    public function URL(): string {
        return URL::root( $this->segment() );
    }

    public function status(): int {
        return (int) ($this->field('status') ?? 0);
    }

    public function lastmod(): string {
        return $this->field('lastmod') ?? '';
    }

    public function meta_description(): string {
        $summary = $this->field('summary');

        if( ! $summary ) {
            $content = $this->field('content');

            if( ! $content ) {
                return '';
            }

            return text_summary_attr($content);
        }

        return Ensure::attr( $summary );
    }


    public function field( string $column ): string|int|null {
        if( ! in_array($column, self::PAGE_COLUMNS) ) {
            return null;
        }

        $cmd = $this->conn->prepare("
            SELECT $column FROM pages WHERE segment = ? AND status = 1 LIMIT 1
        ");

        $cmd->execute([ URL::pathname() ]); 

        $value = $cmd->fetchColumn();
        return ($value === false) ? null : $value;
    }


    /**
     * @todo quebra nome do metodo find() que antes era get_pages()
     * 
     * Exibir conteudo de paginas especificas passadas por slug
     * 
     * @example : 
     * $pages = pages_find( 'home-page, about', null ); 
     * OU
     * $pages = pages_find( ['home-page, about'], null ); 
     * foreach( $pages as $page )
     */
    public function find( string|array $slugs ): array {
        $list = [];

        if( empty($slugs) ) {
            return $list;
        }
        
        $slugs = (array) $slugs;

        $placeholders = implode( ', ', array_fill(0, count($slugs), '?') );

        $cmd = $this->conn->prepare("
            SELECT p.ID, p.title, p.content, p.segment, p.slug, m.attachment 
            FROM pages p 

            LEFT JOIN medias m 
                ON m.related_id = p.ID AND m.related_type = ?

            WHERE p.status = 1 AND p.slug IN ($placeholders)
        ");

        $cmd->execute([ 'page', ...$slugs ]);

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            
            $bind->ID      = $row['ID'];
            $bind->title   = $row['title'];
            $bind->content = $row['content'];
            $bind->URL     = URL::root($row['segment'] ?? '');

            $bind->attachment = Ensure::object($row['attachment']);

            $list[] = $bind;
        }

        return $list;
    }

}
