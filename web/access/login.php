<?php
if( INPUT::formSubmitted() ) {
    require get_web_path('controller/access/login.php');
}
?>
<p class="title"><strong>Login</strong></p>
<p class="title">Acessar recursos do site</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="email" class="screen_reader">E-mail</label>
	<div class="formit">
		<img class="email" src="<?= URL::root('web/assets/img/email.svg') ?>" alt="" />
		<input id="email" type="text" name="email" placeholder="E-mail" 
			value="<?= INPUT::GET('email') ?>" />
	</div>
	<label for="pswd" class="screen_reader">Senha</label>
	<div class="formit password">
		<img class="padlock" src="<?= URL::root('web/assets/img/padlock.svg') ?>" alt="" />
		<input id="pswd" type="text" class="pswd" placeholder="Senha" name="pswd" />
	</div>
	<button type="submit" name="action" value="login">Acessar</button>
</form>
<ul>
	<li><a href="<?= URL::root('access/?action=register') ?>">Criar conta</a></li>
    <li><a href="<?= URL::root('access/?action=lost-password') ?>">Recuperar senha</a></li>
	<li><a href="javascript:history.back()">Voltar</a></li>
	<li><a href="<?= URL::root() ?>">Página inicial</a></li>
</ul>
