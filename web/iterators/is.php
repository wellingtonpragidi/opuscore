<?php
declare( strict_types = 1 );

/**
 * arquivos so com funcoes condicionais 
 * 
 * autenticacoes
 * rotas
 * assincrono
 * 
 * 
 */


 # -------------------------------------------------------------------------- 
 #  AUTENTICACOES :
 # --------------------------------------------------------------------------

/**
 * # administrador logado e autenticado
 * @link https://opuscore.dev/functions/is_admin
 **/
function is_admin(): bool {
    $container = Container::instance();
    $auth = $container->make('Auth');
    
    return $auth->is_admin_valid();
}


# usuario logado e autenticado
function is_user_logged(): bool {
    $auth = Container::call('Auth');

    return $auth->is_logged();
}

# usuario logado, autenticado e em sua propria view
function is_user_self(): bool {
    $auth = Container::call('Auth');

    return $auth->is_self();
}



 # -------------------------------------------------------------------------- 
 #  ROTAS :
 # --------------------------------------------------------------------------

/**
 * @link https://opuscore.dev/functions/is_user 
 **/
function is_user(): bool {
    return Router::case() === 'user';
}


/**
 * @link https://opuscore.dev/functions/is_home
*/
function is_home(): bool {
    return Router::case() === 'home';
}


/**
 * @link https://opuscore.dev/functions/is_query 
 **/
function is_query(): bool {
    $router_case = Router::case();
    return $router_case === 'search' || $router_case === 'query';
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
    $is_router_case = Router::case() === 'page';

    # nao passou argumento slug - verifica na rota e retorna true em qualquer pagina
    if( $slug === '' ) {
        return $is_router_case;
    }


    # passou argumento slug - verifica na rota se eh uma pagina e slug em param 0 ou 1 batem

    # pagina sem parent
    if( URL::paramCount() === 1 ) {
        return $is_router_case && URL::param(0) === $slug;
    }
    # pagina com parent
    else {
        return $is_router_case && URL::param(1) === $slug;
    }
}


function is_policy_pages(): bool {
    if( Router::case() !== 'page' ) {
        return false;
    }

    $pages_legal = [
        'politica-de-privacidade', 'termos-de-uso',  # PT 
        'privacy-policy', 'terms-of-use',            # EN
        'politica-de-privacidad', 'terminos-de-uso', # ES
    ];

    return 
        in_array( URL::param(0), $pages_legal, true ) || 
        in_array( URL::param(1), $pages_legal, true );
}


/**
 * @link https://opuscore.dev/functions/is_article
*/
function is_article( string $segment = '' ): bool {
    $is_router_case = Router::case() === 'article';

    if( $segment === '' ) {
        return $is_router_case;
    }

    return $is_router_case && URL::pathname() === $segment;
}

/**
 * @link https://opuscore.dev/functions/is_articles
*/
function is_articles(): bool {
    return Router::case() === 'articles';
}

/**
 * Retorna true:
 * 1. Se "parametro [0] da URL", que corresponde ao slug apos dominio/ for identico a category_base()
 * 2. Se 1.(da lista acima) for true e "parametro $slug da passado" for identico ao ultimo "segmento (parametro) da URL"
 * @param $slug | slug da categoria atual ( que corresponde ao ultimo "parametro da URL" )
 * @link https://opuscore.dev/functions/is_category
 */
function is_category(): bool {
    return Router::case() === 'category';
}

/**
 * @link https://opuscore.dev/functions/is_categories
 */
function is_categories(): bool {
    return Router::case() === 'categories';
}



/**
 * @link https://opuscore.dev/functions/is_listing
*/
function is_listing(): bool {
    return Router::is_articles_list();
}


/**
 * @link https://opuscore.dev/functions/is_404 
 **/
function is_404(): bool {
    return Router::case() === '404';
}




 # -------------------------------------------------------------------------- 
 #  ASSINCRONOS :
 # --------------------------------------------------------------------------

function is_feed_async(): bool {
    return URL::GET('route') === '/feed-async/' && URL::has('req') && URL::has('src');
}

function is_controller(): bool {
    # padrao GET do sistema
    if( URL::GET('route') === '/feed-async/' && URL::has('req') && URL::has('src') ) {
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
        ( URL::GET('route') === '/feed-async/' && URL::has('req') && URL::has('src') ) || ( 
            
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && 
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' 
        )
    );
}