<?php
defined('ENTRY_GUARD') or die;

INPUT::method_request();


if( $_POST['action'] === 'upgrade' ) {

    if( defined('NOT_UPGRADE') || Upgrade::is_internal_domain() ) {
        alert( "error", 
            "<p>Este domínio é interno ou optou por não ser atualizado.</p>" 
        ); 

        exit;
    }

    $return = Upgrade::update_system( $_POST['zip_filename'] );

    if( $return === true ) {

        alert_redirect(
            "success discard alert-upgrade",
            "A atualização do sistema foi concluída com sucesso!",
            URL::current(), 15000
        );

        Upgrade::hidden_upgrade_content();

    }
    else if( is_string($return) ) {

        alert('warning', 'ERRO: <code> '. $return .'</code>');
    }
    else {
        alert(
            'error discard alert-upgrade', 
            'O sistema não foi atualizado. Por favor, Tente novamente em alguns minutos.'
        );
    }
}