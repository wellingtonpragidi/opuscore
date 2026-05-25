<?php 
/**
 * Controlador responsavel por manipular acoes POST para posts,
 * como insercao, atualizacao, remocao de conteudo com imagens associadas e imagens.
 *
 * Este script lida com diferentes acoes com base no que eh enviado via POST:
 * - 'insert': Cria um novo post.
 * - 'update': Atualiza um post, incluindo o processamento de imagens.
 * - 'delete': Exclui um post ou e suas relacoes.
 * - 'unlink': Remove apenas as imagens associadas a um post.
 *
 * As validacoes de upload de imagem sao realizadas pelo metodo `Validate::image_upload()`
 * O processamento de imagens (redimensionamento e corte) eh concedido pela classe `ImageHandler`
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 */

INPUT::method_request();


$bind = new Assign;

$bind->ID    = URL::int('id') ?: INPUT::int('target_id');
$bind->title = INPUT::GET('title');

$strconv     = INPUT::GET('slug') ?: $bind->title;
$bind->slug  = Ensure::slug( $strconv );

$bind->type  = INPUT::GET('target_type') ?: singular(URL::param(0)) ?: 'post';


if( $_POST['action'] === 'insert' ) {

    $bind->author = $admin->logged_name();
    $bind->date   = date('Y-m-d H:i:s');
    $bind->status = 0;

    if( $post->insert($bind) ) {
        redirect( dash_url('posts/update/?id=' . $bind->LastID), 1500 );
        preloader(900);
    }
    else {
        alert_time('error w1024 mx', 'Ocorreu algum erro.');
    }
}


if( $_POST['action'] === 'update' ) {
    
    # validacao de duplicidade precisara agora ser feito pelo segment
    /*if( $post->exists($bind) ) {
        $attrClass = URL::has('id') ? 'warning' : 'warning w1024 mx';
        alert( $attrClass, 
            'Titulo duplicado, edite o <a href="' . URL::current('/#edit-slug') . '">Slug do post.</a>' 
        );
        return;
    }*/

    $bind->checked = INPUT::array('checkcat');

    # validacao obrigatoria
    if( empty($bind->checked) ) { 
        alert("error", "Selecione pelo menos uma categoria antes de publicar.");
        return;
    }

    # dados
    $bind->author  = INPUT::GET('author');
    $bind->content = Sanitize::editorContent('content');
    $bind->summary = INPUT::GET('summary');
    $bind->updated = date('Y-m-d H:i:s');
    $bind->status  = INPUT::int('status');

    $bind->segment = post_segment( $post, $bind );

    $updated = false;

    # POST (dados principais)
    if( $post->update($bind) ) {
        $updated = true;
    }

    # RELACOES (categorias)
    if( $relation->synchronize($bind) ) {
        $updated = true;
    }

    # IMAGEM DESTACADA (arquivo fisico)
    if( Validate::hasImageFeatured() ) {

        $ihandler = ImageHandler::sizes( $bind->type );

        foreach( $ihandler as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "post-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        # IMAGEM DESTACADA (banco de dados)
        if( $imanager->insert_page_image() ) {

            $updated = true;
        }
    }

    # pos-processo
    if( $updated ) {

        $imanager->update_title_page_image();

        alert_redirect("success", "Post atualizado.", URL::current(), 1500);
    }
    else {
        alert("warning", "Nenhuma alteração foi feita.");
    }
}



if( $_POST['action'] === 'delete' ) {

    delete_post( $imanager, $post, $relation, $bind );
}


if( $_POST['action'] === 'unlink' ) {

    unlink_entity_image( $imanager );
}