<form class="w60" method="POST" action="<?= URL::current() ?>" data-dirty>
	<div class="flexbox">

		<div class="cn_100">
			<?php 
            if( INPUT::formSubmitted() ) {
				require dashboard_path('controller/settings/email.php');
			} 
            
			$setval = fn($name, $func) => $_POST[$name] ?? $func;
			?>
		</div>

        <h2 class="cn_100 mt40 txt_center"><span class="border-span">SMTP</span></h2>
		
	    <hr class="cn_100" />

		<span class="cn_40 mt10"><label>Porta</label></span>
	    <input class="cn_60" type="text" name="port" value="<?= $setval('port', smtp_port()) ?>" />
	    <hr class="cn_100" />

	    <span class="cn_40 mt10"><label>Servidor</label></span>
	    <input class="cn_60" type="text" name="host" value="<?= $setval('host', smtp_host()) ?>" />
	    <hr class="cn_100" />

	    <span class="cn_40 mt10"><label>Usuário</label></span>
	    <input class="cn_60" type="text" name="user" value="<?= $setval('user', smtp_user()) ?>" />
	    <hr class="cn_100" />

	    <span class="cn_40 mt10"><label>Senha</label></span>
	    <input class="cn_60" type="text" name="pswd" placeholder="Por segurança a senha é oculta" />
	    
        <hr class="cn_100" />

	    <span class="cn_40 mt10"><label>Endereço</label></span>
	    <input class="cn_60" type="text" name="address" value="<?= $setval('address', smtp_address()) ?>" />
	    <hr class="cn_100" />

        <h3 class="cn_100 mt40 txt_center">
            <span class="border-span">
                Campos adicionais <small>(Opcional)</small><!-- Opcionais -->
            </span>
        </h3>

        <hr class="cn_100" />

        <span class="cn_40 mt10"><label>E-mail de destino</label></span>
        <input class="cn_60" type="text" name="dest" value="<?= $setval('dest', email_dest()) ?>" />
        <p class="cn_60 fs14 mt0 italic _m">
            Endereço que recebe os e-mails<br>
            Pode ser qualquer e-mail, não necessáriamente um e-mail do seu domínio<br>
            <span class="txt-small-info">Ainda não é usado como destinatário nos formulários de acesso</span><br>
            O padrão é o próprio Endereço SMTP do servidor
        </p>

        <hr class="cn_100" />

        <span class="cn_40 mt10"><label>E-mail para respostas</label> <small>(reply-to)</small></span>
        <input class="cn_60" type="text" name="reply_to" value="<?= $setval('reply_to', email_reply_to()) ?>" />
        <p class="cn_60 fs14 mt0 italic _m">
            Endereço que receberá as respostas quando um usuário clicar em "Responder" no e-mail enviado pelo sistema.<br>
            O padrão também é o Endereço SMTP.
        </p>

        <hr class="cn_100" />

	</div>

	<div class="w85">
    	<button class="btn lg right px40" name="action">Salvar</button>
    </div>
</form>