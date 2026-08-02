<?php
# DIST -------------------------------------------
spl_autoload_register( function( string $class ) {
    if( $class === 'Statistic' ) {
        return;
    }

    $filepaths = [
        DIST_DIR . "classes/{$class}.php",
        DIST_DIR . "classes/libs/{$class}.php", 
        DIST_DIR . "classes/libs/Mailer/{$class}.php",
    ];

    foreach( $filepaths as $filepath ) {
        if( is_file($filepath) ) {
            require $filepath;

            return;
        }
    }
});


# Inclui a classe Statistic, se estiver habilitada 
$data = Provider::settings('options');
if( $data['statistics'] ) {
    require DIST_DIR . "classes/Statistic.php";
}



# inclui arquivos que nao contem classes
foreach( new DirectoryIterator(DIST_DIR . 'iterators/') as $php ) {

    if( $isFile($php) ) {

        require $pathFile($php);
    }
}


class_alias( 'INPUT', '_POST' );
class_alias( 'INPUT', 'PST' );
class_alias( 'URL', 'GET' );
class_alias( 'FILES', 'FIL' );
