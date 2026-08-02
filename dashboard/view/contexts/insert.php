<div id="insert-router">

    <?php 
    if( INPUT::formSubmitted() ) {
        require_annex('deps/sanitize-validate.php');
        require dashboard_path('controller/context.php');
    }
    ?>

    <form method="POST" action="<?= URL::current() ?>">

        <fieldset>
            <label for="section" class="sr">Seção</label>
            <?php 
            $sections = Context::select_sections();
            $count = count( $sections );
            if( ! empty($sections) ) : ?>

                <label for="insert-section_select" class="sr">Selecione seção existente</label>
                <select id="insert-section_select" class="xlg">
                    <option value="">Selecionar seção existente</option>
                    <?php foreach( $sections as $section ) : ?>
                        <option value="<?= htmlspecialchars($section) ?>">
                            <?= $section ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            <?php endif; ?>
            <label for="insert-section" class="sr">Adicionar nova seção</label>
            <input 
                id="insert-section" class="xlg mt10" type="text" 
                name="section" value="<?= INPUT::GET('section') ?>"
                placeholder="<?= ($count > 1) ? 'Adicionar nova seção' : 'Adicionar seção' ?>" 
                max="30" autocomplete="off" required 
            />
        </fieldset>

        <div id="insert-title">
            <label for="title" class="sr">Insira o título do contexto</label>
            <input 
                id="title" type="text" 
                placeholder="Insira o título do contexto" 
                name="title" value="<?= INPUT::GET('title') ?>" 
                autocomplete="off" required 
            />
        </div>

        <button id="btn" type="submit" name="action" value="insert">REGISTRAR CONTEXTO</button>

        <input type="hidden" id="target_type" name="target_type" value="context" />

    </form>

    <button id="goback" class="btn_cancel">CANCELAR</button>

</div>

<script>
document.getElementById('insert-section_select')?.addEventListener( 'change', function() {
    if( this.value !== '' ) {
        document.getElementById('insert-section').value = this.value;
    }
});
</script>