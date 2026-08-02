<?php 
if( INPUT::formSubmitted() ) {
    require dashboard_path('controller/admin.php');
}


$active_role = _POST::has('role') ? _POST::int('role') : 3;
$selected    = fn($n) => ($n === $active_role) ? 'selected' : '';
?>
<form class="flexbox mb10 cn_80" method="POST" action="<?php URL::current() ?>">
	<div class="cn_50 pr10">
        <label for="name" class="sr">Nome</label>
		<input 
            id="name" type="text" placeholder="Nome" 
            name="name" value="<?= _POST::str('name') ?>" 
            required 
        />
	</div>
	<div class="cn_50 pl10">
        <label for="email" class="sr">E-mail</label>
		<input 
            id="email" type="email" placeholder="E-mail" 
            name="email" value="<?= INPUT::str('email') ?>" 
            required 
        />
	</div>

    <div class="cn_50 my15">
    	<label for="role">Função</label>
    	<select id="role" name="role">
            <option value="1" <?= $selected(1) ?>>Principal</option>
            <option value="2" <?= $selected(2) ?>>Gerenciador</option>
            <option value="3" <?= $selected(3) ?>>Produtor</option>
    	</select><br>
    </div>
    <div class="cn_50 my15 pt30 pl10">
        <input 
            id="send_mode" class="ckb" type="checkbox" 
            name="send_mode" value="true" checked 
        />
        <label for="send_mode">
            <span class="fs14">Enviar link para o novo administrador definir a senha e ativar a conta</span>
        </label>
    </div>

    <div id="early-enable" class="clean cn_100">
        <div class="floatleft w50 mt10 pr0">
            <div class="password">
                <label for="pswd" class="sr">Senha</label>
                <input 
                    id="pswd" class="pswd" data-generator="btn-pswd-generator" 
                    type="text" placeholder="Senha" 
                    name="pswd" value="<?= INPUT::str('pswd') ?>" 
                />
            </div>
            <span class="color-danger fs14">
                Mínimo 8 caracteres, letras maiúsculas, minúsculas e números.
            </span>
            <button type="button" id="btn-pswd-generator" class="btn sm dblock mr0 ml my5">
                Gerar senha
            </button>
        </div>

        <div class="floatleft w50 mt20 pl30">
            <span>Status: </span> 
            <span id="status" class="color-link"> Pendente</span>
        </div>
    </div>

    <div class="cn_100 pr10 mt30 txt_right">
    	<button type="submit" class="btn lg" name="action" value="register">
            Registrar
        </button>
    </div>
</form>