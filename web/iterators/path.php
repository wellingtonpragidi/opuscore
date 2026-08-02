<?php 
declare( strict_types = 1 );

/** 
 * @see https://internal/functions/caminhos-e-inclusao-de-arquivos
 */

function template_path( string $filepath ): string {
    return TEMPLATE_PATH . $filepath;
}
/** 
 * @deprecated use template_path
 */
function get_template_path( string $filepath ): string {
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
