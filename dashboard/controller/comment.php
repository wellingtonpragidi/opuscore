<?php 
PST::method_request();



$bind = new Assign;

$bind->ID   = PST::int('target_id') ?: GET::int('id');
$bind->type = 'article';


if( PST::action('update') ) {

	$bind->content = PST::str('content');

	if( $comment->update($bind) ) {

		alert_redirect( 'success', 'Comentário atualizado.', URL::current() );
	} 
    else {

		alert('warning', 'Nenhuma alteração foi feita.');
	}
}


if( PST::action('delete') ) {

    if( $comment->delete($bind) ) {

        alert_redirect( 'success', 'Comentário excluído.', dash_url('comments') );
    }
    else {

        alert('warning', 'Comentário não excluído.');
    }
}


if( PST::action('approved') ) {

    $bind->approved = 1;

    if( $comment->update_approved($bind) ) {

        alert_redirect( 'success', 'Comentário aprovado', URL::current() ); # 3300ms
    }
    else {

        alert_time('warning', 'Comentário não aprovado');
    }
}