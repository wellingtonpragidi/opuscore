<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();


$tmp = FILES::tmp('upload');
$ext = FILES::ext('upload');


if( ! in_array( FileInfo::mimeType($tmp), FileInfo::mimes() )  ) {

    alert_time('error txt_center fs20 discard', 
        'Tipo de arquivo não permitido! 
        <p>Veja nessa pagina quais são os <a href="' . dash_url('medias/allowed-files') . '">tipos de arquivos permitidos</a>
        </p>', 
        3000
    );

    return;
}


$bind = new Assign;


## esse eh o ID de onde a midia esta sendo enviada para uso na coluna `related_id` de `medias`
$bind->ID         = INPUT::int('target_id');

$bind->type       = INPUT::GET('upload_type');
$bind->title      = INPUT::GET('title');
$bind->created    = date('Y-m-d H:i:s');

if( $bind->ID === 0 || $bind->type === '' ) {
    opus_log( "ID '{$bind->ID}' e/ou type '{$bind->type}' vazio" );

    return;
}

// $container = Container::instance();
// $media = $container->make('Media');
// $image = $container->make('Image');

# insert: ---- ----

# anexo JSON (attachment) provisorio vazio no insert
$bind->attachment = '{}'; 

# Insere primeiro pra gerar e pegar o ID
if( ! $media->insert($bind) ) {
    alert('error', (string)$bind );
    opus_log('Media::insert falhou ao inserir o registro do arquivo');

    return;
}


$is_bitmap = FileInfo::is_bitmap($tmp);


# update: ---- ----

$bind->media->ID = $bind->LastID;

## monta o caminho do arquivo para os dados JSON com o ID de `medias`
### **Nao o ID de onde esta sendo feito o upload**
$filepath = fn( ?string $scope = null ) => 
    date('Y/m') . "/{$bind->type}--media-{$bind->media->ID}{$scope}.{$ext}";

# monta o array para os dados da coluna attachment que em seguida sera convertida em JSON
if( $is_bitmap ) {

    $size = FileInfo::imageDimensions($tmp);

    $attachment = [
        'original' => [
            'path'   => $filepath(),
            'width'  => $size['width'] ?? null,
            'height' => $size['height'] ?? null
        ],
        'system' => [
            'path'   => $filepath('-system'),
            'width'  => system_image_size(),
            'height' => system_image_size()
        ],
        
    ];
}
## para outros tipos arquivo diferente de imagem, scope sempre apenas 'original'
else {
    $attachment = [
        'original' => [
            'path' => $filepath()
        ]
    ];
}

$attachment['version'] = mt_rand(1000, 9999);

$bind->attachment = Ensure::json( $attachment );



# e atualiza os dados do anexo 
if( ! $media->update($bind) ) {
    opus_log('Media::update falhou ao atualizar os dados do anexo do arquivo no upload');

    return;
}


# arquivo fisico: ---- ----

## os metodos resolve() e save_file() ja adicionam a extesao do arquivo
$basename = "{$bind->type}--media-{$bind->media->ID}";

## se o arquivo carregado for uma imagem bitmap - redimensiona, corta e move o mesmo 
### para uso no painel
if( $is_bitmap ) {

    ImageHandler::resolve([
        'input'    => 'upload',
        'filename' => $basename . '-system',
        'width'    => system_image_size(),
        'height'   => system_image_size()
    ]);
}


## move o arquivo fisico original para o diretorio de uploads
$media->save_file( 'upload', $basename );



# exibe o arquivo recem carregado: ---- ----

foreach( $image->select_last() as $show ) { 
    $filepath = fn($scope) => $show->attachment->{$scope}->path ?? '';

    if( editor_is('punk') ) {
        if( in_array($ext, ['png', 'jpeg', 'jpg', 'gif', 'webp', 'tif', 'bmp']) ) {

            $source = upload_url($filepath('system'));
        }
        else if( $ext === 'svg' ) {

            $source = upload_url($filepath('original'));
        }
        # outros tipos arquivo diferente de imagem
        else {

            $source = dash_url('assets/img/document.svg');
        }

        echo "
        <label for=\"file-{$show->ID}\" id=\"thumb-{$show->ID}\" class=\"thumb\">
            <input type=\"radio\" 
                id=\"file-{$show->ID}\" class=\"dnone datafile\" 
                name=\"datafile\" value=\"{$show->ID}\" checked 
            />
            <img src=\"{$source}\" alt />
        </label>";
    }
    if( editor_is('tinymce') ) {
        if( is_string($filepath('original')) ) {

            echo upload_url($filepath('original'));
        }
    }
}
