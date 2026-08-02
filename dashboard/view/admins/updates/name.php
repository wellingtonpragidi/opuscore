<?php 
if( $auth->is_self() ) : ?>

    <div class="edit-wrapper">
        <label for="name" class="mt40">Nome</label><br>
        <div class="input-btn-assoc">
            <input 
                id="name" type="text" class="edit-entry" 
                name="name" value="<?= $show->name ?>" 
                data-original="<?= $show->name ?>" 
            />
            <button type="button" class="btn trigger">Alterar</button>
        </div>
        <div class="confirm-submit my15">
            <button type="submit" class="btn right" name="action" value="name">
                Atualizar Nome
            </button>
        </div>

        <div class="response"></div>
    </div>

<?php 
else :

    echo '<input class="mt40" type="text" name="name" value="' . $show->name . '" readonly />';

endif;