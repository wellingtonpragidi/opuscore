<?php 
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Router\Rules
 */

class Router {

    private Category $category; 
    private Post $post;
    private Page $page;
    private User $user;

    private string $basename = '';

    private string $case = '';

    private string $title = ''; 
    private string $title_hook = '';

    private string $html_class = '';

    private ?bool $has_home = null;


    private bool $resolved = false;


    public function __construct( Category $category, Post $post, Page $page, User $user ) {
        
        $this->category = $category;
        $this->post     = $post;
        $this->page     = $page;
        $this->user     = $user;

        $this->resolve();
    }


    /**
     * A ordem das condicoes aqui importam
     * Se mudar olha bem pra ver como TUDO esta a funcionar
     */
    private function resolve(): void {

        # Ja resolvido — nao precisa executar novamente
        if( $this->resolved ) {
            return;
        }

        # se for async nao precisa resolver rota
        if( defined('FEED_ASYNC') && FEED_ASYNC ) {
            return;
        }


        if( URL::param(0) === 'home-page' ) {
            header('Location: ' . URL::root(), true, 301);
            exit;
        }


        # Quando existe home-page.php, o index.php passa a representar a listagem de posts.
        # Caso contrario, index.php eh a home e posts nao existe ( o site nao utiliza blog )

        # HOME:
        # Na condicao dessa rota, o template sempre precisara ter uma pagina inicial 
        #  que nao seja listagem de posts se quiser usar o loop `Seek`

        # Home sempre permanece 'home' conceitualmente.
        # Se nao existir home-page.php, usa index.php como fallback 
        # mas continua sendo HOME (titulo, contexto, etc).

        $this->has_home = $this->has_home ?? file_exists( get_template_path('home-page.php') );

        # POSTS:
        # `/posts` sempre representa listagem de posts.
        # Se nao houver home-page.php, /posts_base() | is_posts() == nao existe

        if( $this->is_home() || $this->is_posts() ) {

            # Se ( URL === `/posts` ) E o template Nao tem o arquivo home-page.php
            # a pagina inicial usa o arquivo index.php, portanto, o site nao tem blog
            # Entao `/posts` redireciona para `/`evitando duplicacao de conteúdo
            if( $this->is_posts() && ! $this->has_home ) {
                header( "Location: " . URL::root(), true, 301 );
                exit;
            }

            # HOME sempre e HOME independente do arquivo usado
            if( $this->is_home() ) {
                $this->case       = 'home';
                $this->title      = site_title();
                $this->title_hook = 'home_title';

                if( $this->has_home ) {
                    $this->basename   = 'home-page';
                    $this->html_class    = 'home-page';
                }
                else {
                    # fallback visual
                    $this->basename   = 'index';
                    $this->html_class    = posts_base();
                }
            }
            # soh is_posts()
            else {
                $this->case       = 'posts';
                $this->basename   = 'index';
                $this->title      = 'Posts';
                $this->title_hook = 'posts_title';
                $this->html_class    = posts_base();
            }
        }

        else if( $this->is_query() ) {
            if( $this->is_query() && ! $this->has_home ) {
                header( "Location: " . URL::root(), true, 301 );
                exit;
            }

            if( file_exists(get_template_path('search.php')) ) {
                $this->basename = 'search';
            }
            else {
                $this->basename = 'index';
            }
            $this->case       = 'search';
            $this->title      = 'Resultados de busca para: ' . URL::GET('q');
            $this->title_hook = 'query_title';
            $this->html_class    = posts_base() . ' search';
        }

        else if( $this->is_categories() ) {
            if( file_exists( get_template_path('categories.php') ) ) {
                $this->case       = 'categories';
                $this->basename   = 'categories';
                $this->title      = 'Categorias';
                $this->title_hook = 'categories_title';
                $this->html_class = 'categories';
            }
            else {
                $this->show_404();
            }
        }

        else if( $this->is_category() ) {
            if( $this->is_category() && ! $this->has_home ) {
                header( "Location: " . URL::root(), true, 301 );
                exit;
            }

            if( file_exists( get_template_path('category.php') ) ) {
                $this->basename = 'category';
            }
            else {
                $this->basename = 'index';
            }
            $this->case       = 'category';
            $this->title      = $this->category->name();
            $this->title_hook = 'category_title';
            
            $pathname = $this->category->pathname();
            $list = str_replace( '/', ' ', $pathname );
            $this->html_class = posts_base() . ' ' . category_base(); # 
        }

        # post pagina unica com URL padrao ou personalizado
        else if( $this->is_post() ) {
            $this->case       = 'post';
            $this->basename   = 'post';
            $this->title      = $this->post->title();
            $this->title_hook = 'post_title';
            $this->html_class = "post post-{$this->post->id()}";
        }

        else if( $this->is_page() ) {
            $page_slug     = $this->page->slug();
            $page_template = $this->page->template();
            if( $page_template === 'page.php' ) {
                $this->basename = 'page';
            }
            else {
                $file['basename'] = str_replace( '.php', '', $page_template ?? '' );
                $this->basename = "pages/{$file['basename']}";

                $basename = ($page_slug !== $file['basename']) ? $file['basename'] : '';
            }
            $this->case       = 'page';
            $this->title      = $this->page->title();
            $this->title_hook = 'page_title';
            $this->html_class    = "page page-{$page_slug}";
        }

        else if( $this->is_user() ) {
            if( ! file_exists( get_template_path('user.php') ) ) {
                throw new OpusException(
                    'Página não encontrada ou template sem suporte para paginas de usuários', 
                    'error', 404
                );
            }
            $this->case       = 'user';
            $this->basename   = 'user';
            $this->title      = 'Usuário ' . User::name();
            $this->title_hook = 'user_title';
            $this->html_class = 'user-profile ' . user_base();
        }

        else if( $this->is_404() ) {
            $this->show_404();
        }

        else {
            $this->show_404();
        }

        URL::normalize();


        $this->resolved = true;
    }


    public function case( string $context ): bool {
        return $this->case === $context;
    }


    public function title( ?string $rule = null ): string {
        if( $this->title_hook !== '' && Hook::has_filter($this->title_hook) ) {
            $title = Hook::call_filter( $this->title_hook, $this->title );
        }
        else {
            if( URL::param(0) === '' && empty($_GET["q"]) ) {
                $title = $this->title;
            } 
            else {
                $title = $rule === 'tag' 
                    ? $this->title . ' – ' . site_title() 
                    : $this->title;
            }
        }

        return $title;
    }


    public function requires(): void {
        require_template('header');

        require_template( $this->basename );

        require_template('footer');
        
        
        if( statistics() ) {
            $this->statistic();
        }
    }


    public function html_class( string $prefix ): string {
        $class = $prefix . str_replace( ' ', " {$prefix}", $this->html_class );
            
        return "class=\"{$class}\"";
    }


    public static function instance(): self {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }



    private function show_404(): void {
        if( ! file_exists( get_template_path('404.php') ) ) {
            throw new OpusException('Página não encontrada', 'error', 404);
        }
        $this->case       = '404';
        $this->basename   = '404';
        $this->title      = 'Página não encontrada';
        $this->title_hook = '404_title';
        $this->html_class = 'not-found error-404 404';
        http_response_code(404);
    }


    # returna o numero de registros inseridos
    private function statistic(): ?int {
        $invalid_GET = array_diff( array_keys($_GET), ['q', 'pg'] );

        // if( $this->case && empty($invalid_GET) ) {
        if( empty($invalid_GET) ) {
            $statistic  = GraphStatistic::instance();
            return (int) $statistic->insert( $this->title );
        }
        
        return null;
    }





    private function is_home(): bool {
        return URL::param(0) === '' && ! URL::has('q');
    }

    private function is_query(): bool {
        return URL::param(0) === '' && URL::has('q');
    }

    private function is_page(): bool {
        # necessario para nao retornar true em home-page e search/query
        if( URL::param(0) === '' ) {
            return false;
        }

        return URL::pathname() === $this->page->segment();
    }

    private function is_post(): bool {
        if( URL::param(0) === '' ) {
            return false;
        }

        return URL::pathname() === $this->post->segment();
    }

    private function is_posts(): bool {
        return URL::param(0) === posts_base();
    }

    private function is_category(): bool {
        return URL::pathname() === $this->category->pathname();
    }

    private function is_categories(): bool {
        return URL::param(0) === category_base() && URL::param(1) === '';
    }

    private function is_404(): bool {
        $httpCode = http_response_code();

        return URL::param(0) === '404' || ( $httpCode !== false && $httpCode === 404 );
    }

    private function is_user(): bool {
        if( URL::param(0) !== user_base() && URL::param(1) === '' ) {
            return false;
        }

        //  $this->user->username() 
        return URL::param(1) === User::username();
    }



    /**
     * Remove parametro da URL atual e redireciona para URL limpa
     * @example [
     *    - Uma URL de post ou page: example.com/page-title | param = 0
     *    - [ 
     *        Se URL example.com/page-title/adicional 
     *        return_param(1) volta para 0
     *        removendo parametro adicional da URL 
     *        redirecionando de volta para example.com/page-title
     *    ]
     * ]
     */
    private function return_param( int $i ): void {
        if( URL::param($i) ) {
            $params = URL::params();

            unset($params[$n]);

            # reindexa
            $param = array_values($params);

            $redirect = URL::root( implode('/', $param) );
            header('Location: ' . $redirect, true, 301);
            exit;
        }
        return;
    }

}