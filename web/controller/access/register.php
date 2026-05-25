<?php
defined( 'ENTRY_GUARD' ) or die;

INPUT::method_request();

if( $_POST['action'] === 'register' ) {

    require get_web_path('access/require/ensure-name.php');

    $fullname = INPUT::GET('name');

    if( ! is_valid_name($fullname) ) {
        return;
    }

    $bind = new Assign;

    $bind->name     = normalize_name($fullname);
    $bind->username = Ensure::slug( $bind->name );
    $bind->email    = Ensure::email( $_POST['email'] );
    $bind->created  = date('Y-m-d H:i:s');
    $bind->token    = token_generator(42);
    $bind->nonce    = token_generator(10);

    if( empty($bind->email) ) {
        alert('warning', 'Insira o endereço de e-mail.');

        return;
    } 

	if( $auth->verify_email($bind->email) === true ) {
        alert( 'warning', 
            'Já existe um registro com esse e-mail.<br>
            Caso tenha perdido o acesso <a href="' . URL::root('access/?action=lost-password') . '">defina uma nova senha</a>.' 
        );

        return;
    }

    if( $auth->register($bind) ) {

        $bind->ID = $bind->LastID;
        $bind->username .= '-' . $bind->ID;
        $auth->update_username( $bind->username, $bind->ID );

        alert( 'success', 'Conta de usuário registrada!', 6000 );

        # apos registro envia e-mail
        $href = URL::root("access/?action=activation&key={$bind->token}&id={$bind->ID}");

        $body = '<h2 style="font-size: 1.25rem">E-mail de verificação para conta de usuário no site '. site_title() .'</h2>
            <p>Clique no link abaixo para ativar sua conta e definir sua senha de acesso.</p>
            <p><a href="' . $href . '">' . $href . '</a></p>';

        $mailer = Provider::send_email([
            'email'   => $bind->email,
            'name'    => $bind->name,
            'subject' => 'Verificação',
            'body'    => $body
        ]);

        if( $mailer ) {
            alert( 'success', 
                "{$bind->name}, foi enviado um e-mail de confirmação.", 6000 );
        }
        else {
            $link = '<a href="' . URL::root('access/?action=lost-password') . '">definir uma nova senha</a>';
            alert( 'error', 
                "E-mail com link para ativar conta não enviado.<br>Será necessário {$link}." );
        }
    }
}