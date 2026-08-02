<?php

spl_autoload_register( function( string $class ) {
    static $_file = [];

    $filepaths = [
        WEB_DIR . "classes/{$class}.php",
        WEB_DIR . "classes/model/{$class}.php",
    ];
    foreach( $filepaths as $filepath ) {
        if( ! empty($_file[$filepath]) ) {
            require $filepath;
            return;
        }

        if( is_file($filepath) ) {
            $_file[$filepath] = true;
            require $filepath;
            return;
        }
    }

});


foreach( new DirectoryIterator(WEB_DIR . 'iterators/') as $php ) {
    
    if( $isFile($php) ) {

        require $pathFile($php);
    }
}