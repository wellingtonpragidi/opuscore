<?php
try {
    if( URL::param(0) === 'access' ) {

        require DIR . 'web/access/index.php';
    }
    else {
        require DIR . 'web/init.php';

        $router->requires();
    } 

}
catch( OpusException $e ) {

    echo $e->error();
}

ob_end_flush();