<?php
/**
 * Gerencia as operacoes CRUD e geracao de HTML para exibicao de paginas e posts 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\Model
 */

class Post extends Model {

    /**
     * Seleciona registros da tabela 'posts' com base em diferentes criterios:
     * pesquisa por termo ('q' na URL), selecao por ID ('id' na URL),
     * ou todos os registros com paginacao como padrao.
     */
    public function select(): array {
        $pagination = new Pagination( Count::posts(), per_page('posts') );

        $columns = URL::has('id') 
            ? 'p.*' 
            : 'p.' . implode( ', p.', ['ID', 'title', 'created', 'updated', 'slug', 'status'] );

        $placeholder = "
            SELECT {$columns}, m.attachment
                FROM posts p 
                LEFT JOIN medias m 
                    ON m.related_id = p.ID 
                    AND m.related_type = ?
        ";
        # registro com base no parametro ID da URL
        if( URL::has('id') ) {
            $sql = "$placeholder WHERE p.ID = ?";

            $params = [ 'post', URL::int('id') ];
        }
        # registros de pesquisa por titulo   'segment'
        else if( URL::has('q') ) {
            $q = '%' . URL::GET('q') . '%';

            $sql = "$placeholder WHERE (p.title LIKE ? OR p.summary LIKE ?) ORDER BY p.ID DESC";

            $params = [ 'post', $q, $q ];
        }
        # selecao de todos os registros (padrao) com paginacao
        else {
            $sql = "$placeholder ORDER BY p.ID DESC LIMIT ?, ?";

            $params = [
                'post',
                $pagination->offset(),
                per_page('posts')
            ];
        }

        $cmd = $this->conn->prepare( $sql );
        $cmd->execute( $params );

        $list = [];

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
            'attachment' => fn($value) => $value ? json_decode($value) : '',
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
    /*$bind->ID         = $row['ID'];
    $bind->title      = $row['title'];
    $bind->author     = $row['author'] ?? '';
    $bind->content    = $row['content'] ?? '';
    $bind->summary    = $row['summary'] ?? '';
    $bind->date       = $row['created'] ?? '';
    $bind->update     = $row['updated'];
    $bind->slug       = $row['slug'];
    $bind->segment    = $row['segment'];
    $bind->status     = $row['status'];
    $bind->attachment = json_decode($row['attachment'] ?? '');*/



    public function insert( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO posts(title, author, created, slug, status) 
            VALUES(?, ?, ?, ?, ?)
        ");
        $cmd->execute([ 
            $bind->title, 
            $bind->author, 
            $bind->date, 
            $bind->slug, 
            $bind->status 
        ]);
        $bind->LastID = $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    public function update( Assign $bind ): bool {
        $columns = ['title', 'content', 'summary', 'updated', 'slug', 'segment', 'status'];

        return parent::updater( 'posts', $columns, $bind );
    }


    public function build_segment( Assign $bind ): string {

        $checked = array_map( 'intval', $bind->checked );

        if( empty($checked) ) {
            return $bind->slug;
        }

        $placeholders = implode( ',', array_fill(0, count($checked), '?') );

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


    public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM posts WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    /**
     * Verifica se ja existe outro registro com o mesmo slug.
     * @todo Esse metodo precisa ser unico para Post e Page
     */ 
    public function exists( Assign $bind  ): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM posts WHERE slug = ? AND ID != ? LIMIT 1");
        $cmd->execute([ $bind->slug, $bind->ID ]);

        return (bool) $cmd->fetchColumn();
    }



    /**
     * # Metodo ::list_post_categories(). Gera uma lista HTML hierarquica de categorias.
     * Com input type checkbox para "marcar" adicionar categorias ao tipo de postagem
     * ( isso insere, atualiza e deleta relacoes na tabela `relations` do banco de dados )
     * tambem eh adicionado um input oculto com name unico para auxiliar `relations`
     *
     * Este metodo executa uma consulta SQL para buscar categorias,
     * monta uma hierarquia baseada no campo 'parent', e retorna
     * um HTML em forma de <ol><li> com links para cada categoria.
     *
     * @return string O HTML gerado.
     */
    public function list_post_categories(): string {
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

    # Verifica se a categoria esta vinculada ao post atual.
    private function checked( int $category_id ): string {
        $cmd = $this->conn->prepare("
            SELECT r.category_id 
                FROM relations r

            JOIN posts p 
                ON p.ID = r.type_id

            WHERE p.ID = ?
        ");
        $cmd->execute([ URL::int('id') ]);

        $checked = '';
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $checked .= $row['category_id'] === $category_id ? 'checked' : '';
        }

        return $checked;
    }


    private function field( string $column ): mixed {
        $cmd = $this->conn->prepare("SELECT $column FROM posts WHERE ID = ?");

        $cmd->execute([ URL::int('id') ]); 

        return $cmd->fetchColumn();
    }

    public function slug(): string {
        return $this->field('slug');
    }

    public function segment(): string {
        return $this->field('segment') ?? '';
    }

}