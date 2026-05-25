<?php 
if( INPUT::formSubmitted() ) {
    require get_dashboard_path('controller/favicons.php');
}

if( file_exists( UPLOAD_DIR . 'favicons/poster.png' ) ) {
	$poster_url = upload_url( 'favicons/poster.png?v=' . mt_rand(10, 99) );
	$poster = <<<HTML
	<figure>
	    <img src="$poster_url" alt="poster" />
	    <figcaption>favicon/poster.png</figcaption>
	</figure>
	HTML;
	$filelabel = 'Substituir Imagem';
}
else {
	$filelabel = 'Escolher Imagem';
}
?>

<p><small>Essas são imagens usadas em compartilhamento de redes sociais como twitter e facebook quando não há nenhuma imagem destacada na página.</small></p>
<p><small>O sistema não faz o redimensionamento, corte e conversão dessa imagem. O tamanho recomendado para a imagem é de no mínimo 600x340 pixels, com a extensão <u>.png</u></small></p>

<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
    <div id="poster_reader" class="upload readers">
        <input id="card" class="files_reader media_reader" type="file" name="upload" accept=".png" />
        <label for="card" class="btn lg"><?= $filelabel ?></label>
    </div>
    <button type="submit" class="btn lg center mt40 btn_change" name="action" value="poster">
        Salvar
    </button>
</form>

<?php echo isset($poster) ? $poster : ''; ?>
