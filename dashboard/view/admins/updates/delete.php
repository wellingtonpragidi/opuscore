<button type="button" id="delete" class="btn danger right">
    <span icon="trash" size="19"></span>
    <span class="ml25"> Excluir administrador</span>
</button>

<div id="confirm-delete">
    <div class="password">
        <label for="pswd_confirm_delete" class="sr">Senha atual</label>
        <input 
            id="pswd_confirm_delete" class="pswd" 
            type="password" placeholder="Senha atual para confirmar" 
            name="pswd_confirm_delete" 
        />
    </div>
    <button type="submit" class="btn dark right mt15" name="action" value="delete">
        Excluir
    </button>
</div>

<input type="hidden" name="name" value="<?= $auth->logged('name') ?>" />
<input type="hidden" name="email" value="<?= $auth->logged('email') ?>" />
<input type="hidden" name="target_id" value="<?= $auth->logged('ID') ?>" />

<?php
if( _POST::formSubmitted() ) {
    require dashboard_path('controller/admin.php');
}