<?php
/**
 * @package System\Core
 */
class Router {

    /**
     * armazena o mapeamento de caminhos relativos para absolutos dos arquivos de view/
     * chave = caminho relativo sem extensao .php
     * valor = caminho absoluto completo
     * @access protected: "compartilha" propriedade com DashboardMenu
     */
    protected array $views = [];


    private string $path;

    # armazena titulos por regras personalizadas por segmento e segmento + query string
    private array $rewrite_title, $write_query_titles = [];

    # traduz subitens do dashboard menu
    protected array $rewrite_menu_label = [];


    private $opus = ' ⮞ Opus Core';


    public function __construct() {

        $this->path = DASH_DIR . 'view/';

        $this->views = $this->mapping();

        # importa arrays $page_titles e $query_titles
        require get_dashboard_path('callable/rewrite-rules.php');
        $this->rewrite_title      = $page_titles;
        $this->write_query_titles = $query_titles;
        $this->rewrite_menu_label = $menu_labels;
    }

    /**
     * escaneia todos os arquivos .php dentro do diretorio view/
     * e cria um array de mapeamento com chaves baseadas no caminho relativo
     */
    protected function mapping(): array {
        $list = [];
        $views = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach( $views as $view ) {
            if( $view->isDir()
                || $view->getExtension() !== 'php'
                || $view->getFilename() == 'index.php'
                || $view->getFilename() == '404.php' ) {
                continue;
            }
            $abs_path = str_replace( '\\', '/', $view->getRealPath() );
            $basename = str_replace( '\\', '/', $this->path );
            $relative = str_replace( $basename, '', $abs_path );
            $relative = ltrim( $relative, '/' );
            $key = strtolower( str_replace('.php', '', $relative) );
            $list[$key] = $abs_path;
        }

        return $list;
    }

    /**
     * inclui o arquivo correspondente com base na rota atual
     * se nao existir, inclui o 404
     */
    public function includes(): void {
        if( Hook::call_filter('run_dashboard', true) ) {

            $key = $this->param_key();

            if( isset($this->views[$key]) ) {
                if( URL::param(0) === 'home' ) {
                    header( 'Location: ' . dash_url() );
                    exit;
                }

                require_dashboard( $this->views[$key] );
            }
            else {

                require_dashboard( $this->path . '404.php' );
            }
        }
        else {

            Hook::call_action('dashboard_page');
        }

        $this->return_segment(2);
    }

    /**
     * retorna o titulo do cabecalho da pagina baseado na rota
     */
    public function master_title(): string {
        if( Hook::call_filter('run_dashboard', true) ) {

            $key = $this->param_key();

            $full_title = '';

            if( isset($this->views[$key]) ) {
                if( isset($this->rewrite_title[$key]) ) {

                    $full_title = $this->rewrite_title[$key];
                }
                else {

                    $full_title = $this->fallback_title();
                }
            }
            else {

                $full_title = $this->rewrite_title['404'];
            }

            # Aplica regras declaradas query string ex: (insert/update/etc)
            foreach( $this->write_query_titles as $rule ) {
                if( $rule['key'] === $key && URL::has($rule['query']) ) {

                    $full_title = $rule['value'];
                }
            }

            return $full_title;
        }
        else {
            
            Hook::call_action('dashboard_title');
        }

        return '';
    }

    # retorna o titulo formatado com o nome do site e dominio usado em title do HTML
    public function title_tag(): string {
        if( Hook::call_filter('run_dashboard', true) ) {

            $key = $this->param_key();

            $firstitle = '';

            if( isset($this->views[$key]) ) {
                if( isset($this->rewrite_title[$key]) ) {

                    $firstitle = $this->rewrite_title[$key];
                }
                else {

                    $firstitle = $this->fallback_title();
                }
            }
            else {

                $firstitle = $this->rewrite_title['404'];
            }

            $full_title = '';

            $byid = ' “' . Model::get_title_by_id() . '” – ';

            $build_title = function( $mode, $wrules = '' ) use ( $firstitle, $byid ) {
                switch( $mode ) {
                    case 'byid':
                        return $firstitle . $byid . site_title() . $this->opus;
                    break;
                    case 'default':
                        return $firstitle . ' – ' . site_title() . $this->opus;
                    break;
                    case 'wrules':
                        return $wrules . ' – ' . site_title() . $this->opus;
                    break;
                }
            };

            if( str_contains($key, URL::param(1)) && URL::has('id') ) {
                # Formato para edicao: "Titulo Base “Titulo edicao” › Titulo do Site — Dominio"
                $full_title = $build_title('byid');
            }
            else {
                # Formato padrao: "Titulo da Pagina › Titulo do Site — Dominio"
                $full_title = $build_title('default');
            }

            # Aplica regras declaradas query string ex: (insert/update/etc)
            foreach( $this->write_query_titles as $rule ) {
                if( $rule['key'] === $key && URL::has($rule['query']) ) {

                    $full_title = $build_title('wrules', $rule['value']);
                }
            }

            return $full_title;
        }
        else {

            Hook::call_action('dashboard_title');
        }

        return '';
    }


    /**
     * Gera o valor do atributo ID para o body baseado no ultimo parametro na URL
     */
    public function body_id() {
        if( Hook::call_filter('run_dashboard', true) ) {

            $key = $this->param_key();

            $id_value = '';

            if( isset($this->views[$key]) ) {

                $id_value = str_replace( '/', '-', $this->param_key() );
            }
            else {

                $id_value = 'not-found';
            }

            return "id=\"{$id_value}\"";
        }
        else {

            Hook::call_action('dashboard_body');
        }
    }


    # transforma os parametros da URL em chave usada para buscar o arquivo da rota
    protected function param_key(): string {
        $params = array_filter( URL::params(), fn($p) => strlen($p) );
        if( isset($params[0]) && $params[0] === 'dashboard' ) {
            array_shift( $params );
        }
        $slug = count($params) === 0 ? 'home' : implode('/', $params);
        
        return strtolower( $slug );
    }

    /**
     * gera um titulo alternativo com base no ultimo parametro
     * usado quando nao ha titulo personalizado
     */
    private function fallback_title(): string {
        $params = array_filter( URL::params(), fn($p) => strlen($p) );
        $last = count($params) === 0 ? 'home' : end( $params );
        return ucfirst( str_replace('-', ' ', $last) );
    }

    /**
     * remove segmento da URL e redireciona
     * usado para limpar URLs com segmentos desnecessarios
     */
    private function return_segment( $n ) {
        if( URL::param($n) ) {
            $redirect = str_replace( URL::param($n), "", URL::current() );
            $redirect = rtrim( $redirect, "/" );
            header('Location:' . $redirect);
            exit;
        }
    }

}