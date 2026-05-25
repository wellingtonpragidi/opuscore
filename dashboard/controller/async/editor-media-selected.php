<?php
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';
    
foreach( media_selection() as $show ) : ?>
	<div id="info">
		<h3>Detalhes do arquivo</h3>
        <p><?= chronos_format($show->created, 2) ?></p>
        <p>
            <strong>Enviado em:</strong> 
            <a href="<?php Media::uploaded_in($show) ?>" target="_blank" rel="noopener">
                <?= $show->title ?>
            </a>
        </p>
        <p><strong>Nome:</strong> <?= FileInfo::filename( Image::source($show) ) ?></p>
        <p><strong>Tipo:</strong> <?= FileInfo::mimetype( Image::source($show) ) ?></p>
        <p><strong>Tamanho:</strong> <?= FileInfo::size( Image::source($show) ) ?></p>
        <p><strong>Dimensão:</strong> <?= FileInfo::dimension( Image::source($show) ) ?></p>

        <div id="edit-alttext">
            <label for="alttext">Texto alternativo *</label>
			<input id="alttext" type="text" name="alttext" />
		</div>

        <p class="purl">URL's do arquivo:</p>
        <?php
        $sourceURL = '';
        foreach( ['original', 'wide', 'larger', 'minor', 'plain'] as $scope ) {

            $label = match($scope) {
                'original' => 'Original',
                'wide'     => 'Amplo',
                'larger'   => 'Maior',
                'minor'    => 'Menor',
                'plain'    => 'Simples | Categoria',
                default    => ucfirst($scope),
            };

            $filepath = $show->attachment->{$scope}->path ?? null;

            if( is_string($filepath) && file_exists(UPLOAD_DIR . $filepath) ) {
                $fileurl = upload_url($show->attachment->{$scope}->path);

                echo "
                <label class=\"fs13\" for=\"{$scope}\">{$label}</label>
                <input id=\"{$scope}\" type=\"url\" value=\"{$fileurl}\" readonly />";

                $sourceURL = $fileurl;
            }
        }
	    ?>
        <input type="hidden" id="fileurl" value="<?= $sourceURL ?>" />
		<p class="deleteit">
            <button type="button" 
                class="input_false link delete right" 
                id="deleteit" name="deleteit" value="<?= $show->ID ?>">
                Excluir arquivo
            </button>
        </p>
	</div>
	<div id="return"></div>
<?php endforeach; 
