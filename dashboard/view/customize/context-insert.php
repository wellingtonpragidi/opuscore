<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php 
	if( INPUT::formSubmitted() ) {
        require_callable('sanitize-validate.php');
		require get_dashboard_path('controller/context.php');
	}
	?>
	<div id="insert-context" class="insert">
		<article class="flexbox">
			<div class="cn_75 pr15">

				<label for="section" class="sr">Seção</label>
				<input 
                    id="section" class="lg mt20 mb5" type="text" 
                    name="section" value="<?= INPUT::GET('section') ?>"
                    max="30" required 
                    placeholder="Seção" 
                />
                <?php $sections = Context::sections();
                if( empty($sections) ) : ?>
                    <label for="section_select" class="sr">Ou selecione existente</label>
                    <select id="section_select" class="lg">
                        <option value="">-- Selecionar seção existente --</option>
                        <?php foreach( $sections as $section ) : ?>
                            <option value="<?= htmlspecialchars($section) ?>"><?= $section ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif;?>

				<label for="title" class="sr">Título</label>
				<input 
                    id="title" class="mt20 lg" type="text" 
                    name="title" value="<?= INPUT::GET('title') ?>"
                    max="30" required 
                    placeholder="Título" 
                />

			</div>

			<div class="cn_25 pr15">
		        <button type="submit" class="btn xlg mt20 ml15 w100" name="action" value="insert">INSERIR</button>
		    </div>


		    <div id="value-mode" class="cn_100 mt40">
			    <?php render_editor() ?>
		    </div>

            <input type="hidden" id="target_type" name="target_type" value="context" />
            <input type="hidden" id="target_id" name="target_id" value="<?= INPUT::GET('name') ?>" />

		</article>
	</div>
</form>

<script>
document.getElementById('section_select')?.addEventListener('change', function() {

    var input = document.getElementById('section');

    if( this.value !== '' ) {
        input.value = this.value;
    }
});
</script>