<?php 
defined('ENTRY_GUARD') or die;

INPUT::method_request();


$bind = new Assign;

$bind->name    = _POST::str('name');
$bind->email   = Ensure::email($_POST['email']);
$bind->created = date('Y-m-d');
$bind->role    = _POST::int('role');
$bind->token   = token_generator(42);
$bind->nonce   = token_generator(10);


$action = $_POST['action'] ?? null;

if( $action === 'register' ) {


    if( ! $auth->is_any_manager() ) {
        alert('error', 'Sem autorização para adicionar conta de administrador.');

        exit;
    }

    if( $admin->exists($bind) ) {
        alert('error', 'Já existe um registro com esse e-mail.');
        return;
    }

    $bind->pswd['input'] = _POST::str('pswd');
    $bind->pswd = null;

    # modo confirmacao e definicao de senha por envio de link no e-mail
    if( empty($bind->pswd['input']) && _POST::bool('send_mode') === true ) {
        $bind->status = 0;
    }
    else {
        if( requisite_password($bind->pswd['input']) ) {
            $bind->pswd = password_hash( $bind->pswd['input'], PASSWORD_DEFAULT );

            $bind->status = 1;
        }
        else {
            alert('error', 'Senhas precisam conter no mínimo 8 caracteres com letras maiúsculas, minúsculas e números!');
            return;
        }
    }


    if( $admin->register($bind) === false ) {

        alert_redirect( 'warning', 
            'Não foi possível cadastrar o administrador. Verifique os dados e tente novamente.', 
            URL::current(), 10000 
        );
        return;
    }

    $bind->ID = $bind->LastID;

    $access_url = URL::root('dashboard/access');

    if( $bind->status === 0 ) {
        $body = Provider::email_body([
            'h2' => 'E-mail de verificação para conta de administrador no site' . site_title(), 
            'p2' => 'Clique no link abaixo para ativar conta e definir a senha de acesso.' . site_title(),
            'link' => "{$access_url}/?act=activation&key={$bind->token}&id={$bind->ID}"
        ]);
    }
    else {
        $body = Provider::email_body([
            'h2' => "Você foi adicionado como administrador no site", 
            'p1' => "Clique no link abaixo para acessar o painel.", 
            'link' => "{$access_url}/?act=login"
        ]);
    }

    $subject = ($bind->status === 0) 
        ? 'Ativação de nova conta administrativa' 
        : 'Sua conta de administrador está pronta';

    $mailer = Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => $subject,
        'body'    => $body
    ]);

    if( $mailer ) {
        alert( 'success', 
            "Administrador cadastrado e e-mail de instruções enviado com sucesso!"
        );
    }
    else {
        alert( 'error', 
            "O administrador foi cadastrado, mas o e-mail de notificação não pôde ser enviado. O novo administrador precisará usar a recuperação de senha para acessar.
            <p>Caso você não tenha criado uma senha, envie este link para ele: {$access_url}/?act=lost-password</p>"
        );
    }
}




if( $action === 'delete' ) {
    
    $bind->ID   = _POST::int('target_id') ?: URL::int('id');
    $bind->pswd = _POST::str('pswd_confirm_delete');

    if( empty($bind->pswd) ) {
        alert( 'warning', 'Digite a senha de confirmação.' );
        return;
    }

    if( password_verify($bind->pswd, $auth->logged('pswd')) === false ) {
        alert( 'error', 'Senha incorreta.' );
        return;
    }

    if( $admin->delete($bind) === false ) {
        alert( 'warning', 'O administrador não foi excluido. Tente novamente.' );
        return;
    }

    Provider::send_email([
        'email'   => $bind->email,
        'name'    => $bind->name,
        'subject' => 'Administrador excluído',
        'body'    => 'Você excluiu o administrador ' . $bind->name . ' do site ' . site_title()
    ]);
    
    success_preloader_redirect( 'Administrador excluído.', dash_url('admins/') );
}