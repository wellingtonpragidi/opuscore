<?php if( $show_form ) : ?>
    
<form method="POST" action="<?= URL::current() ?>">
	<label for="pswd" class="sr">Digite sua senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= dist_img_url('icon/padlock.svg') ?>" alt="" />
        <input 
            id="pswd" class="pswd" 
            type="text" placeholder="Senha" 
            name="pswd" data-generator="btn-pswd-generator" 
        />
    </div>
    <span class="txt-small-info">
        Mínimo 8 caracteres, letras maiúsculas, minúsculas e números.
    </span>
    <button type="button" id="btn-pswd-generator">Gerar senha</button>

	<button type="submit" name="action" value="activate">Enviar</button>
</form>

<?php 
endif;