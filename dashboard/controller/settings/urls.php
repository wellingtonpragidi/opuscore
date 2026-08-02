<?php
INPUT::method_request();


$base_segment = function( $value ) {
    $param = strip_tags( $value );
    $param = strtolower( $param );
    $param = iconv( 'UTF-8', 'ASCII//TRANSLIT', $param );
    $param = preg_replace( '/[^a-z_-]/', '', $param ); # Mantem apenas letras, hifen e underline
    $param = preg_replace( '/[0-9]/', '', $param ); # remove numeros
    $param = str_replace( ['/', '\\', '"', "'"], '', $param ); # remocoes extras
    $param = preg_replace( '/ +/', '', $param ); # remove espacos

    return trim( $param );
};


$category_base = $base_segment( $_POST['category_base'] ?? 'categoria' );
$articles_base    = $base_segment( $_POST['articles_base'] ?? 'articles' );
$users_base    = $base_segment( $_POST['user_base'] ?? 'perfil' );

$URL_data = [
    'category_base' => $category_base,
    'articles_base'    => $articles_base, 
    'user_base'     => $users_base,
];

if( ArrayExport::apply('URL', $URL_data, 'settings') ) {
    
    alert_redirect( 'success cn_100', 'Configuracoes de URL atualizadas!', URL::current() );
} 
else {
    
    alert('warning discard cn_100', 'Falha ao atualizar configuracoes de URL!');
}