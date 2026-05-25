<?php
if( INPUT::formSubmitted() ) {
    require get_web_path('controller/access/lost.php');
}
?>
<p class="title">Recuperar senha</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="email">Digite seu endereço de e-mail. Você receberá um link no mesmo para criar uma nova senha.</label>
	<div class="formit">
		<img class="email" src="<?= URL::root('web/assets/img/email.svg') ?>" alt="" />
		<input type="email" id="email" name="email" placeholder="E-mail" />
	</div>
	<button type="submit" name="action" value="lost">Enviar</button>
</form>
<ul>
	<li><a href="<?= URL::root('access/?action=login') ?>">Login</a></li>
</ul>
