<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}


if( ! $auth->is_authorized() ) {
    alert('error', 'Sem autorização para editar conta de outro administrador.');

    exit;
}

_POST::method_request();


$bind = new Assign;


$bind->name  = _POST::str('name');
$bind->ID    = _POST::int('target_id') ?: URL::int('id');
$bind->email = Ensure::email($_POST['email'] ?? null);
$bind->token = token_generator(42);
$bind->pswd['current'] = _POST::str('current_pswd');
$bind->pswd['new']     = _POST::str('pswd'); # Nova senha


if( _POST::str('action') === 'pswd' ) {

    if( empty($bind->pswd['current']) ) {
        alert('error', 'Digite o campo senha atual!');
        return;
    }
    if( empty($bind->pswd['new']) ) {
        alert('error', 'Digite o campo nova senha!');
        return;
    }


    if( password_verify($bind->pswd['current'], $auth->logged('pswd')) === false ) {
        alert( 'error', 'Senha atual incorreta.' );
        return;
    }

    if( requisite_password($bind->pswd['new']) === false ) {
        alert('error', 'Senhas precisam conter no mínimo 8 caracteres com letras maiúsculas, minúsculas e números!<br><small>Use gerar senha</small>');
        return;
    }

    
    $bind->pswd = password_hash( $bind->pswd['new'], PASSWORD_DEFAULT );

    if( $admin->update_pswd($bind) === false ) {
        alert( 'warning', 'Não foi possível atualizar sua senha, tente novamente.' );
        return;
    }


    Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'Alerta de Segurança: Senha alterada',
        'body'    => Provider::email_body([
            'p1' => 'A senha da sua conta de administrador no site ' . site_title() . ' foi alterada.',
            'p2' => 'Se você não realizou essa ação, proteja sua conta imediatamente.',
        ])
    ]);


    if( $admin->update_token($bind) ) {
        alert( 'success', 'Senha alterada.' ); 
    }
    else {
        alert( 'warning', '
            Senha alterada. <br>
            Porém a sessão não foi atualizada. Para garantir tudo funcionando com segurança clique em <a href="' . dash_url('logout') . '">sair</a>.
        ');
    }

}