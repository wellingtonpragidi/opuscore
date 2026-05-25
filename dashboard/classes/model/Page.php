<?php
/**
 * Gerencia as operacoes CRUD e geracao de HTML para exibicao de paginas e posts 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System/Model
 */

class Page extends Model {

    public function select(): array {
        $pagination = new Pagination( Count::pages(), per_page('pages') );
        $paginate = [ $pagination->offset(), per_page('pages') ];

        $list = [];

        # 1) PAGINA UNICA POR ID  → soh aqui JOIN com `medias` e nao com `pages`
        if( URL::has('id') ) {
            $cmd = $this->conn->prepare("
                SELECT p.*, m.attachment FROM pages AS p 
                LEFT JOIN medias AS m ON m.related_id = p.ID AND m.related_type = 'page' 
                WHERE p.ID = ?
            ");
            $cmd->execute([ URL::int('id') ]);

            while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
                $bind = $this->row($row);
                $bind->content  = $row['content'];
                $bind->summary  = $row['summary'];
                $bind->parent   = $row['parent'];
                $bind->slug     = $row['slug'];
                $bind->attachment = json_decode($row['attachment'] ?? ''); 

                $list[] = $bind;
            }
        }
        # 1) BUSCA (search)
        else if( URL::has('q') ) {
            $q = '%' . URL::GET('q') . '%';
            $cmd = $this->conn->prepare("
                SELECT ID, title, lastmod, segment, template, status FROM pages 
                WHERE (title LIKE ? OR summary LIKE ?) 
                ORDER BY ID DESC LIMIT ?, ?
            ");
            $cmd->execute( array_merge([ $q, $q ], $paginate) );

            while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
                $list[] = $this->row( $row );
            }
        }
        # 3) LISTAGEM PADRAO COM PAGINACAO
        else {
            $cmd = $this->conn->prepare("
                SELECT ID, title, lastmod, segment, template, status FROM pages 
                ORDER BY ID DESC LIMIT ?, ?
            ");
            $cmd->execute( $paginate );

            while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
                $list[] = $this->row( $row );
            }
        }

        return $list;
    }

    private function row( mixed $row ): Assign {
        $bind = new Assign;
        $bind->ID       = (int) $row['ID'];
        $bind->title    = $row['title'];
        $bind->segment  = $row['segment'];
        $bind->template = $row['template'];
        $bind->status   = $row['status'];
        $bind->lastmod  = $row['lastmod'];

        return $bind;
    }


    public function insert( Assign $bind ): bool {
        $cmd = $this->conn->prepare("INSERT INTO pages(title, slug) VALUES(?, ?)");
        $cmd->execute([ $bind->title, $bind->slug ]);
        $bind->LastID = $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    public function update( Assign $bind ): bool {
        $columns = [
            'title', 
            'content', 
            'summary', 
            'slug', 
            'parent', 
            'segment', 
            'template', 
            'lastmod', 
            'status'
        ];

        return parent::updater( 'pages', $columns, $bind );
    }


    public function build_segment( Assign $bind ): array {
        $cmd = $this->conn->prepare("SELECT slug FROM pages WHERE ID = ? LIMIT 1");

        # CURRENT
        $cmd->execute([ $bind->ID ]);
        $current = $cmd->fetch(PDO::FETCH_ASSOC);

        if( ! $current || empty($current['slug']) ) {
            return [
                'error' => true,
                'code'  => 'empty_current_slug'
            ];
        }

        if( $bind->parent === 0 ) {
            return [
                'error' => false,
                'data'  => $current['slug']
            ];
        }

        # HAS PARENT
        $cmd->execute([$bind->parent]);
        $parent = $cmd->fetch(PDO::FETCH_ASSOC);

        if( ! $parent || empty($parent['slug']) ) {
            return [
                'error' => true,
                'code'  => 'empty_parent_slug'
            ];
        }

        return [
            'error' => false,
            'data'  => $parent['slug'] . '/' . $current['slug']
        ];
    }


    public function delete( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM pages WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }


    # Verifica se ja existe outro registro com o mesmo slug.
    public function exists( Assign $bind  ): bool {
        $cmd = $this->conn->prepare("SELECT 1 FROM pages WHERE slug = ? AND ID != ? LIMIT 1");
        $cmd->execute([ $bind->slug, $bind->ID ]);

        return (bool) $cmd->fetchColumn();
    }


    /**
     * @todo verificar slug parent
     */
    private function column( string $column, string $slug = '' ): mixed {
        $cmd = $this->conn->prepare("SELECT $column FROM pages WHERE slug = ?");

        $param = $slug ?: URL::param(0);
        $cmd->execute([ $param ]); 

        return $cmd->fetchColumn();
    }



    public function parent( string $column, int $id ): mixed {
        $cmd = $this->conn->prepare("SELECT $column FROM pages WHERE ID = ? LIMIT 1");
        $cmd->execute([ $id ]);

        return parent::fetchColumn($cmd);
    }



    public function select_option(): iterable {
        $cmd = $this->conn->prepare("SELECT ID, title, parent FROM pages WHERE ID != ?");
        $cmd->execute([ URL::int('id') ]);

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            $bind->ID     = $row['ID'];
            $bind->title  = $row['title'];
            $bind->parent = $row['parent'];

            yield $bind;
        }
    }


    public function is_child(): bool {
        $cmd = $this->conn->prepare("SELECT slug FROM pages WHERE parent != 0");
        
        return (bool) $cmd->fetchColumn();
    }



    /**
     * Exibe o select HTML de templates disponiveis para paginas.
     * Le os arquivos de template de um diretorio especifico, extrai o nome do template
     * de comentarios PHPDoc e os apresenta como opcoes.
     */
    public function template( object $show ): string {

        $template_dir = TEMPLATE_PATH . 'pages/';

        $html = '<select id="template" name="template" class="mt10">
            <option value="page.php">Padrao</option>';

        if( ! is_dir($template_dir) ) {
            return $html . '</select>';
        }

        $selected = $show->template === 'page.php' ? 'selected' : '';

        foreach( new DirectoryIterator($template_dir) as $template ) {
            if( $template->isDot() || $template->getExtension() !== 'php' ) {
                continue;
            }

            $file_content = file_get_contents( $template_dir . $template->getFilename() );

            $template_name = null;

            if( preg_match_all('#/\*\*.*?\*/#s', $file_content, $blocks) ) {
                foreach( $blocks[0] as $block ) {
                    if( preg_match('/Page\s+Template\s*:\s*(.+)/i', $block, $matches) ) {
                        $template_name = trim( $matches[1] );
                        break;
                    }
                }
            }

            if( $template_name ) {
                $selected = ($show->template == $template->getFilename()) ? ' selected' : '';
                $hidden   = ($show->template == 'index.php') ? ' hidden disabled' : '';
                $attrs    = "value=\"{$template->getFilename()}\"{$selected}{$hidden}";

                $html .= "<option {$attrs}>{$template_name}</option>";
            }
        }

        return $html . '</select>';
    }

}