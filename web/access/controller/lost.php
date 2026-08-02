<?php
defined( 'ENTRY_GUARD' ) or die;
INPUT::method_request();


if( $_POST['action'] === 'lost' ) {

    $email = Ensure::email($_POST['email']);
    
    if( empty($email) ) {
        alert('warning', 'Insira o endereço de e-mail!');
        return;
    } 

    if( $access->verify_email($email) === false ) {
        alert( 'error', 'E-mail não cadastrado.' );
        return;
    }


    $field = $access->field_email($email);

    # tudo certo, envia com link pra recuperacao e-mail
    $href = access_url('reset-password', "&key={$field->token}&id={$field->ID}");

    $body = '<h2>E-mail para redefinir senha de usuário no site ' . site_title() . '</h2>
    <p>Clique no link abaixo para criar nova senha.</p>
    <p><a href="' . $href . '">' . $href . '</a></p>
    <p>Caso não seja redirecionado ao clicar no link, copie e cole no navegador.</p>';

    $mailer = Provider::send_email([
        'email'   => $field->email,
        'name'    => $field->name,
        'subject' => 'Redefenir senha',
        'body'    => $body
    ]);

    if( $mailer ) {
        alert('success', 'Foi enviado um e-mail contendo um link para redefinir sua senha.', 6000);
        echo '<ul>
            <li><a href="' . URL::root('access/?action=login') . '">Login</a></li>
            <li><a href="' . URL::root() .'">'. site_title() . '</a></li>
        </ul>';

        return;
    }
    else {
        alert( 'error', 'E-mail para redefinir senha não enviado.<br>Por favor tente novamente.');
        return;
    }

}