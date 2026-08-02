<?php
/**
 * Gerencia a logica de categorias, incluindo listagem hierarquica,
 * associacao com articles e recuperacao de informacoes de categorias.
 *
 * @package Output\Model
 * @subpackage \Hierarchy
 */
class Category extends Model {

    /**
     * Parametros correspondentes aos placeholders (?) na clausula WHERE.
     * Inicialmente com 'article', mas pode ser extendido.
     */
    private array|string $params = ['article'];



    public function select( array $except = [] ): array {

        $where  = [ 'c.type = ?' ];
        //$except = $args['except'] ?? null;

        # excluir slugs se houver
        if( isset($except) && is_array($except) && count($except) > 0 ) {

            $placeholders = rtrim( str_repeat('?,', count($except)), ',' );
            $where[] = 'c.slug NOT IN (' . $placeholders . ')';
            $this->params  = array_merge( $this->params, $except );
        }

        $WHERE = count($where) > 0
            ? 'WHERE ' . implode( ' AND ', $where )
            : '';

        $cmd = $this->conn->prepare("
            SELECT c.ID, c.name, c.parent, c.segment, c.content, m.attachment 
            FROM categories c 
            LEFT JOIN medias m 
            ON m.related_id = c.ID AND m.related_type = 'category-article' 
            {$WHERE}
            ORDER BY c.parent DESC, c.name DESC
        ");

        $cmd->execute([ ...$this->params ]);
        $categories = $cmd->fetchAll( PDO::FETCH_ASSOC );

        $hierarchy = [];
        foreach( $categories as $cat ) {
            $hierarchy[ (int) $cat['parent'] ][] = $cat;
        }

        $stack = array_map(
            function( array $cat ) {
                return [ 'cat' => $cat, 'level' => 0 ];
            },
            $hierarchy[0] ?? []
        );

        $list = [];
        while( ! empty($stack) ) {

            $current = array_pop( $stack );
            $row     = $current['cat'];
            $level   = $current['level'];

            $bind = new Assign;

            $bind->ID         = $row['ID'];
            $bind->name       = $row['name'];
            $bind->parent     = $row['parent'];
            $bind->content    = $row['content'];
            $bind->segment    = $row['segment'];
            $bind->attachment = json_decode( $row['attachment'] ?? '' );

            # true se categoria tiver filhos
            if( ! empty($hierarchy[$bind->ID]) ) {

                foreach( $hierarchy[$bind->ID] as $child ) {
                    array_push( $stack, [ 'cat' => $child, 'level' => $level + 1 ] );
                }

                # (tem filhos) — nao tem pai
                $bind->html->class = 'category-parent has-child';

                # (tem filhos) — e tmb eh filho (tem pai)
                if( $bind->parent !== 0 ) {
                    $bind->html->class = 'category-child has-child child-' . $level;
                }
            }
            # aqui categorias nao tem filhos
            else {
                # (nao tem filhos) — e tmb nao tem pai
                if( $bind->parent === 0 ) {
                    $bind->html->class = 'category-parent';
                }
                # por fim: (nao tem filhos) — eh filho (tmb eh neto) - tem pai e avo
                else {
                    $bind->html->class = 'category-child child-' . $level;
                }
            }

            $list[] = $bind;
        }

        return $list;
    }



    public function list( array $args = [] ): string {
        # reset condicoes locais
        $where  = [ 'c.type = ?' ];

        # excluir slugs se houver
        if( isset($args['except']) && is_array($args['except']) && count($args['except']) > 0 ) {

            $placeholders = rtrim( str_repeat('?,', count($args['except'])), ',' );
            $where[] = 'c.slug NOT IN (' . $placeholders . ')';
            $this->params  = array_merge( $this->params, $args['except'] );
        }

        $WHERE = count($where) > 0
            ? 'WHERE ' . implode( ' AND ', $where )
            : '';

        $thumbnail = $args['thumbnail'] ?? false;

        if( $thumbnail === true ) {

            $cmd = $this->conn->prepare("
                SELECT c.ID, c.name, c.parent, c.slug, c.segment, m.attachment
                FROM categories AS c
                LEFT JOIN medias AS m
                ON m.related_id = c.ID AND m.related_type = 'category-article'
                {$WHERE}
                ORDER BY c.parent DESC, c.name DESC
            ");
        }
        else {

            $cmd = $this->conn->prepare("
                SELECT c.ID, c.name, c.parent, c.slug, c.segment
                FROM categories AS c
                {$WHERE}
                ORDER BY c.parent DESC, c.name DESC
            ");
        }

        $cmd->execute([ ...$this->params ]);
        $categories = $cmd->fetchAll( PDO::FETCH_ASSOC );

        $hierarchy = [];
        foreach( $categories as $cat ) {
            $hierarchy[ (int) $cat['parent'] ][] = $cat;
        }


        $htmlattrs = $args['attrs'] ?? [];

        # Garante que a classe 'listcats' SEMPRE exista
        $class = 'listcats';

        if( isset($htmlattrs['class']) ) {
            # Se veio classe nos args, concatena com listcats
            $htmlattrs['class'] = $class . ' ' . $htmlattrs['class'];
        } 
        else {
            # Se nao veio, adiciona listcats
            $htmlattrs['class'] = $class;
        }

        $attrs = '';
        foreach( $htmlattrs as $key => $value ) {
            $attrs .= " {$key}=\"{$value}\"";
        }

        $html = "<ul{$attrs}>";


        $stack = array_map(
            function( array $cat ) {
                return [ 'cat' => $cat, 'level' => 0 ];
            },
            $hierarchy[0] ?? []
        );

        $prevlevel = 0;
        $compare   = [];
        static $is_article;

        if( $is_article === null ) {
            $is_article = is_article();
        }


        $cmd = $this->conn->prepare("SELECT segment FROM categories WHERE type = 'article'");
        $cmd->execute();
        $cats_segment = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $cats_segment[] = $row['segment'];
        }

        $article_cats_segment = [];
        foreach( $this->article_cats() as $row ) {
            $article_cats_segment[] = $row['segment'];
        }

        if( $is_article ) {
            $compare = array_diff( $cats_segment, $article_cats_segment );
        }

        while( ! empty($stack) ) {

            $current = array_pop( $stack );
            $cat     = $current['cat'];
            $level   = $current['level'];

            # class='last-item' 
            # isso nao depende da "profundidade da arvore", mas da ordem de desempilhamento
            # o ultimo na ordem sempre eh $stack vazio (acabou iteracao)
            $is_last = empty( $stack );

            if( $level < $prevlevel ) {
                $html .= str_repeat( '</ul></li>', $prevlevel - $level );
            }

            $active = '';
            if( $is_article ) {
                if( ! in_array( $cat['segment'], $compare ) ) {
                    $active = ' class="active"';
                }
            }

            // if( ! empty($hierarchy[$cat['ID']]) )
            // $class[] = 'hasub';

            $class = [];
            if( $active !== '' ) {
                $class[] = 'active';
            }
            if( $is_last ) {
                $class[] = 'last-item';
            }
            $classes = count($class) > 0 ? ' class="' . implode(' ', $class) . '"' : '';

            $html .= '<li' . $classes . '>';
            $html .= '<a href="' . URL::root(category_base() . '/' . $cat['segment']) . '">';

            if( $thumbnail && $thumbnail === 'display' || $thumbnail === true ) {

                $attachment = json_decode( $cat['attachment'] ?? '' );

                if( $attachment && isset($attachment->thumb->path) ) {
                    $attr_name = Ensure::attr($cat['name']);
                    $alt = 'alt="' . $attr_name . '"';
                    $dimensions = Image::dimension_attrs( $attachment->thumb ?? null );
                    $attrs =  $alt . ' ' . $dimensions;

                    $source = upload_url( $attachment->thumb->path );

                    $html .= "<span class=\"thumbnail\">
                        <img src=\"{$source}\" {$attrs} />
                    </span>";
                }

                $html .= "<span class=\"catname\">{$cat['name']}</span>";
            }
            else {
                $html .= $cat['name'];
            }

            $html .= '</a>';

            # true se categoria tiver filhos
            if( ! empty($hierarchy[$cat['ID']]) ) { 

                $html .= '<ul>';

                foreach( $hierarchy[$cat['ID']] as $child ) {
                    array_push( $stack, [ 'cat' => $child, 'level' => $level + 1 ] );
                }
            }
            else {
                $html .= '</li>';
            }

            $prevlevel = $level;
        }

        $html .= str_repeat( '</ul></li>', $prevlevel );
        $html .= '</ul>';

        return $html;
    }


    # Retorna um array com ID, nome e segmento de categorias relacionadas (marcadas) por um article 
    public function article_cats(): array {
        $segment = URL::pathname();

        $cmd = $this->conn->prepare("
            SELECT c.ID, c.name, c.segment
            FROM categories c
            JOIN relations r ON c.ID = r.category_id
            JOIN articles p ON p.ID = r.type_id
            WHERE p.segment = ? AND c.type = ? AND r.type = ?
            ORDER BY c.parent ASC, c.name ASC
        ");

        $cmd->execute([ $segment, 'article', 'article' ]);

        return $cmd->fetchAll( PDO::FETCH_ASSOC );
    }
    

    public static function count(): int {
        self::init();
        $cmd = self::$db->prepare("SELECT COUNT(*) FROM categories");
        $cmd->execute();

        return (int) $cmd->fetchColumn();
    }


    /**
     * Recupera uma coluna especifica de uma categoria com base no segmento presente na URL
     *
     * @param $column A Nome do campo a ser recuperado
     */
    private function field( string $column ): int|string|null {
        $cmd = $this->conn->prepare("SELECT $column FROM categories WHERE segment = ? LIMIT 1");
        
        $cmd->execute([ URL::segment() ]); 

        return parent::fetchColumn($cmd);
    }

    # Retorna o ID da categoria com base no segmento da URL
    public function id(): int { 
        return (int) ($this->field('ID') ?? 0); 
    }

    # Retorna o nome da categoria com base no segmento da URL
    public function title(): string { 
        return $this->field('name'); 
    }
    public function name(): string { 
        return $this->field('name') ?? ''; 
    }

    # Imprime o conteudo da categoria com base no segmento da URL
    public function content(): string { 
        return $this->field('content') ?? ''; 
    }

    # Retorna o slug do ultimo parametro da categoria com base no segmento da URL
    public function slug(): string { 
        return $this->field('slug') ?? ''; 
    }

    # Retorna o proprio segmento hierarquico da URL de categoria apos `category_base()/`
    # obtem o segmento da URL que eh de ate 3 niveis armazendo no DB (pai/filho/neto)
    public function segment(): string { 
        return $this->field('segment') ?? ''; 
    }

    public function pathname(): string { 
        return category_base() . '/' . $this->segment();
    }

    public function URL(): string { 
        return URL::root( $this->pathname() );
    }

    # Imprime o conteudo da categoria com base no segmento da URL
    public function date(): string { 
        return $this->field('created'); 
    }

    /**
     * Retorna um resumo do conteudo da categoria para meta-descricao.
     * Utiliza a funcao `summary_attr` para limitar o tamanho e sanitizar.
     */
    public function meta_description(): string {
        return text_summary_attr( $this->field('content') );
    }

}