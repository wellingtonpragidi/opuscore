<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php 
	if( INPUT::formSubmitted() ) {
        require annex_path('deps/controller.php');
	    require dashboard_path('controller/page.php');
	}
	?>
	<div id="update">
		<?php foreach( select_page() as $key => $show ) : ?>
		<div id="article">
			<input type="text" name="title" id="title" value="<?= $show->title ?>" />

			<?php render_editor( 'page', $show->content ) ?>

	        <label for="summary">Resumo</label>
	        <textarea id="summary" name="summary" minlength="50" maxlength="180" placeholder="Mínimo 50 e máximo 180 caracteres"><?= $show->summary ?></textarea>
		</div><!-- #article -->

		<div id="aside">
			<div class="section section-submit">
                <div class="title">
                    <?= ($show->status == 1) ? 'Página publicada' : 'Página salva como rascunho' ?>
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
                        onclick="javascript: return confirm(`Vai mesmo deletar esta página?`)"  
                        type="submit" class="input_false link delete" 
                        name="action" value="delete">
                        Excluir
                    </button>
				</div>
			</div>
			<div class="section page-template">
				<label for="template" class="title">Modelo da página</label>
				<?php echo $page->template( $show ) ?>
			</div>
 
            <div class="section page-parent">
                <label for="parent" class="title mb10">Vinculado a página:</label>
                <select id="parent" name="parent">
                    <option value="0" selected>Página superior</option>
                    <?php 
                    foreach( $page->select_option() as $value ) : 
                        $selected = ($show->parent === $value->ID) ? ' selected' : '';
                        echo <<<HTML
                        <option value="{$value->ID}"{$selected}>{$value->title}</option>
                        HTML;
                    endforeach; ?>
                </select>
            </div>

			<div class="section featured-image">
				<div class="title">Imagem destacada</div>
                <?php 
                $render = Image::render($show);
                if( $render['has_image'] ) : ?>
                    <img src="<?= $render['show_image'] ?>" alt="" />
                    <button 
                        onclick="javascript: return confirm(`Quer mesmo deletar a imagem destacada da página?`)"   
                        type="submit" class="unlink btn sm m mt5" 
                        name="action" value="unlink">
                        Remover imagem
                    </button>
	        	<?php else : ?>
    			    <div id="load-image" class="upload readers">
    			        <input id="featured" class="files_reader" type="file" 
    			            accept=".gif, .jpg, .jpeg, .png, .webp" name="attachment" />
    			         <label for="featured" class="btn lg">Escolher imagem</label>
    			    </div>
			    <?php endif; ?>
			</div>
			<div class="section section-slug">
				<label for="edit-slug" class="title">Slug</label>
				<p><input id="edit-slug" class="sm" type="text" name="slug" value="<?= $show->slug ?>" /></p>
			</div>
			<div class="section info">
				<div class="title title-info">Informações da página</div>
				<p>
				    <span icon="calendarfill" aria-label="Atualizado em:" title="Atualizado em:"></span> 
				    &nbsp;<?= chronos_format($show->lastmod) ?>
				</p>

				<?php 
                if( $show->segment ) :
                    $page_url = URL::root($show->segment); ?>
                    <p>URL: <a href="<?= $page_url ?>" target="_blank"><?= $page_url ?></a></p>
                <?php endif ?>

				<input type="hidden" name="author" value="<?= $show->author ?>" />
				<input type="hidden" name="date" value="<?= $show->lastmod ?>" />
                <input type="hidden" id="target_type" name="target_type" value="page" />
                <input type="hidden" id="target_id" name="target_id" value="<?= $show->ID ?>" />
			</div>
		</div><!-- #aside -->
		
	<?php endforeach; ?>
	</div><!-- #update -->
</form>