<?php
$show_form = false;
require get_web_path('access/require/validations.php');

if( INPUT::formSubmitted() ) {
    require get_web_path('controller/access/activation.php');
}

if( $show_form ) :
?>
<form method="POST" action="<?= URL::current() ?>">
	<label for="pswd" class="sr">Digite sua senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= URL::root('web/assets/img/padlock.svg') ?>" alt="" />
        <input id="pswd" type="text" class="pswd" placeholder="Senha" name="pswd" />
    </div>
	<button type="submit" name="action" value="activation">Enviar</button>
</form>

<?php endif;