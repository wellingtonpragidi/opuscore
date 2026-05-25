<?php
static $cache = ['icon' => []];

$path = TEMPLATE_PATH . "assets/icons/{$name}.svg";

if( ! array_key_exists($name, $cache['icon']) ) {
    $cache['icon'][$name] = file_get_contents( $path );
}

$svg = $cache['icon'][$name];

$height = empty($height) ? $width : $height;

$attrs = ' aria-hidden="true"';

# Ajusta width e height — substitui se existir, adiciona se faltar
if( preg_match('/\bwidth="/', $svg) ) {
    $svg = preg_replace('/\bwidth="[^"]*"/', 'width="' . $width . '"', $svg);
} 
else {
    $attrs .= ' width="' . $width . '"';
}

if( preg_match('/\bheight="/', $svg) ) {
    $svg = preg_replace('/\bheight="[^"]*"/', 'height="' . $height . '"', $svg);
} 
else {
    $attrs .= ' height="' . $height . '"';
}



# Insere os atributos no SVG original
$svg = preg_replace( '/^<svg\b/', '<svg ' . $attrs, $svg );

return $svg;