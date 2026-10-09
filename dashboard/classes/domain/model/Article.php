<?php
declare( strict_types = 1 );
/**
 * Gerencia as operacoes CRUD e geracao de HTML para exibicao de paginas e artigos 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\Model
 */

class Article extends Model {

    private static array $cache = [];


    /**
     * Seleciona registros da tabela 'articles' com base em diferentes criterios:
     * pesquisa por termo ('q' na URL), selecao por ID ('id' na URL),
     * ou todos os registros com paginacao como padrao.
     */
    public function select(): array {
        $query_id = URL::int('id');

        $count = URL::has('by') 
            ? Count::articles_by_category($query_id) 
            : Count::articles();

        $pagination = new Pagination( $count, per_page('articles') );

        $columns = URL::query(['not' => 'by', 'has' => 'id']) 
            ? 'a.*' 
            : 'a.' . implode( ', a.', [
                'ID', 'title', 'created', 'updated', 'slug', 'segment', 'status'
            ] );

        $placeholder = "
            SELECT {$columns}, m.attachment
            FROM articles a 

            LEFT JOIN medias m 
                ON m.related_id = a.ID 
                AND m.related_type = ?
        ";

        # registro com base no parametro ID da URL
        if( URL::param(1) === 'update' ) {
            $sql = "$placeholder WHERE a.ID = ?";

            $params = [ 'article', $query_id ];
        }

        # registros de pesquisa por titulo e resumo
        else if( URL::has('q') ) {
            $q = '%' . URL::GET('q') . '%';

            $sql = "$placeholder WHERE (a.title LIKE ? OR a.summary LIKE ?) ORDER BY a.ID DESC";

            $params = [ 'article', $q, $q ];
        }

        # listagem de artigos por categoria
        else if( URL::has('by') ) {
            # SELECT a.ID, a.title, a.created, a.updated, a.slug, a.status, m.attachment
            $sql = "
                SELECT {$columns}, m.attachment 
                    FROM articles a 

                LEFT JOIN medias m 
                    ON m.related_type = ? 
                    AND m.related_id = a.ID 

                JOIN relations r 
                    ON r.type_id = a.ID 
                
                JOIN categories c 
                    ON r.category_id = c.ID 

                WHERE c.ID = ? AND r.type = ? 
                ORDER BY a.ID DESC LIMIT ?, ?
            ";

            $params = [
                'article',
                $query_id,
                'article',
                paginator()->offset(),
                per_page('articles')
            ];
        }

        # selecao de todos os registros (padrao) com paginacao
        else {
            $sql = "{$placeholder} ORDER BY a.ID DESC LIMIT ?, ?";

            $params = [
                'article',
                paginator()->offset(),
                per_page('articles')
            ];
        }

        $cmd = $this->conn->prepare( $sql );
        $cmd->execute( $params );

        $list = [];

        $attachment = fn($value) => $value ? Ensure::object($value) : '';

        $mapper = [  
            'ID'         => 0,
            'title'      => '',
            'author'     => '',
            'content'    => '',
            'summary'    => '',
            'created'    => '',
            'updated'    => '',
            'slug'       => '',
            'segment'    => '',
            'status'     => 0,
            'attachment' => $attachment,
        ];

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            foreach( $mapper as $key => $converter ) {
                $exists = array_key_exists( $key, $row );
                $value  = $exists ? $row[$key] : null;

                $bind->$key = is_callable($converter)
                    ? $converter($value)
                    : ( $exists ? $value : $converter );
            }

            $list[] = $bind;
        }

        return $list;
    }


    public function insert( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO articles(title, author, created, slug, status) 
            VALUES(?, ?, ?, ?, ?)
        ");
        $cmd->execute([ 
            $bind->title, 
            $bind->author, 
            $bind->created, 
            $bind->slug, 
            $bind->status 
        ]);
        $bind->LastID = (int) $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    public function update( Assign $bind ): bool {
        $columns = ['title', 'content', 'summary', 'updated', 'slug', 'segment', 'status'];

        return parent::updater( 'articles', $columns, $bind );
    }


    /**
     * Constroi o segmento publico do artigo
     *
     * O segmento segue a estrutura: categoria/slug-do-artigo
     *
     * As categorias vem dos checkboxes selecionados no formulario ($bind->html->checked). 
     * Quando multiplas categorias sao marcadas, 
     *  utiliza a categoria definida pela regra de profundidade da arvore.
     *
     * Usado quando o segmento ainda nao existe ou quando categorias/slug foram alterados
     */
    public function build_segment( Assign $bind ): string {

        # IDs das categorias selecionadas no formulario
        $checked = array_map( 'intval', $bind->html->checked );

        if( empty($checked) ) {
            return $bind->slug;
        }

        $placeholders = implode( ',', array_fill(0, count($checked), '?') );

        # Busca a categoria que representa o nivel mais profundo da hierarquia
        $cmd = $this->conn->prepare("
            SELECT slug FROM categories 
            WHERE ID IN ($placeholders) 
            ORDER BY parent DESC, ID ASC 
            LIMIT 1
        ");

        $cmd->execute($checked);

        $cat = $cmd->fetch( PDO::FETCH_ASSOC );

        return $cat['slug'] . '/' . $bind->slug;
    }


    public function update_category_segments( int $catID ): bool {
        $cmd = $this->conn->prepare("
            SELECT DISTINCT type_id FROM relations 
            
            WHERE type = ? AND category_id = ?
        ");

        $cmd->execute([ 'article', $catID ]);

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {

            $article_id = (int) $row['type_id'];

            # Busca categorias relacionadas ao artigo
            $categories = $this->conn->prepare("
                SELECT category_id
                FROM relations
                WHERE type = ? 
                AND type_id = ?
            ");

            $categories->execute([ 'article', $article_id ]);

            $checked = [];

            while( $category = $categories->fetch(PDO::FETCH_ASSOC) ) {

                $checked[] = (int) $category['category_id'];
            }

            if( empty($checked) ) {
                continue;
            }

            $bind = new Assign;

            $bind->slug = $this->field( 'slug', $article_id );
            $bind->html->checked = $checked;

            $segment = $this->build_segment($bind);

            $update = $this->conn->prepare("
                UPDATE articles
                SET segment = ?
                WHERE ID = ?
            ");

            $update->execute([
                $segment,
                $article_id
            ]);
        }

        return true;
    }


    public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM articles WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    /**
     * Verifica se ja existe outro registro com o mesmo slug.
     * @todo Esse metodo precisa ser unico para Article e Page
     */ 
    public function exists( Assign $bind  ): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM articles WHERE slug = ? AND ID != ? LIMIT 1");
        $cmd->execute([ $bind->slug, $bind->ID ]);

        return (bool) $cmd->fetchColumn();
    }



    /**
     * # Metodo ::list_article_categories(). Gera uma lista HTML hierarquica de categorias.
     * Com input type checkbox para "marcar" adicionar categorias ao tipo de articleagem
     * ( isso insere, atualiza e deleta relacoes na tabela `relations` do banco de dados )
     * tambem eh adicionado um input oculto com name unico para auxiliar `relations`
     *
     * Este metodo executa uma consulta SQL para buscar categorias,
     * monta uma hierarquia baseada no campo 'parent', e retorna
     * um HTML em forma de <ol><li> com links para cada categoria.
     *
     * @return string O HTML gerado.
     */
    public function list_article_categories(): string {
        $cmd = $this->conn->prepare("
            SELECT ID, name, parent, slug 
            FROM categories ORDER BY parent, name ASC
        ");
        $cmd->execute();

        $rows = $cmd->fetchAll( PDO::FETCH_ASSOC );

        $hierarchy = [];
        foreach( $rows as $cat ) {
            $hierarchy[$cat['parent']][] = $cat; # Agrupa categorias pelo parent
        }

        $html = '<ol class="scrollbar">';

        # Prepara pilha inicial com categorias de nivel raiz (parent = 0)
        # O operador ?? [] garante que $hierarchy[0] seja um array mesmo se nao houver categorias pai.
        $stack = array_map( function( $cat ) {
            return ['cat' => $cat, 'level' => 0];
        }, $hierarchy[0] ?? [] );

        # Inverte a pilha para garantir a ordem correta de processamento para uma travessia especifica.
        # A ordem de array_pop depende da ordem em que os elementos sao empilhados.
        $stack = array_reverse( $stack ); # @todo Esta linha parece estar faltando no codigo original se a intencao era reverter a pilha inicial.

        $prevlevel = 0; # Nivel anterior, para controle de fechamento de tags
        while( ! empty($stack) ) {
            $current = array_pop($stack); # Pega o ultimo item da pilha
            $cat = $current['cat'];
            $level = $current['level'];
            # Fecha tags anteriores quando o nivel diminui
            if( $level < $prevlevel ) {
                $html .= str_repeat( '</ol></li>', $prevlevel - $level );
            }
            $html .= '<li>';
            $html .= "<input 
                type=\"checkbox\" class=\"ckb\" id=\"ckb-{$cat['ID']}\" 
                name=\"checkcat[]\" value=\"{$cat['ID']}\" 
                data-slug=\"{$cat['slug']}\" 
                {$this->checked($cat['ID'])} 
            />";
            $html .= '<label for="ckb-'. $cat['ID'] .'"><span>'. $cat['name'] .'</span></label>';

            //$html .= '<input type="hidden" name="catslug[]" value="'. $cat['slug'] .'" />';

            # input com name unico para inserir e atualizar relations
            // $html .= '<input type="hidden" name="id-'. $cat['ID'] .'" value="'. $cat['ID'] .'" />';

            # Se houver filhos, abre nova lista e empilha filhos
            if( ! empty($hierarchy[$cat['ID']]) ) {
                $html .= '<ol>';
                # Empilha filhos em ordem reversa para processar na ordem correta
                foreach( array_reverse($hierarchy[$cat['ID']]) as $child ) {
                    array_push( $stack, ['cat' => $child, 'level' => $level + 1] );
                }
            }
            else {
                $html .= '</li>'; # Fecha item se nao houver filhos
            }
            $prevlevel = $level; # Atualiza nivel anterior ($prevlevel)
        }
        # fecha quaisquer tags pendentes
        $html .= str_repeat( '</ol></li>', $prevlevel );

        $html .= '</ol>';

        return $html;
    }

    # Verifica se a categoria esta vinculada ao artigo atual.
    private function checked( int $catID ): string {
        $cmd = $this->conn->prepare("
            SELECT r.category_id 
                FROM relations r

            JOIN articles a 
                ON a.ID = r.type_id

            WHERE a.ID = ?
        ");
        $cmd->execute([ URL::int('id') ]);

        $checked = '';
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $checked .= $row['category_id'] === $catID ? 'checked' : '';
        }

        return $checked;
    }


    public function field( string $column, ?int $id = null ): mixed {
        $article_id = $id ?? URL::int('id') ?: null;

        # checa se rota eh update antes, assim evita verificar in_array sem necessidade
        if( $article_id === null ) {
            return null;
        }

        $columns = ['ID', 'title', 'updated', 'slug', 'segment', 'status'];

        if( ! in_array($column, $columns, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'field', 'Article') 
            );
        }

        if( array_key_exists($article_id, self::$cache) === false ) {

            $cmd = $this->conn->prepare("
                SELECT " . implode( ',', $columns ) . " FROM articles WHERE ID = ?
            ");

            $cmd->execute([ $article_id ]); 


            self::$cache[$article_id] = $cmd->fetch( PDO::FETCH_ASSOC ) ?: [];
        }

        return self::$cache[$article_id][$column] ?? null;
    }

}