<?php if( $auth->is_logged() ) : ?>

<form id="comment-form" method="POST" action="<?= URL::current() ?>">
    <label for="comment" class="sr">Digite seu comentário</label>
    <textarea id="comment" placeholder="Digite seu comentário..." name="comment" required></textarea>

    <!-- <input type="hidden" id="related_id" name="related_id" value="<?php // $article->target()->ID ?>" /> -->

    <div id="comment-submit">
        <button type="submit" class="btn" name="action" value="insert">Comentar</button>
    </div>
</form>

<?php endif; ?>