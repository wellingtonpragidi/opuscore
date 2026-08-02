<div id="wrapper">
	<?php require dashboard_path('access/action/activation.php') ?>
	<form method="POST" action="<?= URL::current() ?>">
		<label for="pswd" class="screen_reader">Digite sua senha.</label>
		<div class="formit password">
			<img class="padlock" src="<?= dash_url('access/assets/img/padlock.svg?v=1') ?>" alt="" />
			<input id="pswd" type="text" class="pswd" placeholder="Senha" name="pswd" />
		</div>
		<button type="submit" name="activation">Enviar</button>
	</form>
</div>