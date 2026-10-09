<?php
INPUT::method_request();

$port    = trim( $_POST['port'] ?? smtp_port() ); # 587
$host    = trim( $_POST['host'] ?? smtp_host() );
$user    = trim( $_POST['user'] ?? smtp_user() );
# O op. Elvis garante que a senha tenha um valor mesmo se o POST estiver vazio
$pswd    = trim( $_POST['pswd'] ?? smtp_pswd() ) ?: smtp_pswd();
$address = trim( $_POST['address'] ?? smtp_address() );

$dest     = trim( $_POST['dest'] ?? email_dest() );
$reply_to = trim( $_POST['reply_to'] ?? email_reply_to() );


$email_data = [
    'smtp' => [
        'port'    => (int) $port,
        'host'    => $host,
        'user'    => $user,
        'pswd'    => $pswd,
        'address' => $address
    ],
    'dest'     => $dest,
    'reply_to' => $reply_to
];


if( ArrayExport::apply('email', $email_data, 'settings') ) {

    alert_redirect( 'success', 'Definições de e-mail atualizadas.', URL::current() );
    return;
}
else {

    alert( 'warning', 'Falha ao atualizar configurações de e-mail.' );
}
