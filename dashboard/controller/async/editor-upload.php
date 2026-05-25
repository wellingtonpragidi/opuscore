<?php
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';
INPUT::method_request();


$tmp = FILES::temp('upload');
$ext = FILES::ext('upload');

if(  in_array( mime_content_type($tmp), FILES::MIME )  ) {

    $media = Container::call('Media');

    $recorded = $media->insert_editor( $ext, $tmp );

    if( ! $recorded ) {
        opus_log('insert_editor falhou ao mover arquivo', [
            'media_id' => $recorded['media_id'] ?? null
        ]);
        return;
    }

    $type = $_POST['target_type'] ?? '';

    $output = "editor-{$type}--media-{$recorded['media_id']}";


    # insere soh a midia para uso do sistema (scope = system) se arquivo enviado for um imagem bitmap
    if( Media::is_bitmap($tmp) ) {
        ImageHandler::resolve([
            'input'    => 'upload',
            'filename' => $output . '-system',
            'width'    => system_image_size(),
            'height'   => system_image_size()
        ]);
    }

    Media::save_file( 'upload', $output );

    foreach( select_last_image() as $show ) { 
        $filepath = fn($scope) => $show->attachment->{$scope}->path ?? null;
        $source = '';
        if( editor_is('punk') ) {
            if( in_array($ext, ['png', 'jpeg', 'jpg', 'gif', 'webp', 'tif', 'bmp']) ) {
                if( is_string($filepath('system')) ) {
                    $source = upload_url($filepath('system'));
                }
            }
            elseif( $ext === 'svg' ) {
                if( is_string($filepath('original')) ) {
                    $source = upload_url($filepath('original'));
                }
            }
            else {
                $source = dash_url('assets/img/document.svg');
            }
            echo "<label for=\"file-{$show->ID}\" id=\"thumb-{$show->ID}\" class=\"thumb\">
                <input type=\"radio\" 
                    id=\"file-{$show->ID}\" 
                    name=\"datafile\" value=\"{$show->ID}\" 
                    class=\"dnone datafile\" checked  
                />
                <img src=\"{$source}\" alt=\"\" />
            </label>";
        }
        if( editor_is('tinymce') ) {
            if( is_string($filepath('original')) ) {
                $source = upload_url($filepath('original'));
            }
            echo $source; 
        }
    }

}
else {

    alert_time('error txt_center fs20 discard', 
        'Tipo de arquivo não permitido! <p>Veja nessa pagina quais são os 
            <a href="' . dash_url('media/allowed-files') . '">tipos de arquivos permitidos</a>
        </p>', 3000, 1050
    );
        
}