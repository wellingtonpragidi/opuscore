<?php
/**
 * Funcoes de Acesso a Configuracoes pre-definidas do Sistema
 *
 * Este arquivo contem uma serie de funcoes auxiliares que fornecem acesso
 * a diversas configuracoes do sistema, armazenadas no arquivo 'dist/data-settings.php'
 *
 * As funcoes aqui permitem recuperar valores como:
 * - Titulo do site, nome e slug do template
 * - Bases de URL para categorias, posts e usuarios
 * - Formato de data.
 * - Dimensoes de imagens para diferentes tipos de conteudo (categorias, paginas, posts, usuarios)
 * - Configuracoes de paginacao (posts por pagina, comentarios por pagina)
 * - etc
 * 
 * @see https://opuscore.dev/functions/funcoes-de-configuracoes-pre-definidas
 */

/**
 * Carrega o arquivo de configuracoes uma unica vez depois (static cache)
 * 
 * Obtem arrays de configuracoes do arquivo data-settings.php requerido
 * e retorna o array especifico solicitado pela chave `$var`
 * 
 * @author O voce de 2025
 * 
 * @param $var | nome do variavel que armazena o array desejado
 */
function get_settings( string $var ): array {
    static $settings = null;

    if( $settings === null ) {
        $file = STORAGE_DIR . 'settings.php';

        $settings = Provider::include_file_vars($file);
    }

    return $settings[$var] ?? [];
}


/**
 * Retorna o titulo do site
 */
function site_title(): string {
	$core = get_settings('core');
    return $core['site_title'] ?? '';
}

/**
* bloco de funcoes que retornam configuracao de envio de e-mail como o servidor SMTP
* @see https://opuscore.dev/constants/configuracao-de-e-mail
*/
function email_port(): int {
    $email = get_settings('email');
    return (int) $email['port'] ?? 587;
}
function email_host(): string {
    $email = get_settings('email');
    return $email['host'] ?? '';
}
function email_user(): string {
    $email = get_settings('email');
    return $email['user'] ?? '';
}
function email_pswd(): string {
    $email = get_settings('email');
    return $email['pswd'] ?? '';
}
function email_address(): string {
    $email = get_settings('email');
    return $email['address'] ?? '';
}


/**
 * Retorna a URL da rede social configurada
 *
 * @param $network O nome `slug` da rede social - ex: 'github'
 * @return string O valor da rede social ou string vazia se nao encontrado
 * 
 * @todo Usar Ensure::URL() que eh = Validator + Sanitize
 */
function socialnet( string $network ): string {
    $socialnet = get_settings('socialnet');
    return $socialnet[$network] ?? '';
}


/**
 * Retorna o formato de data padrao.
 */
function chronos_setting(): string {
    $core = get_settings('core');
    return $core['dateformat'] ?: 'd/m/Y';
}

function timezone() {
    $core = get_settings('core');
    $timezone = $core['timezone'] ?? false;

    # 2. Fuso do servidor
    $serverTz = date_default_timezone_get();
    if( $serverTz && in_array($serverTz, timezone_identifiers_list()) ) {
        return $serverTz;
    }

    return $core['timezone'] ?? 'America/Sao_Paulo';
}


function posts_per_page(): int {
	$reading = get_settings('reading');
	return (int) $reading['posts_per_page'] ?? 6;
}

# dashboard
	## editor

/**
 * @internal
 * Retorna a base da URL para posts
 * Se nao definida, retorna 'posts'
 */
function posts_base(): string {
    $URL = get_settings('URL');
    return $URL['posts_base'] ?? 'posts';
}

/**
 * @internal
 * Retorna a base da URL para categorias
 * Se nao definida, retorna 'categoria'
 */
function category_base(): string {
	$URL = get_settings('URL');
    return $URL['category_base'] ?? 'categoria';
}

/**
 * @internal
 * Retorna a base da URL para perfis de usuario
 * Se nao definida, retorna 'perfil'
*/
function user_base(): string {
    $URL = get_settings('URL');
    return $URL['user_base'] ?? 'perfil';
}



/**
* retorna o valor inserido no painel pela chave de configuracao
*
* @param $key | chave de configuracao pre-definida do array. Valores possiveis:
* - 'homepage_description'
* - 'posts_description'
* - 'categories_description'
* - 'user_description'
* - 'google_verification'
* - 'bing_verification'
* - 'homepage_lastmod'
*/
function seo( string $key ): string {
    $SEO  = get_settings('SEO');
    $site = site_title();

    $user = IS_WEB && ( $key === 'user_description' || is_user() ) 
        ? User::name() : '{Nome do usuário}';

    $filter_dscpt = [
        'homepage_description',
        'posts_description',
        'categories_description',
        'user_description',
    ];

    $dscpt = match($key) {
        'homepage_description'   => "Bem-vindo ao {$site}. Explore conteúdos, artigos e novidades organizadas para você.",
        'posts_description'      => "Confira todos os artigos publicados no site {$site}. Conteúdos organizados por data e relevância.",
        'categories_description' => "Explore todas as categorias do site {$site} e navegue pelos conteúdos organizados por tema.",
        'user_description'       => "Veja o perfil do usuário {$user} no site {$site} e acompanhe sua participação.",
        default                  => ""
    };

    if( in_array($key, $filter_dscpt, true) ) {

        $filtered = Hook::call_filter('seo_descriptions', $SEO);

        # isso remove valores vazios de filtro mal feito
        $filtered = array_filter($filtered, function($v) {
            return ! empty($v);
        });

        $SEO = array_merge( $SEO, $filtered );
    }

    return ( ! empty($SEO[$key]) ? $SEO[$key] : $dscpt );
}


/**
 * @deprecated use socialnet('network')
 
function social_network( string $network ): string {
	return socialnet($network) ?? '';
}*/


/**
 * @internal
 * Retorna o slug ou nome do template em uso
 * @param $get | valores possiveis: 'slug' e 'name'
 */
function template(): string {
	return get_settings('template')['slug'] ?? '';
}


/**
 * retorna o nome do editor de texto definido/ativo. Padrao: 'punk'
 */
function editor_name(): string { 
    $options = get_settings('options');
    $editor = $options['editor'] ?? 'punk';
    return $editor;
}
/**
 * verifica se o editor definido/ativo eh o mesmo passado pelo parametro $check
 */
function editor_is( string $check ): bool { 
    $options = get_settings('options');
    $defined = strtolower( $options['editor'] ?? 'punk' );
    return $defined === $check;
}


/**
 * Caso/Quando tiver mais pre-definicoes no array `$options` usar:
function options( string $defined ): int { 
    $options = get_settings('options');
    return $options[$defined] ?? xxx;
}
*/


function statistics(): bool {
	$options = get_settings('options');
    return $options['statistics'] ?? false;
}


/**
 * user pic
 * tamanhos/dimensoes para imagem de usuario:
 * (medio) usado na pagina de perfil e (pequeno) em lista de comentarios
 * @param $size | 'sm' = 'small' imagem menor e 'md' = 'medium' imagem maior
 */
function user_pic_sz( string $size ): int { 
    $image = get_settings('image');
    switch( $size ) {
        case 'sm': case 'pic': case 'avatar': case 'thumb':
            return $image['user']['avatar'] ?? 60;
        break;
        case 'md': case 'profile':
            return $image['user']['profile'] ?? 100;
        break;
        default:
            return 80; # fallback padrao - caso houver algum problema
        break;
    }
}
