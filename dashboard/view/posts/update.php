<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php 
	if( INPUT::formSubmitted() ) {
        require_callable('controller-helpers.php');
        require_callable('sanitize-validate.php');
	    require get_dashboard_path('controller/post.php');
	}
	?>
	<div id="update">
		<?php foreach( post_select() as $show ) : ?>
		<div id="article">
			<input type="text" name="title" id="title" value="<?= $show->title ?>" />

			<?php render_editor( $show ) ?>

	        <label for="summary">Resumo</label>
	        <textarea id="summary" name="summary" minlength="50" maxlength="180" placeholder="Mínimo 50 e máximo 180 caracteres"><?= $show->summary ?></textarea>
		</div><!-- #article -->

		<div id="aside">
			<div class="section section-submit">
                <div class="title">
                    <?= ($show->status == 1) ? 'Post publicado' : 'Post salvo como rascunho' ?>
                </div>
                <button type="submit" class="btn progress mt10" name="status" value="1">
                    ATUALIZAR
                </button>
                <button type="submit" class="btn progress sm mt10" name="status" value="0">
                    Salvar rascunho
                </button>
                <input type="hidden" name="action" value="update">
			</div>

			<div class="section section-delete">
				<div class="title">
                    <button 
                        onclick="javascript: return confirm(`Vai mesmo deletar esse post?`)" 
                        type="submit" class="input_false link delete" 
                        name="action" value="delete">
                        Excluir
                    </button>
				</div>
			</div>

			<div class="section post-categories">
				<div class="title list-category">Categorias</div>
				<?php echo $post->list_post_categories() ?>
                <input type="hidden" name="categories_changed" value="0">
			</div>

			<div class="section featured-image">
				<div class="title">Imagem destacada</div>
                <?php 
                $render = Image::render($show);
                if( $render['has_image'] ) : ?>
                    <img src="<?= $render['show_image'] ?>" alt="" />
                    <button 
                        onclick="javascript: return confirm(`Quer mesmo deletar a imagem destacada do post?`)"  
                        type="submit" class="unlink btn sm m mt5" 
                        name="action" value="unlink">
                        Remover imagem
                    </button>
                <?php else : ?>
		            <div id="load-image" class="upload readers">
			            <input id="featured" class="files_reader" type="file" 
			            accept="image/*" name="attachment" />
			            <label for="featured" class="btn lg">Escolher imagem</label>
			        </div>
			    <?php endif; ?>
			</div>

			<div class="section section-slug">
				<label class="title" for="edit-slug">Slug</label>
				<p><input id="edit-slug" class="sm" type="text" name="slug" value="<?= $show->slug ?>" /></p>
			</div>

            <div class="section section-segment">
                <?php if( $show->segment ) : ?>
                <label for="segment" class="title">Caminho da URL</label>
                <p>
                    <select id="segment" class="sm" name="segment">
                        <option selected value="<?= $show->segment ?>"><?= $show->segment ?></option>
                    </select>
                    <input type="hidden" name="segment_changed" value="0">
                </p>
                <?php endif ?>
            </div>

			<div class="section info">
				<div class="title title-info">Informações do post</div>
				<p>
					<span icon="user" aria-label="Publicado por:" title="Publicado por:"></span>
					&nbsp;<?= $show->author ?>
				</p>
				<p>
					<span icon="calendar" aria-label="Publicado em:" title="Publicado em:"></span>
					&nbsp;<?= chronos_format( $show->created ) ?>
				</p>
				<?php 
				if( $show->updated ) : ?>
				<p>
				    <span icon="calendarfill" aria-label="Atualizado em:" title="Atualizado em:"></span> 
				    &nbsp;<?= chronos_format( $show->updated ) ?>
				</p>
				<?php endif;
                
                if( $show->segment ) :
                    $post_url = URL::root($show->segment); ?>
                    <p>URL: <a href="<?= $post_url ?>" target="_blank"><?= $post_url ?></a></p>
                <?php endif ?>

				<input type="hidden" name="author" value="<?= $show->author ?>" />
				<input type="hidden" name="date" value="<?= $show->date ?>" />

                <input type="hidden" id="target_id" name="target_id" value="<?= $show->ID ?>" />
                <input type="hidden" id="target_type" name="target_type" value="post" />
			</div>
		</div><!-- #aside -->
		
	<?php endforeach; ?>
	</div>
</form>
<script>
document.querySelector('form').addEventListener('submit', () => {
    console.log('segment enviado:', document.getElementById('segment').value);
});
</script>