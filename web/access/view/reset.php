<?php if( $show_form ) : ?>
    
<form method="POST" action="<?= URL::current() ?>">
    <label for="pswd">Digite sua nova senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= dist_img_url('icon/padlock.svg') ?>" alt="" />
        <input 
            id="pswd" class="pswd" 
            type="text" placeholder="Senha" 
            name="pswd"  data-generator="btn-pswd-generator" 
        />
    </div>
    <span class="txt-small-info">
        Mínimo 8 caracteres, letras maiúsculas, minúsculas e números.
    </span>
    <button type="button" id="btn-pswd-generator">Gerar senha</button>

    <label for="confirm-pswd">Digite novamente sua nova senha.</label>
    <div class="formit password">
        <img class="padlock" src="<?= dist_img_url('icon/padlock.svg') ?>" alt="" />
        <input type="text" id="confirm-pswd" class="pswd" placeholder="Confirmar senha" name="confirm-pswd" />
    </div>
    <button type="submit" name="action" value="reset">Atualizar</button>
</form>
<ul>
    <li><a href="<?= access_url('login') ?>">Login</a></li>
</ul>

<?php endif;