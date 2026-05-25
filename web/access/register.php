<?php
if( INPUT::formSubmitted() ) {
    require get_web_path('controller/access/register.php');
}
?>
<p class="title">Criar conta</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="name" class="screen_reader">Nome</label>
	<div class="formit">
		<img class="padlock" src="<?= assets('img/icon/user.svg') ?>" alt="" />
		<input id="name" type="text" name="name" placeholder="Nome" />
	</div>
	<label for="email" class="screen_reader">E-mail</label>
	<div class="formit">
		<img class="padlock" src="<?= URL::root('web/assets/img/email.svg') ?>" alt="" />
		<input id="email" type="email" name="email" placeholder="E-mail" />
	</div>
	<button type="submit" name="action" value="register">Registrar</button>
</form>
<ul>
	<li><a href="javascript:history.back()">Voltar</a></li>
    <li><a href="<?= URL::root('access/?action=login') ?>">Login</a></li>
	<li><a href="<?= URL::root() ?>">Página inicial</a></li>
</ul>
<?php

echo URL::get('id');