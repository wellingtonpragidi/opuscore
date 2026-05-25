<?php
INPUT::method_request();

if( $_POST['action'] === 'favicon' ) {

    if( ! FILES::mime('favicon', 'image/png') ) {
        alert('warning discard', 'Esse arquivo de imagem precisa ser um <strong>PNG</strong>');
        return;
    }

    $favicons = [
        'pwa-512' => ['width' => 512, 'height' => 512],
        'pwa-192' => ['width' => 192, 'height' => 192],
        'apple'   => ['width' => 180, 'height' => 180],
        'firefox' => ['width' => 144, 'height' => 144], # ms legado | firefox atalhos
        'icon-96' => ['width' => 96,  'height' => 96],
        'icon-48' => ['width' => 48,  'height' => 48],
        'icon-32' => ['width' => 32,  'height' => 32],
        'icon-16' => ['width' => 16,  'height' => 16],
    ];
    foreach( $favicons as $scope => $size ) {
        ImageHandler::resolve([
            'input'    => 'favicon',
            'filename' => "{$size['width']}x{$size['height']}", // png
            'width'    => $size['width'],
            'height'   => $size['height'],
            'path'     => UPLOAD_DIR . 'favicons/'
        ]);
    }


    $ico = new ConvertIco( 'favicon', [32, 32] );
    $ico->save( DIR . 'favicon.ico' );


    if( file_exists(UPLOAD_DIR . 'favicons/192x192.png') ) {

        require_callable('manifest-json.php');
    }
}


if( $_POST['action'] === 'poster' ) {

    if( ! FILES::mime('upload', 'image/png') ) {
        alert('warning discard', 'Esse arquivo de imagem precisa ser um <strong>PNG</strong>');
        return;
    }

    Media::save_file( 
        'upload', 
        'poster', 
        UPLOAD_DIR . 'favicons/' 
    );
}