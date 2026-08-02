<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

_POST::method_request();


$bind = new Assign;

$bind->type    = 'user';
$bind->ID      = $auth->id();
$bind->updated = date('Y-m-d H:i:s');

$action = $_POST['action'] ?? null;

if( $action === 'name' ) {

    $uname = _POST::str('uname');

    $valid_name = Validate::name($uname);

    if( $valid_name !== true ) {
        json_response([
            'status' => false,
            'input'  => $uname, 
            'alert'  => Validate::name($uname) 
        ]);
    }

    $bind->name = Sanitize::name($uname);

    if( $user->update_name($bind) ) {

        $image->update_related_title($bind);

        # sempre atualizar timestamp `updated` quando algo mudar
        $user->update_lastupdate($auth);

        json_response([
            'status' => true,
            'input' => $bind->name, 
            'alert'  => 'Nome atualizado.'
        ]);
    }
    else {

        json_response([ 
            'status' => false,
            'input'  => $bind->name, 
            'alert'  => 'Nome não atualizado, tente novamente.'
        ]);
    }
}


if( $action === 'username' ) {

    $bind->username = _POST::str('uusername');
    $bind->token    = token_generator();
    $bind->nonce    = token_generator(11);

    if( $user->username_exists($bind) ) {
        json_response([
            'status' => false,
            'input' => $bind->username, 
            'alert'  => 'Esse nome de usuário já existe.'
        ]);
    }

    if( $user->update_username($bind) === false ) {
        json_response([ 
            'status' => false,
            'input'  => $bind->username, 
            'alert'  => 'Nome de usuário não atualizado, tente novamente.'
        ]);
    }

    # atualizada as colunas `token` e `nonce`
    $user->update_tokens($bind);


    # sempre que algo muda, atualiza a timestamp da coluna `updated`
    $user->update_lastupdate($auth);


    json_response([ 
        'status' => true,
        'input'  => $bind->username, 
        'alert'  => 'Nome de usuário atualizado.'
    ]);

}