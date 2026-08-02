<label for="role">Função</label><br>
<?php 
if( $auth->is_staff() ) :

    echo '<input type="text" name="role" value="' . $admin->role($show) . '" readonly />';

else : 

    $selected = fn($val) => ($val === $show->role) ? ' selected' : ''; ?>

    <div class="edit-wrapper">
        <div class="input-btn-assoc">
            <select id="role" name="role" class="edit-role" disabled>
                <option value="1" <?= $selected(1) ?>><?= $admin->role(1) ?></option>
                <option value="2" <?= $selected(2) ?>><?= $admin->role(2) ?></option>
                <option value="3" <?= $selected(3) ?>><?= $admin->role(3) ?></option>
            </select>

            <button type="button" class="btn trigger">Alterar</button>
        </div>
        <div class="confirm-submit my15">
            <button type="submit" class="btn right" name="action" value="role">
                Atualizar Função
            </button>

            <div class="response"></div>
        </div>
    </div>

<?php 
endif;