<?php
declare( strict_types = 1 );


/**
 * @see https://opuscore.dev/site_logo
 * @usage ALL
 */
function site_logo( array $args = []  ): void {
    $filepath = $args['filepath'] ?? 'assets/img/logo.svg';
    $alt      = $args['alt'] ?? Ensure::attr( site_title() );

    $width    = (int) $args['width'] ?? null;
    $height   = (int) $args['height'] ?? null;
    
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
 * funcao para usar titulo fora do loop 
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
 * @see https://opuscore.dev/functions/search_title
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
 * @see https://opuscore.dev/functions/category_title
 */
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
 * @see https://opuscore.dev/functions/article_count
 * @usage index.php, articles.php, search.php, category.php
*/
function article_count( array $args = [] ): void {
    $article = Container::call('Article');

    echo $article->show_record($args);
}
