<?php
if( $auth->is_self() ) : 
    # Somente o dono da propria conta pode alterar sua senha

    $selected = fn($val) => ($val === $show->role) ? 'selected' : '';
?>
    <div class="edit-wrapper">
        <span class="label">Senha</span><br>
        <div class="password">
            <label for="current_pswd" class="sr">Senha atual</label>
            <input 
                id="current_pswd" class="pswd edit-entry" 
                type="text" placeholder="Senha atual" 
                name="current_pswd" 
            />
        </div>
        <div class="password mt10">
            <label for="pswd" class="sr">Nova senha</label>
            <input 
                id="pswd" class="pswd" data-generator="btn-pswd-generator" 
                type="text" placeholder="Nova senha" 
                name="pswd" 
            />
        </div>
        
        <span class="txt-small-info">
            Mínimo 8 caracteres, letras maiúsculas, minúsculas e números.
        </span>
        <button type="button" id="btn-pswd-generator" class="btn sm dblock mr0 ml my5">
            Gerar senha
        </button>

        <button type="submit" class="btn right" name="action" value="pswd">
            Atualizar senha
        </button>

        <div class="response"></div>

    </div>

<?php 
endif;