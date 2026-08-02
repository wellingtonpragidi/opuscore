<?php
declare( strict_types = 1 );
/**
 * @package System\Core
 */
class Router {

    /**
     * armazena o mapeamento de caminhos relativos para absolutos dos arquivos de view/
     * chave = caminho relativo sem extensao .php
     * valor = caminho absoluto completo
     * @access protected: "compartilha" propriedade com NavMenu
     */
    protected static array $views = [];

    private string $path;

    private string $segment;

    private bool $is_404 = false;

    # armazena titulos por regras personalizadas por segmento e segmento + query string
    private array $router_titles = [];

    # "traduz" subitens do menu de navegacao do dashboard
    protected array $menu_labels = [];


    public function __construct() {

        $this->path = DASH_DIR . 'view/';

        self::$views = self::$views ?: $this->mapping();

        $this->segment = URL::pathname() ?: 'home';

        if( ! isset(self::$views[$this->segment]) ) {
            $this->is_404 = true;
            http_response_code(404);
        }

        # importa arrays $menu_labels e $router_titles
        require annex_path('infra/rewrite-rules.php');       
        $this->menu_labels   = $menu_labels;
        $this->router_titles = $router_titles;
    }


    private function mapping(): array {
        $list = [];

        $views = new DirectoryIterator( $this->path );

        # iniciando iterador base
        foreach( $views as $base ) {
            if( $base->isDot() ) {
                continue;
            }

            $baseFilename = $base->getFilename();

            # child: view / subdirs / files
            if( $base->isDir() ) {

                foreach( new DirectoryIterator($base->getPathname()) as $child ) {

                    if( $child->isDot() ) {
                        continue;
                    }

                    $list[
                        strtolower(
                            $baseFilename . '/' . $child->getBasename('.php')
                        )
                    ] = str_replace( '\\', '/', $child->getRealPath() );
                }

                continue;
            }

            # base: view / files
            if( $baseFilename === 'index.php' || $baseFilename === '404.php' ) {
                continue;
            }

            $list[
                strtolower( $base->getBasename('.php') )
            ] = str_replace( '\\', '/', $base->getRealPath() );
        }

        return $list;
    }


    /**
     * inclui o arquivo correspondente com base na rota atual
     * se nao existir, inclui o 404
     */
    public function requires(): void {
        if( Hook::call_filter('run_dashboard', true) ) {

            if( isset(self::$views[$this->segment]) ) {

                $this->redirects_to();

                require_dashboard( self::$views[$this->segment] );
            }
            else {

                require_dashboard( $this->path . '404.php' );
            }
        }
        else {

            Hook::call_action('dashboard_page');
        }

        # remove parametro (slug) adicionais da URL e redireciona de volta
        if( URL::param(2) ) {
            header( 'Location:' . str_replace(URL::param(2), '', URL::current()) );
            exit;
        }
    }


    public function master_title(): ?string {
        if( Hook::call_filter('run_dashboard', true) ) {

            $title = '';

            if( isset(self::$views[$this->segment]) ) {
                if( isset($this->router_titles[$this->segment]) ) {

                    if( $this->segment === 'medias' && URL::has('id') ) {
                        $title = Media::title();
                    }
                    else {
                        $title = $this->router_titles[$this->segment];
                    }
                }
                else {

                    $title = $this->fallback_title();
                }
            }
            else {

                $title = $this->router_titles['404'];
            }

            return $title;
        }
        else {
            
            Hook::call_action('dashboard_title');
        }

        return null;
    }


    # retorna o titulo formatado com o nome do site e dominio usado em title do HTML
    public function title_tag(): ?string {
        if( Hook::call_filter('run_dashboard', true) ) {

            $title = [];

            if( isset(self::$views[$this->segment]) ) {

                if( isset($this->router_titles[$this->segment]) ) {

                    if( $this->segment === 'medias' && URL::has('id') ) {
                        $title['current'] = 'Mídia “' . Media::title() . '”';
                    }
                    else {
                        $title['current'] = $this->router_titles[$this->segment];
                    }

                }
                else {

                    $title['current'] = $this->fallback_title();
                }
            }
            else {

                $title['current'] = $this->router_titles['404'];
            }


            $title['fixed'] = ' – ' . site_title() . ' ⮞ Opus Core';

            if( ! $this->is_404 && has_identifier_query()  ) {
                # Formato para edicao: (Titulo Base “Titulo edicao” ⮞ Opus Core)
                $title['full'] = $title['current'] . $this->title_by_query() . $title['fixed'];
            }
            else {
                # Formato padrao: "Titulo da Pagina › Titulo do Site ⮞ Opus Core"
                $title['full'] = $title['current'] . $title['fixed'];
            }


            return $title['full'];
        }
        else {

            Hook::call_action('dashboard_title');
        }

        return null;
    }


    /**
     * Gera o valor do atributo ID para o body baseado no ultimo parametro na URL
     */
    public function body_id() {
        if( Hook::call_filter('run_dashboard', true) ) {
            $value = '';

            if( isset(self::$views[$this->segment]) ) {

                $value = str_replace( '/', '-', $this->segment );
            }
            else {

                $value = 'not-found';
            }

            return "id=\"{$value}\"";
        }
        else {

            Hook::call_action('dashboard_body');
        }
    }


    private function title_by_query(): ?string {
        if( is_context() ) {
            $context = Container::call('Context');
            $title = $context->title();
        }
        else if( is_media() ) {
            return null;
        }
        else if( has_active_menu() ) {
            $title = Menu::title();
        }
        else {
            $title = Model::get_title_by_id();
        }

        return " “{$title}”";
    }

    /**
     * gera um titulo alternativo com base no ultimo parametro
     * usado quando nao ha titulo personalizado
     */
    private function fallback_title(): string {
        $parts = explode('/', $this->segment);
        return ucfirst( end($parts) );
    }

    private function redirects_to() {
        if( URL::param(0) === 'home' ) {
            header( 'Location: ' . URL::root('dashboard') );
            exit;
        }
        if( URL::param(1) === 'update' && ! URL::has('id') ) {
            $redirect_to = str_replace( 'update', '', URL::current() );
            header( "Location: {$redirect_to}" );
            exit;
        }
        if( URL::param(0) === 'menus' && URL::has('key') ) {
            if( ! in_array(URL::GET('key'), array_column(Menu::load(), 'key')) ) {
                header( 'Location: ' . URL::root('dashboard/menus') );
                exit;
            }
        }
    }
    
}