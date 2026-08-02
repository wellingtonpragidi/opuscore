<?php
defined( 'ENTRY_GUARD' ) or die;
INPUT::method_request();


if( $_POST['action'] === 'activate' ) {

    $password = INPUT::str('pswd');

    if( empty($password) ) {
        alert('warning', 'Insira a senha!');
        return;
    }

    if( requisite_password($password) === false ) {
        alert('warning', 'Senhas devem conter no minimo 8 caracteres com letras maiúsculas, minúsculas e números!');
        return;
    } 

    $bind = new Assign;

    $bind->pswd   = password_hash( $password, PASSWORD_DEFAULT );
	$bind->token  = token_generator(42);
	$bind->status = 1;
	$bind->ID     = URL::int('id');

    if( $access->update_activate($bind) ) {
        alert( 'success', 
        	'Conta Ativada: <a href="' . access_url('login') . '">Fazer login</a>.'
        );
    }
    else {
        alert( 'error', 'Não foi possível concluir a ativação agora.<br>Tente novamente.' );
        return;
    }

}