<?php

/**
 * @see https://opuscore.dev/functions/comment_area
 */
function comment_area( array $args = [] ): void {

    $show = fn($key) => array_key_exists($key, $args) ? (bool) $args[$key] : true;

    $opts = [ 
        'show-title' => $show('show-title'),
        'show-count' => $show('show-count'),

        'tag'        => $args['tag'] ?? 'strong', 
        'id'         => $args['id'] ?? 'comment-title', 
        'class'      => $args['class'] ?? null, 
        'innerText'  => $args['innerText'] ?? 'Comentar'
    ];

    $class = isset($opts['class']) ? ' class="' . $opts['class'] . '"' : null;

    if( $opts['show-title'] === true ) {
        echo "
        <{$opts['tag']} id=\"{$opts['id']}\"{$class}>
            {$opts['innerText']}
        </{$opts['tag']}>";
    }


    $container = Container::instance();

    $article = $container->make('Article');
    $comment = $container->make('Comment');


    if( $opts['show-count'] === true ) {
        echo '<p id="comment-count">' . $comment->count($article) . '</p>';
    }


    $auth    = $container->make('Auth');
    $image   = $container->make('Image');
    $user    = $container->make('User');


    $suffixes = ['header', 'form', 'list'];
    $fillpath = [ rtrim(WEB_DIR, '/'), 'view', 'comment-' ];

    foreach( $suffixes as $suffix ) {

        require implode( '/', $fillpath ) . $suffix . '.php';
    }
}


function comment_avatar( object $show ): void {
    $path = $show->attachment->avatar->path ?? null;

    if( $path !== null ) {
        $version = '?v=' . ($show->attachment->version ?? 0);
        $sz = $path->width ?? null;

        echo '<img 
            src="' . upload_url($path . $version) . '" 
            alt="' . $show->name . '" 
            width="' . $sz . '" height="' . $sz . '" 
        />';
    }   
}


function commenter_url( object $show ): string {
    $pathname = user_base() . $show->username;

    return URL::root($pathname);
}


function comment_replies(): void {
}
