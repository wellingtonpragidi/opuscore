<?php 
defined('ENTRY_GUARD') or die; # so acessivel por url dentro da rota

$bind = new Assign;

$bind->token = token_generator(); # 42
$bind->nonce = token_generator( 16, HIGH_ENTROPY );
$bind->ID    = $auth->id();

$has_updated = false;

if( $access->update_token($bind) ) {
    $has_updated = true;
}

if( $access->update_nonce($bind) ) {
    $has_updated = true;
}


if( $has_updated ) {
    $info = 'Sessão encerrada.';
}
else {
    $info = 'Sessão encerrada. <small>Falha ao atualizar chaves.</small>';
}

unset( $_SESSION['user_id'] );
unset( $_SESSION['user_token'] );

session_regenerate_id( true );


$redirect = URL::has('redirect') ? URL::GET('redirect') : access_url('login');

alert_redirect( 'success', $info, $redirect, 2500 );