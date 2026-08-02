<?php
defined( 'IS_WEB' ) || define( 'IS_WEB', true );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', false );

require str_replace( '\\', '/', dirname(__DIR__, 2) ) . '/config.php';


extract( Container::scope(), EXTR_SKIP );


function route_404() {
    http_response_code( 404 );
    exit;
}


function json_response( array $args = [] ): void {
    header('Content-Type: application/json');

    echo json_encode([
        'status' =>  (bool)  ($args['status'] ?? false),
        'input'  => (string) ($args['input'] ?? ''),
        'alert'  => (string) ($args['alert'] ?? ''),
    ]);

    exit;
}


# Ex caminho url : `https:://domain.ext/web/controller/?route=/settings/`
$route = $_GET['route'] ?? null;

if( $route === '/feed-async/' ) {
    INPUT::method_request();
    
    ob_clean();

    header( 'Content-Type: application/json' );
    

    $file_location = URL::has('req') ? URL::GET('req') : 'feed-async';
    require TEMPLATE_PATH . $file_location . '.php';
}

else {

    $basename = match( $route ) {
        '/comment-area/'  => 'comment',
        '/user-picture/'  => 'user-picture',
        '/user-settings/' => 'user-settings',
        default => route_404(),
    };

    require WEB_DIR . 'controller/' . $basename . '.php';
}
