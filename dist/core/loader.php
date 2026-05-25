<?php
# DIST
spl_autoload_register( function( string $class ) {
    if( $class === 'GraphStatistic' ) {
        return;
    }

    static $_file = [];

    $filepaths = [
        DIST_DIR . "classes/{$class}.php",
        DIST_DIR . "classes/libs/{$class}.php", 
        DIST_DIR . "classes/libs/Mailer/{$class}.php",
    ];

    foreach( $filepaths as $filepath ) {
        # se sabemos que existe
        if( ! empty($_file[$filepath]) ) {
            require $filepath;
            return;
        }

        # nao cacheado, faz primeira verificacao
        if( is_file($filepath) ) {
            $_file[$filepath] = true;
            require $filepath;
            return;
        }
    }
});

# Inclui a classe GraphStatistic, Se Estatisticas estiver habilitada 
$data = Provider::settings('options');
if( $data['statistics'] ) {
    require DIST_DIR . "classes/GraphStatistic.php";
}


$is_php = fn($file) => $file->isFile() && $file->getExtension() === 'php';
$path   = fn($file) => str_replace( "\\", "/", $file->getRealPath() );


# inclui arquivos que nao contem classes
foreach( new DirectoryIterator( DIST_DIR ) as $file ) {
    if( $is_php($file) ) {
        require $path($file);
    }
}



/**
 * Definicao da constant de diretorio absoluto para template ativo, na raiz templates ou nao
 * Precisa ser definida aqui, antes de carregar dependencias de `dashboard/` e `web/` que dependem de TEMPLATE_PATH
 * 
 * @see https://opuscore.dev/constants/template_path
 */
$template = Container::call('TemplateManager');
define( 'TEMPLATE_PATH', $template->path() );



#  DASHBOARD   
if( defined('IS_DASHBOARD') && IS_DASHBOARD ) {
    spl_autoload_register( function( string $class ) {
        static $_file = [];

        $filepaths = [
            DASH_DIR . "classes/{$class}.php",
            DASH_DIR . "classes/model/{$class}.php",
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

    foreach( new DirectoryIterator( DASH_DIR . 'iterators/' ) as $file ) {
        if( $is_php($file) ) {
            require $path($file);
        }
    }
}



#  WEB  spl_autoload_register
if( defined('IS_WEB') && IS_WEB ) {
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

    foreach( new DirectoryIterator( WEB_DIR . 'iterators/' ) as $file ) {
        if( $is_php($file) ) {
            require $path($file);
        }
    }

}
