<p class="title"><strong>Login</strong></p>
<p class="title">Acessar recursos do site</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="email" class="screen_reader">E-mail</label>
	<div class="formit">
		<img class="email" src="<?= dist_img_url('icon/email.svg') ?>" alt="" />
		<input id="email" type="text" name="email" placeholder="E-mail" 
			value="<?= INPUT::GET('email') ?>" />
	</div>
	<label for="pswd" class="screen_reader">Senha</label>
	<div class="formit password">
		<img class="padlock" src="<?= dist_img_url('icon/padlock.svg') ?>" alt="" />
		<input id="pswd" class="pswd" type="text" placeholder="Senha" name="pswd" />
	</div>
    
	<button type="submit" name="action" value="login">Acessar</button>
</form>
<ul>
	<li><a href="<?= access_url('register') ?>">Criar conta</a></li>
    <li><a href="<?= access_url('lost-password') ?>">Recuperar senha</a></li>
	<li><a id="goback" href="#">Voltar</a></li>
	<li><a href="<?= site_url() ?>">Página inicial</a></li>
</ul>