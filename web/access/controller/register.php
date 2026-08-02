<?php
defined( 'ENTRY_GUARD' ) or die;
_POST::method_request();


if( $_POST['action'] === 'register' ) {

    $fullname = INPUT::GET('name');

    if( empty($fullname) ) {
        alert('warning', 'Forneça seu nome completo.');
        return;
    }

    $valid_name = Validate::name($fullname);

    if( $valid_name !== true ) {
        alert( 'warning', $valid_name );
        return;
    }

    $bind = new Assign;

    $bind->name     = Sanitize::name( $fullname );

    # username eh NOT NULL, iserimos um valor qualquer aqui, 
    #  e depois do register atualizamos ela concatenando o ID
    $bind->username = Ensure::slug($bind->name);

    $bind->email    = Ensure::email( $_POST['email'] );
    $bind->created  = date('Y-m-d H:i:s');
    $bind->token    = token_generator(42);
    $bind->nonce    = token_generator(10);

    if( empty($bind->email) ) {
        alert('warning', 'Insira o endereço de e-mail.');
        return;
    } 

	if( $access->verify_email($bind->email) === true ) {
        alert( 'warning', 
            'Já existe um registro com esse e-mail.<br>
            Caso tenha perdido o acesso <a href="' . access_url('lost-password') . '">defina uma nova senha</a>.' 
        );
        return;
    }

    if( $access->register($bind) === false ) {
        alert( 'error', 
            'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.'
        );
        return;
    }

    $bind->ID = $bind->LastID;

    # agora que temos o ID concatenamos ele com o username separado por um hifen
    $bind->username .= '-' . $bind->ID;

    $access->update_username( $bind );

    alert( 'success', 'Cadastro concluído, verifique seu e-mail.', 6000 );

    # apos registro envia e-mail
    $body = Provider::email_body([
        'h2' => 'E-mail de verificação para conta de usuário no site '. site_title(),
        'p1' => 'Clique no link abaixo para ativar sua conta e definir sua senha de acesso.',
        'link' => access_url('activate', "&key={$bind->token}&id={$bind->ID}")
    ]);

    $mailer = Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'Verificação',
        'body'    => $body
    ]);

    if( $mailer ) {
        alert( 'success', "{$bind->name}, foi enviado um e-mail para confirmação.", 6000 );
    }
    else {
        alert( 'error', 
            'E-mail com link para ativar conta não enviado.<br>Será necessário 
             <a href="' . access_url('lost-password') . '">definir uma nova senha</a>.'
        );
        return;
    }
}