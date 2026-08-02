<?php 
declare( strict_types = 1 );


/**
 * @see https://opuscore.dev/x @docx
 * @usage ALL
 */
function site_logo( array $args = []  ): void {
    $filepath = $args['filepath'] ?? 'assets/img/logo.svg';
    $alt      = $args['alt'] ?? escattr( site_title() );
    $width    = Ensure::tryInt( $args['width'] ?? null );
    $height   = Ensure::tryInt( $args['height'] ?? null );
    
    $logo_url = template_url( $filepath );

    if( is_home() ) {
        $open  = '<h1 id="logo">';
        $close = '</h1>';
    }
    else {
        $open = 
        '<div id="logo">
            <a href="' . URL::root() . '">';
        $close = 
            '</a>
        </div>';
    }

    echo $open; 

        echo '<img 
            src="' . $logo_url . '" 
            alt="' . $alt . '" 
            width="' . $width . '" 
            height="' . $height . '" 
        />';

    echo $close;
}


/**
 * funcao para usar titulo de paginas fora do loop 
 * @see https://opuscore.dev/functions/master_title
 */
function master_title( array $args = [] ): void {
    if( is_query() || is_category() ) {
        return;
    }

    $htmlAttrs = ($args['attrs'] ?? null) ?: ['id' => 'master-title'];

    $attrs = '';
    foreach( $htmlAttrs as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    $router = Container::instance()->make('Router');

    echo '<h1' . $attrs . '>' . $router->title() . '</h1>';
}


/**
 * @see https://opuscore.dev/functions/category_title
 * 
 * @todo atualizacao com quebra de compatibilidade
 * @changed :
 * # removido escolha de tag que já por padrao era: 'tag' => 'h1', passando exibir h1 direto
 **/
function category_title( array $args = [] ): void {
    if( ! is_category() ) {
        return;
    }

    $defaults = [
        'attrs'  => ['id' => 'master-title'],
        'prefix' => 'Categoria: ',
    ];

    $arg = array_merge( $defaults, $args );

    $attrs = '';
    foreach( $arg['attrs'] as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    $category = Container::instance()->make('Category');

    echo '<h1' . $attrs . '>' . $arg['prefix'] . $category->name() . '</h1>';
}


/**
 * @see https://opuscore.dev/functions/category_description
 * 
 * @todo atualizacao com quebra de compatibilidade
 */
function category_description( array $args = [] ): void {
    $dscpt = Container::instance()->make('Category')->content();

    if( ! is_category() || empty($dscpt) ) {
        return;
    }

    $tag = $args['tag'] ?? 'div';

    $attributes = $args['attrs'] ?? ['id' => 'category-description'];

    $attrs = '';
    foreach( $attributes as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    echo '<' . $tag . $attrs . '>' . $dscpt . '</' . $tag . '>';
}


/**
 * @see https://opuscore.dev/functions/search_title
 * 
 * @todo atualizacao com quebra de compatibilidade
 * @changed :
 * # removido escolha de tag que já por padrao era: 'tag' => 'h1', passando exibir h1 direto
 * # classe padrao do 'prefix' antes era query-prefix agora query-label
 */
function search_title( array $args = [] ): void {
    if( ! is_query() ) {
        return;
    }

    $defaults = [
        'attrs'  => ['id' => 'master-title'],
        'prefix' => '<span class="query-label">Resultados de busca para:</span> ',
    ];

    $arg = array_merge( $defaults, $args );

    $search = URL::GET('q');

    $attrs = '';
    foreach( $arg['attrs'] as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    if( strlen($search) < 3 ) {
        $msg = 'Digite pelo menos 3 caracteres para realizar a busca.';
        echo "<h1{$attrs}>{$msg}</h1>";
        search_form([
            'placeholder' => 'Faça uma nova busca',
            'btntext'     => 'Buscar',
            'btnclass'    => 'btn'
        ]);
        
        return;
    }

    $fulltitle = $arg['prefix'] . $search;

    echo '<h1' . $attrs . '>' . $fulltitle . '</h1>';
}


/**
 * @see https://opuscore.dev/functions/article_count
 * @usage index.php, articles.php, search.php, category.php
 * */
function article_count( array $args = [] ): void {
    $article = Container::call('Article');

    echo $article->show_record($args);
}

/**
 * @see 
 * @usage index.php, articles.php, search.php, category.php
 */
function articles_paginator(): void {
    $pagination = Container::call('Pagination');

    echo $pagination->article_paginator();
}



/**
 * @see 
 * @usage ALL
 */
function pages_find( string|array $slugs, string $scope = 'larger' ): array {
    $page = Container::call('Page');

    return $page->find( $slugs, $scope ); 
}




/**
 * Sanitiza string para uso em atributos HTML, removendo tags e escapando caracteres especiais
 * @see https://opuscore.dev/functions/escattr
 */
function escattr( ?string $string ): string {
    return Ensure::attr( $string );
}


/**
 * @see https://opuscore.dev/functions/html_class
 * */
function selector_value( string $prefix ): void {
    echo Router::selector_value($prefix);
}
function html_class( string $prefix ): void {
    echo Router::html_class($prefix);
}
function html_id( string $prefix ): void {
    echo Router::html_id($prefix);
}




/**
 * retorna a url da rota publica
 * @param $value | valor da query string 'action' 
 * @param $queries | sequencia de query(ies) strings, ex: '&chave=valor'
 */
function access_url( string $value, string $queries = '' ): string {
    $queries = ($queries === 'redirect') 
        ? '&redirect=' . URL::current() 
        : $queries;

    return URL::root('access/?action=' . $value . $queries);
}

/**
 * retorna a url fisica apontando diretamente para o diretorio
 * @param $extend | estender a url para subdiretorios e arquivos
 */
function web_access_url( string $extend = '' ): string {
    return URL::root( 'web/access/' . $extend );
}




/**
 * @see https://opuscore.dev/functions/admin_edit
 */
function admin_edit( string $display = 'Editar' ): void {
    if( ! is_admin() ) {
        return;
    }

    $container = Container::instance();

    if( is_page() ) {
        $page = $container->make('Page');

        $entity = 'pages';
        $id = $page->id(); 
    }
    else if( is_article() ) {
        $article = $container->make('Article');

        $entity = 'articles';
        $id = $article->target()->ID; 
    }
    else if( is_listing() || is_feed_async() ) {
        $entity = 'articles';
        $id = Seek::ID(); 
    }
    else {
        # se nao for pagina, article ou listagem nao tem ID pre referencia - exibe nada
        return;
    }

    $href = URL::root("dashboard/{$entity}/update/?id={$id}");

    echo <<<HTML
    <div class="admin-edit">
        <a href="{$href}" target="_blank" rel="noopener">{$display}</a>
    </div>
    HTML;
}



function feed_async( array $args = [] ): void {
    Hook::append_action( 'feed_async', function() use ($args) {
        require annex_path('feed-async.php');
    });
}



function icon( string $name, int $width = 20, int $height = 20 ): string {
    require annex_path('icons.php');

    return $svg;
}


function shares( array $args = [] ): string {
    require annex_path('shares.php');

    return $shares;
}



/**
 * @see https://opuscore.dev/functions/head
 **/
function head(): void {
    
    $signals = new Signals;
    echo $signals->routes();

    Hook::call_action('document_signals');


    Hook::call_action('priority_head');


    Stylesheets::block();

    Stylesheets::linked();


    Hook::call_action('head');
}

/**
 * @see https://opuscore.dev/functions/foot
 * 
 * @deprecated const URL | use → BASE_URL
 **/
function foot(): void {
    
    # if( Hook::call_filter('late_load_style', false) ) {}
    Stylesheets::late_block();

    Hook::call_action('priority_foot');

    block_script("
        const BASE_URL = '" . URL::root() . "';
        const TEMP_URL = '" . template_url() . "/';
    ");

    if( is_article() && has_resource('comment_area') ) {
        import_script( URL::root('web/assets/js/comments.js') );
    }

    if( is_user() ) {
        import_script( URL::root('web/assets/js/profile.js') );
    }

    if( function_exists('template_scripts') ) {
        template_scripts();
    }

    Hook::call_action_once('feed_async');

    Hook::call_action('foot');
}


/**
 * @see https://opuscore.dev/functions/append_script
 **/
function append_script( string $path, string $v = '', string $attrs = '' ): void {
    $attributes = empty($attrs) ? '' : " {$attrs}";
    $version = empty($v) ? '' : "?v={$v}";
    $url = template_url( $path . $version );

    echo "<script src=\"{$url}\"{$attributes}></script>\n";
}
/**
 * @deprecated use append_script()
 **/
function add_script( string $path, string $version = '', string $attributes = '' ): void {
    $attrs = empty( $attributes ) ? '' : " {$attributes}";
    $v = empty( $version ) ? '' : "?v={$version}";
    $url = template_url( $path . $v );
    echo "<script src=\"{$url}\"{$attrs}></script>\n";
}


/**
 * @see https://opuscore.dev/functions/import_script
 **/
function import_script( string $url, string $attrs = '' ): void {
    $attributes = empty( $attrs ) ? '' : " {$attrs}";

    echo "<script src=\"{$url}\"{$attributes}></script>\n";
}


/**
 * @see https://opuscore.dev/functions/block_script
 **/
function block_script( string $script ): void {
    $script = trim($script);
    $lines  = preg_split('/\R/', $script);
    $indent = 0;
    $out    = [];
    foreach( $lines as $line ) {
        $line = trim($line);

        if( $line === '' ) {
            continue;
        }

        # diminui indentacao antes se linha comeca com }
        if( $line[0] === '}' ) {
            $indent--;
        }

        $out[] = str_repeat( '    ', max(0, $indent) ) . $line;

        # aumenta indentacao depois se linha termina com {
        if( substr($line, -1) === '{' ) {
            $indent++;
        }
    }

    echo "<script>\n" . implode( "\n", $out ) . "\n</script>\n";
}
/**
 * @deprecated use block_script()
 **/
function inline_script( string $script ): void {
    block_script( $script );
}




function block_style( string $css ): void {
    $content = file_get_contents($css);

    echo "<style>\n" . compress_CSS($content) . "\n</style>\n";
}
/**
 * @deprecated use block_style()
 **/
function inline_style( string $css ): void {
    block_style( $css );
    
}

function append_style( string $path, string $v = '', string $attrs = '' ): void {
    $attributes = empty($attrs) ? '' : " {$attrs}";
    $version    = ($v === '') ? '' : "?v={$v}";
    $url        = template_url( $path . $v );

    echo "<link rel=\"stylesheet\" href=\"{$url}{$version}\"{$attributes} />\n";
}

function import_style( string $url, string $attrs = '' ): void {
    
    echo "<link rel=\"stylesheet\" href=\"{$url}\" {$attrs} />\n";
}





function resources( string $action, string $key ): bool {
    static $store = [];

    if( $action === 'append' ) {
        $store[$key] = true;
        return true;
    }

    if( $action === 'has' ) {
        return isset($store[$key]);
    }

    return false;
}

function append_resource( string $key ): void {
    resources('append', $key);
}

function has_resource( string $key ): bool {
    return resources('has', $key);
}





/**
 * ----------------- Context ----------------------
 * 
 * @deprecated use os metodos estaticos da classe Context
 */
/*function context( string $name ): void {
    echo Context::value($name);
}
function get_context( string $name ): string {
    return Context::value($name);
}

function context_title( string $name ): void {
    echo Context::title($name);
}
function get_context_title( string $name ): string {
    return Context::title($name);
}

function context_content( string $name ): void {
    echo Context::value($name);
}

function context_section( string $name ): void {
    echo Context::section($name);
}
function get_context_section( string $name ): string {
    return Context::section($name);
}

function get_context_basename( string $name ): string {
    return Context::basename($name);
}
function get_context_filename( string $name ): string {
    return Context::filename($name);
}
function get_context_filepath( string $name ): string {
    return Context::filepath($name);
}*/
# ----------------- Context ----------------------

