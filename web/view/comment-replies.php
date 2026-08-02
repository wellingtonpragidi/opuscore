<ol id="list-comments-reply-<?= $show->ID ?>" class="list-comments-reply"> 
    <?php 
    $comment = Container::call('Comment');
    foreach( $comment->reply($show->ID) as $show ) : ?>
    <li id="comment-<?= $show->ID ?>" class="the-comment-reply comment-parent-<?= $show->parent ?>">
        <div class="comment-author">
            <span class="author-photo">
                <?php
                if( $user->picture() ) {
                    echo $user->picture();
                } 
                else {
                    list( $w, $h, $t, $attrs ) = getimagesize( site_url('web/img/user-sm.jpg') );
                    echo '<img src="'.site_url('web/img/user-sm.jpg').'" alt="" '.$attrs.' />';
                } ?>
            </span>
            <span class="author-name">
                <a href="<?= site_url($show->username) ?>"><?= $show->name ?></a>
            </span>
        </div>
        <time class="comment-date"><?= chronos_format($show->date, 2) ?></time>
        <p class="comment-content"><?= $show->content ?></p>
    </li>
    <?php endforeach; ?>
</ol>