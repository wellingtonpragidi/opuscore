<?php 
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();


if( $_POST['action'] === 'login' ) {

    $email = Ensure::email($_POST['email']);

    $field = $access->field_email($email);

    if( empty($email) ) {
        alert('warning', 'Insira o endereço de e-mail!');
        return;
    }

    if( $access->verify_email($email) === false ) {
        alert( 'error', 'E-mail não cadastrado.' );
        return;
    }

	if( $field->status === 0 ) {
        alert( 'error', 
            'Este e-mail já está cadastrado, mas a conta ainda não foi confirmada.' 
        );
        return;
    }

    if( INPUT::empty('pswd') ) {
        alert('warning', 'Insira a senha.');
        return;
    }

    if( password_verify( INPUT::GET('pswd'), $field->pswd ) === false ) {

        alert( 'error', 'Senha incorreta.' );

        return;
    }

    $_SESSION['user_id']    = $field->ID;
    $_SESSION['user_token'] = $field->token;


    $fragment = URL::has('to') ? '#' . URL::GET('to') : '';

    $redirect = $auth->validate_redirect_url(
        URL::GET('redirect') . $fragment
    );

    $to  = $redirect ? 'local solicitado' : 'inicio';
    $url = $redirect ?? URL::root();


    success_preloader_redirect( 
        'Login efetuado.<br>Redirecionando para o ' . $to . '. .  .', 
        $url, 1800 
    );

}