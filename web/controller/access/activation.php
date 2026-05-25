<?php
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();


if( $_POST['action'] === 'activation' ) {

    $password = INPUT::GET('pswd');

    $pswd_length = strlen( $password );
    $min = 8;
    $max = 26;

    if( INPUT::empty('pswd') ) {

        alert('warning', 'Insira a senha!');
        
    }
    else if( $pswd_length < $min ) {

        alert('warning', "A senha deve ter no mínimo {$min} caracteres.");

    } 
    else if( $pswd_length > $max ) {

        alert('warning', "A senha deve ter no máximo {$max} caracteres.");

    }
    else {

        $bind = new Assign;

        $bind->pswd   = password_hash( $password, PASSWORD_DEFAULT );
		$bind->token  = token_generator(42);
		$bind->status = 1;
		$bind->ID     = URL::int('id');

        if( $auth->activation_update($bind) ) {
            alert( 'success', 
            	'Conta Ativada: <a href="' . URL::root('access/?action=login') . '">Fazer login</a>.'
            );
        }
        else {
            alert( 'error', 
            	'Não foi possível concluir a ativação agora.<br>Tente novamente mais tarde ou entre em contato com o site.'
            );
        }
    }

}