<?php 
declare( strict_types = 1 );


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
function html_class(): void {
    $value  = Router::selector_values();

    echo 'class="' . $value . '"';
}

/**
 * @see https://opuscore.dev/functions/html_id
 * */
function html_id( string $prefix ): void {
    $values   = Router::selector_values();
    $selector = explode( ' ', $values );
    $value    = $prefix . '-' . $selector[0];

    echo 'id="' . $value . '"';
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



/**
 * @see https://opuscore.dev/subsystems/feed-async
 */
function feed_async( array $args = [] ): void {
    Hook::append_action( 'feed_async', function() use ($args) {
        require annex_path('feed-async.php');
    });
}


/**
 * @see https://opuscore.dev/functions/icon
 */
function icon( string $name, int $width = 20, int $height = 20 ): string {
    require annex_path('icons.php');

    return $svg;
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
 */
function foot(): void {
    
    # if( Hook::call_filter('late_load_style', false) ) {}
    Stylesheets::late_block();

    Hook::call_action('priority_foot');

    block_script("
        const BASE_URL = '" . URL::root() . "';
        const TEMP_URL = '" . template_url() . "';
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
 * @see 
 * @usage index.php, articles.php, search.php, category.php
 */
function articles_paginator(): void {
    $pagination = Container::call('Pagination');

    echo $pagination->article_paginator();
}


/**
 * @see https://opuscore.dev/functions/pages_find
 * @usage ALL
 */
function pages_find( string|array $slugs ): array {
    $page = Container::call('Page');

    return $page->find( $slugs ); 
}
