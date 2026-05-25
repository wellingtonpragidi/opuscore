<?php
/**
 * Gera e injeta o conteudo do arquivo `manifest.json` no diretorio raiz do sistema.
 * Este arquivo e essencial para funcionalidades de Progressive Web App (PWA),
 * permitindo que o site seja 'instalado' como um aplicativo.
 */

$sitetitle = site_title();
$shortname = short_name($sitetitle);

# Conteudo JSON do manifest, utilizando um Heredoc para formatacao legivel.
$manifest = '{
    "name": "' . $sitetitle . '",
    "short_name": "' . $shortname . '",
    "icons": [
        {
            "src": "' . upload_url("favicon/192x192") . '",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "' . upload_url("favicon/512x512") . '",
            "sizes": "512x512",
            "type": "image/png"
        }
    ],
    "start_url": "/", // URL de inicio do PWA.
    "theme_color": "#E5E5E5", // Cor da barra de status do navegador em dispositivos moveis.
    "background_color": "#3E454F", // Cor de fundo da tela de splash do PWA.
    "display": "standalone" // Modo de exibicao (standalone para parecer um app nativo).
}';

Ensure::writeLock( DIR . 'manifest.json', $manifest );


function short_name( string $sitetitle ): string {
    # Se o titulo tiver mais de 8 caracteres, tenta criar um nome curto.
    if( strlen( $sitetitle ) > 8 ) {
        $name = explode( " ", $sitetitle );
        foreach( $name as $short ) {
            $short_name = mb_substr( $short, 0, 1 );
        }
    }
    else {
        # Se o titulo for curto, usa ele mesmo como short_name.
        $short_name = $sitetitle;
    }

    return $short_name;
}