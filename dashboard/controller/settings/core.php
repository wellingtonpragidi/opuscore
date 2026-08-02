<?php
INPUT::method_request();

$site_title = trim( $_POST['site_title'] ?? '' );
$dateformat = $_POST['dateformat'] ?? 'd/m/Y';

$core_data = [
    'site_title' => $site_title,
    'dateformat' => $dateformat,
    'timezone'   => 'America/Sao_Paulo',
    'setlocale'  => 'pt_BR',
];
if( ArrayExport::apply('core', $core_data, 'settings') ) {
    alert_redirect( 'success', 'Configurações atualizadas!', URL::current() );
}
else {
    alert( 'warning', 'Falha ao atualizar configurações!' );
}