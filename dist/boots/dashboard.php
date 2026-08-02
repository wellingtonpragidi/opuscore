<?php
require DASH_DIR . 'classes/infra/annex/Router.php';

spl_autoload_register( function( string $class ) {

    foreach( [
        DASH_DIR . 'classes/domain/' . $class . '.php',
        DASH_DIR . 'classes/infra/' . $class . '.php',
        DASH_DIR . 'classes/domain/auto/' . $class . '.php',
        DASH_DIR . 'classes/domain/model/' . $class . '.php',
    ] 
    as $filepath ) {

        if( is_file($filepath) ) {
            require $filepath;

            return;
        }
    }

});




foreach( 
    new DirectoryIterator(DASH_DIR . 'iterators/') as $php ) {

    if( $isFile($php) ) {

        require $pathFile($php);
    }
}