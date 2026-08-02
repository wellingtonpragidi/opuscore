    <ol id="list-comments">

        <?php 
        $comments = $comment->select( $article->target()->ID );

        foreach( $comments as $show ) : ?>

            <li id="comment-<?= $show->ID ?>" class="comment">
                <div class="author">
                    <span class="avatar">
                        <?php comment_avatar($show) ?>
                    </span>
                    <span class="commenter-name">
                        <a href="<?= commenter_url($show) ?>"><?= $show->name ?></a>
                    </span>
                </div>
                <time class="comment-date"><?= chronos_format($show->created, 2) ?></time>
                <div class="comment-content"><?= $show->content ?></div>

                <?php /* replies */ ?>
                
            </li>

        <?php endforeach; ?>

    </ol>

</section> <?php # aberto em comment-header