<?php
$show_form = false;
require get_web_path('access/require/validations.php');

if( INPUT::formSubmitted() ) {
    require get_web_path('controller/access/reset.php');
}

if( $show_form ) :
?>
<form method="POST" action="<?= URL::current() ?>">
    <label for="pswd">Digite sua nova senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= URL::root('web/assets/img/padlock.svg') ?>" alt="" />
        <input type="text" id="pswd" class="pswd" placeholder="Senha" name="pswd" />
    </div>
    <label for="confirm-pswd">Digite novamente sua nova senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= URL::root('web/assets/img/padlock.svg') ?>" alt="" />
        <input type="text" id="confirm-pswd" class="pswd" placeholder="Confirmar senha" name="confirm-pswd" />
    </div>
    <button type="submit" name="action" value="reset">Atualizar</button>
</form>
<ul>
    <li><a href="<?= URL::root('access/?action=login') ?>">Login</a></li>
</ul>

<?php endif;