<?php
INPUT::method_request();

$action = $_POST['form_action'] ?? null;

if( $action === 'editor' ) {

    $editor_data = $_POST['editor'] ?? 'punk';

    if( ArrayExport::apply('options', ['editor' => $editor_data], 'settings') ) {
        alert_redirect( 'success', "Editor do sistema alterado.", URL::current() );
    } 
    else {
        alert('warning', "Falha o editor do sistema não foi alterado.");
    }
}


if( $action === 'statistics' ) {

    $statistics_data  = ( $_POST['action'] === 'true' );

    $message = $statistics_data ? 'habilitada' : 'desabilitada';

    if( ArrayExport::apply('options', ['statistics' => $statistics_data], 'settings') ) {
        alert_redirect( 'success', "Estatísticas {$message}.", URL::current() );
    } 
    else {
        alert('warning', "Estatísticas não {$message}.");
    }
}