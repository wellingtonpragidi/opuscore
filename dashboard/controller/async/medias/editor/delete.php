<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();


require annex_path('deps/controller.php');


# input=hidden name="upload_type" se encontra no arquivo `editor-media-selected.php`
# seu valor eh o type dos scope-s ImageSize para valor da coluna `related_type` da tabela `medias`
$_id = INPUT::int('upload_id') ?: URL::int('checked_id');
$result = $media->delete( $_id, INPUT::GET('upload_type') );

if( $result['deleted_file'] ) {
    $deleted_file['message'] = '<p class="suc">Registro de mídia excluído.</p>';
    $deleted_file['status'] = true;
}
else {
    $deleted_file['message'] = '<p class="err">Registro de mídia não excluído.</p>';
    $deleted_file['status'] = false;
}

if( $result['deleted_record'] ) {
    $deleted_record['message'] = '<p class="suc">Arquivos físicos excluídos.</p>';
    $deleted_record['status'] = true;
}
else {
    $deleted_record['message'] = '<p class="err">Arquivos físicos não excluídos.</p>';
    $deleted_record['status'] = false;
}

echo 
json_encode([
    'content' => $deleted_file['message'] . $deleted_record['message'],
    'deleted' => $deleted_file['status'] && $deleted_record['status']
]);

exit;