<div class="boxreply">
    <h6 class="reply_title">RESPONDER</h6>
    <form id="replyform-<?= $show->ID ?>" class="replycommentform" method="POST" action="<?= URL::current() ?>">
        <label for="rc-<?= $show->ID ?>" class="screen_reader">Responda ao comentário</label>
        <textarea id="rc-<?= $show->ID ?>" class="replycomment" name="replycomment" placeholder="Digite seu comentário..." required></textarea>

        <input class="replyparent" type="hidden" name="replyparent" value="<?= $show->ID ?>" />
        <input class="replyrelated" type="hidden" name="replyrelated" value="<?= URL::param(1) ?>" />
        <?php // nao existe $user->picture()
        if( $user->picture() ) {
            echo '<input class="replyuserimage" type="hidden" name="replyuserimage" value="'.$user->picture().'" />';
        } 
        else {
            echo '<input class="replyuserimage" type="hidden" name="replyuserimage" value="'.site_url('web/assets/img/user-sm.jpg').'" />';
        } ?>
        <input class="replyuserurl" type="hidden" name="replyuserurl" value="<?= site_url('perfil/'. $userProfile->username()) ?>" />
        <div class="replycomment-submit">
            <button name="replyinsert-comment" type="submit" class="btn">Responder</button>
        </div>
    </form>
</div>