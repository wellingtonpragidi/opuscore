<?php

/**
 * retorna a URL raiz do site, com uma extensao opcional.
 * @see https://opuscore.dev/functions/site_url
 */
function site_url( string $extend = '' ): string {
    $url = URL::root( $extend );

    return Hook::call_filter( 'site_url', $url );
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

function assets( string $extend ): string {
    return URL::root( 'dist/assets/' . $extend );
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
        <input class="rand-num" type="text" name="number_1" value="$number_1" readonly />
        <span>+</span>
        <input class="rand-num" type="text" name="number_2" value="$number_2" readonly />
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
    <input type="hidden" name="entry_time" value="$time" />
    HTML;
}


/**
 * @internal
 * @deprecated use ->> nada. isso eh gambiarra que um dia foi necessaria
 * Em breve a unica ultima funcao que precisa dessa sera excluida
 * 
 * Converte uma URL absoluta de upload para um caminho de diretorio.
 */
function replace_upload_url( ?string $absURL ): string {
    $replace = '';
    if( $absURL ) {
        # Substitui a URL base de upload pela constante de diretorio fisico.
        $replace = str_replace( upload_url(), UPLOAD_DIR, $absURL );
    }
    return $replace;
}



function exception( string $message, ?string $origin = null ): void {

    $isdebug = defined('OPUS_ERROR_REPORTING') || OPUS_ERROR_REPORTING;
    
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

    if( ! defined('OPUS_DEBUG') || ! OPUS_DEBUG ) {
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

    echo "<div class=\"alert {$type}\" role=\"alert\">{$content}</div>
    <script>
        window.setTimeout( function() {
            fade.out.selector('.alert', {$fadeTime});
        }, {$setTime} );
    </script>";
}

# Exibe uma mensagem de alerta e redireciona o usuario apos um tempo
function alert_redirect( 
    string $type, string $content, string $redirect, int $setTime = 3300 ): void {

    echo "<div class=\"alert {$type}\" role=\"alert\">{$content}</div>
    <script>
        window.setTimeout( function() {
            window.location='{$redirect}';
        }, {$setTime} );
    </script>";
}
# Redireciona para uma nova URL apos um tempo especificado

function redirect( string $redirect, $type = '', int $setTime = 3200, int $code = 303 ): void {
    if( $type === 'header' ) {
        header("Location: {$redirect}", true, 303);
        exit;
    }
    else {
        echo "<script>
            window.setTimeout( function() {
                window.location='{$redirect}';
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
    echo '<div class="loader">
        <div class="loading"></div>
        <div data-window="left"></div>
        <div data-window="right"></div>
    </div>
    <script>
        setTimeout(function() {
            document.body.classList.add("loaded");
        }, '. $time .');
    </script>';
}


/**
 * Converte uma data ou datetime para um formato de saida especifico.
 * @param $input  | entrada da data
 * @param $output | a escolha do formato de data que sera exibido
 * @see https://opuscore.dev/functions/date_format
 * 
 * Tabela mental útil:
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

    //$ptBR = array_merge( $years, $weeks );

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
 * @internal
 * Gera uma string aleatoria segura, podendo ser utilizada como token para URLs ou como senha.
 *
 * A funcao permite definir o tamanho do token e, opcionalmente, incluir simbolos
 * especificos caso seja para uso como senha. Eh aplicada uma verificacao minima e maxima
 * para o tamanho do token, evitando valores inseguros (muito curtos) ou excessivos.
 *
 * @param 
 * $size Tamanho desejado do token (minimo 10, maximo 80), padrao 32
 * $symbol Se for 'password', inclui simbolos extras para fortalecer senhas
 */
function token_generator( int $size = 32, string $symbol = '' ): string {
    if( $size < 10 || $size > 80 ) {
        return "Token precisam ter no minimo 10 e no maximo 80 caracteres.";
    }
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-.~@';
    if( $symbol === 'password' ) {
        $chars .= '!@#$%&*()_+=-[]{}~^><.:;?';
    }
    $token = '';
    $max = strlen( $chars ) - 1;
    for( $i = 1; $i <= $size; $i++ ) {
        $random = mt_srand( random_int(0, PHP_INT_MAX) );
        $random = random_int( 0, $max );
        $token .= $chars[$random];
    }

    return $token;
}


/**
 * Valida uma senha com base em requisitos predefinidos de forca.
 *
 * A funcao utiliza uma expressao regular para verificar se a senha contem:
 * - Pelo menos uma letra minuscula (`(?=.*[a-z])`).
 * - Pelo menos uma letra maiuscula (`(?=.*[A-Z])`).
 * - Pelo menos um numero (`(?=.*\d)`).
 * - Entre 8 e 20 caracteres de comprimento (`{8,20}`).
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
    # 7) `{8,20}`: Quantificador: o comprimento total da senha deve ser entre 8 e 20 caracteres.
    # 8) `$`: Fim da string.
    $pattern = "|^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%&*()_+=-><.,;?\s]{8,20}$|";

    return (bool) preg_match( $pattern, $password );
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
 *
 *  indented indented()
 */
function unindent( string $string ): string {
    $str = preg_replace( '/^\R+|\R+$/', '', $string );

    preg_match( '/^[ \t]*(?=\S)/m', $str, $m );
    $indent = $m[0] ?? '';

    return preg_replace( '/^' . preg_quote($indent, '/') . '/m', '', $str );
}
/*
    return
    "    <url>{$br}" .
    "        <loc>{$sm_url}</loc>{$br}" .
    "        <lastmod>{$sm_mod}</lastmod>{$br}" .
    "    </url>{$br}";
*/


/**
 * Desabilita o cache do navegador para a pagina atual.
 *
 * Envia headers HTTP que instruem o navegador e proxies a nao armazenar em cache o conteudo.
 * Util para paginas com informacoes sensiveis ou que mudam constantemente.
 * @return void
 */
function disable_cache() {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/**
 * Habilita o cache do navegador para a pagina atual por um periodo de tempo.
 *
 * Envia headers HTTP que instruem o navegador a armazenar em cache o conteudo
 * pelo numero de segundos especificado.
 *
 * @param int $seconds O tempo em segundos que o cache deve ser valido (padrao: 86400 segundos = 24 horas).
 * @return void
 */
function enable_cache( $seconds = 86400 ) {
    header("Cache-Control: public, max-age=$seconds");
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT");
}


function get_core_path( string $extend ): string {
    return DIST_DIR . ltrim( $extend, '/' );
}


function dumper( $data ) {
    echo '<style>
        .dumper-container {
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            font-family: Consolas, Monaco, "Courier New", Courier, monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre;
            word-wrap: break-word;
            overflow: auto;
            color: #333;
            position: relative;
        }
        .dumper-container pre {
            margin: 0;
            padding: 0;
        }
        .dumper-type {
            font-weight: bold;
            color: #888;
        }
        .dumper-string { color: #d14; }
        .dumper-int { color: #099; }
        .dumper-bool { color: #099; }
        .dumper-null { color: #777; }
        .dumper-key { color: #905; font-weight: bold; }
        .dumper-value { color: #000; }
        .dumper-toggle {
            cursor: pointer;
            font-size: 12px;
            color: #007bff;
            display: inline-block;
            margin-right: 5px;
            font-weight: bold;
        }
        .dumper-toggle:hover {
            text-decoration: underline;
        }
        .dumper-toggle-icon {
            font-family: monospace;
            display: inline-block;
            width: 1em;
            text-align: center;
        }
        .dumper-content {
            display: block;
            margin-left: 20px;
        }
        .dumper-collapsed {
            display: none;
        }
    </style>';

    echo '<div class="dumper-container">';

    // Inicia a captura de saída do var_dump
    ob_start();
    var_dump( $data );
    $dump = ob_get_clean();

    // Adiciona as cores e a formatação com Regex
    $dump = preg_replace('/=>\s+/', '<span class="dumper-key">=></span> ', $dump);
    $dump = preg_replace('/string\((.*?)\)/', '<span class="dumper-type">string</span>($1)', $dump);
    $dump = preg_replace('/int\((.*?)\)/', '<span class="dumper-type">int</span>(<span class="dumper-int">$1</span>)', $dump);
    $dump = preg_replace('/float\((.*?)\)/', '<span class="dumper-type">float</span>(<span class="dumper-int">$1</span>)', $dump);
    $dump = preg_replace('/bool\((true|false)\)/', '<span class="dumper-type">bool</span>(<span class="dumper-bool">$1</span>)', $dump);
    $dump = preg_replace('/array\((.*?)\)/', '<span class="dumper-type">array</span>($1)', $dump);
    $dump = preg_replace('/NULL/', '<span class="dumper-null">NULL</span>', $dump);

    // Substitui as strings capturadas
    $dump = preg_replace("/'([^']*)'/", '<span class="dumper-string">\'$1\'</span>', $dump);

    // Adiciona o toggle para arrays e objetos. Isso é feito com uma lógica simples de regex para adicionar o JS
    // A única parte JS é para o toggle, mas é mínimo e não depende de bibliotecas
    $dump = preg_replace_callback( '/(array|object)\((.*?)\)\s*\{/', function($matches) {
        $toggle_id = uniqid('toggle_');
        return '<span class="dumper-type">' . $matches[1] . '</span>(' . $matches[2] . ') { 
            <a class="dumper-toggle" onclick="var el = document.getElementById(\'' . $toggle_id . '\');
                var icon = this.querySelector(\'.dumper-toggle-icon\'); 
                if( el.style.display === \'none\' ) { 
                    el.style.display = \'block\'; icon.innerHTML = \'▼\'; 
                } 
                else { 
                    el.style.display = \'none\'; icon.innerHTML = \'►\'; 
                }
            ">
                <span class="dumper-toggle-icon">▼</span>
            </a>
            <div id="'. $toggle_id .'" class="dumper-content">';
    }, $dump );

    # Fecha as tags
    $dump = str_replace( "}\n", "}</div>\n", $dump );

    echo '<pre>'. $dump .'</pre>';
    echo '</div>';
}
/*
HTML var_dump:
<pre class="xdebug-var-dump" dir="ltr"><small>C:\wamp\www\opuscore.dev\lab\dashboard\view\home.php:112:</small>
<b>array</b> <i>(size=7)</i>
  'php' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=4)</i>
      'title' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Versão do PHP'</font> <i>(length=14)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">true</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Versão atual: 8.3.6'</font> <i>(length=20)</i>
      'recommended' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Recomendado: 8.1 ou superior'</font> <i>(length=28)</i>
  'gd' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">true</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Biblioteca GD detectada'</font> <i>(length=23)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Necessária para gerar miniaturas e imagens destacadas.'</font> <i>(length=55)</i>
  'imagick' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">false</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Biblioteca ausente'</font> <i>(length=18)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Recomendado para melhor qualidade e suporte a múltiplos formatos.'</font> <i>(length=66)</i>
  'opcache' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">true</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Ativo (0% de memória usada)'</font> <i>(length=28)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Reduz parsing e compilação de scripts, melhorando desempenho.'</font> <i>(length=63)</i>
  'gzip' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">''</font> <i>(length=0)</i>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Compressão desativada'</font> <i>(length=22)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Ative para reduzir tamanho de resposta em até 70%.'</font> <i>(length=51)</i>
  'dom' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">true</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Extensão DOM disponível'</font> <i>(length=25)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Necessária para sanitização e manipulação de HTML.'</font> <i>(length=55)</i>
  'intl' <font color="#888a85">=&gt;</font> 
    <b>array</b> <i>(size=3)</i>
      'status' <font color="#888a85">=&gt;</font> <small>boolean</small> <font color="#75507b">true</font>
      'info' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Biblioteca Intl ativa'</font> <i>(length=21)</i>
      'recommendation' <font color="#888a85">=&gt;</font> <small>string</small> <font color="#cc0000">'Útil para formatação de números, datas e idiomas.'</font> <i>(length=53)</i>
</pre>
*/




function dumpHighlight($var) {
    // Captura o var_dump como string
    ob_start();
    var_dump($var);
    $output = ob_get_clean();

    // Substituições simples para destaque
    $replacements = [
        'int(' => '<span style="color:#FF5722;font-weight:bold;">int</span>(',
        'float(' => '<span style="color:#3F51B5;font-weight:bold;">float</span>(',
        'string(' => '<span style="color:#4CAF50;font-weight:bold;">string</span>(',
        'bool(' => '<span style="color:#009688;font-weight:bold;">bool</span>(',
        'NULL' => '<span style="color:#9E9E9E;font-style:italic;">NULL</span>',
        'array(' => '<span style="color:#FF9800;font-weight:bold;">array</span>(',
        'object(' => '<span style="color:#9C27B0;font-weight:bold;">object</span>(',
        '=>' => '<span style="color:#607D8B;">=&gt;</span>',
        'string' => '<span style="color:#4CAF50;">string</span>',
    ];

    $highlighted = strtr($output, $replacements);

    // Envolve em pre para manter identação
    echo '<pre style="background:#2E2E2E;color:#F1F1F1;padding:1rem;border-radius:6px;font-family:Consolas,monospace;font-size:0.95rem;">' 
         . $highlighted . 
         '</pre>';
}