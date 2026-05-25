<?php
/**
 * Gera um script de paginacao assincrona por botao (Load More)
 * 
 * O gancho eh chamado na funcao food() por call_action_once('feed_async') evitando duplicacao de chamada
 * 
 * Esse arquivo eh requerido/incluido pela funcao `call_feed_async()`
 * * Em seguida adicionado o gancho append_action('feed_async')
 * * Que internamente chama essa funcao `feed_async($args);`
 * 
 * Para usar em templates (no arquivo features.php) ou addons 
 * * basta agora chamar `call_feed_async()` passar os argumentos ou 
 * 
 * O gancho nao he mais verificado com Hook::has_action('feed_async') no classe Selection
 * * agora a classe soh verifica a query string ?async=true 
 * * e tmb se existe a chave context na URL por `is_feed_async()`
 * * apos isso obtem outros possiveis como '&segment=' e '&orderby='
 * 
 * @param $args Argumentos para configurar o feed_async
 * - selector: seletor CSS onde o conteudo sera carregado
 * padroes: #feed-async
 * - file_location: arquivo PHP que renderiza o conteudo
 * padroes: feed-async.php
 * 
 * Veja mais nas documentacoes
 * 
 * @todo Atualizar documentacoes:
 * @doc Gancho 'loadmore' :
 * @see https://opuscore.dev/hooks/_loadmore_
 * @doc function 'loadmore()' :
 * @see https://opuscore.dev/functions/loadmore
 */

function feed_async( array $args = [] ): void {

    $category = Container::call('Category');


    import_script( site_url('web/assets/js/FeedAsync.js') );


    $args['selector'] = $args['selector'] ?? '#feed-async';

    $query = [
        'context' => null,
        'segment' => null,
        'q'       => null,
        'async'   => 'true',
        'orderby' => Hook::has_filter('orderby') ? Hook::call_filter('orderby', '') : null
    ];

    if( is_home() ) {
        $query['context'] = posts_base();
    }
    elseif( is_query() ) {
        $query['context'] = 'search';
        $query['q'] = URL::Get('q');
    }
    elseif( is_posts() ) {
        $query['context'] = posts_base();
    }
    elseif( is_category() ) {
        $query['context'] = category_base();
        $query['segment'] = $category->segment();
    }

    $query = array_filter( $query );
    $params = http_build_query( $query );

    $file_loc = $args['file_location'] ?? 'feed-async.php';
    $url = template_url($file_loc) . '?' . $params;

    inline_script("
        new FeedAsync ({
            limit:    " . posts_per_page() . ",
            selector: '{$args['selector']}',
            url:      '{$url}',
        }); 
   ");
    
}


/**
 * @deprecated use feed_async() 

function loadmore( array $args = [] ): void {
    $category = Container::call('Category');


    import_script( site_url('web/assets/js/LoaderAsync.js') );


    $args['selector'] = $args['selector'] ?? '#loader';

    $query = [
        'context' => null,
        'segment' => null,
        'q'       => null,
        'async'   => 'true',
        'orderby' => Hook::has_filter('orderby') ? Hook::call_filter('orderby', '') : null
    ];

    if( is_home() ) {
        $query['context'] = posts_base();
    }
    elseif( is_query() ) {
        $query['context'] = 'search';
        $query['q'] = URL::Get('q');
    }
    elseif( is_posts() ) {
        $query['context'] = posts_base();
    }
    elseif( is_category() ) {
        $query['context'] = category_base();
        $query['segment'] = $category->segment();
    }

    $query = array_filter( $query );
    $params = http_build_query( $query );

    $url = $args['file_location'] ?? 'feed.php';
    $url = template_url($url) . '?' . $params;

    inline_script("
        new LoaderAsync ({
            limit:    " . posts_per_page() . ",
            selector: '{$args['selector']}',
            url:      '{$url}',
        }); 
   ");
} */