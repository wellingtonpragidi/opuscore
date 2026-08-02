<?php 
require 'init.php';

if( $auth->is_logged() ) {

    require dashboard_path( 'view/index.php' );

    ob_end_flush();
}
else {

    $auth->set_session_redirect( URL::current() );

    header( 'Location: ' . dash_url('access/?act=login') );

    ob_end_flush();
    exit;
}