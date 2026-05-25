<?php 
INPUT::method_request();

if( $_POST['action'] === 'update' ) {

	$assign = new Assign;

	$assign->content = $_POST["content"];
	$assign->type    = 'post';
	$assign->ID      = URL::int('id');

	if( $comment->update($assign) > 0 ) {
		alert_time('success', 'Comentário atualizado.');
	} else {
		alert('warning', 'Ocorreu algum erro e o comentário não foi atualizado.');
	}
}

if( $_POST['action'] === 'delete' ) {
	$deleted = (int) $_POST['comment_id'];
    if( $comment->delete($deleted) ) {
        preloader(1200);
        alert_redirect('success', 'Comentário excluído.', dash_url('comments'), 2200);
    }
    else {
        alert('warning', 'O comentário não foi excluído.');
    }
}

if( $_POST['action'] === 'approve' ) {
    $approved = (int) $_POST['comment_id'];
    if( $comment->approvedupdated($approved) ) {
        alert_redirect('success', 'Comentário aprovado', URL::current(), 4500, 1000);
    }
    else {
        alert_time('warning', 'Nenhuma linha afetada');
    }
}