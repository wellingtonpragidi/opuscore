<?php
defined( 'IS_WEB' ) || define( 'IS_WEB', false );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', true );

require str_replace( '\\', '/', dirname(__DIR__, 2) ) . '/config.php';


extract( Container::scope(), EXTR_SKIP );


require dashboard_path( 'access/header.php' );

switch( URL::Get('act') ) {
    case 'login':
        require dashboard_path( 'access/login.php' );
    break;
    case 'lost-password':
        require dashboard_path( 'access/lost-password.php' );
    break;
    case 'reset-password':
        require dashboard_path( 'access/reset-password.php' );
    break;
    case 'activation':
        require dashboard_path( 'access/activation.php' );
    break;
    default:
        header( 'Location: ' . dash_url('access/?act=login') );
    break;
}

require dashboard_path( 'access/footer.php' );


URL::normalize();