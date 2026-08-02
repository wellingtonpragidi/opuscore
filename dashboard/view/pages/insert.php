<div id="insert-router">

	<?php
    if( INPUT::formSubmitted() ) {
        require annex_path('deps/controller.php');
        require dashboard_path('controller/page.php');
    }
    ?>
	<form method="POST" action="<?= URL::current() ?>">

		<div id="insert-title">
            <label for="title" class="screen_reader">Insira o título da página</label>
			<input 
                id="title" type="text" 
                placeholder="Insira o título da página" 
                name="title" value="<?= INPUT::GET('title') ?>" 
                autocomplete="off" autofocus 
            />
		</div>

		<button id="btn" type="submit" name="action" value="insert">REGISTRAR PÁGINA</button>

        <input type="hidden" id="target_type" name="target_type" value="page" />

	</form>

	<button id="goback" class="btn_cancel">CANCELAR</button>

</div>