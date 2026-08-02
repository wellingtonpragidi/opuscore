<?php

/**
 * retorna a URL raiz do site, com uma extensao opcional.
 * @see https://opuscore.dev/functions/site_url
 */
function site_url( string $extend = '' ): string {
    return URL::root($extend);
}

/**
 * retorna a URL do diretorio de uploads, com uma extensao opcional.
 * @see https://opuscore.dev/functions/upload_url
 */
function upload_url( $extend = '' ): string {
    return URL::root( 'uploads/' . $extend );
}

/**
 * Gera uma URL para o painel de controle (dashboard), com uma extensao opcional.
 * @see https://opuscore.dev/functions/dash_url
 **/
function dash_url( string $extend = '' ): string {
    return URL::root( 'dashboard/' . $extend );
}

/**
 * Gera uma URL para o diretorio do template atualmente ativo, com uma extensao opcional
 * @see https://opuscore.dev/functions/template_url
 */
function template_url( string $extend = '' ): string {
    $slug = get_settings('template')['slug'] ?? '';
    $diretory = ($slug === '') ? 'templates' : 'templates/' . $slug;

    return URL::root( $diretory . '/' . $extend );
}


function user_url(): string {
    $pathname = user_base() . '/' . User::username();
    
    return URL::root($pathname);
}


/**
 * O unico diretorio publico do distribuidor eh o assets/ 
 *   por esse motivo a funcao dist_url se inicia em 'dist/assets/' e nao 'dist/'
 */
function dist_url( string $extend ): string {
    return URL::root( 'dist/assets/' . $extend );
}
function dist_css_url( string $extend ): string {
    return URL::root( 'dist/assets/css/' . $extend );
}
function dist_font_url( string $extend ): string {
    return URL::root( 'dist/assets/fonts/' . $extend );
}

function dist_img_url( string $extend ): string {
    return URL::root( 'dist/assets/img/' . $extend );
}
function dist_thumbnail( string $extend ): string {
    return URL::root( 'dist/assets/img/thumbnail/' . $extend );
}
function dist_icon( string $extend ): string {
    return URL::root( 'dist/assets/img/icon/' . $extend );
}

# retorna a url ate o diretorio /js/ do distribuidor
function dist_js_url( string $extend ): string {
    return URL::root( 'dist/assets/js/' . $extend );
}
# retorna a url completa ate o arquivo dist.js do distribuidor
function dist_js(): string {
    return URL::root( 'dist/assets/js/dist.js' );
}


/**
 * Gera um resumo (trecho) de um conteudo textual, com limite de tamanho e marcador final.
 * @see https://opuscore.dev/functions/text_summary
 */
function text_summary( ?string $content, int $length = 180, string $hellip = '&hellip;' ): string {
    if( $content === null ) {
        return '';
    }

    # Adiciona espaco quando encontra o fechamento de tags HTML - substituindo-as
    $content = preg_replace( '/<[^>]+>/', ' ', $content );

    # Substitui quebras de linha por um unico espaco para facilitar a normalizacao do texto.
    $content = str_replace( ["\r\n", "\r", "\n"], ' ', $content );

    $content = strip_tags( $content );

    # Troca multiplos espacos consecutivos por um unico espaco.
    $text = preg_replace( "/\s+/", ' ', $content );

    $text = trim( $text );

    # Se o conteudo for maior que o tamanho desejado, trunca e adiciona o marcador.
    if( mb_strlen($text, 'UTF-8') > $length ) {
        # mb_substr - que eh multibyte safe
        # caso o corte caia no meio de um caractere especial ou acentuado nao fique assim
        $text = mb_substr( $text, 0, $length, 'UTF-8' );
        # Remove pontuacoes e espacos do final do resumo para evitar cortes feios.
        $text = rtrim( $text, ' .,;:-' );
        $text .= $hellip; # Adiciona o marcador de continuacao (ex: " … (...) ").
    }

    return $text;
}


/**
 * Gera um resumo de conteudo para uso em atributos HTML como content de meta-tags
 * @see https://opuscore.dev/functions/text_summary_attr
 */
function text_summary_attr( 
    ?string $content, int $length = 180, string|bool $hellip = false ): string {

    if( $content === null ) {
        return '';
    }

    if( $hellip === true || $hellip === '&hellip;' ) {
        $hellip === '&hellip;';
    }

    $summary = text_summary( $content, $length, $hellip );

    return htmlspecialchars( $summary, ENT_QUOTES, 'UTF-8' );
}


/**
 * Gera um resumo baseado na quantidade de palavras reais ( >= 3 caracteres )
 * Suporta caracteres multybite
 */
function word_summary( ?string $content, int $limit = 35, array $suffix = [] ): string {
    $words = trim( (string) $content );

    if( $words === '' ) {
        return '';
    }

    $readmore = '';

    $url  = $suffix['url'] ?? null;
    $text = $suffix['text'] ?? null;

    if( $url && $text ) {
        $readmore = '&hellip; <a href="' . $url . '">' . $text . '</a>';
    }

    preg_match_all(
        '/\p{L}{3,}+/u',
        $words,
        $matches,
        PREG_OFFSET_CAPTURE
    );

    if( empty($matches[0][$limit - 1]) ) {
        return $words;
    }

    $last = $matches[0][$limit - 1];

    $position = $last[1] + mb_strlen($last[0]);

    return mb_substr($words, 0, $position) . $readmore;
}


/**
 * Gera um campo de "recaptcha" simples para validacao humana.
 * E campos ocultos para verificacao de bots
 *
 * $number_1 gera um numero aleatorio entre 1 e 9
 * $number_2 gera um numero aleatório entre 1 e 10 — menos o resultado de $number_1
 * - A soma de $number_1 e $number_2 nunca passam de 10
 * 
 * @see https://opuscore.dev/functions/recaptcha
 */
function recaptcha(): void {
    ## Verificar se he humano
    $number_1 = rand( 1, 9 ); # nunca passa de 9
    $number_2 = rand( 1, 10 - $number_1 ); # nunca passa de 9
    echo <<<HTML
    <div class="recaptcha">
        <label for="antispam">Resolva a soma para enviar</label><br/>
        <input class="rand-num" type="text" name="number_1" value="{$number_1}" readonly />
        <span>+</span>
        <input class="rand-num" type="text" name="number_2" value="{$number_2}" readonly />
        <span>=</span>
        <input id="antispam" type="text" inputmode="numeric" pattern="[0-9]*" name="total" required />
    </div>
    HTML;
    
    ## Campos adicionai para Verificar se he um Bot
    $time = time();
    echo <<<HTML
    <label style="display: none;"><!-- Nao preencha este campo -->
        <input type="text" name="bot_trap" placeholder="Preencha este campo Bot" value="" />
    </label>
    <input type="hidden" name="entry_time" value="{$time}" />
    HTML;
}



function exception( string $message, ?string $origin = null ): void {

    $isdebug = defined('DISPLAY_ERRORS') || DISPLAY_ERRORS;
    
    if( is_XHR() && $isdebug === false ) {
        return;
    }

    $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $trace[1] ?? [];

    $file = $file ?? ($caller['file'] ?? 'unknown');
    $line = $line ?? ($caller['line'] ?? 0);

    # throw new OpusException( $message, $file, $line );

    $declaration = $origin !== null ? '<b>Declaração</b>: ' . $origin : '';
    echo 
    '<div style="
        background:#8b7a3c;
        color:#1f1f1f;
        padding:1rem;
        margin:1rem;
        border-radius:6px;
        font-family:ui-system,sans-serif;
        font-size:1.15rem;
        box-shadow:0 2px 6px rgba(0,0,0,.2);
    ">'
        . $message 
        . '<div style="margin-top:0.5rem;font-family:monospace;">'
        . '<b>Arquivo</b>: ' . $file . '<br>'
        . '<b>Linha</b>: ' . (int) $line . '<br>'
        . $declaration
        . '</div>
        
    </div>';
}

function is_XHR(): bool {
    $has_headers = implode( ' ', headers_list() );
    $headers = ( headers_sent() && str_contains($has_headers, 'application/json') );

    $httpxrw = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] );
    $isXHR = $httpxrw && strtolower($httpxrw) === 'xmlhttprequest';

    return $isXHR || $headers;
}

function opus_log( string $message, array $context = [] ): void {

    if( ! defined('ERROR_LOG') ) {
        error_log("Constante 'ERROR_LOG' não definida no arquivo config.php");
        return;
    }
    
    if( ERROR_LOG === false ) {
        return;
    }

    $date = date('Y-m-d H:i:s');

    if( ! empty($context) ) {
        $message .= ' | ' . json_encode( $context, JSON_UNESCAPED_SLASHES );
    }

    $line = "[{$date}] {$message}\n";

    @file_put_contents( DIR . 'opus.log', $line, FILE_APPEND | LOCK_EX );
}



/**
 * ---------------------------------------------------------------
 * Funcoes de Alertas `HTML` e Redirecionamentos com `JavaScript`:
 * @see https://packit.ui.webship.com.br/alerts/#php 
 * @param string $type O tipo do alerta (ex: 'success', 'danger', 'warning', 'info').
 * @param string $content O conteudo HTML ou texto da mensagem de alerta.
 * @param string $redirect A URL para a qual o usuario sera redirecionado.
 * @param int $setTime O tempo em milissegundos antes do redirecionamento (padrao: 6000/3200ms).
 * @param int $fadeTime | duracao da animacao fade em milissegundos (padrao: 2100ms).
 * @return void | imprime o HTML e o script diretamente.
 * 
 * @see https://opuscore.dev/functions/funcoes-de-alerta-e-redirecionamento
 */
# Exibe mensagem de alerta simples. Nao ha um mecanismo para fechar esse alerta
function alert( string $type, string $content ): void {
    echo "<div class=\"alert {$type}\" role=\"alert\">{$content}</div>";
}

# Exibe uma mensagem de alerta que desaparece apos um tempo determinado
function alert_time( 
    string $type, string $content, int $setTime = 6000, int $fadeTime = 2100 ): void {

    echo "
    <div class=\"alert {$type}\" role=\"alert\">{$content}</div>
    <script>
        window.setTimeout( () => {
            fade.out.selector('.alert', {$fadeTime});
        }, {$setTime} );
    </script>";
}

# Exibe uma mensagem de alerta e redireciona o usuario apos um tempo
function alert_redirect( 
    string $type, string $content, string $url, int $setTime = 3300 ): void {

    echo "
    <div class=\"alert {$type}\" role=\"alert\">{$content}</div>
    <script>
        window.setTimeout( () => {
            window.location='{$url}';
        }, {$setTime} );
    </script>";
}

# Redireciona para uma nova URL apos um tempo especificado
function redirect( 
    string $url, int $setTime = 3200, string $redirType = '', int $code = 303 ): void {

    if( $redirType === 'header' ) {
        header("Location: {$url}", true, 303);
        exit;
    }
    else {
        echo "
        <script>
            window.setTimeout( () => {
                window.location='{$url}';
            }, {$setTime} );
        </script>";
    }
}


/**
 * Exibe um preloader animado na tela enquanto o conteudo esta carregando 
 * e o esconde apos um tempo determinado.
 *
 * @see https://packit.ui.webship.com.br/complements/preloader/ 
 * 
 * @see https://opuscore.dev/functions/preloader
 */
function preloader( int $time = 2300 ): void {
    echo '
    <div class="loader">
        <div class="loading"></div>
        <div data-window="left"></div>
        <div data-window="right"></div>
    </div>
    <script>
        setTimeout( () => {
            document.querySelector(".loader").classList.add("show");
            setTimeout( () => {
                document.body.classList.add("loaded");
            }, ' . $time . ');
        }, ' . $time . ');
    </script>';
}

function success_preloader_redirect( string $message, string $url = '', int $time = 6000 ): void {

    $redirect = $url;

    if( $url === '' ) {
        $redirect = URL::current();
    }

    $alertTime    = (int) ($time * 0.4);
    $loaderTime   = (int) ($time * 0.3);
    $redirectTime = $time - $alertTime - $loaderTime;

    echo <<<HTML
    <div class="alert success" role="alert">$message</div>

    <div class="loader">
        <div class="loading"></div>
        <div data-window="left"></div>
        <div data-window="right"></div>
    </div>
    <script>
        setTimeout( () => {
            document.querySelector('.loader').classList.add('show');

            setTimeout( () => {
                document.body.classList.add('loaded');

                setTimeout( () => {

                    window.location = "$redirect";

                }, $redirectTime );

            }, $loaderTime );

        }, $alertTime );
    </script>
    HTML;
}

function header_location( string $loc, int $status = 301 ): never {
    while( ob_get_level() ) {
        ob_end_clean();
    }

    header( 'Location: ' . URL::root($loc), true, $status );
    exit;
}


/**
 * Converte uma data ou datetime para um formato de saida especifico.
 * @param $input  | entrada da data
 * @param $output | a escolha do formato de data que sera exibido
 * @see https://opuscore.dev/functions/date_format
 * 
 * Tabela mental util:
 *  d → dia (01-31)
 *  D → dia da semana curto (Seg)
 *  l → dia da semana completo (Segunda-feira)
 *  m → mes numerico (01-12)
 *  M → mes curto (Jan)
 *  F → mes completo (January)
 *  Y → ano (2026)
 *  H:i:s → (hora/minuto/segundos)
 */
function chronos_format( ?string $input, int|string|null $output = null ): string {

    if( $input === null ) {
        return '';
    }
    
    $ts = strtotime($input);

    if( $ts === false ) {
        exception( 'Formato de data inválida');
        return '';
    }

    if( is_string($output) ) {
        return chronos_translate( date($output, $ts) );
    }

    $format = match($output) {
        1 => date('d \d\e F \d\e Y', $ts),
        2 => date('d/m/Y \a\s H:i', $ts),
        3 => date('d/m/Y \a\s H:i:s', $ts),
        4 => date('d \d\e F \d\e Y \a\s H:i', $ts),
        5 => date('d \d\e F \d\e Y \a\s H:i:s', $ts),
        6 => "
            <div>" . date('d', $ts) . "</div>
            <div>" . date('F', $ts) . "</div>
            <div>" . date('Y', $ts) . "</div>
        ",

        default => date('d/m/Y', $ts),
    };

    return chronos_translate($format);
}
/**
 * Traduz nomes de meses e dias da semana de datas formatadas do Ingles para o Portugues.
 * 
 * @see https://opuscore.dev/functions/date_translate
 */
function chronos_translate( string $input ): string {
    static $mapper = [
        'January'   => 'Janeiro',
        'February'  => 'Fevereiro',
        'March'     => 'Março',
        'April'     => 'Abril',
        'May'       => 'Maio',
        'June'      => 'Junho',
        'July'      => 'Julho',
        'August'    => 'Agosto',
        'September' => 'Setembro',
        'October'   => 'Outubro',
        'November'  => 'Novembro',
        'December'  => 'Dezembro',

        # 'Jan' => 'Jan',
        'Feb' => 'Fev',
        # 'Mar' => 'Mar',
        'Apr' => 'Abr',
        # 'May' => 'Mai', Duas chaves May, em vez de complicar remove
        # 'Jun' => 'Jun',
        # 'Jul' => 'Jul',
        'Aug' => 'Ago',
        'Sep' => 'Set',
        'Oct' => 'Out',
        # 'Nov' => 'Nov',
        'Dec' => 'Dez',

        'Sunday'    => 'Domingo',
        'Monday'    => 'Segunda-feira',
        'Tuesday'   => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday'  => 'Quinta-feira',
        'Friday'    => 'Sexta-feira',
        'Saturday'  => 'Sabado',

        'Mon' => 'Seg',
        'Tue' => 'Ter',
        'Wed' => 'Qua',
        'Thu' => 'Qui',
        'Fri' => 'Sex',
        'Sat' => 'Sáb',
        'Sun' => 'Dom',
    ];

    // $ptBR = array_merge( $years, $weeks );

    return strtr( $input, $mapper );
}


/**
 * Converte palavras (ingles) do Plural para Singular
 * Usa `strtolower()`
 * # NOTA: Esta funcao nao eh confiavel, use com cuidado 
 * # Nao cobre 100% dos casos irregulares, nem chega perto de 100
 * 
 * # Adicione excecoes no array $irregular conforme necessario
 */
function singular( string $word ): string {
    $word = strtolower( $word );

    $irregular = [
        'people'   => 'person',
        'children' => 'child',
        'feet'     => 'foot',
        'teeth'    => 'tooth',
        'mice'     => 'mouse',
    ];

    if( isset($irregular[$word]) ) {
        return $irregular[$word];
    }

    if( substr($word, -3) === 'ies' ) {
        return substr($word, 0, -3) . 'y';
    }

    if( preg_match('/(sses|shes|ches|xes|zes)$/', $word) ) {
        return substr($word, 0, -2);
    }

    if( substr($word, -1) === 's' && substr($word, -2) !== 'ss' ) {
        return substr($word, 0, -1);
    }

    return $word;
}


/**
 * Gera uma string aleatoria segura, podendo ser utilizada como tokens, gerador de senhas
 * 
 * bin2hex( random_bytes(32) )
 * 
 * @param 
 * $limit Tamanho desejado do token (minimo 10, maximo 80), padrao 32
 * $symbol Se for 'password', inclui simbolos extras para fortalecer senhas
 */
function token_generator( int $limit = 42, int $flags = 0 ): string {
    if( $limit < 10 || $limit > 80 ) {
        exception(
            "Limites do gerador de token: mínimo 10 e máximo 80. Valor fornecido: {$limit}",
            'token_generator()'
        );

        return '';
    }

    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $nums  = '1234567890';
    $specs = '_-.~'; # simbolos que nao quebram URL

    if( $flags & HIGH_ENTROPY ) {
         $specs .= '!@#$%&*()+=[]{}^><:;?';
    }

    # define a proporcao de numeros e simbolos baseado no tamanho do token
    $proportion = match (true) {
        $limit > 70 => 20,
        $limit > 60 => 17,
        $limit > 50 => 14,
        $limit > 40 => 11,

        $limit > 30 => 8,
        $limit > 21 => 5, 
        $limit > 12 => 3,
        $limit > 8 => 2,
        default    => 1,
    };

    $num_qty  = $proportion;
    $spec_qty = $proportion;
    
    # O que sobrar vai para as letras (maiusculas e minusculas)
    $remainder = $limit - ($num_qty + $spec_qty);
    $upper_qty  = (int) floor($remainder / 2);
    $lower_qty  = $remainder - $upper_qty;

    $token = [];

    # funcao auxiliar para buscar os caracteres
    $grab = function( string $str, int $qty ) use ( &$token ) {
        if( $qty <= 0 ) {
            return;
        }

        $max_idx = strlen($str) - 1;

        for( $i = 0; $i < $qty; $i++ ) {
            $token[] = $str[ random_int(0, $max_idx) ];
        }
    };

    # preenche respeitando o limite do tamanho enviado
    $grab( $upper, $upper_qty );
    $grab( $lower, $lower_qty );
    $grab( $nums,  $num_qty   );
    $grab( $specs, $spec_qty  );

    # embaralha
    for( $i = count($token) - 1; $i > 0; $i-- ) {
        $rand = random_int( 0, $i );

        $tmp = $token[$i];

        $token[$i] = $token[$rand];
        $token[$rand] = $tmp;
    }

    return implode( '', $token );
}


/**
 * Valida uma senha com base em requisitos predefinidos de forca.
 *
 * A funcao utiliza uma expressao regular para verificar se a senha contem:
 * - Pelo menos uma letra minuscula (`(?=.*[a-z])`).
 * - Pelo menos uma letra maiuscula (`(?=.*[A-Z])`).
 * - Pelo menos um numero (`(?=.*\d)`).
 * - Entre 8 e 24 caracteres de comprimento (`{8,24}`).
 * - Permite caracteres especificos alem de alfanumericos (simbolos definidos).
 */
function requisite_password( string $password ): bool {
    # Expressao regular para validacao de senha.
    # 1) `|`: Delimitador da regex.
    # 2) `^`: Inicio da string.
    # 3) `(?=.*[a-z])`: Lookahead positivo: deve conter pelo menos uma letra minuscula.
    # 4) `(?=.*[A-Z])`: Lookahead positivo: deve conter pelo menos uma letra maiuscula.
    # 5) `(?=.*\d)`: Lookahead positivo: deve conter pelo menos um numero.
    # 6) `[a-zA-Z\d!@#$%&*()_+=-><.,;?\s]`: Conjunto de caracteres permitidos (letras, numeros e simbolos).
    #    `\s` foi adicionado para permitir espacos, se for a intencao.
    # 7) `{8,24}`: Quantificador: o comprimento total da senha deve ser entre 8 e 20 caracteres.
    # 8) `$`: Fim da string.
    
    return (bool) preg_match( 

        "|^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%&*()_+=-><.,;?\s]{8,24}$|", 

        $password 

    );
}

/**
 * @internal
 * Calcula a idade de uma pessoa com base na sua data de nascimento.
 *
 * Espera a data de nascimento no formato 'dd/mm/YYYY'.
 * A logica considera o dia e o mes para um calculo preciso da idade.
 */
function calc_age( $birth ) {
    $birthParts = explode( '/', $birth );
    $birthDay   = (int) $birthParts[0];
    $birthMonth = (int) $birthParts[1];
    $birthYear  = (int) $birthParts[2];

    $today      = new DateTime();
    $currentDay = (int) $today->format('d');
    $currentMonth = (int) $today->format('m');
    $currentYear  = (int) $today->format('Y');

    $years = $currentYear - $birthYear;

    # Ajusta a idade se o mes de aniversario ainda nao chegou
    if( $birthMonth > $currentMonth ) {
        return $years - 1;
    }
    # Ajusta a idade se o mes de aniversario ja chegou, mas o dia ainda nao
    if( $birthMonth === $currentMonth ) {
        if( $birthDay > $currentDay ) {
            return $years - 1;
        }
    }
    # Se o aniversario ja passou ou eh hoje
    return $years;
}

/**
 * @internal
 * Tenta obter o endereco IP do cliente, considerando diferentes headers HTTP
 * e opcionalmente ignorando IPs privados e reservados.
 *
 * Esta funcao eh util para logs, seguranca ou analiticos.
 * Prioriza headers que podem ser setados por proxies ou load balancers.
 */
function get_client_ip( bool $ignore_private = true ): string {
    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    foreach( $keys as $key ) {
        if( isset($_SERVER[$key]) ) {
            # Suporte a multiplos IPs em headers como HTTP_X_FORWARDED_FOR.
            $ip_list = explode( ',', $_SERVER[$key] );

            foreach( $ip_list as $ip ) {
                $ip = trim($ip);

                if( filter_var($ip, FILTER_VALIDATE_IP) ) {
                    # Se for para ignorar IPs privados/localhost/reservados.
                    if( $ignore_private ) {
                        if( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ) {
                            return $ip;
                        }
                    }
                    else {
                        # Retorna o primeiro IP valido encontrado (incluindo privados).
                        return $ip; 
                    }
                }
            }
        }
    }

    return 'DESCONHECIDO';
}


# Helpers para geracao de saida (HTML/XML/TXT) identado
/**
 * 
 * @example : 
    $html = unindent("
        <div>
            <span>Oi</span>
        </div>
    ");
    Ensure::writeLock( 'x.html', $html );

    Saida +- isso
    return
    "    <url>{$br}" .
    "        <loc>{$sm_url}</loc>{$br}" .
    "        <lastmod>{$sm_mod}</lastmod>{$br}" .
    "    </url>{$br}";
 *
 *  indented indented()
 */
function unindent( string $string ): string {
    $str = preg_replace( '/^\R+|\R+$/', '', $string );

    preg_match( '/^[ \t]*(?=\S)/m', $str, $m );
    $indent = $m[0] ?? '';

    return preg_replace( '/^' . preg_quote($indent, '/') . '/m', '', $str );
}



/**
 * Desabilitar cache do navegador para a view atual
 *
 * Envia headers HTTP que instruem o navegador e proxies a nao armazenar em cache o conteudo.
 * Util para paginas com informacoes sensiveis ou que mudam constantemente.
 */
function disable_cache(): void {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/**
 * Habilita o cache do navegador para a view atual por um periodo de tempo
 *
 * Envia headers HTTP que instruem o navegador a armazenar em cache o conteudo
 * pelo numero de segundos especificado
 */
function enable_cache( int $seconds = 86400 ): void {
    header("Cache-Control: public, max-age={$seconds}");
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT");
}



function dist_path( string $filename ): string {
    return DIST_DIR . $filename;
}

function dist_annex( string $filename ): string {
    return DIST_DIR . 'annexes/' . $filename;
}
