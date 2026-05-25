<?php
/**
 * Este arquivo contem funcoes auxiliares (helpers) de uso geral no dashboard
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System/Helpers
 */

/**
 * @see https://opuscore.dev/functions/get_dashboard_path
 */
function get_dashboard_path( string $extend ): string {
    return DASH_DIR . ltrim( $extend, '/' );
}


/**
 * Inclui um arquivo PHP especifico no contexto do dashboard, disponibilizando
 * instancias de classes pelo container
 * Isso garante que as views e controladores tenham acesso facil aos recursos necessarios.
 *
 * usa todas as classes de dashboard/ registradas no container 
 * $path - caminho completo ate o arquivo a ser incluido (arquivo com extensao '.php')
 */
function require_dashboard( string $path ): void { 
    $container = Container::instance();
    $admin     = $container->make('Admin');
    $category  = $container->make('Category');
    $comment   = $container->make('Comment');
    $context   = $container->make('Context');
    $media     = $container->make('Media');
    $relation  = $container->make('Relations');
    $page      = $container->make('Page');
    $post      = $container->make('Post');
    $router    = $container->make('Router');
    if( statistics() ) {
        $statistic  = GraphStatistic::instance();
    }
    $user = $container->make('User');
    # Image:
    $iattachment = $container->make('ImageAttachment');
    $imanager    = $container->make('ImageManager');

    require $path;
}


function require_callable( string $file ): void {
    static $_call = [];
    $path = DASH_DIR . 'callable/' . $file;
    
    if( ! isset($_call[$file]) ) {
        $_call[$file] = $path;

        require $_call[$file];
    }
}


/**
 * Relatório do servidor php - PHP Server Report
 * Status do servidor PHP - PHP Server Status
 * Informações do servidor PHP - PHP Server Information
 */
function php_server_report(): array {
    $check = [];

    # PHP Version
    $check['php'] = [
        'title' => 'Versão do PHP',
        'status' => version_compare( PHP_VERSION, MIN_PHP_VERSION, '>=' ),
        'info' => 'Versão atual: ' . PHP_VERSION,
        'recommended' => 'O sistema requer PHP 8.1 ou superior para funcionar corretamente'
    ];


    # OPcache
    $opcache_status = false;

    if( function_exists('opcache_get_status') ) {
        $opcache_status = @opcache_get_status(true);
    }

    $enabled = $opcache_status && ! empty($opcache_status['opcache_enabled']);


    $check['opcache'] = [
        'title' => 'OpCache',
        'status' => $enabled,
        'info' => $enabled
            ? 'Habilitado'
            : 'Desabilitado: <small>Fale com seu provedor de hospedagem para ativar. Isso é de extrema importância para o desempenho.</small>',
        'recommended' => 'Reduz parsing e compilação de scripts, melhorando muito o desempenho do site.'
    ];


    # Imagick
    $has_imagick = extension_loaded('imagick');
    $supports_webp = $has_imagick && in_array('WEBP', Imagick::queryFormats());
    $check['imagick'] = [
        'title' => 'Imagick',
        'status' => $has_imagick,
        'info' => $has_imagick
            ? 'Habilitado' . ($supports_webp ? ' <small>(e com suporte a WebP)</small>' : ' <small>(sem suporte a WebP)</small>')
            : 'Desabilitado',
        'recommended' => 'Recomendado para melhor qualidade e suporte a múltiplos formatos.'
    ];

    # GD
    $check['gd'] = [
        'title' => 'GD',
        'status' => extension_loaded('gd'),
        'info' => (extension_loaded('gd') ? 'Habilitado' : 'Desabilitado'),
        'recommended' => 'Garante compatibilidade e suporte a WebP quando o Imagick não estiver disponível.'
    ];

    $check['images'] = [
        'status' => $check['imagick']['status'] && $check['gd']['status'] && $supports_webp ? 
        '<span icon="check"></span>' : 
        '<span icon="question"></span>' 
    ];

    # -------------------------
    # 1) Zlib (PHP) — compressão interna (gzencode/gzcompress/gzopen)
    # -------------------------
    $zlib_enabled = extension_loaded('zlib') && (
        function_exists('gzencode') ||
        function_exists('gzcompress') ||
        function_exists('gzopen')
    );
    $check['zlib'] = [
        'title' => 'Zlib (PHP)',
        'status' => (bool) $zlib_enabled,
        'info' => $zlib_enabled
            ? 'Habilitado'
            : 'Desabilitado',
        'recommended' => 'Necessária para operações internas de compressão. Sem ele, algumas funções de empacotamento/backup podem falhar.'
    ];

    # -------------------------
    # 2) Gzip (HTTP) — compressão de respostas pelo servidor (Apache/Nginx/PHP output compression)
    # -------------------------
    $gzip_http_enabled = false;
    # A) Verifica se PHP esta configurado para compressao de saída (php.ini)
    # ini_get pode retornar '', '0', 'off', 'On', '1' — tratar conservadoramente
    $zlib_output = ini_get('zlib.output_compression');
    if( $zlib_output && strtolower($zlib_output) !== '0' && strtolower($zlib_output) !== 'off' ) {
        $gzip_http_enabled = true;
    }
    # B) Se executando em Apache, verifica mod_deflate (apenas valido se funcao disponivel)
    elseif( function_exists('apache_get_modules') ) {
        $mods = apache_get_modules();
        if (in_array('mod_deflate', $mods, true) || in_array('deflate_module', $mods, true)) {
            $gzip_http_enabled = true;
        }
    }
    # C) Tenta ver se o output handler global esta configurado para ob_gzhandler (indicio)
    else {
        $output_handler = ini_get('output_handler');
        if( $output_handler && stripos($output_handler, 'ob_gzhandler') !== false ) {
            $gzip_http_enabled = true;
        }
    }
    $check['gzip'] = [
        'title' => 'Gzip (HTTP)',
        'status' => (bool) $gzip_http_enabled,
        'info' => $gzip_http_enabled
            ? 'Habilitado'
            : 'Desabilitado: <small>Seu servidor web não está aplicando compressão HTTP</small>',
        'recommended' => 'A compressão HTTP reduz o tamanho das páginas enviadas ao navegador e melhora o tempo de carregamento. Se desativado, peça ao provedor para habilitar a compressão (mod_deflate no Apache ou gzip no Nginx) ou zlib.output_compression no PHP.'
    ];

    # -------------------------
    # (Opcional) 3) Zip (PHP) — ZipArchive, usado para criar/extrair .zip (atentar se usa pacotes .zip)
    # -------------------------
    $zip_enabled = class_exists('ZipArchive');
    $check['zip'] = [
        'title' => 'Zip (PHP)',
        'status' => (bool) $zip_enabled,
        'info' => $zip_enabled ? 'Habilitado' : 'Desabilitado',
        'recommended' => 'Zip - (ZipArchive) é necessário para extrair pacotes .zip de atualizações.'
    ];


    # DOM
    $check['dom'] = [
        'title' => 'DOM',
        'status' => extension_loaded('dom'),
        'info' => (extension_loaded('dom') ? 'Habilitado' : 'Desabilitado'),
        'recommended' => 'Necessária para sanitização e manipulação de HTML.'
    ];

    # Intl
    $check['intl'] = [
        'title' => 'Intl',
        'status' => extension_loaded('intl'),
        'info' => extension_loaded('intl')
            ? 'Habilitado'
            : 'Desabilitado',
        'recommended' => 'Útil para formatação de números, datas e idiomas.'
    ];

    return $check;
}
# helper da funcao  na view
function check_status( string $key ): string {
    $check = php_server_report();
    $status = $check[$key]['status'] ? 
        ' <span icon="check"></span>' : 
        ' <span icon="close"></span>';

    return $status;
}
/*
echo '<blockquote>
Agradecimento especial a todos os envolvidos, e especialmente a <strong>Rasmus Lerdorf</strong>, criador do PHP <strong>e sua filosofia</strong>.
</blockquote>';
*/


/**
 * remove 's' (plural) de parametro da URL para obter o tipo relacionado 
 * Essa funcao he um helper muito usado no CRUD para relacoes de tabelas no banco de dados
 * retorna string do parametro da URL no singular
 * 
 * @todo REFATORAR: kind() otimização de performance
 * - Evitar chamadas desnecessárias em contextos sem URL relevante `$allowed_types`
 * - Considerar cache ou early return mais agressivo
 * - Validar se realmente precisa ser chamada em cada contexto
 * */
function kind( string $plural = '' ): string {
    $param = URL::param(0);

    if( empty($param) ) {
        return '';
    }

    $allowed_types = ['posts', 'pages'];

    if( in_array($param, $allowed_types) ) {
                                 # plural       # singular 
        return ($plural === 's') ? $param : rtrim($param, 's');
    }

    return '';
}


/**
 * ========================================================================
 *     Configuracoes pre-definidas `settings` so utilizadas no dashboard
 * ========================================================================
 */

/**
 * Recupera o limite de itens configurado para listagens de tabelas do painel
 * @param $abbr | O prefixo inicial da chave para concatenar com o final. Possiveis valores:
 * - 'pages'
 * - 'posts'
 * - 'comments'
 * - 'users'
 * - 'statistics'
 */
function per_page( string $abbr ): int { 
    $dashboard = get_settings('dashboard');
    return $dashboard['reading'][$abbr.'_per_page'] ?? 30;
}

/**
 * Recupera o limite de midia configurado para acoes de carregamento assincrono no painel 
 * @param $abbr | O prefixo inicial da chave para concatenar com o final. Possiveis valores:
 * - 'page'  # pagina dedicada a listagem de midia
 * - 'popup' # listagem de midias no popup usando o editor `Punk`
 * */
function per_load( string $abbr ): int { 
    $dashboard = get_settings('dashboard');
    return $dashboard['reading'][$abbr.'_media_per_load'] ?? 30;
}


function system_image_size(): int { 
    $image = get_settings('image');
    return $image['system'] ?? 140;
}


/**
 * Imagens destacadas para paginas "pages type=page"
 * Tamanhos padroes pensado totalmente no design responsivo
 */
# largura da imagem de pagina (grande)
function page_w(): int { 
    $image = get_settings('image');
    return $image['page']['wide']['width'] ?? 1700;
}
# altura da imagem destacada de pagina (grande)
function page_h(): int { 
    $image = get_settings('image');
    return $image['page']['wide']['height'] ?? 600;
}

# largura da imagens destacada de post (medio)
function page_md_w(): int { 
    $image = get_settings('image');
    return $image['page']['larger']['width'] ?? 1400;
}
# altura da imagens destacada de post (medio)
function page_md_h(): int { 
    $image = get_settings('image');
    return $image['page']['larger']['height'] ?? 500;
}

# largura da imagens destacada de post (pequena)
function page_sm_w(): int { 
    $image = get_settings('image');
    return $image['page']['minor']['width'] ?? 650;
}
# altura da imagens destacada de post (pequena)
function page_sm_h(): int { 
    $image = get_settings('image');
    return $image['page']['minor']['height'] ?? 600;
}

/**
 * Imagens destacadas para posts "pages type=post"
 * Tamanhos padroes pensado para uso geral
 * # Esses padroes nao abramge bem o design responsivo como o padrao de type=page
 * ## Porque type=post em grande parte de templates necessita de thumbnail e posts relacionados
 */
# largura de imagens p/ post amplo (full/wide)
function post_w(): int { 
    $image = get_settings('image');
    return $image['post']['wide']['width'] ?? 1600;
}
# altura de imagens p/ post amplo (full/wide)
function post_h(): int { 
    $image = get_settings('image');
    return $image['post']['wide']['height'] ?? 550;
}

# largura de imagens de post - (maior/largo) pensado para ser usado em relacionados e mobile
function post_lg_w(): int { 
    $image = get_settings('image');
    return $image['post']['larger']['width'] ?? 1000;
}
# altura de imagens de post (maior/largo) pensado para ser usado em relacionados e mobile
function post_lg_h(): int { 
    $image = get_settings('image');
    return $image['post']['larger']['height'] ?? 400;
}

# largura de imagens de post - ("medio" padrao) pensado para ser usado em relacionados e mobile
function post_md_w(): int { 
    $image = get_settings('image');
    return $image['post']['minor']['width'] ?? 650;
}
# altura de imagens de post ("medio" padrao) pensado para ser usado em relacionados e mobile
function post_md_h(): int { 
    $image = get_settings('image');
    return $image['post']['minor']['height'] ?? 480;
}

# largura de imagens de post (pequena/miniatura)
function post_sm_w(): int { 
    $image = get_settings('image');
    return $image['post']['thumb']['width'] ?? 70;
}
# altura de imagens de post (pequena/miniatura)
function post_sm_h(): int { 
    $image = get_settings('image');
    return $image['post']['thumb']['height'] ?? 60;
}


/**
 * Imagens destacadas para categorias de posts
 * Tamanhos padroes pensado pensado para uso geral
 */
# largura de imagens para categoria (maior)
function cat_w(): int {
    $image = get_settings('image');
    return $image['category']['plain']['width'] ?? 500;
}
# altura de imagens para categoria (maior)
function cat_h(): int { 
    $image = get_settings('image');
    return $image['category']['plain']['height'] ?? 350;
}
# largura de imagens para categoria (pequena/miniatura)
function cat_sm_w(): int { 
    $image = get_settings('image');
    return $image['category']['thumb']['width'] ?? 70;
}
# altura de imagens para categoria (pequena/miniatura)
function cat_sm_h(): int { 
    $image = get_settings('image');
    return $image['category']['thumb']['height'] ?? 60;
}
