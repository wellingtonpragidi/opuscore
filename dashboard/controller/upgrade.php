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

    if( $return == true ) {

        alert_redirect(
            "success discard alert-upgrade",
            "A atualização do sistema foi concluída com sucesso!",
            URL::current(), 12000, 6000
        );

        Upgrade::hidden_upgrade_content();

        $markers = "## start versao do sistema" . PHP_EOL .
                   "define('VERSION', '". $_POST['latest_version'] ."');" . PHP_EOL .
                   "## end version";

        $init_file = DIST_DIR . 'core/init.php';

        $markers = trim($markers);
        $current = file_get_contents($init_file);
        $regex = '/## start versao do sistema.*?## end version/s';

        if( preg_match($regex, $current) ) {
            $current = preg_replace($regex, $markers, $current);
            Ensure::writeLock( 
                $init_file, 
                $current, 
                Ensure::FILE_HANDLING_LOCK | Ensure::USE_REAL_FILEPATH
            );
        } 
        else {
            alert('error alert-upgrade', 'Os marcadores para a atualização da constant <code>VERSION</code> no arquivo <code>'. DIR .'/dist/core/init.php</code> foram modificados ou removidos.');
        }
    } 
    else if( is_string($return) ) {
        alert('warning', 'ERRO: <code> '. $return .'</code>');
    }
    else {
        alert('error discard alert-upgrade', 'O sistema não foi atualizado. Por favor, Tente novamente em alguns minutos.');
    }
}