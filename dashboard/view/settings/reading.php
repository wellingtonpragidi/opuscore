<div id="default-settings" class="reading mt40 cn_50">
	<hr>
	<?php
	if( INPUT::formSubmitted() ) {
        require get_dashboard_path( 'controller/settings/reading.php' );
    }
	?>
	<h2 class="pl15"><span class="border-span">Públicas <small>(template)</small></span></h2>
	<form class="pb40" method="POST" action="<?= URL::current() ?>">
		<div class="flexbox">		
			<span class="cn_70 mt10"><label for="pppage">Exibição de posts por paginação</label></span>
            <input id="pppage" class="cn_25 lg" type="number" name="posts_perpage" 
                value="<?= $_POST['posts_perpage'] ?? posts_per_page() ?>">
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
            <input id="pppage" class="cn_25 lg" type="number" name="pages_perpage" value="<?= $_POST['pages_perpage'] ?? per_page('pages') ?>">

        	<hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="pppost">Exibição de posts por paginação</label>
			</span>
            <input id="pppost" class="cn_25 lg" type="number" name="posts_perpage" value="<?= $_POST['posts_perpage'] ?? per_page('posts') ?>">

            <hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="ppcomm">Exibição de comentários por paginação</label>
			</span>
            <input id="ppcomm" class="cn_25 lg" type="number" name="comments_perpage" value="<?= $_POST['comments_perpage'] ?? per_page('comments') ?>">

            <hr class="cn_100">

			<span class="cn_70 mt10">
				<label for="ppuser">Exibição de usuários por paginação</label>
			</span>
            <input id="ppuser" class="cn_25 lg" type="number" name="users_perpage" value="<?= $_POST['users_perpage'] ?? per_page('users') ?>">

            <?php if( statistics() ) : ?>
	            <hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="ppsts">Exibição de estatísticas por paginação</label>
				</span>
	            <input id="ppsts" class="cn_25 lg" type="number" name="statistics_perpage" value="<?= $_POST['statistics_perpage'] ?? per_page('statistics') ?>">
	        <?php endif; ?>

	        <?php if( editor_is('punk') ) : ?>
            	<hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="mediapage_perload">Exibição de mídia por carga na pagina de mídias</label>
				</span>
	            <input id="mediapage_perload" class="cn_25 lg" type="number" name="mediapage_perload" value="<?= $_POST['mediapage_perload'] ?? per_load('page') ?>">

	            <hr class="cn_100">

				<span class="cn_70 mt10">
					<label for="mediapopup_perload">Exibição de mídia por carga no popup do editor</label>
				</span>
	            <input id="mediapopup_perload" class="cn_25 lg" type="number" name="mediapopup_perload" value="<?= $_POST['mediapopup_perload'] ?? per_load('popup') ?>">
	        <?php endif; ?>

        </div>
        <input type="hidden" name="form_action" value="admin">
        <button type="submit" class="btn mt30" name="action">Salvar alterações</button>
	</form>

</div>