<?php 
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';
INPUT::method_request();

require_callable('controller-helpers.php');

$imanager = Container::call('ImageManager');

$delete = INPUT::int('deleteit');
$result = $imanager->delete($delete);

$record_deleted = $result['deleted_record'];
$files_deleted  = ! empty($result['deleted_file']);

echo images_deletion_message( $record_deleted, $files_deleted );
