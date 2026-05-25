<?php 
$new_token = token_generator(15);

if( $admin->update_token($new_token) ) {

    unset( $_SESSION['admin_id'] );
    unset( $_SESSION['admin_token'] );
    unset( $_SESSION['admin_redirect'] );

    alert_redirect( 'warning', 
    	'Token atualizado. Limpando sessões e fazendo logout. . &nbsp; .', 
    	dash_url('access'), 2900
    );
}
else {
    alert_redirect('error', 
        'Token não atualizado. Não foi possível fazer logout. Tente novamente em alguns instantes',
        URL::current(), 4000
    );
}