<?php
INPUT::method_request();


$bind = new Assign;

$bind->ID    = URL::int('id') ?: INPUT::int('target_id');
$bind->title = INPUT::GET('title');

$pageslug   = INPUT::GET('slug') ?: $bind->title;
$bind->slug = Ensure::slug($pageslug);

$bind->type  = INPUT::GET('target_type');

    # ambos sao para media 
    $bind->media->type = $bind->type;
    $bind->created = date('Y-m-d H:i:s');

if( $_POST['action'] === 'insert' ) {

    $bind->status = 0;

    if( $page->insert($bind) ) {
        redirect( dash_url('pages/update/?id=' . $bind->LastID), 1500 );
        preloader(900);
    }
    else {
        alert_time('error w1024 mx', 'Ocorreu algum erro.');
        return;
    }
}


if( $_POST['action'] === 'update' ) {

    if( $page->exists($bind) ) {
        alert( 'warning', 
            'Titulo duplicado, edite o <a href="' . URL::current('/#edit-slug') . '">Slug da pagina.</a>' 
        );

        return;
    }
    
    $bind->content  = Sanitize::editorContent('content');
    $bind->summary  = INPUT::GET('summary');
    $bind->parent   = INPUT::int('parent');
    $bind->template = INPUT::GET('template');
    $bind->lastmod  = date('Y-m-d H:i:s');
    $bind->status   = INPUT::int('status');

    $segment = $page->build_segment($bind);

    if( $segment['error'] ) {
        switch($segment['code']) {
            case 'empty_current_slug' :
                alert('error', 'Página atual sem slug válido');
            break;
            case 'empty_parent_slug' :
                alert('error', 'A página que você está vinculano como pai está sem slug válido');
            break;
        }

        return;
    }

    $bind->segment = $segment['data'];


    $updated = false;

    if( $page->update($bind) ) {

        $updated = true;
    }
    
    if( Validate::hasImageFeatured() ) {
        if( $image->exists($bind) ) {
            return;
        }
            
        foreach( ImageSize::dimensions($bind->type) as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "{$bind->type}-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        if( $image->insert($bind) ) {

            $updated = true;
        }
    }

    if( $updated ) {

        $image->update_title($bind);

        alert_redirect("success", "Página atualizado.", URL::current(), 1500);
    }
    else {
        alert("warning", "Nenhuma alteração foi feita");
        return;
    }
}



if( $_POST['action'] === 'delete' ) {
    $result = $image->delete($bind);

    $record = $result['deleted_record'];
    $files  = $result['deleted_file'];

    $msg_deleted_img = delete_image_messages( $record, $files );

    if( $page->delete($bind) ) {
        alert_redirect( 
            'success', 

            '<strong>Página excluída</strong>.' . $msg_deleted_img,

            URL::has('id') ? dash_url('articles') : URL::current()
        );

        preloader();
    }
    else {
        alert( 
            'warning',

            '<strong>Ocorreu algum erro e a página não foi excluído.</strong>' 
            . $msg_deleted_img 
        );

        return;
    }
}



if( $_POST['action'] === 'unlink' ) {

    $result = $image->delete($bind);

    $record = $result['deleted_record'];
    $files   = $result['deleted_file'];

    $alert_msg = delete_image_messages( $record, $files );

    if( $record || $files ) {

        alert_redirect( 'success', $alert_msg, URL::current() );
    }
    else {
        alert( 'warning', $alert_msg );
        return;
    }
}