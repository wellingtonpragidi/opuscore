<?php 
defined('ENTRY_GUARD') or die;
_POST::method_request();


$bind = new Assign;

$bind->ID    = URL::int('id') ?: INPUT::int('target_id');
$bind->title = INPUT::GET('title');

$articleslug = INPUT::GET('slug') ?: $bind->title;
# converte a entrada para slug independente de onde ela tenha vindo
$bind->slug = Ensure::slug($articleslug);

$bind->type  = INPUT::GET('target_type');

$bind->media->type = $bind->type; # para media 
$bind->created = date('Y-m-d H:i:s'); # para media e article 


if( $_POST['action'] === 'insert' ) {

    $bind->author = $auth->logged('name');
    $bind->status = 0;

    if( $article->insert($bind) ) {
        redirect( dash_url('articles/update/?id=' . $bind->LastID), 1000 );
        preloader(800);
    }
    else {
        alert_time('error w1024 mx', 'Ocorreu algum erro.');
        return;
    }
}



if( $_POST['action'] === 'update' ) {
    
    # validacao de duplicidade precisara agora ser feito pelo segment
    /*if( $article->exists($bind) ) {
        $attrClass = URL::has('id') ? 'warning' : 'warning w1024 mx';
        alert( $attrClass, 
            'Titulo duplicado, edite o <a href="' . URL::current('/#edit-slug') . '">Slug do article.</a>' 
        );
        return;
    }*/

    $bind->html->checked = INPUT::array('checkcat');

    # validacao obrigatoria
    if( empty($bind->html->checked) ) { 
        alert("error", "Selecione pelo menos uma categoria antes de publicar.");
        return;
    }

    # dados
    $bind->author  = INPUT::GET('author');
    $bind->content = Sanitize::editorContent('content');
    $bind->summary = INPUT::GET('summary');
    $bind->updated = date('Y-m-d H:i:s');
    $bind->status  = INPUT::int('status');
    if( $bind->status === 0 ) {
        $bind->segment = null;
    }
    else {
        $bind->segment = article_segment( $article, $bind );
    }

    $updated = false;

    # POST (dados principais)
    if( $article->update($bind) ) {
        $updated = true;
    }

    # RELACOES (categorias)
    if( $relation->synchronize($bind) ) {
        $updated = true;
    }


    # IMAGEM DESTACADA (registro e arquivos fisico)
    if( Validate::hasImageFeatured() ) {
        if( $image->exists($bind) ) {
            return;
        }

        foreach( ImageSize::dimensions($bind->type) as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "article-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        # IMAGEM DESTACADA (banco de dados)
        if( $image->insert($bind) ) {

            $updated = true;
        }
    }

    # pos-processo
    if( $updated ) {
        $image->update_title($bind);

        alert_redirect("success", "Artigo atualizado.", URL::current(), 1500);
    }
    else {
        alert("warning", "Nenhuma alteração foi feita.");
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




if( $_POST['action'] === 'delete' ) {

    $result = $image->delete($bind);

    $record  = $result['deleted_record'];
    $files   = $result['deleted_file'];

    $msg_deleted_img = delete_image_messages( $record, $files );

    if( $article->delete($bind) ) {

        $relation->delete_type( $bind );

        alert_redirect( 
            'success', 

            '<strong>Artigo excluído</strong>.' . $msg_deleted_img, 

            URL::has('id') ? dash_url('articles') : URL::current()
        );

        preloader();
    }
    else {
        alert( 
            'warning', '<strong>Ocorreu algum erro e o artigo não foi excluído.<strong>' 

            . $msg_deleted_img 
        );
        return;
    }
}