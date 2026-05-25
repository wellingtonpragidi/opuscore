<?php 
/*
->> controller
if( $admin->logged_role() !== 1 ) {
    echo '<p>Sem autorização para adicionar ou excluir categoria.</p>';
}*/
?>
<div id="categories" class="flexbox">
	<div class="insert cn_40">
        <?php 
        if( INPUT::formSubmitted() ) {
            require_callable('controller-helpers.php');
            require_callable('sanitize-validate.php');
            require get_dashboard_path('controller/category.php');
        }
        ?>
		<form method="POST" action="<?php URL::current() ?>" enctype="multipart/form-data">
			<label for="title">Nome</label>
			<input id="title" type="text" name="title" required />

			<label for="parent">Subcategoria de:</label>
			<select id="parent" name="parent">
				<option value="0" selected>Nenhum</option>
                <?php echo $category->select_option() ?>
			</select>

			<label for="dscpt">Descrição</label>
			<div class="textfield formit">
			    <div class="lg" data-content="dscpt" contenteditable="true" placeholder="Descrição"></div>
			    <textarea id="dscpt" name="dscpt"></textarea>
			</div>

		    <div id="load-image" class="upload readers">
		        <input id="upload" class="files_reader" type="file" name="attachment" />
		        <label for="upload" class="btn progress upper">Escolher Imagem</label>
		    </div>

            <button class="btn lg" type="submit" name="action" value="insert">PUBLICAR</button>


            <input type="hidden" id="target_type" name="target_type" value="post" />
            <input type="hidden" name="media_type" value="category-post" />
            <input type="hidden" name="target" value="category" />
		</form>
	</div>
	<div class="select cn_60 mt20">
		<table class="striped horz outline">
            <tr>
            	<th><span icon="image" size="20"></span></th>
                <th>Título</th>
                <th>Slug</th>
                <th>Data</th>
                <th>Nº</th>
                <th><span icon="trash" size="20"></span></th>
            </tr>
            <?php echo $category->select_table() ?>
        </table>
	</div>
</div>