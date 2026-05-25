<?php
declare( strict_types = 1 );

/**
 * arquivos so com funcoes condicionais de ambiente/contexto
 * 
 * 
 */


/**
 * @link https://opuscore.dev/functions/is_admin
 **/
function is_admin(): bool {
    static $_admin = false;
    
    if( empty($_admin) ) {
        require DASH_DIR . 'classes/model/Admin.php';
        $_admin = true;
    }

    $admin = Container::call('Admin');
    return $admin->logged_in();
}


/**
 * @link https://opuscore.dev/functions/is_home
*/
function is_home(): bool {
    $router = Container::call('Router');
    return $router->case('home');
}


/**
 * @link https://opuscore.dev/functions/is_query 
 **/
function is_query(): bool {
    $router = Container::call('Router');
    return $router->case('search');
}


/**
 * @link https://opuscore.dev/functions/is_home_or_query
*/
function is_home_or_query(): bool {
    return URL::param(0) === '' || ( URL::param(0) === '' && URL::GET('q') );
}


/**
 * Para criar condicional de todas as paginas ou uma unica pagina passado pelo slug da mesma
 * @param $slug | Cuidado ao usar! O slug pode nao ser o mesmo que o titulo da pagina sanitizado
 * @link https://opuscore.dev/functions/is_page
*/
function is_page( string $slug = '' ): bool {
    $router = Container::call('Router');
    $page_route = $router->case('page');

    # nao passou argumento slug - verifica na rota e retorna true em qualquer pagina
    if( $slug === '' ) {
        return $page_route;
    }


    # passou argumento slug - verifica na rota se eh uma pagina e slug em param 0 ou 1 batem

    # pagina sem parent
    if( URL::paramCount() === 1 ) {
        return $page_route && URL::param(0) === $slug;
    }
    # pagina com parent
    else {
        return $page_route && URL::param(1) === $slug;
    }
}


function is_policy_pages(): bool {
    $router = Container::call('Router');
    if( ! $router->case('page') ) {
        return false;
    }

    $policy_pages = ['politica-de-privacidade', 'termos-de-uso', 'privacy-policy', 'terms-of-use'];
    return 
        in_array( URL::param(0), $policy_pages, true ) || 
        in_array( URL::param(1), $policy_pages, true );
}


/**
 * @link https://opuscore.dev/functions/is_post
*/
function is_post( string $segment = '' ): bool {
    $router = Container::call('Router');
    $post_route  = $router->case('post');

    if( $segment === '' ) {
        return $post_route;
    }

    return $post_route && URL::pathname() === $segment;
}

/**
 * @link https://opuscore.dev/functions/is_posts
*/
function is_posts(): bool {
    $router = Container::call('Router');

    return $router->case('posts');
}

/**
 * Retorna true:
 * 1. Se "parametro [0] da URL", que corresponde ao slug apos dominio/ for identico a category_base()
 * 2. Se 1.(da lista acima) for true e "parametro $slug da passado" for identico ao ultimo "segmento (parametro) da URL"
 * @param $slug | slug da categoria atual ( que corresponde ao ultimo "parametro da URL" )
 * @link https://opuscore.dev/functions/is_category
 */
function is_category(): bool {
    $router = Container::call('Router');

    return $router->case('category');
}

/**
 * @link https://opuscore.dev/functions/is_categories
 */
function is_categories(): bool {
    $router = Container::call('Router');

    return $router->case('categories');
}



/**
 * @link https://opuscore.dev/functions/is_listing
*/
function is_listing(): bool {
    $router = Container::call('Router');
    return
        $router->case('posts')      ||
        $router->case('search')     ||
        $router->case('category');
}


/**
 * @link https://opuscore.dev/functions/is_404 
 **/
function is_404(): bool {
    $router = Container::call('Router');
    return $router->case('404');
}


/**
 * @link https://opuscore.dev/functions/is_user 
 **/
function is_user(): bool {
    $router = Container::call('Router');
    
    return $router->case('user');

    /*return router('user');
    return is('user');
    return Router::loc('user');
    return Router::$loc === 'user';
    return Router::$is_in === 'user';
    return Router::$in === 'user';
    return Router::$case === 'user';*/
}


function is_feed_async(): bool {
    return URL::has('context') && (URL::has('async') && URL::GET('async') === 'true');
}

function is_controller(): bool {
    # padrao GET do sistema
    if( URL::has('async') && URL::GET('async') === 'true' ) {
        return true;
    }
    
    # padrao Header do sistema
    if( isset($_SERVER['HTTP_X_OPUS_ASYNC']) ) {
        return true;
    } # headers: 'X-Opus-Async': 'true' || setRequestHeader('X-Opus-Async', 'true')

    # padrao XHR
    if( 
        ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
        &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) 
    { return true; }

    # padrao universal (XHR/fetch libs) com JSON
    if( 
        isset($_SERVER['HTTP_ACCEPT']) 
        && 
        str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
    ) 
    { return true; }

    # qualquer metodo POST
    if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        return true;
    }

    return false;
}


function is_async_request(): bool {
    return (
        (isset($_GET['async']) && $_GET['async'] === '1') ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
    );
}