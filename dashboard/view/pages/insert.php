<div id="insert-title-before">

	<?php
    if( INPUT::formSubmitted() ) {
        require_callable('sanitize-validate.php');
        require get_dashboard_path('controller/page.php');
    }
    ?>
	<form method="POST" action="<?= URL::current() ?>">

		<div class="the-title">
            <label for="title" class="screen_reader">Insira o título da página</label>
			<input 
                id="title" type="text" name="title" 
                placeholder="Insira o título da página" autocomplete="off" autofocus 
            />
		</div>

		<button type="submit" name="action" value="insert">REGISTRAR PÁGINA</button>

        <input type="hidden" id="target_type" name="target_type" value="page" />

	</form>

	<button id="go-back" class="btn_cancel">CANCELAR</button>

</div>