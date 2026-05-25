<?php
require dirname(__DIR__, 2) .'/config.php';

if( $userStatus->logged() ) :

	$bind = new Assign;

	$bind->type    = 'post';
	$bind->slug    = Ensure::string( $_POST['related'] );
	$bind->author  = $userStatus->logged_name();
	$bind->email   = $userStatus->logged_email();
	$bind->parent  = Ensure::int( $_POST['parent'] );
	$bind->content = Sanitize::comment( $_POST['comment'] );
	$bind->date    = date('Y-m-d H:i:s');

	$image    = Ensure::URL( $_POST['userimage'] );
	$user_url = Ensure::URL( $_POST['userurl'] );

	if( $comment->insert($bind) > 0 ) :
        $attr_name = Ensure::attr( $userStatus->logged_name() );
	    echo '<li id="comment-'.$bind->LastID.'" class="the_comment">
	        <div class="comment-author">
	            <span class="author-photo">
	            	<img src="' . $image . '" alt="' . $attr_name . '" />
	            	<span class="frame"></span>
	            </span>
	            <span class="author-name">
	                <a href="' . $user_url . '">' . $userStatus->logged_name() . '</a>
	            </span>
	        </div>
	        <time class="comment-date">'. chronos_format($bind->date, 2) .'</time>
	        <p class="comment-content">'. $bind->content .'</p>
	    </li>';
	endif;
else :
	alert( 'warning', 
		'<p><a href="'. site_url('access/?action=register') .'" target="_blank">Registre-se</a> ou faça <a href="'. site_url('access/?action=login&redirect='. URL::current()) .'">Login</a> para poder comentar.</p>'
	);
endif;