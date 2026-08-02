<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();

# role >= 3
if( $auth->is_staff() ) {
    alert('error', 'Sem autorização para alterar funções administrativas.');
    exit;
}


$bind = new Assign;

$bind->role = _POST::int('role');

# ninguem altera um master
if( $auth->is_from_master() ) { 
    alert( 'error', 'Não se deve alterar a função de um administrador Master.' );
    exit;
}

# role = 2
if( $auth->is_manager() ) { 
    if( $auth->is_from_manager() ) { 
        alert('error', 'Um Gerenciador não deve alterar a própria função ou de outros Gerenciadores.');
        exit;
    }
    # tentando tornar alguem master
    if( $bind->role === 1 ) { 
        alert('error', 'Somente um administrador Master pode alterar a função de outro administrador para Master.');
        exit;
    }
}


$old_rule = (string) $admin->role( (int) $admin->target('role') );
$new_rule = (string) $admin->role( $bind->role );

$bind->ID    = _POST::int('target_id') ?: URL::int('id');
$bind->name  = _POST::str('name');
$bind->email = Ensure::email($_POST['email'] ?? null);
$bind->token = token_generator(42);


if( _POST::str('action') === 'role' ) {

    if( $admin->update_role($bind) === false ) {
        alert( 'warning', 'Nenhuma alteração foi feita, tente novamente.');

        return;
    }

    Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'Função alterado',
        'body'    => Provider::email_body([
            'p1' => 'A função da sua conta de administrador no site ' . site_title() . ' foi alterada.<br>De: <b>' . $old_rule . '</b> Para: <b>' . $new_rule . '</b>'
        ])
    ]);


    if( $admin->update_token($bind) ) {
        alert( 'success', 'Função alterada.' ); 
    }
    else {
        alert( 'warning', '
            Função de administrador alterada.<br>
            Porém a sessão não atualizou. Para garantir tudo funcionando com segurança clique em <a href="' . dash_url('logout') . '">sair</a>.
        ');
        return;
    }
}
