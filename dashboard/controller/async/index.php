<?php
defined( 'IS_WEB' ) || define( 'IS_WEB', false );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', true );

require str_replace( '\\', '/', dirname(__DIR__, 3) ) . '/config.php';


extract( Container::scope(), EXTR_SKIP );

if( ! $auth->is_logged() ) {
    http_response_code(403);
    exit('Forbidden');
}


function json_response( bool|string $success, ?string $message = null ): void {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}


annex_class('RouterAsync');
(new RouterAsync)->dispatch();