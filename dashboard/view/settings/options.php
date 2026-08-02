<div class="w75">
<?php 
	if( INPUT::formSubmitted() ) {
		require dashboard_path('controller/settings/options.php');
	}
?>
</div>
<form class="w75" method="POST" action="<?= URL::current() ?>">
	<?php 
    $opt = get_settings('options');
    $option = INPUT::GET('editor') ?: ($opt['editor'] ?? null);

    $checked = fn($key) => $option === $key ? 'checked' : null;
    ?>

	<fieldset>
        <legend>Editor</legend>
		<div class="rad">
			<input id="punk" type="radio" name="editor" <?= $checked('punk') ?> value="punk">
			<label for="punk"><span>Punk <small class="op08"> &nbsp; Conteúdo (rich-text)</small></span></label>
		</div>
		<div class="rad mt20">
			<input id="tiny" type="radio" name="editor" <?= $checked('tinymce') ?> value="tinymce">
			<label for="tiny"><span>TinyMCE <small class="op08"> &nbsp; Conteúdo (rich-text)</small></span></label>
		</div>
		<div class="rad mt20">
			<input id="ace" type="radio" name="editor" <?= $checked('ace') ?> value="ace">
			<label for="ace"><span>Ace <small class="op08"> &nbsp; Código (HTML)</small></span></label>
		</div>

		<p class="mt40 mr10 txt_right">
			<button class="btn md" name="action" value="editor">Atualizar</button>
		</p>
	</fieldset>
</form>
