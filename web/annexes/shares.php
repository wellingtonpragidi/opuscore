<?php
declare( strict_types = 1 );

/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package Output/Componente
 * 
 * function shares( array $args = [] ): string | /iterators/essents.php (proxima a linha 500)
 * 
 * @see https://opuscore.dev/functions/shares
 * */

$mode = $args['mode'] ?? 'icon';


$h_title = $args['title'] ?? '<div><strong>Compartilhar:</strong></div>';

$size    = (int) ($args['icon_size'] ?? 26);

$except  = is_array($args['except'] ?? null) ? $args['except'] : [];


$article = Container::call('Article');

$article_url =  URL::root( $article->target()->segment );
$title    = rawurlencode( $article->target()->title );
$url      = rawurlencode( $article_url );


# Esses atributos em "arrow function" nao estao sendo usados  # Mantendo aqui caso algo mude 
$aria_label = fn($name) => "aria_label=\"Clique para compartilhar no {$name}\"";
$attr_title = fn($name) => "title=\"Compartilhar no {$name}\"";
$a11y       = fn($name) => "{$aria_label($name)}{$attr_title($name)}";
$attrs_icon = fn($name, $size) => '
    icon="' . strtolower($name) . '" 
    size="' . $size . '" 
    aria-hidden="true"
';

$networks = [
    'Telegram'  => "https://t.me/share/url?url={$url}&text={$title}",
    'WhatsApp'  => "https://api.whatsapp.com/send?text={$url}",
    'LinkedIn'  => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
    'Twitter'   => "https://x.com/intent/article?text={$title}&url={$url}",
    'Pinterest' => "https://www.pinterest.com/pin/create/button/?url={$url}&description={$title}",
    'Tumblr'    => "https://www.tumblr.com/share?v=3&u={$url}&t={$title}",
    'Reddit'    => "https://www.reddit.com/submit?url={$url}&title={$title}",
    'Facebook'  => "https://www.facebook.com/share.php?u={$url}",
    'Discord'   => "https://discord.com/channels/@me",
    'Pocket'    => "https://getpocket.com/save?url={$url}&title={$title}",
    'Blogger'   => "https://www.blogger.com/blog-this.g?u={$url}&n={$title}"
];

$shares = "<div id=\"shares\">";

$shares .= $h_title;

$render_icon = function( $name ) use ( $mode, $size, $args ) {
    $label = strtolower($name);

    # modo atual
    if( $mode === 'icon' ) {
        return "<span icon=\"{$label}\" size=\"{$size}\" aria-hidden=\"true\"></span>";
    }

    # modo SVG (usa sua funcao PHP)
    if( $mode === 'svg' ) {
        return icon( $label, $size, $size );
    }

    # modo personalizado
    if( $mode === 'customized' && isset($args['customized'][$name]) ) {
        return $args['customized'][$name] ?? '';
    }
    
    return '';
};


foreach( $networks as $name => $href ) {
    $label = strtolower($name);

    if( in_array($label, $except, true) ) {
        continue;
    }

    $onclick = "onclick=\"window.open('{$href}', 'page', 'width=700, height=500'); return false;\"";

    $mod = $args['mode'] ?? null;
    $isValidMode = $mod === null || in_array($mod, ['icon', 'svg', 'customized']);

    if( $isValidMode ) {
        $shares .= "<a 
            class=\"share-{$label}\" 
            {$onclick} 
            aria_label=\"Clique para compartilhar no {$name}\" 
            title=\"Compartilhar no {$name}\" 
            href=\"{$href}\"
        >
            {$render_icon($label)}
        </a>";
    }
    else {
        exception("Argumento <code>'mode'</code> inválido");
        return '';
    }
}

// return $shares .= "</div>";
return $shares . "</div>";



/*$target = ($label === 'whatsapp') 
    ? 'target="_blank" rel="nofollow noopener noreferrer"' 
    : "onclick=\"window.open('{$href}', 'page', 'width=700, height=500'); return false;\"";*/
