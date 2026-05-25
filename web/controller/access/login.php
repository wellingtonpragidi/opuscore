<?php 
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();


if( $_POST['action'] === 'login' ) {

    $email = Ensure::email($_POST['email']);

    $field = $auth->fields_by_email($email);

    if( empty($email) ) {
        
        alert('warning', 'Insira o endereço de e-mail!');
        return;
    }

    else if( $auth->verify_email($email) === false ) {

        alert( 'error', 'E-mail não cadastrado.' );

        return;
    }
	else if( $field->status === 0 ) {

        alert( 'error', 'Existe o registro com esse e-mail, mas a conta não foi confirmado.' );

        return;
    }
    else {

        $hash = password_verify( INPUT::GET('pswd'), $field->pswd );

        if( INPUT::empty('pswd') ) {

            alert('warning', 'Insira a senha.');

        } 
        else if( $hash === false ) {

            alert( 'error', 'Senha incorreta.' );

        }
        else {

            $_SESSION['user_id']    = $field->ID;
            $_SESSION['user_token'] = $field->token;

            $to = URL::has('redirect') ? 'de destino' : 'inicial';
            $url = URL::has('redirect') ? URL::GET('redirect') : URL::root();

            alert_redirect( 
                'success', "Login efetuado.<br>Redirecionando para a página {$to}. .  .", $url, 1800 );
        }
	}

}