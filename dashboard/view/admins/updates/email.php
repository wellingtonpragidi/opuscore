<?php 
if( $auth->is_authorized() ) : ?>

    <div class="edit-wrapper">
        <label for="email">E-mail</label><br>
        <div class="input-btn-assoc">
            <input 
                id="email" type="email" class="edit-entry" 
                name="email" value="<?= $show->email ?>" 
                data-original="<?= $show->email ?>" 
                readonly 
            />
            <button type="button" class="btn trigger">Alterar</button>
        </div>

        <div class="confirm-submit my15">
            <div class="password">
                <label for="pswd_confirm_email" class="sr">Senha atual</label>
                <input 
                    id="pswd_confirm_email" class="pswd" 
                    type="password" placeholder="Senha atual para confirmar" 
                    name="pswd_confirm_email" 
                />
            </div>

            <button type="submit" class="btn right mt15" name="action" value="email">
                Atualizar E-mail
            </button>

            <div class="response"></div>
        </div>
    </div>

<?php 
else :

    echo '<input type="text" name="email" value="' . $show->email . '" readonly />';

endif;