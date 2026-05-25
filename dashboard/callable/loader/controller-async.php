<?php
defined( 'IS_WEB' ) || define( 'IS_WEB', false );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', true );

require dirname( __DIR__, 3 ) . '/config.php';

defined('ENTRY_GUARD') or die;


require_callable('sanitize-validate.php');


# helper menu async
function json_response( bool|string $success, ?string $message = null ): void {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}


error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
