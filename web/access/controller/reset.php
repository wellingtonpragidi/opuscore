<?php
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();

/**
 * @todo Adicionar verificacao de senha em 'pswd':
 * caso seja igual a senha atual interior fazer algo: 
 * ( alerta e/ou envio de email com a senha ... etc )
 */

if( $_POST['action'] === 'reset' ) {

    $password = INPUT::GET('pswd');

    if( empty($password) ) {
        alert('warning', 'Insira a senha!');
        return;
    }
    if( INPUT::empty('confirm-pswd') ) {
        alert('warning', 'Insira a confirmação da senha!');
        return;
    }

    if( requisite_password($password) === false ) {
        alert('warning', 'Senhas devem conter no minimo 8 caracteres com letras maiúsculas, minúsculas e números!');
        return;
    } 

    if( $password !== INPUT::GET('confirm-pswd') ) {
        alert('warning', 'As senhas não coincidem!');
        return;
    }


	$bind = new Assign;

	$bind->pswd    = password_hash( $password, PASSWORD_DEFAULT );
    $bind->token   = token_generator(42);
    $bind->updated = date('Y-m-d H:i:s');
    $bind->status  = 1;
    $bind->ID      = URL::int('id');

    if( $access->update_reset($bind) ) {
        alert( 'success', 'Senha atualizada: <a href="' . access_url('login') . '">Acessar</a>' );
    }
    else {
        alert( 'error', 'A senha não foi atualizada.<br> Tente novamente' );
    }
        
}
