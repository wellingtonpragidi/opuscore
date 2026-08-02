<div id="default-settings" class="reading mt40 cn_50">
	<hr>
	<?php
	if( INPUT::formSubmitted() ) {
        require dashboard_path( 'controller/settings/reading.php' );
    }
	?>
	<h2 class="pl15"><span class="border-span">Públicas <small>(template)</small></span></h2>
	<form class="pb40" method="POST" action="<?= URL::current() ?>">
		<div class="flexbox">		
			<span class="cn_70 mt10"><label for="pppage">Exibição de artigos por paginação</label></span>
            <input id="pppage" class="cn_25 lg" type="number" name="articles_per_page" 
                value="<?= $_POST['articles_per_page'] ?? articles_per_page() ?>">
        </div>
        <input type="hidden" name="form_action" value="public">
        <button type="submit" class="btn mt30" name="action">Salvar alterações</button>
	</form>

    <hr class="my40">

	<h2 class="mt40 pt40 pl15"><span class="border-span">Painel de controle <small>(dashboard)</small></span></h2>
	<form method="POST" action="<?= URL::current() ?>">
		<div class="flexbox">		
			<span class="cn_70 mt10">
				<label for="pppage">Exibição de páginas por paginação</label>
			</span>
            <input id="pppage" class="cn_25 lg" type="number" name="pages_per_page" value="<?= $_POST['pages_per_page'] ?? per_page('pages') ?>">

        	<hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="pparticle">Exibição de articles por paginação</label>
			</span>
            <input id="pparticle" class="cn_25 lg" type="number" name="articles_per_page" value="<?= $_POST['articles_per_page'] ?? per_page('articles') ?>">

            <hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="ppcomm">Exibição de comentários por paginação</label>
			</span>
            <input id="ppcomm" class="cn_25 lg" type="number" name="comments_per_page" value="<?= $_POST['comments_per_page'] ?? per_page('comments') ?>">

            <hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="ppuser">Exibição de usuários por paginação</label>
			</span>
            <input id="ppuser" class="cn_25 lg" type="number" name="users_per_page" value="<?= $_POST['users_per_page'] ?? per_page('users') ?>">

            <?php if( statistics() ) : ?>
	            <hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="ppsts">Exibição de estatísticas por paginação</label>
				</span>
	            <input id="ppsts" class="cn_25 lg" type="number" name="statistics_per_page" value="<?= $_POST['statistics_per_page'] ?? per_page('statistics') ?>">
	        <?php endif; ?>

	        <?php if( editor_is('punk') ) : ?>
            	<hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="media_manager_perload">Exibição de mídia por carga na pagina de mídias</label>
				</span>
	            <input id="media_manager_perload" class="cn_25 lg" type="number" name="media_manager_perload" value="<?= $_POST['media_manager_perload'] ?? media_manager_limit() ?>">

	            <hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="media_popup_perload">Exibição de mídia por carga no popup do editor</label>
				</span>
	            <input id="media_popup_perload" class="cn_25 lg" type="number" name="media_popup_perload" value="<?= $_POST['media_popup_perload'] ?? media_popup_limit() ?>">
	        <?php endif; ?>

        </div>
        <input type="hidden" name="form_action" value="admin">
        <button type="submit" class="btn mt30" name="action">Salvar alterações</button>
	</form>

</div>