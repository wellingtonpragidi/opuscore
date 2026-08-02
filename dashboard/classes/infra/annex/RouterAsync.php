<?php
class RouterAsync {

    public function dispatch(): void {
        $route = strtolower( URL::GET('route') );

        $controllers = $this->mapper( DASH_DIR . 'controller/async/' );

        if( isset($controllers[$route]) ) {

            require_dashboard( $controllers[$route] );
        }
        else {
            http_response_code(404);
            exit;
        }
    }


    private function mapper( string $directory, string $route = '' ): array {   
        $list = [];

        foreach( new DirectoryIterator($directory) as $item ) {

            if( $item->isDot() ) {
                continue;
            }

            if( $item->isDir() ) {

                $route_stack =  $route . $item->getFilename() . '/';

                $list += $this->mapper( $item->getPathname(), $route_stack );

                continue;
            }

            if( $item->getFilename() === 'index.php' ) {
                continue;
            }

            # gera o caminho do arquivo dentro de /async 
            # esse caminho eh o valor da query string `?route=`
            # o caminho fica com barras no inicio e fim, ex.: `/async/?route=/settings/seo/`
            $path = '/' . strtolower($route . $item->getBasename('.php')) . '/';

            $filepath = str_replace('\\', '/', $item->getPathname());

            $list[$path] = $filepath;
        }

        return $list;
    }

}



/**
 * @example
                                               -------------------
xhr.open('POST', '/dashboard/controller/async/?route=/articles/upload/', true);
                                               -------------------
*/