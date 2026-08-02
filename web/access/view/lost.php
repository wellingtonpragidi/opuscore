<p class="title">Recuperar senha</p>
<form method="POST" action="<?= URL::current() ?>">
	<label for="email">Digite seu endereço de e-mail. Você receberá um link no mesmo para criar uma nova senha.</label>
	<div class="formit">
		<img class="email" src="<?= dist_img_url('icon/email.svg') ?>" alt="" />
		<input type="email" id="email" name="email" placeholder="E-mail" />
	</div>
	<button type="submit" name="action" value="lost">Enviar</button>
</form>
<ul>
    <li><a id="goback" href="#">Voltar</a></li>
	<li><a href="<?= access_url('login') ?>">Login</a></li>
</ul>
