<div id="wrapper">
	<?php require dashboard_path( 'access/action/recover.php' ) ?>
	<form method="POST" action="<?= URL::current() ?>">
		<label for="pswd">Digite sua nova senha.</label>
		<div class="formit password">
			<img class="padlock" src="<?= dash_url('access/assets/img/padlock.svg') ?>" alt="" />
			<input id="pswd" type="text" class="pswd" placeholder="Senha" name="pswd" />
		</div>
		<label for="confirm-pswd">Digite novamente sua nova senha.</label>
		<div class="formit password">
			<img class="padlock" src="<?= dash_url('access/assets/img/padlock.svg') ?>" alt="" />
			<input id="confirm-pswd" type="text" class="pswd" placeholder="Confirmar senha" name="confirm-pswd" />
		</div>
		<button type="submit" name="reset">Atualizar</button>
	</form>
	<ul>
		<li><a href="<?= dash_url('access/?act=login') ?>">Login</a></li>
	</ul>
</div>