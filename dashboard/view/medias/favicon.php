<?php 
if( INPUT::formSubmitted() ) {
    require dashboard_path('controller/favicon.php');
}


if( file_exists(DIR . 'favicon.ico') && file_exists(UPLOAD_DIR . 'favicon/32x32.png') ) {
    $filelabel = 'Substituir Icones';
}
else {
    $filelabel = 'Escolher Icones';
}
?>

<p><small>O Ícone do site (favicon) é a imagem que se vê nas abas dos navegadores, nas barras de favoritos, nas pesquisas dos mecanismos de busca e nos aplicativos. <br>A imagem deve ser quadrado e ter pelo menos 512x512 píxeis, com a extensão <u>.png</u>.<br>O sistema gerará 8 tamanhos de ícones .png o .ico e o arquivo manifest para PWA.</small></p>

<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
    <div id="favicon_reader" class="upload readers">
        <input id="icon" class="files_reader media_reader" type="file" name="favicon" accept=".png" />
        <label for="icon" class="btn lg"><?= $filelabel ?></label>
    </div>
    <button type="submit" class="btn lg center mt40 btn_change" name="action" value="favicon">
        Salvar
    </button>
</form>

<div class="favicons">
	<?php 
    $v = '?v=' . mt_rand(10, 99);
	if( file_exists(DIR .'favicon.ico') ) {
		echo '<figure>
		    <img src="'. site_url('favicon.ico' . $v) .'" alt="favicon.ico" />
		    <figcaption>favicon<br>.ico</figcaption>
		</figure>';
	}
	foreach( ["16x16", "32x32", "48x48", "96x96", "144x144", "180x180", "192x192", "512x512"] as $ico ) :
		if( file_exists(UPLOAD_DIR . 'favicons/' . $ico . '.png') ) {
			$favicon = upload_url('favicons/' . $ico . '.png' . $v);
		    echo "<figure>
			    <img src=\"{$favicon}\" alt=\"{$favicon}.png\" />
			    <figcaption>{$ico}<br>.png</figcaption>
			</figure>";
		}
	endforeach;
	?>
</div>

<script>
let mediaReader = document.querySelector('.media_reader');
document.querySelector('.btn_change').style.display = 'none';
mediaReader.addEventListener('change', function () {
    fade.in.selector('.btn_change', 1000);
});
</script>