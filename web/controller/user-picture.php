<?php
require dirname(__DIR__, 2) .'/config.php';

require dirname(__DIR__, 2) .'/web/invoke/io-setup.php';

require_web('invoke/sanitize-validate');
require_call('sanitize-validate');

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) :

    $bind = new Assign;
    $image = Container::call('Image');
    $user = Container::call('UserProfile');

    if( ! Validate::image_upload('upload') ) {
        return;
    }

    $ext = FILES::ext("upload");

    $image_name = Ensure::string( $_POST["imgname"] );
    $attachments = array(
        "medium" => date('Y/m/') . $image_name .'-sz'. user_md() .'.'. $ext,
        "small"  => date('Y/m/') . $image_name .'-sz'. user_sm() .'.'. $ext,
    );

    $bind->relatedID    = Ensure::int( $_POST["getid"] ); 
    $bind->relatedtype  = 'user'; 
    $bind->relatedtitle = Ensure::string( $_POST["imgtitle"] ); 
    $bind->date         = date('Y-m-d H:i:s');
    $bind->attachment   = json_encode( $attachments ); 
    $bind->ID           = Ensure::int( $_POST["getid"] );
    $bind->update       = date('Y-m-d H:i:s');

    # lastupdate:
    $userProfile->update_from_picture( $bind );

    if( $user->user_has_picture( $bind->ID ) > 0 ) {
        # se ja existir um registro atualize:
        $image->update_user( $bind );
    }
    else {
        # se nao existir um registro inserir:
        $image->insert_user( $bind );
    }

    image_handler(
        array(
            'input'    => 'upload',
            'path'     => null,
            'filename' => $image_name .'-sz'. user_md() .'.'. $ext,
            'width'    => user_md(),
            'height'   => user_md()
        ) 
    );
    image_handler(
        array(
            'input'    => 'upload',
            'path'     => null,
            'filename' => $image_name .'-sz'. user_sm() .'.'. $ext,
            'width'    => user_sm(),
            'height'   => user_sm()
        ) 
    );

    echo upload_url( date('Y/m/') . $image_name .'-sz'. user_md() .'.'. $ext );

endif;