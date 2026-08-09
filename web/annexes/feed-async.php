<?php
/**
 * Gera um script JS de feed assincrono que carrega mais conteudo por click em botao (Load More)
 * 
 * O gancho eh chamado na funcao food() por call_action_once('feed_async') evitando duplicacao de chamada
 * 
 * Esse arquivo aqui eh incluido dentro funcao `feed_async()` e closure do gancho append_action('feed_async')
 * 
 * Para usar em templates (no arquivo features.php), basta agora chamar `feed_async()` e passar os argumentos
 * "Ainda nao eh possival habilitar essa funcionalidade por um addon" 
 * 
 * @param $args Argumentos para configurar o feed_async
 * - selector: seletor CSS onde o conteudo sera carregado padrao: #feed
 * - file_location: caminho e nome ou nome base do arquivo PHP que renderiza o conteudo padrao: feed-async.php
 * 
 * Veja mais nas documentacoes
 * @see https://opuscore.dev/subsystems/feed-async
 */


$selector = $args['selector'] ?? '#feed';

# O fallback de file_location eh 'feed-async.php' na raiz do diretorio do template ativo
# Esse fallback eh adicionado no roteador async em 'web/controller/index.php' 
$file_location = $args['file_location'] ?? ''; 

# removemos a extencao .php aqui para nao aparecer na URL, 
# o controller adiciona novamente para uso no require
$fillpath = str_ends_with($file_location, '.php') ? substr($file_location, 0, -4) : $file_location;

$query = [
    'req'   => $fillpath,
    'src'   => null,
    'cat'   => null,
    'q'     => null,
    'order' => Hook::has_filter('orderby') ? Hook::call_filter('orderby', '') : null,
];


$category = Container::instance()->make('Category');

$is_feed_async = false;

if( is_home() || is_articles() ) {

    $query['src'] = articles_base();

    $is_feed_async = true;
}
else if( is_query() ) {

    $query['src'] = 'search';
    $query['q']   = URL::GET('q');

    $is_feed_async = true;
}
else if( is_category() ) {

    $query['src'] = category_base();
    $query['cat'] = $category->segment();

    $is_feed_async = true;
}

# Monta a URL
$build_queries = http_build_query( array_filter($query) );

$root_url ??= URL::root();

$url = $root_url . 'web/controller/?route=/feed-async/&' . $build_queries;

# Valida a URL:
$parse = parse_url( $url );

if( ! isset($parse['path']) ) {
    return;
}
if( ! str_ends_with($parse['path'], '/web/controller/') ) {
    return;
}

if( ! isset($parse['query']) ) {
    return;
}

# converte a string de queries em array
$params = [];
parse_str( $parse['query'], $params );

if( ! isset($params['route']) ) {
    return;
}
if( $params['route'] !== '/feed-async/' ) {
    return;
}


if( $is_feed_async ) {

    import_script( $root_url . 'web/assets/js/FeedAsync.js' );

    block_script("
        new FeedAsync ({
            limit:    " . articles_per_page() . ",
            selector: '{$selector}',
            url:      '{$url}',
        });
    ");
}