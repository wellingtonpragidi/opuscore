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

function dashboard_path( string $filepath ): string {
    return DASH_DIR . $filepath;
}


/**
 * Essa funcao inclue o $filepath carregado por require
 * O $filepath precisa ser o caminho **completo + extensao .php** ate o arquivo a ser incluido
 * 
 * O proposito desse tipo de funcao sao as instancias de classes pelo Container,
 *  isso garante que as views e controllers acessem facilmente os recursos
 */
function require_dashboard( string $filepath ): void {
    if( statistics() ) {
        $stats = Statistic::instance();
    }

    extract( Container::scope(), EXTR_SKIP );

    require $filepath;
}


/**
 * 
 * funcoes de path para `view/`
 * 
 * \ ! /
 * Nao se deve requirir/incluir arquivos direto de view/ ou view/subpath/
 * esses arquivos sao requiridos/incluido pelo Router, e somente ele ou hook ligado a ele
 * Para adicionar arquivos de view/ e depois requirir eh necessario ter diretorios: view/subpath/*
 * 
 * Para arquivos nao .php use a funcao dashboard_view_path(string:$filepath) ou a constante DASH_DIR
 * Exemplos:
 * dashboard_view_path('x/x/x.ext') 
 * ou :
 * DASH_DIR . 'view/x/x/x.ext'
 */

# caminho base de view ate arquivo necessario
function dashboard_view_path( string $filepath ): string {
    return DASH_DIR . 'view/' . $filepath;
}

/** 
 * caminho para um diretorio dentro de um sub diretorio de rota da view 
 * o diretorio principal da view eh obtido pelo primeiro parametro/slug [0] da URL
 * 
 * a funcao forca para que $fillpath seja um arquivo .php
 * 
 * $fillpath DEVE ser extendido/preenchendo para mais sub diretorios
 * exemplo: view_param_path( 'updates/status' )
 * Se preferir usar diretorios separado do nome do arquivo use view_subx_path('?dir/dir', 'file')
 */
function view_param_path( string $fillpath ): string {
    return DASH_DIR . 'view/' . URL::param(0) . '/' . $fillpath . '.php';
}

/** 
 * caminho para um diretorio `partial/` dentro de um sub diretorio de rota da view 
 * o diretorio principal da view eh obtido pelo primeiro parametro/slug [0] da URL
 * 
 * a funcao forca para que $basename seja um arquivo seja .php
 * 
 * Portanto parametro/argumento $basename pode soh conter o nome base do arquivo, e  
 *  extendido para mais sub diretorios preenchendo como um $fillpath se preciso
 */
function view_partial_path( string $basename ): string {
    return DASH_DIR . 'view/' . URL::param(0) . '/partial/' . $basename . '.php';
}

/**
 * Essa funcao eh uma adicional para quando view_param_path() e view_partial_path() nao serem ideais
 * Descricao:
 * **Essa funcao nao usa a rota como base para o nome do diretorio principal da view/
 * caminho para arquivo em diretorio dentro de um sub diretorio de view
 * - Essa funcao exige dois parametros/argumentos:
 * - - $subdir   | nome do diretorio filho direto de view/
 * - - $basename | nome base do arquivo que eh um .php
 * - - $ext      | extensao de arquivo opcional (somente por visual limpo). Padrao: .php
 * 
 * Os parametros/argumentos Nao devem ser usados para extender/preencher caminhos
 * para isso use dashboard_view_path()
 */
function view_subx_path( string $subdir, string $basename, string $ext = '.php' ): string {
    return DASH_DIR . 'view/' . $subdir . '/' . $basename . $ext;
}





function annex_path( string $filename ): string {
    return DASH_DIR . 'annexes/' . $filename;
}

function require_annex( string $filename ): void {
    require DASH_DIR . 'annexes/' . $filename;
}

function annex_class( string $class ): void {
    require DASH_DIR . 'classes/infra/annex/' . $class . '.php';
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
    # 2) Gzip (HTTP) — compressao de respostas pelo servidor (Apache/Nginx/PHP output compression)
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

    $allowed_types = ['articles', 'pages'];

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
 * @param $prefix | O prefixo inicial da chave para concatenar com o final. Possiveis valores:
 * - 'pages'
 * - 'articles'
 * - 'comments'
 * - 'users'
 * - 'statistics'
 */
function per_page( string $prefix ): int { 
    $dashboard = get_settings('dashboard');

    return $dashboard['reading'][$prefix . '_per_page'] ?? 30;
}

/**
 * Recupera o limite de midia configurado para acoes de carregamento assincrono no painel 
 * */
# Quantia de miniaturas por carregamento na galeira da ( pagina/rota de Midias )
function media_manager_limit(): int {
    $dashboard = get_settings('dashboard');
    return $dashboard['reading']['media_manager_perload'] ?? 30;
}

# Quantia de miniaturas por carregamento na galeria do ( popup do editor Punk )
function media_popup_limit(): int {
    $dashboard = get_settings('dashboard');
    return $dashboard['reading']['media_popup_perload'] ?? 30;
}


/**
 * Imagens destacadas para paginas "page"
 * Tamanhos padroes pensado no design responsivo
 * 
 * pages nao tem thumbnail `*_sm_*()`
 */
# largura da imagem de pagina (full/wide)
function page_w(): int { 
    $image = get_settings('image');
    return $image['page']['wide']['width'] ?? 1700;
}
# altura da imagem destacada de pagina (full/wide)
function page_h(): int { 
    $image = get_settings('image');
    return $image['page']['wide']['height'] ?? 600;
}

# largura da imagens destacada de artigo (medio)
function page_lg_w(): int { 
    $image = get_settings('image');
    return $image['page']['larger']['width'] ?? 1400;
}
# altura da imagens destacada de artigo (medio)
function page_lg_h(): int { 
    $image = get_settings('image');
    return $image['page']['larger']['height'] ?? 500;
}

# largura da imagens destacada de artigo (pequena)
function page_md_w(): int { 
    $image = get_settings('image');
    return $image['page']['minor']['width'] ?? 650;
}
# altura da imagens destacada de artigo (pequena)
function page_md_h(): int { 
    $image = get_settings('image');
    return $image['page']['minor']['height'] ?? 600;
}

/**
 * Imagens destacadas para artigos "pages type=artigo"
 * Tamanhos padroes pensado para uso geral
 * # Esses padroes nao abramge bem o design responsivo como o padrao de type=page
 * ## Porque type=artigo em grande parte de templates necessita de thumbnail e artigos relacionados
 */
# largura de imagens p/ artigo amplo (full/wide)
function article_w(): int { 
    $image = get_settings('image');
    return $image['article']['wide']['width'] ?? 1600;
}
# altura de imagens p/ artigo amplo (full/wide)
function article_h(): int { 
    $image = get_settings('image');
    return $image['article']['wide']['height'] ?? 550;
}

# largura de imagens de artigo - (maior/largo) pensado para ser usado em relacionados e mobile
function article_lg_w(): int { 
    $image = get_settings('image');
    return $image['article']['larger']['width'] ?? 1000;
}
# altura de imagens de artigo (maior/largo) pensado para ser usado em relacionados e mobile
function article_lg_h(): int { 
    $image = get_settings('image');
    return $image['article']['larger']['height'] ?? 400;
}

# largura de imagens de artigo - ("medio" padrao) pensado para ser usado em relacionados e mobile
function article_md_w(): int { 
    $image = get_settings('image');
    return $image['article']['minor']['width'] ?? 650;
}
# altura de imagens de artigo ("medio" padrao) pensado para ser usado em relacionados e mobile
function article_md_h(): int { 
    $image = get_settings('image');
    return $image['article']['minor']['height'] ?? 480;
}

# largura de imagens de artigo (pequena/miniatura)
function article_sm_w(): int { 
    $image = get_settings('image');
    return $image['article']['thumb']['width'] ?? 70;
}
# altura de imagens de artigo (pequena/miniatura)
function article_sm_h(): int { 
    $image = get_settings('image');
    return $image['article']['thumb']['height'] ?? 60;
}


/**
 * Imagens destacadas para categorias de artigos
 * Tamanhos padroes pensado pensado para uso geral
 */
# largura de imagens para categoria (maior)
function cat_w(): int {
    $image = get_settings('image');
    return $image['category-article']['plain']['width'] ?? 500;
}
# altura de imagens para categoria (maior)
function cat_h(): int { 
    $image = get_settings('image');
    return $image['category-article']['plain']['height'] ?? 350;
}
# largura de imagens para categoria (pequena/miniatura)
function cat_sm_w(): int { 
    $image = get_settings('image');
    return $image['category-article']['thumb']['width'] ?? 70;
}
# altura de imagens para categoria (pequena/miniatura)
function cat_sm_h(): int { 
    $image = get_settings('image');
    return $image['category-article']['thumb']['height'] ?? 60;
}
