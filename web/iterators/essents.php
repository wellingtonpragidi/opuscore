<?php 
declare( strict_types = 1 );

/** 
 * @see https://opuscore.dev/functions/get_template_path
 */
function template_path( string $extend ): string {
    return TEMPLATE_PATH . ltrim( $extend, '/' );
}
/** 
 * @deprecated use template_path
 */
function get_template_path( string $extend ): string {
    return TEMPLATE_PATH . ltrim( $extend, '/' );
}

/** 
 * @internal 
 * @return void | Acao `require`
 * @see https://opuscore.dev/variables/variaveis-de-escopo-do-template
 * @see https://int.opuscore.dev/web/require_template
 * */
function require_template( string $extend ): void {
    $container = Container::instance();
    $category  = $container->make('Category');
    $comment   = $container->make('Comment');
    $image     = $container->make('Image');
    $page      = $container->make('Page');
    $post      = $container->make('Post');
    $router    = $container->make('Router');
    $user      = $container->make('User');
    $auth      = $container->make('UserAuth');
    $profile   = $container->make('UserProfile');
    $status    = $container->make('UserStatus');

    require TEMPLATE_PATH . ltrim( $extend, '/' ) . '.php';
}


function web_path( string $extend ): string {
    return WEB_DIR . ltrim( $extend, '/' );
}
/** 
 * @deprecated use web_path
 */
function get_web_path( string $extend ): string {
    return WEB_DIR . ltrim( $extend, '/' );
}

function require_web( string $extend ): void {
    $container = Container::instance();
    $category  = $container->make('Category');
    $comment   = $container->make('Comment');
    $image     = $container->make('Image');
    $page      = $container->make('Page');
    $post      = $container->make('Post');
    $router    = $container->make('Router');
    $user      = $container->make('User');
    $auth      = $container->make('UserAuth');
    $profile   = $container->make('UserProfile');
    $status    = $container->make('UserStatus');

    require WEB_DIR . ltrim( $extend, '/' ) . '.php';
}


/**
 * @see https://opuscore.dev/functions/image_size_attrs
 * @deprecated use Image::dimensions_attrs
 * @deprecated use Image::dimensions_attrs
 * @deprecated use Image::dimensions_attrs
 * @deprecated use Image::dimensions_attrs
 **/
function image_size_attrs( string $imageURL ): string {
    if( empty($imageURL) ) {
        return '';
    }
    $imageInfo = '';
    $file_disk = replace_upload_url( $imageURL ?? '' );
    if( file_exists($file_disk) ) {
        $imageInfo = getimagesize( $imageURL ?? '' ) ?: [];
    }
    else {
        return '';
    }

    # isso contem os atributos width/height formatados como string
    return $imageInfo[3] ?? ''; 
}


/**
 * Sanitiza string para uso em atributos HTML, removendo tags e escapando caracteres especiais
 * @see https://opuscore.dev/functions/escattr
 */
function escattr( ?string $string ): string {
    return Ensure::attr( $string );
}
/**
 * @deprecated use escattr()
 
function attr( mixed $string ): string {
    return Ensure::attr( $string );
}*/


/**
 * @see https://opuscore.dev/functions/html_class
 * */
function html_class( string $prefix ): void {
    $router = Container::call('Router');
    echo $router->html_class($prefix);
}


/**
 * funcao para usar titulo de paginas fora do loop 
 * @see https://opuscore.dev/functions/master_title
 */
function master_title( array $args = [] ): void {
    if( is_query() || is_category() ) {
        return;
    }
    $router = Container::call('Router');

    $html_attrs = $args['attrs'] ?? ['id' => 'master-title'];

    $attrs = '';
    foreach( $html_attrs as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    $title = $args['title'] ?? $router->title() ?? null;

    echo "<h1{$attrs}>{$title}</h1>";
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

    $category = Container::call('Category');
    $title    = $category->name();

    echo "<h1{$attrs}>{$arg['prefix']}{$title}</h1>";
}


/**
 * @see https://opuscore.dev/functions/category_description
 * 
 * @todo atualizacao com quebra de compatibilidade
 */
function category_description( array $args = [] ): void {
    $category    = Container::call('Category');
    $description = $category->content();

    if( ! is_category() || empty($description) ) {
        return;
    }

    $tag = $args['tag'] ?? 'div';

    $attributes = $args['attrs'] ?? ['id' => 'category-description'];

    $attrs = '';
    foreach( $attributes as $key => $value ) {
        $attrs .= " {$key}=\"{$value}\"";
    }

    echo "<{$tag}{$attrs}>{$description}</{$tag}>";
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

    echo "<h1{$attrs}>{$arg['prefix']}{$search}</h1>";
}


/**
 * imprime get_user_picture_url()
 * @see https://opuscore.dev/functions/user_picture
 * 
 * @deprecated X
 **/
function user_picture() {}
/**
 * funcoes de imagem do usuario logado | retorna a imagem pequena
 * usado em comment-header e comment-form 
 * @see https://opuscore.dev/functions/get_user_picture_url
 * 
 * @deprecated X
 **/
function get_user_picture_url() {}


/**
 * exibe as categorias marcadas em uma publicacao 
 * @see https://opuscore.dev/functions/post_categories
 */
function post_categories( string $separator = ', ' ): string {
    $category = Container::call('Category');

    $rows = $category->post_cats();

    if( empty($rows) ) {
        return '';
    }

    $links = [];
    foreach( $rows as $row ) {
        $href = site_url( category_base() . '/' . $row['segment'] );

        $links[] = "<a href=\"{$href}\">{$row['name']}</a>";
    }

    return implode( $separator, $links );
}


/**
 * @see https://opuscore.dev/functions/post_count
 * */
function post_count( array $args = [] ): void {
    $post = Container::call('Post');
    echo $post->show_record($args);
}


/**
 * @see 
 **/
function posts_paginator(): void {
    $pagination = Container::call('Pagination');
    echo $pagination->post_paginator();
}


/**
 * @since 1.0.0
 * @changed 1.0.1 $limit nao he mais opcional
 * @todo Essa função ainda não funciona com a URL de posts hierarquica configurada
 * @see https://opuscore.dev/functions/posts_relateds
 **/
function posts_relateds( array $args = [] ): string {
    $post = Container::call('Post');

    $scope = $args['scope'] ?? 'minor';
    $h     = $args['item_title_tag'] ?? 'strong';
    $limit = (int) ($args['limit'] ?? 4);

    $itens = '';
    foreach( $post->relateds($limit) as $show ) {
        $filepath   = $show->attachment->{$scope}->path ?? null;
        $alt        = escattr($show->title);
        $dimensions = Image::dimensions_attrs($show->attachment->{$scope} ?? null);

        $image = '';
        if( $filepath ) {
            $imageurl = upload_url($filepath);
            $image = "<img src=\"{$imageurl}\" alt=\"{$alt}\" {$dimensions} />";
        }

        $itens .= "
        <li>
            <a href=\"{$show->URL}\">
                {$image}
                <{$h}>{$show->title}</{$h}>
            </a>
        </li>";
    }

    return "<ul>{$itens}</ul>";
}


function pages_find( string|array $slugs, string $scope = 'larger' ): array {
    $page = Container::call('Page');

    return $page->find( $slugs, $scope ); 
}


function site_logo( array $args = []  ): void {
    $filepath = $args['filepath'] ?? 'assets/img/logo.svg';
    $alt      = $args['alt'] ?? escattr( site_title() );
    $width    = Ensure::tryInt( $args['width'] ?? null );
    $height   = Ensure::tryInt( $args['height'] ?? null );
    
    $logo_url = template_url( $filepath );

    if( is_home() ) {
        $container['open']  = '<h1 id="logo">';
        $container['close'] = '</h1>';
    }
    else {
        $container['open'] = '<div id="logo"><a href="' . site_url() . '">';
        $container['close'] = '</a></div>';
    }

    echo 
    $container['open'] 
        
        . "<img src=\"{$logo_url}\" alt=\"{$alt}\" width=\"{$width}\" height=\"{$height}\" />"

    . $container['close'];
}


/**
 * @internal helper
 * @see https://int.opuscore.dev/output/calc_time
 **/
function calc_time( string $registered ): string {
    try {
        $origin = new DateTime( $registered );
        $target = new DateTime( date('Y-m-d') );
        
        # Verifica se a data eh valida e nao futura
        if( $origin > $target ) {
            return 'Data de registro inválida.';
        }
        
        $interval = $origin->diff($target);

        $year  = (int) $interval->format('%y');
        $month = (int) $interval->format('%m');
        $day   = (int) $interval->format('%d');
        
        if( $year === 0 && $month === 0 && $day === 0 ) {
            return 'Ingressado hoje.';
        }
        else if( $year === 0 && $month === 0 ) {
            return $interval->format('Membro há %d dias.');
        }
        else if( $year === 0 ) {
            return $interval->format('Membro há %m meses e %d dias.');
        }
        else {
            return $interval->format('Membro há %y anos, %m meses e %d dias.');
        }
        
    } 
    catch( Exception $e ) {
        return 'Data de registro inválida.';
    }
}




/**
 * ----------------- Context ----------------------
 */
function context( string $name ): void {
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
}
# ----------------- Context ----------------------




/**
 * @see https://opuscore.dev/functions/admin_edit
 */
function admin_edit( string $display = 'Editar' ): void {
    if( ! is_admin() ) {
        return;
    }

    if( is_page() ) {
        $page = Container::call('Page');

        $entity = 'pages';
        $id = $page->id(); 
    }
    else if( is_post() ) {
        $post = Container::call('Post');

        $entity = 'posts';
        $id = $post->id(); 
    }
    else if( is_listing() || is_async() ) {
        $entity = 'posts';
        $id = Seek::ID(); 
    }
    else {
        # se nao for pagina, post ou listagem nao tem ID pre referencia - exibe nada
        return;
    }

    $href = dash_url("{$entity}/update/?id={$id}");

    echo <<<HTML
    <div class="admin-edit">
        <a href="{$href}" target="_blank" rel="noopener">{$display}</a>
    </div>
    HTML;
}


function call_feed_async( array $args = [] ): void {
    require WEB_DIR . 'invoke/feed-async.php';

    Hook::append_action('feed_async', function() use ($args) {

        feed_async( $args );
    });
}
/**
 * @deprecated use call_feed_async() 
*/
function filter_feed_async( array $args = [] ): void {
    require WEB_DIR . 'invoke/feed-async.php';

    Hook::append_action('feed_async', function() use ($args) {
        feed_async( $args );
    });
} 


function icon( string $name, int $width = 20, int $height = 20 ): string {
    require get_web_path( 'invoke/icons.php' );

    return $svg;
}

function shares( array $args = [] ): string {
    require get_web_path( 'invoke/shares.php' );

    return $shares;
}


/**
 * @see https://int.opuscore.dev/hooks/comment_area-e-comment_area_loaded
 * @see https://opuscore.dev/functions/comment_area
 **/
function comment_area( 
    string $comment_title = '<h3 id="comment-title">Comentar</h3>' ): void {

    $container = Container::instance();
    $status    = $container->make('UserStatus');
    $comment   = $container->make('Comment');

    # exibe os comentarios quando que funcaoo eh chamada
    echo '<section id="comments">';
        echo $comment_title;
        require get_web_path('view/comment-header.php');
        require get_web_path('view/comment-form.php');
        require get_web_path('view/comments.php');
    echo '</section>';

    # O gancho aqui eh necessario para detectar se a funcao comment_area() foi chamada 
    # para adidionar comments.js nas paginas de post pela funcao foot() usando condicional: 
    # ( is_post() && Hook::has_action('comment_area_loaded') )
    Hook::append_action('comment_area_loaded', function() {
        # Closure vazia - soh importa que o hook exista
    });

    # chama imediatamente o hook de acao registrado acima
    Hook::call_action('comment_area_loaded');
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

    inline_script("
        const BASE_URL = '" . URL::root() . "';
        const TEMP_URL  = '" . template_url() . "/';

        const URL = '" . URL::root() . "';
        const TMP_URL  = '" . template_url() . "/';
    ");

    if( is_post() && Hook::has_action('comment_area_loaded') ) {
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

    echo "<script src=\"{$url}\"{$attributes}></script>" . PHP_EOL;
}
/**
 * @deprecated use append_script()
 **/
function add_script( string $path, string $version = '', string $attributes = '' ): void {
    $attrs = empty( $attributes ) ? '' : " {$attributes}";
    $v = empty( $version ) ? '' : "?v={$version}";
    $url = template_url( $path . $v );
    echo "<script src=\"{$url}\"{$attrs}></script>" . PHP_EOL;
}


/**
 * @see https://opuscore.dev/functions/import_script
 **/
function import_script( string $url, string $attrs = '' ): void {
    $attributes = empty( $attrs ) ? '' : " {$attrs}";

    echo "<script src=\"{$url}\"{$attributes}></script>" . PHP_EOL;
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