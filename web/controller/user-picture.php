<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

_POST::method_request();


$valid_image = Validate::imageUpload();

if( $valid_image['status'] === false ) {

    json_response([
        'status' => false,
        # => quando status eh false o JS nao altera o src da img com 'input'
        'alert'  => $valid_image['alert']
    ]);
}

 
# se ja existir um registro atualiza
if( $image->has_user_picture() ) {
    
    $image->user_update();
}
# se nao existir um registro insere
else {

    $image->user_insert();
}



foreach( ImageSize::dimensions('user') as $scope => $size ) {

    ImageHandler::resolve([
        'input'    => 'attachment',
        'path'     => date( 'Y/m/', strtotime($auth->logged()->created) ),
        'filename' => "user-{$auth->id()}-{$scope}",
        'width'    => $size['width'],
        'height'   => $size['height'],
        'quality'  => 85
    ]);

}


# sempre que algo muda, atualiza a timestamp da coluna `updated`
$user->update_lastupdate($auth);


    json_response([
        'status' => true,
        'input'  => $image->user_url('profile'), 
        'alert'  => null
    ]);