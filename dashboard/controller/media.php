<?php
INPUT::method_request();

$action = $_POST['action'] ?? null;

if( $action === 'delete' ) {

    $result = $imanager->delete();

    $record_deleted = $result['deleted_record'];
    $files_deleted  = ! empty($result['deleted_file']);

    $fileAlert = images_deletion_message( $record_deleted, $files_deleted );

    if( $record_deleted || $files_deleted ) {

        alert_redirect( 'success', $fileAlert, dash_url('media'), 22000, 600 );
    }
    else {
        alert( 'warning', $fileAlert );
    }

}