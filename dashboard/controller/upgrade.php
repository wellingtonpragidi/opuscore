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


        $start = '## start opuscore version';
        $end   = '## end opuscore version';

        $file_init = DIST_DIR . 'init.php';

        $current   = file_get_contents( $file_init );

        # Acha o bloco onde começa e onde termina
        $pos_start = strpos( $current, $start );
        $pos_end   = strpos( $current, $end );

        # Garante que os dois marcadores existem no arquivo
        if( $pos_start !== false && $pos_end !== false && $pos_end > $pos_start ) {
            
            # Monta o novo conteudo do bloco
            $new_block = 
                "\t" . $start . "\n" . 
                "\t" . "define( 'VERSION', '{$_POST['latest_version']}' );\n" . 
                "\t" . $end;

            # Descobre o tamanho total do bloco antigo (do inicio de $start ate o fim de $end)
            $length_replace = ($pos_end + strlen($end)) - $pos_start;

            # Substitui exatamente aquela fatia do texto no arquivo
            $current = substr_replace(
                $current, 
                $new_block, 
                $pos_start, 
                $length_replace
            );

            Ensure::writeLock( 
                $file_init, 
                $current, 
                Ensure::FILE_HANDLING_LOCK | Ensure::USE_REAL_FILEPATH
            );
        } 
        else {
            alert(
                'error alert-upgrade', 
                'Os marcadores para a atualização da constante <code>VERSION</code> no arquivo <code>' . $file_init . '</code> foram modificados ou removidos.'
            );
        }
        //-----------
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