<?php 
# define o ambiente
define( 'IS_WEB', false );
define( 'IS_DASHBOARD', true );

require dirname( __DIR__, 1 ) . '/config.php';


$container = Container::instance();
$admin     = $container->make('Admin');

if( $admin->logged_in() ) {

    require get_dashboard_path('motion.php');
	
    require_dashboard( DASH_DIR . 'view/index.php' );

}
else {

	$_SESSION["admin_redirect"] = URL::current();

	$redirect = dash_url( 'access/?act=login' );

	if( $admin->has_redirect() ) {
	    $redirect = dash_url( 'access/?redirect=' . $admin->redirect() );
	}

	header( 'Location: ' . $redirect );
	exit;

}

ob_end_flush();