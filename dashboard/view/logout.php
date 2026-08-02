<?php 
defined('ENTRY_GUARD') or die;

$bind = new Assign;

$bind->token = token_generator(); # 42
$bind->nonce = token_generator( 16, HIGH_ENTROPY );
$bind->ID    = $auth->id();

$has_updated = false;

if( $admin->update_token($bind) ) {
    $has_updated = true;
}

if( $admin->update_nonce($bind) ) {
    $has_updated = true;
}


if( $has_updated ) {
    $info = 'Sessão encerrada com segurança.';
}
else {
    $info = 'Sessão encerrada. Falha ao atualizar chaves.';
}

unset( $_SESSION['admin_id'] );
unset( $_SESSION['admin_token'] );
unset( $_SESSION['admin_redirect'] );

session_regenerate_id( true );


success_preloader_redirect( $info, dash_url('access') );
