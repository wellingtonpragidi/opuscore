<?php
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';

defined('ENTRY_GUARD') or die;


INPUT::method_request();


if( $_POST['action'] === 'refresh_cache' ) {

    header('Content-Type: text/html; charset=utf-8');
    ob_clean();

    if( Upgrade::refresh_cache('package.json') ) {

        Upgrade::refresh_cache( 'evidence.json' );
        Upgrade::refresh_cache( 'CHANGELOG.md' );

        $response = Upgrade::has()
            ? '<a href="' . INPUT::GET('href') . '">Atualização disponível</a>'
            : 'Nenhuma atualização disponível';

        echo "<div class='alert success mb15'>
            Cache atualizado com sucesso!
            <p>{$response}</p>
        </div>";
    } 
    else {
        echo "<div class='alert warning mb15'>Cache não atualizado.</div>";
        return;
    }
}