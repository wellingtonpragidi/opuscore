<?php 
declare( strict_types = 1 );

/** 
 * @see int: / functions/caminhos-e-inclusao-de-arquivos
 */

function template_path( string $filepath ): string {
    return TEMPLATE_PATH . $filepath;
}



function web_path( string $filepath ): string {
    return DIR . 'web/' . $filepath;
}



function access_path( string $filepath ): string {
    return DIR . 'web/access/' . $filepath;
}



function require_template( string $fillpath ): void {
    extract( Container::scope(), EXTR_SKIP );

    require TEMPLATE_PATH . $fillpath . '.php';
}



function annex_path( string $filepath ): string {
    static $once = [];
    
    if( ! isset($once[$filepath]) ) {

        $once[$filepath] = DIR . 'web/annexes/' . $filepath;
    }

    return $once[$filepath];
}



/** 
 * 
 * URLs : 
 */

/**
 * @see https://opuscore.dev/functions/access_url
 * retorna a url de acesso da rota publica
 * @param $value | valor da query string 'action' 
 * @param $queries | sequencia de query(ies) strings, ex: '&chave=valor'
 */
function access_url( string $value, string $queries = '' ): string {
    $queries = ($queries === 'redirect') 
        ? '&redirect=' . URL::current() 
        : $queries;

    return URL::root('access/?action=' . $value . $queries);
}

/**
 * @see https://opuscore.dev/functions/web_access_url
 * retorna a url fisica apontando diretamente para o diretorio de acesso
 * @param $extend | estender a url para subdiretorios e arquivos
 */
function web_access_url( string $extend = '' ): string {
    return URL::root( 'web/access/' . $extend );
}
