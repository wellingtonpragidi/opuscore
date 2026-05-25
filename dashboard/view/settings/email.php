<form class="w70" method="POST" action="<?= URL::current() ?>">
	<div class="flexbox">

		<div class="cn_85">
			<?php 
            if( INPUT::formSubmitted() ) {
				require get_dashboard_path('controller/settings/email.php');
			} 
            
			$setval = fn($name, $func) => $_POST[$name] ?? $func;
			?>
		</div>
		
	    <hr class="cn_85" />

		<span class="cn_40 mt10"><label>Porta</label></span>
	    <input class="cn_45" type="text" name="port" value="<?= $setval('port', email_port()) ?>" />
	    <hr class="cn_85" />

	    <span class="cn_40 mt10"><label>Servidor</label></span>
	    <input class="cn_45" type="text" name="host" value="<?= $setval('host', email_host()) ?>" />
	    <hr class="cn_85" />

	    <span class="cn_40 mt10"><label>Usuário</label></span>
	    <input class="cn_45" type="text" name="user" value="<?= $setval('user', email_user()) ?>" />
	    <hr class="cn_85" />

	    <span class="cn_40 mt10"><label>Senha</label></span>
	    <input class="cn_45" type="text" name="pswd" placeholder="Por segurança a senha é oculta" />
	    <hr class="cn_85" />

	    <span class="cn_40 mt10"><label>Endereço <small>&nbsp;(Quase sempre o mesmo que usuário)</small></label></span>
	    <input class="cn_45" type="text" name="address" value="<?= $setval('address', email_address()) ?>" />
	    <hr class="cn_85" />
	</div>
	<div class="w85">
    	<button class="btn lg right px40" name="action">Salvar</button>
    </div>
</form>