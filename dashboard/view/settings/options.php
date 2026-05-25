<div class="w75">
<?php 
	if( INPUT::formSubmitted() ) {
		require get_dashboard_path('controller/settings/options.php');
	}
?>
</div>
<form class="w75" method="POST" action="<?= URL::current() ?>">
	<?php 
	$checked = function($editor) {
	    $current = $_POST['editor'] ?? 'punk';
	    return $current === $editor ? 'checked' : '';
	}; 
	?>
	<fieldset><legend>Editor</legend>
		<div class="rad">
			<input id="punk" type="radio" name="editor" <?= $checked('punk') ?> value="punk">
			<label for="punk"><span>Punk <small class="op08"> &nbsp; Conteúdo (Estilo Word)</small></span></label>
		</div>
		<div class="rad mt20">
			<input id="tiny" type="radio" name="editor" <?= $checked('tinymce') ?> value="tinymce">
			<label for="tiny"><span>TinyMCE <small class="op08"> &nbsp; Conteúdo (Estilo Word)</small></span></label>
		</div>
		<div class="rad mt20">
			<input id="ace" type="radio" name="editor" <?= $checked('ace') ?> value="ace">
			<label for="ace"><span>Ace <small class="op08"> &nbsp; Código Fonte</small></span></label>
		</div>
		<div class="rad mt20">
			<input id="codemirror" type="radio" name="editor" <?= $checked('codemirror') ?> value="codemirror">
			<label for="codemirror"><span>CodeMirror <small class="op08"> &nbsp; Código Fonte</small></span></label>
		</div>

		<p class="mt40 mr10 txt_right">
			<button class="btn md" name="action">Atualizar</button>
		</p>
		<input type="hidden" name="form_action" value="editor">
	</fieldset>
</form>

<form id="form_sts" class="w75 mt30" method="post" action="<?= URL::current() ?>">
	<?php $sts_status = $_POST['action'] ?? statistics(); ?>
	<fieldset><legend>Estatísticas</legend>
	    <input type="hidden" id="hidden_sts" name="action" value="<?= ($sts_status) ? 'true' : 'false' ?>">
	    <label id="toggle-switch" title="<?= ((bool) $sts_status) ? 'Desabilitar' : 'Habilitar' ?>">
	        <input id="ckb_sts" type="checkbox" <?= ($sts_status) ? 'checked' : '' ?>>
	        <span id="slide-round"></span>
	        <span class="span-label">
	            <?= ((bool) $sts_status) ? 'Habilitado' : 'Desabilitado' ?>
	        </span>
	    </label>
	    <input type="hidden" name="form_action" value="statistics">
	</fieldset>
</form>

<script>
document.getElementById('ckb_sts').addEventListener('change', function() {
    const formSts = document.getElementById('form_sts');
    const hiddenSts = formSts.querySelector('#hidden_sts');
    hiddenSts.value = this.checked ? 'true' : 'false';
    formSts.submit();
});
</script>