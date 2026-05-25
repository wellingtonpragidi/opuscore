<?php
declare( strict_types = 1 );

/**
 * @see https://opuscore.dev/functions/post_title
 **/
function post_title(): string {
    $post = Container::call('Post');
    return $post->title();
}


/**
 * @see https://opuscore.dev/functions/post_url
 **/
function post_url(): string {
    $post = Container::call('Post');
    
    return URL::root( $post->segment() );
}