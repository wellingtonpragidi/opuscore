<?php
$mail = isset($_POST['mail']) ? $_POST['mail'] : NULL; ?>

<div id="wrapper">

	<?php require dashboard_path( 'access/action/login.php' ); ?>

	<p class="title">Acessar painél de controle</p>
	<form method="POST" action="<?= URL::current() ?>">
		<label for="mail" class="screen_reader">E-mail</label>
		<div class="formit">
			<img class="email" src="<?= dash_url('access/assets/img/email.svg') ?>" alt="" />
			<input id="mail" type="text" name="mail" placeholder="E-mail" value="<?= $mail ?>" />
		</div>
		<label for="pswd" class="screen_reader">Senha</label>
		<div class="formit password">
			<img class="padlock" src="<?= dash_url('access/assets/img/padlock.svg') ?>" alt="" />
			<input id="pswd" type="text" class="pswd" placeholder="Senha" name="pswd" />
		</div>
		<button type="submit" name="login">Acessar</button>
	</form>
	<ul>
		<li><a href="<?= dash_url('access/?act=lost-password') ?>">Recuperar senha</a></li>
	</ul>
</div>