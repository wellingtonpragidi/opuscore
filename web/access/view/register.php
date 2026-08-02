<p class="title">Criar conta</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="name" class="screen_reader">Nome</label>
	<div class="formit">
		<img class="padlock" src="<?= dist_img_url('icon/user.svg') ?>" alt="" />
		<input id="name" type="text" name="name" placeholder="Nome" />
	</div>
	<label for="email" class="screen_reader">E-mail</label>
	<div class="formit">
		<img class="padlock" src="<?= dist_img_url('icon/email.svg') ?>" alt="" />
		<input id="email" type="email" name="email" placeholder="E-mail" />
	</div>
	<button type="submit" name="action" value="register">Registrar</button>
</form>
<ul>
	<li><a id="goback" href="#">Voltar</a></li>
    <li><a href="<?= access_url('login') ?>">Login</a></li>
	<li><a href="<?= site_url() ?>">Página inicial</a></li>
</ul>