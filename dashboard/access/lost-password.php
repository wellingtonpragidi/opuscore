<div id="wrapper">
	<?php 
	require get_dashboard_path( 'access/action/lost.php' ); 

	$hidden = '';
	if( URL::Get('sent') ) {
		alert('success', 'Foi enviado um e-mail contendo um link para redefinir sua senha.');
		$hidden = 'style="display: none"';
	}
	?>
	
	<form <?= $hidden ?> method="POST" action="<?= URL::current() ?>">
		<p class="title">Recuperar senha</p>
		<label for="mail">Digite seu endereço de e-mail. Você receberá um link no mesmo para criar uma nova senha.</label>
		<div class="formit">
			<img class="email" src="<?= dash_url('access/assets/img/email.svg') ?>" alt="" />
			<input id="mail" type="email" name="mail" placeholder="E-mail" />
		</div>
		<button type="submit" name="lost">Enviar</button>
	</form>
	<ul>
		<li><a href="<?= dash_url('access/?act=login') ?>">Login</a></li>
	</ul>
</div>