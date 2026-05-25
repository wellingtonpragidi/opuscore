<?php
INPUT::method_request();

$input = fn($name) => trim( $_POST[$name] ?? '' );

$input_data = [
    'whatsapp'  => $input('whatsapp'),
    'telegram'  => $input('telegram'),
    'youtube'   => $input('youtube'),
    'twitter'   => $input('twitter'),
    'pinterest' => $input('pinterest'),
    'tiktok'    => $input('tiktok'),
    'instagram' => $input('instagram'),
    'facebook'  => $input('facebook'),
    'github'    => $input('github'),
    'linkedin'  => $input('linkedin'),
    'behance'   => $input('behance'),
    'tumblr'    => $input('tumblr')
];

$socialnet_data = array_map( 'Ensure::URL', $input_data );

if( ArrayExport::apply('socialnet', $socialnet_data, 'settings') ) {

    alert_redirect( 'success', 'Links para Redes Sociais atualizadas.', URL::current() );
}
else {

    alert( 'warning discard', 'Nenhum campo atualizado / alterado.' );
}