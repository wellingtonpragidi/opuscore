<?php
/**
 * Funcoes de Acesso a Configuracoes pre-definidas do Sistema
 *
 * Este arquivo contem uma serie de funcoes auxiliares que fornecem acesso
 * a diversas configuracoes do sistema, armazenadas no arquivo 'dist/data-settings.php'
 *
 * As funcoes aqui permitem recuperar valores como:
 * - Titulo do site, nome e slug do template
 * - Bases de URL para categorias, articles e usuarios
 * - Formato de data.
 * - Dimensoes de imagens para diferentes tipos de conteudo (categorias, paginas, articles, usuarios)
 * - Configuracoes de paginacao (articles por pagina, comentarios por pagina)
 * - etc
 * 
 * @see https://opuscore.dev/functions/funcoes-de-configuracoes-pre-definidas
 */

/**
 * Carrega o arquivo de configuracoes uma unica vez por requisicao
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


function articles_per_page(): int {
	$reading = get_settings('reading');
	return (int) $reading['articles_per_page'] ?? 6;
}

# dashboard
	## editor

/**
 * @internal
 * Retorna a base da URL para articles
 * Se nao definida, retorna 'articles'
 */
function articles_base(): string {
    $URL = get_settings('URL');
    return $URL['articles_base'] ?? 'articles';
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
* - 'articles_description'
* - 'categories_description'
* - 'user_description'
* - 'google_verification'
* - 'bing_verification'
* - 'homepage_lastmod'
*/
function SEO( string $key ): string {
    $SEO  = get_settings('SEO');

    $main = get_settings('core');
    $site = $main['site_title'] ?? '';

    $user = IS_WEB && ($key === 'user_description' || is_user()) 
        ? User::name() 
        : '{Nome do usuário}';

    $dscpt = match($key) {
        'homepage_description'   => "Bem-vindo ao {$site}. Explore conteúdos, artigos e novidades organizadas para você.",
        'articles_description'      => "Confira todos os artigos publicados no site {$site}. Conteúdos organizados por data e relevância.",
        'categories_description' => "Explore todas as categorias do site {$site} e navegue pelos conteúdos organizados por tema.",
        'user_description'       => "Veja o perfil do usuário {$user} no site {$site} e acompanhe sua participação.",
        default                  => ""
    };

    $seo_config = ( ! empty($SEO[$key]) ? $SEO[$key] : $dscpt );

    if( str_ends_with($key, '_description') ) {

        $filtered = Hook::call_filter('seo_descriptions', $SEO);

        # isso remove valores vazios de filtro mal feito
        $filtered = array_filter($filtered, function($value) {
            return ! empty($value);
        });

        $SEO = array_merge( $SEO, $filtered );


        return Ensure::attr($seo_config);
    }

    return $seo_config;
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
 * tamanhos/dimensoes para imagem de usuario:
 * (medio) usado na pagina de perfil e (pequeno) em lista de comentarios
 */
function user_pic_sz( string $size ): int { 
    $image = get_settings('image');

    return match( $size ) {
        'avatar'  => $image['user']['avatar'] ?? 50,
        'profile' => $image['user']['profile'] ?? 120,
        default   => 100
    };
}



/**
 * tamanhos/dimensoes para corte de imagem para uso no painel
 * 
 * Precisa ser declarada aqui para que o metodo `default_dimensions()` da clase `ImageSize`
 * nao de indefinido, ja que essa funcao fica armazenada em uma variavel fora do `match()`
 */
function system_image_size(): int { 
    $image = get_settings('image');
    return $image['system'] ?? 140;
}