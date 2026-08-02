<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

_POST::method_request();


if( ! $auth->is_logged() ) {
    $message = sprintf(
        'Somente usuários cadastrados podem comentar.<br>
        <a href="%1$s" %2$s>Registre-se</a> ou faça <a href="%3$s">Login</a>.',

        access_url('register'),
        'target="_blank" rel="noopener"',
        access_url('login', 'redirect') . '&to=comment-area'
    );

    alert( 'warning', $message );

    return;
}


$bind = new Assign;


$bind->type             = 'article';
$bind->related->ID      = $article->async_id();
$bind->related->user_id = $auth->logged()->ID;
$bind->content          = Sanitize::comment($_POST['comment']);
$bind->date->created    = date('Y-m-d H:i:s');

$bind->date->format = chronos_format( $bind->date->created, 2 );


if( $comment->insert($bind) ) {

    # sempre que o usuario interage, atualiza a timestamp da coluna `updated` do mesmo
    $user->update_lastupdate($auth);

    echo '
    <li id="comment-' . $bind->LastID . '" class="comment">
        <div class="comment-author">
            <span class="author-avatar">
            	' . $image->user_avatar() . '
            </span>
            <span class="author-name-url">
                <a href="' . $auth->URL() . '">' . $auth->logged()->name . '</a>
            </span>
        </div>
        <time class="comment-date">' . $bind->date->format . '</time>
        <div class="comment-content">' . $bind->content . '</div>
    </li>';

}