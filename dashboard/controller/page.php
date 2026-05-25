<?php
/**
 * Controlador responsavel por manipular acoes POST para paginas do tipo page,
 * como insercao, atualizacao, remocao de conteudo com imagens associadas e imagens.
 *
 * Este script lida com diferentes acoes com base no que eh enviado via POST:
 * - 'insert': Cria uma novo pagina.
 * - 'update': Atualiza uma pagina, incluindo o processamento de imagens.
 * - 'delete': Exclui um pagina ou e suas relacoes.
 * - 'unlink': Remove apenas as imagens associadas a uma pagina.
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

$bind->ID      = URL::int('id') ?: INPUT::int('target_id');
$bind->title   = INPUT::GET('title');

$strconv       = INPUT::GET('slug') ?: $bind->title;
$bind->slug    = Ensure::slug( $strconv );

$bind->type    = INPUT::GET('target_type') ?: singular(URL::param(0)) ?: 'page';


if( $_POST['action'] === 'insert' ) {

    $bind->status = 0;

    if( $page->insert($bind) ) {
        redirect( dash_url('pages/update/?id=' . $bind->LastID), 1500 );
        preloader(900);
    }
    else {
        alert_time('error w1024 mx', 'Ocorreu algum erro.');
    }
}


if( $_POST['action'] === 'update' ) {

    if( $page->exists($bind) ) {
        $attrClass = URL::has('id') ? 'warning' : 'warning w1024 mx';
        alert( $attrClass, 
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
        foreach( ImageHandler::sizes($bind->type) as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "page-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        if( $imanager->insert_page_image() ) {

            $updated = true;
        }
    }

    if( $updated ) {

        $imanager->update_title_page_image();

        alert_redirect("success", "Página atualizado.", URL::current(), 1500);
    }
    else {
        alert("warning", "Nenhuma alteração foi feita");
    }
}


if( $_POST['action'] === 'delete' ) {

    delete_page( $imanager, $page, $bind );
}


if( $_POST['action'] === 'unlink' ) {

    unlink_entity_image( $imanager );
}