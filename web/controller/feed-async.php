<?php 
defined( 'IS_WEB' ) || define( 'IS_WEB', true );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', false );

require dirname(__DIR__, 2) . '/config.php';


defined( 'ENTRY_GUARD' ) or die;


define( 'FEED_ASYNC', true );


INPUT::method_request();



function end_rows( ?string $message = null ): string {
    $text = $message ?? 'Parece que você chegou ao fim.';
    
    return "<p class=\"loadmore-end\">{$text}</p>";
}


ob_clean();

header( 'Content-Type: application/json' );