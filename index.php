<?php
define( 'IS_WEB', true );
define( 'IS_DASHBOARD', false );

if( file_exists(__DIR__ . '/.maintenance') ) {
    readfile( __DIR__ . '/maintenance.html' );
    exit;
}

require 'config.php';

require WEB_DIR . 'index.php';