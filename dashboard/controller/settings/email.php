<?php
INPUT::method_request();

$port    = trim( $_POST['port'] ?? email_port() ); # 587
$host    = trim( $_POST['host'] ?? email_host() );
$user    = trim( $_POST['user'] ?? email_user() );
$pswd    = trim( $_POST['pswd'] ?? email_pswd() );
$address = trim( $_POST['address'] ?? email_address() );

# Garante que a senha tenha um valor mesmo se o POST estiver vazio
$password = $pswd ?: email_pswd();

$smtp_data = [
    'port'    => (int) $port,
    'host'    => $host,
    'user'    => $user,
    'pswd'    => $password,
    'address' => $address
];


if( ArrayExport::apply('email', $smtp_data, 'settings') ) {

    alert_redirect( 'success', 'Definições de e-mail atualizadas.', URL::current() );
}
else {

    alert( 'warning', 'Falha ao atualizar configurações de e-mail.' );
}