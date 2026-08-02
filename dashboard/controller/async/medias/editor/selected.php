<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

$id = URL::int('checked_id');

foreach( $image->select_checked($id) as $show ) : ?>
    
	<div id="info">
		<h3>Detalhes do arquivo</h3>

        <p><span icon="calendar"></span> <?= chronos_format($show->created, 2) ?></p>
        <p>
            <strong>Enviado em:</strong> 
            <a href="<?= url_by_media_type($show) ?>" target="_blank" rel="noopener">
                <?= $show->title ?>
            </a>
        </p>
        <?php
        $data = Media::data_attachment($show);

        $filename = @basename($data['dir']);

        $filemime = FileInfo::mimeType($data['dir'] ?? '');

        $filesize = FileInfo::size($data['dir'] ?? '');


        $dimensions['info']  = '0 &times; 0';
        $dimensions['input'] = null;


        if( isset($data['width'], $data['height']) ) {
            $dimensions['info'] = $data['width'] . ' &times; ' . $data['height'];


            $dimensions['value'] = 
                "{\"width\": {$data['width']}, \"height\": {$data['height']}}";

            $dimensions['input'] = '<input 
                type="hidden" id="upload_dimensions" 
                name="upload_dimensions" 
                value="' . Ensure::string($dimensions['value']) . '" 
            />';
        }

        echo "
        <p><strong>Nome:</strong> {$filename}</p>
        <p><strong>Tipo:</strong> {$filemime}</p>
        <p><strong>Tamanho:</strong> {$filesize}</p>
        <p><strong>Dimensão:</strong> {$dimensions['info']}</p>
        ";

        $fileurl = '';
        if( $filesize !== '0 B' ) {

            echo <<<HTML
            <div id="edit-alttext">
                <label for="alttext">Texto alternativo *</label>
                <input id="alttext" type="text" name="alttext" />
            </div>
            <p class="purl">URLs do arquivo:</p>
            HTML;

            foreach( ['original', 'plain', 'minor', 'larger', 'wide'] as $scope ) {

                $label = match($scope) {
                    'original' => 'Original',
                    'wide'     => 'Amplo',
                    'larger'   => 'Maior',
                    'minor'    => 'Menor',
                    'plain'    => 'Simples | Categoria',
                    default    => ucfirst($scope),
                };

                $filepath = $show->attachment->{$scope}->path ?? null;

                if( is_string($filepath) ) {
                    $fileurl = upload_url($show->attachment->{$scope}->path);

                    echo "
                    <label class=\"fs13\" for=\"{$scope}\">{$label}</label>
                    <input id=\"{$scope}\" type=\"url\" value=\"{$fileurl}\" readonly />";
                }
            }
        }
	    ?>
        <input type="hidden" id="fileurl" value="<?= $fileurl ?>" />
		<div class="delete_media">
            <button type="button" 
                class="input_false link delete right" 
                id="delete_media" name="delete_media">
                Excluir arquivo
            </button>

            <input type="hidden" id="upload_id" name="upload_id" value="<?= $show->ID ?>" />
            <input type="hidden" id="upload_type" name="upload_type" value="<?= $show->type ?>" />
            <?= $dimensions['input'] ?>
        </div>
	</div>
	<div id="return"></div>
<?php endforeach; 
