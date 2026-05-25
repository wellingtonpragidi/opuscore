<?php
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();


if( $_POST['action'] === 'reset' ) {

    $password = INPUT::GET('pswd');

    $pswd_length = strlen( $password );
    $min = 8;
    $max = 26;

    if( INPUT::empty('pswd') ) {

        alert('warning', 'Insira a senha!');
        
    }
    else if( INPUT::empty('confirm-pswd') ) {

        alert('warning', 'Insira a confirmação da senha!');

    } 
    
    else if( $pswd_length < $min ) {

        alert('warning', "A senha deve ter no mínimo {$min} caracteres.");

    } 
    else if( $pswd_length > $max ) {

        alert('warning', "A senha deve ter no máximo {$max} caracteres.");

    }

    else if( $password !== INPUT::GET('confirm-pswd') ) {

        alert('warning', 'As senhas não coincidem!');

    } 
    else {

    	$bind = new Assign;

		$bind->pswd   = password_hash( $password, PASSWORD_DEFAULT );
	    $bind->token  = token_generator(42);
	    $bind->update = date('Y-m-d H:i:s');
	    $bind->status = 1;
	    $bind->ID     = URL::int('id');

        if( $auth->reset_update($bind) ) {
            alert(
            	'success', 
            	'Senha atualizada: <a href="' . URL::root('access/?action=login') . '">Acessar</a>'
            );
        }
        else {
            alert(
            	'error', 
            	'Ocorreu algum erro.<br>Por favor tente novamente ou entre em <a href="'.URL::root('contato').'">contato</a> com o proprietário do site'
            );
        }
    }
        
}
