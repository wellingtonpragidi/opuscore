<?php
declare( strict_types = 1 );

/**
 * Arquivo de inicializacao do sistema
 * Esse eh o 3º arquivo a ser carregado pelo sistema, e o 1º do core /dist 
 * Sendo: /index.php >> /config.php >> /dist/init.php
 * 
 * — Quando necessario este arquivo tambem pode ser atualizado pelos marcadores 
 *   comentarios entre: ## start ## end 
 * - Nao remova esses comentarios a fim de evitar problemas 
 * 
 * — O valor da constant VERSION eh atualizada junto com a atualizacao do sistema
 * - Outras constantes podem ser atualizadas pelos marcadores se necessario, 
 *   existe uma preparacao para isso
 * 
 * — Constantes para exibicao de erros eh controlada por DISPLAY_ERRORS em `/config.php`
 * 
 * Este script eh responsavel por configurar o ambiente PHP para o funcionamento do sistema
 * 
 * Ele realiza as seguintes configs essenciais:
 *
 * - Definicao de Constantes para Diretorios principais
 * - Localizacao e Codificacao: Define o locale padrao, a codificacao interna de caracteres (UTF-8) e o fuso horario.
 * - Output Buffering e Header HTTP: Inicia o buffering de saida e define o
 * cabecalho Content-Type para UTF-8.
 * - Inclusao de boots: autoload de classes e requires recursivo
 * - Gerenciamento de Sessoes: Configura e inicia a sessao PHP com um nome exclusivo baseado no dominio/caminho, e ajusta parametros de seguranca e duracao dos cookies de sessao.
 * - Implementacao de manipulador de excecoes
 * 
 * @subpackage Core\Init
 */



# definicoes para diretorios absolutos
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    /**
     * `DIR` caminho raiz definido em `config.php`
     * 
     * @see https://opuscore.dev/constants/diretorios-absolutos
     */
    # diretorio do painel de administracao
    define( 'DASH_DIR', DIR . 'dashboard/' );

    # diretorio de uploads
    define( 'UPLOAD_DIR', DIR . 'uploads/' );

    # diretorio `web`
    define( 'WEB_DIR', DIR . 'web/' );

    # diretorio `templates`
    define( 'TEMPLATE_DIR', DIR . 'templates/' );

    # diretorio do distribuidor - o sistema inclui os arquivos desse diretorio em todos os outros
    define( 'DIST_DIR', DIR . 'dist/' );

    # diretorio de armazenamento de dados em arquivos
    define( 'STORAGE_DIR', DIR . 'storage/' );

    # diretorio de complementos
    define( 'ADDONS_DIR', DIR . 'addons/' );

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# definicoes de localizacao e codificacao
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    setlocale( LC_ALL, 'pt_BR.UTF-8' ); # define o locale para PT-BR com UTF-8
    mb_internal_encoding( 'UTF8' );     # define a codificacao interna de caracteres
    mb_regex_encoding( 'UTF8' );        # define a codificacao para expressoes regulares

    /**
     * Define fuso horario padrao do sistema.
     * @link https://www.php.net/manual/timezones.america.php
     */
    date_default_timezone_set( 'America/Sao_Paulo' );

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# info e versoes do sistema
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    # Carrega definicoes de dados
    /**
     * @see https://opuscore.dev/constants/versao-do-sistema
     **/

    ## start opuscore version
    define( 'VERSION', '1.1.0' );
    ## end opuscore version


    ## start opuscore DB version
    define( 'DB_VERSION', '2.0.0' );
    ## end opuscore DB version


    # URL do servidor que hospeda os pacotes de atualizacoes
    ## start opuscore engine_url
    define( 'ENGINE_URL', 'https://opuscore.dev' );
    ## end opuscore engine_url

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# definicoes para versoes minima PHP e DBs
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    /**
     * versao minima do PHP
     * @see https://opuscore.dev/constants/@docs
     **/
    define( 'MIN_PHP_VERSION', '8.1' );

    # versao minima do banco de dados MySQL 
    define( 'MIN_MYSQL_VERSION', '8.0' );

    # versao minima do banco de dados MariaDB
    define( 'MIN_MARIADB_VERSION', '10.11' );

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——






# buffer e content-type inicial...
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    # Inicia o output buffering
    ob_start(); 

    # Define o Content-Type para HTML com UTF-8
    header( 'Content-Type: text/html; charset=utf-8' ); 

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# definicoes e configuracoes de erros
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    /**
     * O valor da constant DISPLAY_ERRORS eh alterado no arquivo config.php
     * 
     * @link https://www.php.net/manual/pt_BR/function.error-reporting.php
     * @link https://www.php.net/manual/pt_BR/function.ini-set.php
     * 
     * @see https://opuscore.dev/constants/controle-de-exibicao-de-erros
     */

    # Determina do error_reporting e display_errors do ini_set
    if( DISPLAY_ERRORS === 'ALL' ) {
        $display_errors  = 1;
        $reporting_level = E_ALL;
    } 
    else if( DISPLAY_ERRORS === true ) {
        $display_errors  = 1;
        $reporting_level = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED;
    }
    else {
        $display_errors  = 0;
        $reporting_level = 0;
    }

    ini_set( 'display_errors', $display_errors );
    ini_set( 'display_startup_errors', $display_errors );
    error_reporting( $reporting_level );



    define( 'ERROR_REPORTING', (bool) $display_errors );




    # detalhes de aviso e erro da classe OpusException
    define( 'EXCEPTION_DETAILS', ERROR_REPORTING ); 

    # erros e aviso da classe PHPMailer
    define( 'MAIL_ERROR_INFO', ERROR_REPORTING ); 
    define( 'MAIL_SMTP_DEBUG', false ); # ->SMTPDebug = 2;

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——



# exception handler
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— 
     /**
     * Manipulador de excecoes
     *
     * Captura excecoes do tipo `Throwable`. Se a excecao for uma `OException`,
     * trata-a de forma personalizada (avisos ou erros fatais com limpeza de buffer)
     * 
     * Tratamento de erros para outras excecoes com HTML e CSS proprio melhorando a leitura
     */

    # ERROR, PARSE, (TypeError ParseError)
    set_exception_handler( function(Throwable $e): void {
        # se for erro fatal limpa tudo e exibe pagina de erro
        while( ob_get_level() ) {

            ob_end_clean();
        }


        # garante o cabeçalho 500 para o navegador/cliente HTTP
        if( ! headers_sent() ) {

            header('HTTP/1.1 500 Internal Server Error');
        }


        # trata excecoes customizadas OpusException
        if( $e instanceof OpusException ) {

            # se for aviso leve tenta/exibe a mensagem sem limpar buffer
            if( strpos($e->getType(), 'e-warning') !== false ) {
                echo $e->warning();
                return;
            }

            echo $e->error();
            exit;
        }

        # se DISPLAY_ERRORS estiver habilitado (Ambiente Dev):
        if( ERROR_REPORTING ) {
            html_error_handler( $e );
            exit;
        }

        # se DISPLAY_ERRORS estiver desabilitado (Producao)
        # nao exibe NENHUM detalhe tecnico para o usuario final
        int_server_error_500();
        exit;

    });

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——




# error handler
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    if( ERROR_REPORTING ) {
        # WARNING, NOTICE, DEPRECATED
        set_error_handler( function(
            int $severity, string $message, string $file, int $line): bool {

            # Respeita o operador de silencio @ (ex: @file_get_contents)
            if( ! (error_reporting() & $severity) ) {

                return false;
            }

            # Mapeiamento do nome dos tipos de "erro"
            $type = match ($severity) {
                E_WARNING, E_USER_WARNING       => 'WARNING',
                E_NOTICE, E_USER_NOTICE         => 'NOTICE',
                E_DEPRECATED, E_USER_DEPRECATED => 'DEPRECATED',
                default                         => 'NOTICE'
            };

            # Renderiza um box inline escuro no proprio ponto onde o aviso ocorreu
            echo <<<HTML
            <div 
                style="background:#161b22; color:#e6edf3; margin:15px; padding:10px 14px;
                font-family:monospace; font-size:15px; line-height:1.8;
                border:1px solid #30363d; border-left:4px solid#d29922; border-radius:4px; 
                box-shadow:0 2px 4px rgba(0, 0, 0, 0.25);"
            >
                ⚠️ <b style="color:#d29922;">[{$type}]</b> {$message}<br>
                <span style="color:#8b949e; font-size:14px;">{$file} na linha <b>{$line}</b></span>
            </div>
            HTML;


            # Retornar true impedindo que o script pare e esconde o texto cru do PHP
            return true;
        });
    }

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# autoboot
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    $isFile   = fn($file) => $file->isFile() && $file->getExtension() === 'php';
    $pathFile = fn($file) => str_replace( "\\", "/", $file->getRealPath() );


    require DIST_DIR . 'boots/dist.php';

    /**
     * Definicao da constant de diretorio absoluto para o template ativo
     *  na raiz templates ou nao
     * 
     * Precisa ser definida antes de carregar dependencias de `dashboard/` e `web/` 
     * pois ambos dependem de dele
     * 
     * @see https://opuscore.dev/constants/template_path
     */
    $template = Container::call('TemplateManager');
    define( 'TEMPLATE_PATH', $template->path() );


    if( defined('IS_DASHBOARD') && IS_DASHBOARD ) {

        require DIST_DIR . 'boots/dashboard.php';
    }


    if( defined('IS_WEB') && IS_WEB ) {

        require DIST_DIR . 'boots/web.php';
    }

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# definicoes globais gerais
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    define( 'MB', 1024 * 1024 );


    define( 'ENTRY_GUARD', true );


    define( 'HIGH_ENTROPY', 1 );

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——




# sessions
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    # Determina o nome da sessao com base no ambiente (localhost ou producao)
    $base = '';
    if( IS_LOCAL ) {
        # Remove DOCUMENT_ROOT e pega soh o ultimo diretorio
        $relative = str_replace( $_SERVER['DOCUMENT_ROOT'], '', DIR );
        $relative = trim( $relative, '/' );
        $parts    = explode( '/', $relative );
        $base     = end( $parts );
    } 
    else {
        # Usa o dominio completo, substituindo pontos por underscore
        $base = str_replace( '.', '_', $_SERVER['SERVER_NAME'] );
        $base = str_replace( 'OPUSCORE', '', $base ); # remove OPUSCORE do hostname
    }
    # Limpa tudo que for diferente de letras, numeros e undescore
    $clean = preg_replace( '/[^A-Za-z0-9_]/', '', $base );
    # Monta o nome final, tudo maiusculo e com limite de tamanho
    $sessioname = substr( 'OPUSCORE_' . strtoupper($clean), 0, 28 );

    if( session_status() === PHP_SESSION_NONE ) { 
        $secure = IS_LOCAL ? false : true;

        session_name( $sessioname );

        $handler = new OpusSessionHandler(); 
        session_set_save_handler( $handler, true ); 

        ini_set( 'session.gc_maxlifetime', (string) SESSION_LIFETIME );
        session_save_path( $handler->getSavePath() ); # session.save_path para debug

        session_set_cookie_params([ 
            'lifetime' => SESSION_LIFETIME, # Tempo da sessao, definido em config.php 
            'path'     => '/',     # Disponivel em todas os diretorios 
            'domain'   => '',      # ('') cookie valido apenas para esse dominio 
            'secure'   => $secure, # true em producao, false em desenvolvimento (localhost) 
            'httponly' => true,    # JS nao acessa 
            'samesite' => 'Lax'    # Protecao contra CSRF 
        ]); 
        register_shutdown_function('session_write_close'); 

        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '1000');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.sid_length', 128);
        ini_set('session.sid_bits_per_character', 6);

        session_start();
    }

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——






# helpers : exception handler `set_exception_handler()`
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    function html_error_handler( Throwable $e ): void {
        $typerror = get_class( $e );
        $message  = htmlspecialchars( $e->getMessage() );
        $file     = htmlspecialchars( $e->getFile() );
        $line     = $e->getLine();
        $trace    = htmlspecialchars( $e->getTraceAsString() );

        $style_css = '<style>' . css_error_handler() . '</style>';

        $v = VERSION;

        # Renderiza uma tela limpa, escura e formatada
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Erro do sistema ({$typerror})</title>
            {$style_css}
        </head>
        <body>
            <div id="error-card">
                <div id="error-title"><b>{$typerror}</b> {$message}</div>
                <div id="error-file">
                    <b>Arquivo:</b> {$file} na linha <span>{$line}</span>
                </div>
                
                <div id="trace-header">Stack Trace:</div>
                <pre id="trace">{$trace}</pre>
            </div>
            <div id="error-footer">Opus Core {$v}</div>
        </body>
        </html>
        HTML;
    }


    function css_error_handler(): string {
        return <<<CSS
        body { 
            background-color: #0d1117; 
            color: #c9d1d9; 
            font-family: monospace; 
            font-size: 14px; 
            line-height: 1.6; 
            margin: 0; 
            padding: 20px;
            position: relative;
        }
        #error-card { 
            background: #161b22; 
            border: 1px solid #30363d;
            border-left: 6px solid #f85149; 
            border-radius: 6px; 
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); 
        }
        #error-title { 
            color: #f85149; 
            font-size: 18px; 
            font-weight: bold; 
            margin-top: 0; 
            word-break: break-word;
        }
        #error-file { 
            color: #8b949e; 
            background: #21262d; 
            padding: 8px 12px; 
            border-radius: 4px; 
            font-size: 13px; 
            margin: 15px 0; 
        }
        #error-file span { 
            color: #58a6ff; 
        }
        #trace-header { 
            color: #79c0ff; 
            font-size: 14px; 
            font-weight: bold; 
            margin-top: 20px; 
            margin-bottom: 8px; 
            border-bottom: 1px solid #21262d; 
            padding-bottom: 4px; 
        }
        pre#trace { 
            background: #0d1117; 
            padding: 15px; 
            border-radius: 6px; 
            overflow-x: auto; 
            color: #e6edf3; 
            border: 1px solid #21262d; 
            white-space: pre-wrap; 
            word-wrap: break-word; 
        }
        #error-footer {
            position: absolute;
            bottom: 6px;
            right: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.45);
        }
        CSS;
    }


    function int_server_error_500(): void {
        $v = VERSION;
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Erro 500</title>
        </head>
        <body style="background:#0d1117;color:#c9d1d9;font-family:system-ui,sans-serif;margin:0;padding:20px;">
            <div style="margin:160px auto 0;text-align:center;">
                <h1>Há algum problema neste site</h1>
                O servidor retornou o erro <span style="color:#f85149;">500</span> Internal Server Error
            </div>
            <div style="position:absolute;bottom:6px;right:10px;font-size:12px;color:rgba(255,255,255,.4)">Opus Core {$v}</div>
        </body>
        </html>
        HTML;
    }

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
