<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

if( ! $auth->is_self() ) {
    alert('error', 'Somente o dono da conta de administrador pode alterar o seu nome.');

    exit;
}

_POST::method_request();


$bind = new Assign;

$bind->name  = _POST::str('name');
$bind->ID    = _POST::int('target_id') ?: URL::int('id');
$bind->email = Ensure::email($_POST['email'] ?? null);
$bind->token = token_generator(42);


if( _POST::str('action') === 'name' ) {
    if( $admin->update_name($bind) ) {
        
        if( $admin->update_token($bind) ) {
            alert( 'success', 'Nome da conta de administrador atualizada.<br><small>Essa alteração não envia e-mail de aviso</small>' );
        }
        else {
            alert( 'warning', '
                Nome da conta de administrador alterada.<br>
                Porém a sessão não atualizou. Para garantir tudo funcionando com segurança clique em <a href="' . dash_url('logout') . '">sair</a>.
            ');
            return;
        }
    }
    else {
        alert( 'warning', 'Nenhuma alteração foi feita, tente novamente.' );
        return;
    }
}