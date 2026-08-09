<?php
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\
 */

# 1. A Classe Principal (metodos utilitarios de strings HTML)
class Assets {

    private ?JavaScript $javascript = null;

    private ?int $global_version = null;

    public function scripts(): JavaScript {
        if( $this->javascript === null ) {
            $this->javascript = new JavaScript($this);
        }

        return $this->javascript;
    }

    /**
     * @param 
     * $fillpath : preencher o caminho para o arquivo
     * nome do diretorio dentro de diretorio /assest/js/ e nome base do arquivo — sem extencao `.js`
     * $v :
     * versao do arquivo (nao eh um sys de verisonamento) eh apenas controle de cache do navegador
     * tipo float para simbolizar major e minor
     */
    public function append_script( string $fillpath, ?int $v = null, string $attr = '' ): string {
        $attr = ($attr !== '') ? " {$attr}" : '';

        $v    = $v ?? $this->global_version ?? VERSION;

        $url  = URL::root( "dashboard/assets/js/{$fillpath}.js?v={$v}" );

        return "<script src=\"{$url}\"{$attr}></script>\n";
    }


    public function import_script( string $url, string $attr = '' ): string {
        $attr = ($attr !== '') ? " {$attr}" : '';

        return "<script src=\"{$url}\"{$attr}></script>\n";
    }

    public function block_script( string $code_put ): string {
        $code_put = trim( $code_put );
        $lines    = preg_split( '/\R/', $code_put );
        $indent   = 0;
        $code_out = [];
        foreach( $lines as $line ) {
            $line = trim($line);
            if( $line === '' ) {
                continue;
            }
            # diminui indentacao antes se linha comeca com }
            if( $line[0] === '}' ) {
                $indent--;
            }
            $code_out[] = str_repeat( '    ', max(0, $indent) ) . $line;
            # aumenta indentacao depois se linha termina com {
            if( substr($line, -1) === '{' ) {
                $indent++;
            }
        }

        return "<script>\n" . implode( "\n", $code_out ) . "\n</script>\n";
    }

    public function return_file( string $filepath ): string {
        ob_start();

        require $filepath;

        return ob_get_clean();
    }

}

# 2. A Classe de JavaScript "combos"
class JavaScript {

    private Assets $sys;

    private ?Emitter $emitter = null;

    public function __construct( Assets $sys ) {
        $this->sys = $sys;
    }

    public function resolve(): Emitter {
        if( $this->emitter === null ) {
            $this->emitter = new Emitter($this);
        }

        return $this->emitter;
    }

    public function globals(): string {
        # arquivos de configuracoes `config-js.php` e `init.js` 
        # `init.js` tambem contem scripts de inicializacoes antecipadas e ate mesmo criticas
        $js  = $this->sys->return_file( DASH_DIR . 'assets/js/config-js.php' );
        $js .= $this->sys->append_script( 'init', null );

        # outros arquivos que sao iniciados antes, e nessa mesma ordem:
        $js .= $this->sys->append_script( 'global/packit', null );
        $js .= $this->sys->append_script( 'global/icons', null );
        $js .= $this->sys->append_script( 'global/nav', null );

        return $js;
    }

    public function home( bool $stats = false ): string {
        $js = $this->sys->append_script( 'routes/upgrades', null );

        if( $stats ) {
            $js .= $this->sys->append_script( 'routes/statistics/Chart', null );
            $js .= $this->sys->return_file( 
                DASH_DIR . 'assets/js/routes/statistics/chart-init-js.php' 
            );
        }

        return $js;
    }

    public function editors(): string {
        if( editor_is('punk') ) {
            $js  = $this->sys->append_script( 'routes/editor/punk/punk', null );
            $js .= $this->sys->append_script( 'routes/editor/punk/punk-popup-media', null );

            return $js;
        }
        else if( editor_is('tinymce') ) {
            $js = $this->sys->import_script( 
                'https://cdn.tiny.cloud/1/zk1otbqmj6bcltbt3xhrx7vw58sjspoo4zjhyj3uc0kt72c8/tinymce/6/tinymce.min.js', 
                null 
            );

            if( Hook::has_action('tinymce_init') ) {
                Hook::call_action('tinymce_init');
            }
            else {
                $js .= $this->sys->append_script( 'routes/editor/tinymce-init', null );
            }

            return $js;
        }
        else if( editor_is('ace') ) {
            $js  = $this->sys->append_script( 'routes/editor/ace/ace', null );
            $js .= $this->sys->append_script( 'routes/editor/ace/init', null );
            
            return $js;
        }
        else {
            return $this->sys->block_script("
                console.error('Nenhum editor definido');
            ");
        }
    }

    public function medias(): string {
        return $this->sys->append_script( 'routes/medias/loadmore', null );
    }

    public function menus(): string {
        $js  = $this->sys->append_script( 'routes/menus/menu', null );
        $js .= $this->sys->append_script( 'routes/menus/sortable', null );
        $js .= $this->sys->append_script( 'routes/menus/sortable-init', null );

        return $js;
    }

    public function article_update(): string {
        return $this->sys->append_script( 'routes/article-update', null );
    }

    public function settings( array $settings ): string {
        foreach( $settings as $basename => $condition ) {
            if( $condition === true ) {
                $js = $this->sys->append_script("routes/settings/{$basename}", null);
            }
        }

        return $js ?? '';
    }


    public function dist(): string {
        return $this->sys->import_script( URL::root('dist/assets/js/dist.js') );
    }
    #
    #
    # para arquivos com codigos que dependem de `dist.js`
    #
    public function admin_register(): string {
        return $this->sys->append_script("routes/admins/register", 0);
    }
    #
    public function admin_update(): string {
        return $this->sys->append_script("routes/admins/update", 0);
    }

    /**
     * um unico arquivo de carregamento tardio ( `late.js` )
     */
    public function late(): string {
        return $this->sys->append_script( 'global/late', null );
    }

}


# 3. A Classe de Roteamento `Emitter` decide e exibe
class Emitter {

    private JavaScript $js;

    private array $slug = [];

    public function __construct( JavaScript $javascript ) {
        $this->js = $javascript;

        $this->slug[0] = URL::param(0);
        $this->slug[1] = URL::param(1);

        # nao existe parametro de slug com indice 2 entao usamos para o segmento de slugs
        $this->slug['full'] = URL::pathname();
    }


    public function dispatcher(): string {
        # toda rota recebe os arquivos de js/global/
        $output = $this->js->globals();

        #
        # verifica as condicionais de rota e acumula

        if( $this->slug[0] === '' ) {
            $output .= $this->js->home( statistics() );
        }

        else if( is_update() ) { 
            # rotas de atualizacoes para documentos especificos [page, article, context] 
            if( IS_DOCUMENTS_CONTENTS ) {
                # Carrega o editor
                $output .= $this->js->editors();
            }
            
            # Eh especificamente uma rota de artigo unico: articles/update/?id
            if( $this->slug[0] === 'articles' ) {
                $output .= $this->js->article_update();
            }
        }

        else if( $this->slug[0] === 'medias' ) {
            $output .= $this->js->medias();
        }

        else if( $this->slug[0] === 'menus' ) {
            $output .= $this->js->menus();
        }

        else if( $this->slug[0] === 'settings' ) {
            $output .= $this->settings();
        }

        # --
        #
        # toda rota recebe o arquivo dist.js
        $output .= $this->js->dist();

        # mais verificacoes condicionais de rota, so que agora apos, pois dependem de `dist.js`
        if( $this->slug[0] === 'admins' ) {
            if( $this->slug[1] === 'register' ) {
               $output .= $this->js->admin_register();
            }
            else if( $this->slug[1] === 'update' && URL::has('id') ) {
               $output .= $this->js->admin_update();
            }
        }

        return $output .= $this->js->late();
    }


    # carrega apenas em /settings/
    private function settings(): string {
        if( $this->slug[0] !== 'settings' ) {
            return ''; 
        }

        return $this->js->settings( [
            # 'basename'   => URL::param(1) === '', # titulo site & formatos de data
            # 'basename'   => URL::param(1) === 'email',
            'image-sizes'  => URL::param(1) === 'media',
            # 'basename'   => URL::param(1) === 'options',
            # 'basename'   => URL::param(1) === 'reading',
            # 'basename'   => URL::param(1) === 'seo',
            # 'basename'   => URL::param(1) === 'socialnet',
            # 'basename'   => URL::param(1) === 'urls',
        ] );
    }

}


$assets = new Assets;