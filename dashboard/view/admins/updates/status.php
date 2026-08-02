<span class="label">Status</span><br>
<?php 
if( $show->status === 0 && $auth->is_any_manager() ) : ?>

    <div class="edit-wrapper">
        <div class="input-btn-assoc">
            <div style="width: 100%;">
                <input 
                    id="status-1" class="rad" type="radio" 
                    name="status" value="1" 
                />
                <label for="status-1"><span class="fs14">Confirmado</span></label>

                <input 
                    id="status-0" class="rad" type="radio" 
                    name="status" value="0" checked 
                />
                <label for="status-0"><span class="fs14">Pendente</span></label>
            </div>
            <button type="button" class="btn trigger">Alterar</button>
        </div>
        <div class="confirm-submit my15">
            <button type="submit" class="btn right" name="action" value="status">
                Atualizar Status
            </button>

            <div class="response"></div>
        </div>
    </div>

<?php 
else :

    echo '<input type="text" name="status" value="' . $admin->status($show) . '" readonly />';

endif;