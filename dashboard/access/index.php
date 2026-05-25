<?php
defined( 'IS_WEB' ) || define( 'IS_WEB', false );
defined( 'IS_DASHBOARD' ) || define( 'IS_DASHBOARD', true );

require dirname(__DIR__, 2) .'/config.php';

require get_dashboard_path( 'access/header.php' );

$conn = Container::call('Connection');

switch( URL::Get('act') ) :
    case 'login':
        require get_dashboard_path( 'access/login.php' );
    break;
    case 'lost-password':
        require get_dashboard_path( 'access/lost-password.php' );
    break;
    case 'reset-password':
        require get_dashboard_path( 'access/reset-password.php' );
    break;
    case 'activation':
        require get_dashboard_path( 'access/activation.php' );
    break;
    default:
        header( 'Location: ' . dash_url('access/?act=login') );
    break;
endswitch;

require get_dashboard_path( 'access/footer.php' );