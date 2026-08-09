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
    private Article $article;
    private Page $page;

    private string $basename = '';

    private string $title = ''; 
    private string $title_hook = '';


    private static string $selector_value = '';

    private static string $case = '';

    private static bool $is_articles_list = false;


    private ?bool $has_home = null;

    private bool $resolved = false;


    public function __construct( Category $category, Article $article, Page $page ) { 
        $this->category = $category;
        $this->article     = $article;
        $this->page     = $page;

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


        if( URL::param(0) === 'access' ) {
            return;
        }


        if( URL::param(0) === 'home-page' ) {
            header('Location: ' . URL::root(), true, 301);
            exit;
        }


        # Quando existe home-page.php, o index.php passa a representar a listagem de articles.
        # Caso contrario, index.php eh a home e articles nao existe ( o site nao utiliza blog )

        # HOME:
        # Na condicao dessa rota, o template sempre precisara ter uma pagina inicial 
        #  que nao seja listagem de articles se quiser usar o loop `Seek`

        # Home sempre permanece 'home' conceitualmente.
        # Se nao existir home-page.php, usa index.php como fallback 
        # mas continua sendo HOME (titulo, contexto, etc).

        $this->has_home = $this->has_home ?? file_exists( template_path('home-page.php') );

        # POSTS:
        # `/articles` sempre representa listagem de articles.
        # Se nao houver home-page.php, /articles_base() | is_articles() == nao existe

        if( $this->is_home() || $this->is_articles() ) {

            # Se ( URL === `/articles` ) E o template Nao tem o arquivo home-page.php
            # a pagina inicial usa o arquivo index.php, 
            # neste caso concluimos que: o site nao tem blog OU usa a pagina inicial como listagem
            # E entao `/articles` redireciona para `/`evitando duplicacao de conteúdo
            if( $this->is_articles() && ! $this->has_home ) {

                self::$is_articles_list = true;

                //header( "Location: " . URL::root(), true, 301 );
                //exit;
            }

            # HOME sempre e HOME independente do arquivo usado
            if( $this->is_home() ) {
                self::$case       = 'home';
                $this->title      = site_title();
                $this->title_hook = 'home_title';

                if( $this->has_home ) {
                    $this->basename = 'home-page';
                    self::$selector_value = 'home-page';
                }
                else {
                    # fallback visual
                    $this->basename   = 'index';
                    self::$selector_value = articles_base();

                    # se home-page nao existe a pagina inicial pode ser listagem
                    self::$is_articles_list = true;
                }
            }
            # soh is_articles()
            else {
                self::$case       = 'articles';
                $this->basename   = 'index';
                $this->title      = 'Artigos';
                $this->title_hook = 'articles_title';
                self::$selector_value = articles_base();

                self::$is_articles_list = true;
            }
        }

        else if( $this->is_query() ) {
            if( file_exists(template_path('search.php')) ) {
                $this->basename = 'search';
            }
            else {
                $this->basename = 'index';
            }
            self::$case       = 'search';
            $this->title      = 'Resultados de busca para: ' . URL::GET('q');
            $this->title_hook = 'query_title';
            self::$selector_value = articles_base() . ' search';

            self::$is_articles_list = true;
        }

        else if( $this->is_categories() ) {
            if( file_exists( template_path('categories.php') ) ) {
                self::$case       = 'categories';
                $this->basename   = 'categories';
                $this->title      = 'Categorias';
                $this->title_hook = 'categories_title';
                self::$selector_value = 'categories';
            }
            else {
                $this->show_404();
            }
        }

        else if( $this->is_category() ) {
            if( file_exists( template_path('category.php') ) ) {
                $this->basename = 'category';
            }
            else {
                $this->basename = 'index';
            }
            self::$case       = 'category';
            $this->title      = $this->category->name();
            $this->title_hook = 'category_title';
            
            $pathname = $this->category->pathname();
            $list = str_replace( '/', ' ', $pathname );
            self::$selector_value = articles_base() . ' articles-category ' . category_base(); 

            self::$is_articles_list = true;
        }

        # article pagina unica com URL padrao ou personalizado
        else if( $this->is_article() ) {
            self::$case       = 'article';
            $this->basename   = 'article';
            $this->title      = $this->article->target()->title;
            $this->title_hook = 'article_title';
            self::$selector_value = "article article-{$this->article->target()->ID}";
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
            self::$case       = 'page';
            $this->title      = $this->page->title();
            $this->title_hook = 'page_title';
            self::$selector_value    = "page page-{$page_slug}";
        }

        else if( $this->is_user() ) {
            if(  ! file_exists( template_path('user.php') )  ) {

                if( DISPLAY_ERRORS ) {

                    throw new OpusException(
                        'Template sem suporte para view de usuários.', 'error', 404
                    );
                }
                else {

                    header('Location: ' . URL::root(), true, 301);
                    exit;
                }
            }
            self::$case       = 'user';
            $this->basename   = 'user';
            $this->title      =  User::name();
            $this->title_hook = 'user_title';
            self::$selector_value = 'user-profile ' . user_base();
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


    public static function case(): string {
        return self::$case;
    }


    public static function is_articles_list(): bool {
        return self::$is_articles_list;
    }


    public function title( ?string $rule = null ): string {
        if( $this->title_hook !== '' && Hook::has_filter($this->title_hook) ) {

            $title = Hook::call_filter( $this->title_hook, $this->title, $rule );
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
        extract( Container::scope(), EXTR_SKIP );

        require TEMPLATE_PATH . 'header.php';

        require TEMPLATE_PATH . $this->basename . '.php';

        require TEMPLATE_PATH . 'footer.php';
        
        if( statistics() ) {
            $this->statistic();
        }
    }


    public static function selector_values(): string {
        
        return self::$selector_value;
    }



    private function show_404(): void {
        if( URL::param(0) === 'access' ) {
            return;
        }
        if( ! file_exists( template_path('404.php') ) ) {
            throw new OpusException('Página não encontrada', 'error', 404);
        }
        self::$case           = '404';
        $this->basename       = '404';
        $this->title          = 'Página não encontrada';
        $this->title_hook     = '404_title';
        self::$selector_value = 'not-found error-404';
        http_response_code(404);
    }


    # returna o numero de registros inseridos
    private function statistic(): ?int {
        $invalid_GET = array_diff( array_keys($_GET), ['q', 'pg'] );

        // if( self::$case && empty($invalid_GET) ) {
        if( empty($invalid_GET) ) {
            $stats = Statistic::instance();
            return (int) $stats->insert( $this->title );
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

    private function is_article(): bool {
        if( URL::param(0) === '' ) {
            return false;
        }

        return URL::pathname() === $this->article->target()->segment;
    }

    private function is_articles(): bool {
        return URL::pathname() === articles_base();
    }

    private function is_category(): bool {
        return URL::pathname() === $this->category->pathname();
    }

    private function is_categories(): bool {
        return URL::pathname() === category_base();
    }

    private function is_404(): bool {
        $httpCode = http_response_code();

        return URL::param(0) === '404' || ( $httpCode !== false && $httpCode === 404 );
    }

    private function is_user(): bool {
        $username = User::username();
        
        if( ! $username ) {
            return false;
        }

        return URL::pathname() === user_base() . '/' . $username;
    }



    /**
     * Remove parametro da URL atual e redireciona para URL limpa
     * @example [
     *    - Uma URL de article ou page: example.com/page-title | param = 0
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