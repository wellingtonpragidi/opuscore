<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();


if( ! $auth->is_any_manager() ) {
    alert('error', 'Sem autorização para alterar status de outro administrador.');

    exit;
}


$bind = new Assign;

$bind->status = _POST::int('status');
$bind->ID     = _POST::int('target_id') ?: URL::int('id');
$bind->name   = $admin->target('name');
$bind->email  = $admin->target('email');
$bind->token  = token_generator(42);


if( _POST::str('action') === 'status' ) {

    if( $admin->update_status($bind) === false ) {
        alert( 'warning', 'Nenhuma alteração foi feita, tente novamente.' );
        return;
    }

    # envio de e-mail para o admin que alterou o status da conta de outro
    Provider::send_email([
        'email'   => $auth->logged('email'),
        'name'    => $auth->logged('name'),
        'subject' => 'Status alterado',
        'body'    => '<h2 style="font-size: 1.25rem; font-weight: 500">Você alterou o status da conta de administrador de ' . $bind->name . ' no site ' . site_title() . '.</h2>Proteja sua conta caso não tenha sido você a fazer a alteração.',
    ]);
    
    # envio de e-mail para a conta alterada
    Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'Status alterado',
        'body'    => 'O status da sua conta de administrador no site ' . site_title() . ' foi alterado por ' . $auth->logged('name'),
    ]);

    if( $admin->update_token($bind) ) {
        alert( 'success', 'Status alterado.' ); 
    }
    else {
        alert( 'warning', '
            Função de administrador alterada. <br>
            Porém a sessão não atualizou. Para garantir tudo funcionando com segurança clique em <a href="' . dash_url('logout') . '">sair</a>.
        ');
        return;
    }
}