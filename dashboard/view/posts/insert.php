<div id="insert-title-before">

	<?php 
    if( INPUT::formSubmitted() ) {
        require_callable('sanitize-validate.php');
        require get_dashboard_path('controller/post.php');
    }
    ?>

	<form method="POST" action="<?= URL::current() ?>">

		<div class="the-title">
			<input 
                type="text" name="title" 
                placeholder="Insira o título do post" autocomplete="off" autofocus 
            />
		</div>

		<button type="submit" name="action" value="insert">REGISTRAR POST</button>

        <input type="hidden" id="target_type" name="target_type" value="post" />

	</form>

	<button id="go-back" class="btn_cancel">CANCELAR</button>

</div>