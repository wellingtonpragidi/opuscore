<?php
INPUT::method_request();

$action = $_POST['action'] ?? null;

$SEO_data = [];
$alert    = [];
$target   = '';
$ensure   = fn($key) => Ensure::string($_POST[$key] ?? '');

switch( $action ) { # se entrou aqui 'action' existe
    case 'google_action':
        $SEO_data = ['google_verification' => $ensure('google_verification')];
        $target = 'google_verification';
    break;
    case 'bing_action':
        $SEO_data = ['bing_verification' => $ensure('bing_verification')];
        $target = 'bing_verification';
    break;
    case 'home_action':
        $lastmod = $ensure('homepage_lastmod') ?: date('Y-m-d H:i:s');
        $SEO_data = [
            'homepage_description' => $ensure('homepage_description'),
            'homepage_lastmod' => $lastmod
        ];
        $target = 'homepage_description';
    break;
    case 'posts_action':
        $SEO_data = ['posts_description' => $ensure('posts_description')];
        $target = 'posts_description';
    break;
    case 'cats_action':
        $SEO_data = ['categories_description' => $ensure('categories_description')];
        $target = 'categories_description';
    break;
    case 'user_action':
        $SEO_data = ['user_description' => $ensure('user_description')];
        $target = 'user_description';
    break;
}
$anchor = '';
if( ArrayExport::apply('SEO', $SEO_data, 'settings') ) {
    $text = ( $action === 'google_action' || $action === 'bing_action' ) 
        ? 'Verificação' 
        : 'Descrição';

    $alert[$target] = '<div id="'. $target .'" class="alert success discard cn_100">'. $text .' atualizada.</div>';

    $anchor = '#' . $target;
} 
else {
    $alert[$target] = '<div id="'. $target .'" class="alert warning discard cn_100">Ocorreu algum erro.</div>';

    $anchor = '#' . $target;
}

$current = URL::current();

redirect( $current . $anchor, 140 );

echo "<script>
    window.setTimeout(function() {
        fade.out.selector('.alert', 2500);
    }, 8000);
    window.setTimeout(function() {
        window.location='{$current}';
    }, 60000);
</script>";