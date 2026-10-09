<?php
declare( strict_types = 1 );


/**
 * @see https://opuscore.dev/functions/append_script
 **/
function append_script( string $path, string $v = '', string $attrs = '' ): void {
    $attributes = empty($attrs) ? '' : " {$attrs}";
    $version = empty($v) ? '' : "?v={$v}";
    $url = template_url( $path . $version );

    echo "<script src=\"{$url}\"{$attributes}></script>\n";
}


/**
 * @see https://opuscore.dev/functions/import_script
 **/
function import_script( string $url, string $attrs = '' ): void {
    $attributes = empty( $attrs ) ? '' : " {$attrs}";

    echo "<script src=\"{$url}\"{$attributes}></script>\n";
}


/**
 * @see https://opuscore.dev/functions/block_script
 **/
function block_script( string $script ): void {
    $script = trim($script);
    $lines  = preg_split('/\R/', $script);
    $indent = 0;
    $out    = [];
    foreach( $lines as $line ) {
        $line = trim($line);

        if( $line === '' ) {
            continue;
        }

        # diminui indentacao antes se linha comeca com }
        if( $line[0] === '}' ) {
            $indent--;
        }

        $out[] = str_repeat( '    ', max(0, $indent) ) . $line;

        # aumenta indentacao depois se linha termina com {
        if( substr($line, -1) === '{' ) {
            $indent++;
        }
    }

    echo "<script>\n" . implode( "\n", $out ) . "\n</script>\n";
}



/**
 * 
 * @see https://opuscore.dev/functions/funcoes-para-inclusao-de-estilos
 * 
 * block_style(...) - append_style(...) - import_style(...)
 */

function block_style( string $css, bool $abs_path = true ): void {
    if( $abs_path ) {
        $css = TEMPLATE_PATH . $css;
    }
    
    $content = file_get_contents($css);

    echo "<style>\n" . compress_CSS($content) . "\n</style>\n";
}

function append_style( string $path, string $v = '', string $attrs = '' ): void {
    $attributes = empty($attrs) ? '' : " {$attrs}";
    $version    = ($v === '') ? '' : "?v={$v}";
    $url        = template_url( $path . $version );

    echo "<link rel=\"stylesheet\" href=\"{$url}{$version}\"{$attributes} />\n";
}

function import_style( string $url, string $attrs = '' ): void {
    
    echo "<link rel=\"stylesheet\" href=\"{$url}\" {$attrs} />\n";
}





/**
 * @see https://opuscore.dev/functions/append_resource
 */
function append_resource( string $key ): void {
    resources('append', $key);
}

/**
 * @see https://opuscore.dev/functions/has_resource
 */
function has_resource( string $key ): bool {
    return resources('has', $key);
}


/**
 * @access private
 */
function resources( string $action, string $key ): bool {
    static $store = [];

    if( $action === 'append' ) {
        $store[$key] = true;
        return true;
    }

    if( $action === 'has' ) {
        return isset($store[$key]);
    }

    return false;
}