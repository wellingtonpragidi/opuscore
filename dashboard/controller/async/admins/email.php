<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

if( ! $auth->is_authorized() ) {
    alert('error', 'Sem autorização para alterar e-mail de outro administrador.');
    exit;
}

INPUT::method_request();


if( $auth->is_self() ) {
    $old_email = $admin->target('email');
    $is_self = true;
}
else {
    $old_email = $auth->logged('email');
    $is_self = false;
}


$bind = new Assign;

$bind->ID    = _POST::int('target_id') ?: URL::int('id');
$bind->email = Ensure::email($_POST['email']);
$bind->name  = _POST::str('name');
$bind->pswd  = _POST::str('pswd_confirm_email');
$bind->token = token_generator(42);


if( _POST::str('action') === 'email' ) {

    if( $admin->exists($bind) ) {
        alert('error', 'Já existe outro registro com esse e-mail.');
        return;
    }

    if( empty($bind->email) ) {
        alert( 'warning', 'Campo e-mail vazio.' );
        return;
    }
    if( empty($bind->pswd) ) {
        alert( 'warning', 'Digite a senha.' );
        return;
    }

    if( password_verify($bind->pswd, $auth->logged('pswd')) === false ) {
        alert( 'error', 'Senha incorreta.' );
        return;
    }


    if( $admin->update_email($bind) === false ) {
        alert( 'warning', 'Nenhuma alteração foi feita, tente novamente.');

        return;
    }


    $st = site_title();

    if( $is_self ) {
        $body = Provider::email_body([
            'h2' => "O e-mail da sua conta de administrador {$st} foi alterado para <b>{$bind->email}</b>.",
            'p1' => 'Se você não solicitou isso, sua conta pode ter sido violada. Entre em contato com a equipe.',
            /**
             * @todo Se nao solicitou seria interessante talvez mudar status do admin pra 0 etc
             */ 
        ]);
    }
    else {
        $body = Provider::email_body([
            'h2' => "O e-mail da conta de administrador de <b>{$bind->name}</b> no site {$st} foi alterado por você.",
            'p1' => "O novo e-mail definido para este admin é: <b>{$bind->email}</b>.",
            'p2' => 'Se você não realizou essa ação, proteja sua conta imediatamente.',
        ]);
    }

    # OLD e-mail
    # Aqui envia e-mail de aviso para o antigo e-mail ou admin manager que esta alterando
    Provider::send_email([
        'email'   => $old_email,
        'name'    => $bind->name,
        'subject' => 'Alerta de Segurança: E-mail alterado',
        'body'    => $body
    ]);
    # NEW e-mail
    # Aqui envia e-mail de aviso para o novo e-mail, quando o proprio admin da conta altera
    Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'E-mail alterado',
        'body'    => Provider::email_body(['p1' => "E-mail da sua conta de administrador no site {$st} foi alterado."])
    ]);


    if( $admin->update_token($bind) ) {
        alert( 'success', 'E-mail alterado.' ); 
    }
    else {
        alert( 'warning', '
            E-mail alterado. <br>
            Porém a sessão não atualizou. Para garantir tudo funcionando com segurança clique em <a href="' . dash_url('logout') . '">sair</a>.
        ');
    }
}
