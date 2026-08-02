<?php 
if( INPUT::formSubmitted() ) {
    require annex_path('deps/controller.php');
    require dashboard_path('controller/category.php');
}
?>
<form id="category" method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php foreach( select_categories() as $show ) : ?>
		<div class="article">
		    <label for="title" class="screen_reader">Nome</label>
			<input id="title" class="lg" type="text" name="title" value="<?= $show->name ?>" />

			<label for="parent">Subcategoria de:</label>
			<select id="parent" name="parent">
				<option selected>Nenhum</option>
				<?= $category->select_option() ?>
			</select>

			<label for="dscpt">Descrição</label>
			<div class="textfield formit">
			    <div class="lg" data-content="dscpt" contenteditable="true" placeholder="Descrição"><?= $show->content ?></div>
			    <textarea id="dscpt" name="dscpt"><?= $show->content ?></textarea>
			</div>
		</div>

		<div id="aside" class="aside">
			<div class="section section-submit">
                <div class="title"></div>
                <button class="btn lg m txt_center" type="submit" name="action" value="update">ATUALIZAR</button>
            </div>
            
            <div class="section section-attachment">
	            <div class="title mb5 mt40">Imagem destacada</div>
			    <?php 
                $render = Image::render($show); 
                if( $render['has_image'] ) : ?>
                    <img src="<?= $render['show_image'] ?>" alt="" />
                    <button 
                        onclick="javascript: return confirm(`Quer mesmo deletar a imagem destacada da categoria?`)" 
                        type="submit" class="unlink btn sm m mt5" 
                        name="action" value="unlink">
                        Remover imagem
                     </button>
                <?php else : ?>
    			    <div id="load-image" class="upload readers">
    			        <input id="featured" class="files_reader" type="file" name="attachment" />
    			        <label for="featured" class="btn lg">Escolher imagem</label>
    			    </div>
			    <?php endif; ?>
			</div>
            <div class="section section-delete">
            	<div class="title mt40 mb5">
                    <button onclick="javascript: return confirm(`Vai mesmo deletar esta categoria?\n\nNão será possível caso essa categoria possua sucessores.`)" 
                        class="input_false link delete txt_right ml mt40" 
                        name="action" value="delete">
                        Excluir Categoria
                    </button>
			    </div>
			</div>
			<div class="section info">
				<div class="title mt40 mb5"></div>
			    <p>Publicado em: <?= chronos_format($show->created) ?></p>

			    <p>URL:&nbsp;
                    <a href="<?= category_url($show) ?>" target="_blank" rel="noopener">
                        <?= category_url($show) ?>
                    </a>
                </p>
			    
			    <input type="hidden" name="date" value="<?= $show->created ?>" />
			    <input type="hidden" name="slug" value="<?= Ensure::slug($show->name) ?>" />

                <input type="hidden" id="target_id" name="target_id" value="<?= $show->ID ?>" />
                <input 
                    type="hidden" id="target_type" 
                    name="target_type" value="category-article" 
                />
                <input type="hidden" id="type" name="type" value="article" />
			</div>
		</div>

    <?php endforeach; ?>
</form>