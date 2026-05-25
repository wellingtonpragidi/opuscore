<?php
if( file_exists(__DIR__ . '/.maintenance') ) {
    readfile( __DIR__ . '/maintenance.html' );
    exit;
}


define( 'IS_WEB', true );
define( 'IS_DASHBOARD', false );

require 'config.php';

require WEB_DIR . 'index.php';