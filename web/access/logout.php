<?php
$assign = new Assign;

/* atualizar token no banco de dados */
$assign->token = token_generator(42);

if( ! $auth->token_updated($assign) ) {
    alert( 'error', 'Ocorreu algum erro, e o token não foi atualizado.' );
    return;
}

unset( $_SESSION["user_id"] );
unset( $_SESSION["user_token"] );

$redirect = URL::has('redirect') ? URL::GET('redirect') : URL::root('access/?action=login');

alert_redirect( 'warning', 'Fazendo logout. . &nbsp; .', $redirect, 2900 );