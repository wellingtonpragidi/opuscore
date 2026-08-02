<?php
defined('ENTRY_GUARD') or die;

INPUT::method_request();



if( INPUT::action('delete') ) {

    $result = $media->delete( 
        INPUT::int('upload_id') ?: URL::int('id'), 
        INPUT::str('upload_type') 
    );


    if( $result['deleted_file'] ) {
        $deleted_file = '<p class="suc">Registro de mídia excluído.</p>';
    }
    else {
        $deleted_file = '<p class="err">Registro de mídia não excluído.</p>';
    }

    if( $result['deleted_record'] ) {
        $deleted_record = '<p class="suc">Arquivos físicos excluídos.</p>';
    }
    else {
        $deleted_record = '<p class="err">Arquivos físicos não excluídos.</p>';
    }

    $message = $deleted_record . $deleted_file;


    if( $result['deleted_file'] || $result['deleted_record'] ) {

        alert_redirect( 'success', $message, dash_url('medias') );
    }
    else {

        alert_redirect( 'warning', $message, URL::current() );
    }

}