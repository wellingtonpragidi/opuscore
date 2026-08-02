<?php
defined('ENTRY_GUARD') or die;
INPUT::method_request();


if( ($_POST['action'] ?? null) === 'editor' ) {

    $editor_data = $_POST['editor'] ?? 'punk';

    if( ArrayExport::apply('options', ['editor' => $editor_data], 'settings') ) {
        alert_redirect( 'success', "Editor do sistema alterado.", URL::current() );
    } 
    else {
        alert('warning', "Falha o editor do sistema não foi alterado.");
    }
}
