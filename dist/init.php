<?php
declare( strict_types = 1 );
/**
 * 
 * @subpackage Core\Init
 */
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
    ini_set( 'display_errors', DISPLAY_ERRORS ? 1 : 0 );
    ini_set( 'display_startup_errors', DISPLAY_ERRORS ? 1 : 0 );
    error_reporting( DISPLAY_ERRORS ? E_ALL : 0 );

    # OpusException
    define( 'EXCEPTION_DETAILS', DISPLAY_ERRORS ); 

    # PHPMailer
    define( 'MAIL_ERROR_INFO', DISPLAY_ERRORS ); 
    define( 'MAIL_SMTP_DEBUG', false ); # ->SMTPDebug = 2;

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——





# buffer e content-type inicial...
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——

    # Inicia o output buffering
    ob_start(); 

    # Define o Content-Type para HTML com UTF-8
    header( 'Content-Type: text/html; charset=utf-8' ); 

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——




# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——
    $isFile    = fn($file) => $file->isFile() && $file->getExtension() === 'php';
    $pathFile  = fn($file) => str_replace( "\\", "/", $file->getRealPath() );


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



    $settings = Provider::include_file_vars(STORAGE_DIR . 'settings.php');
    define( 'SYSTEM_EMAIL_ADDRESS', ($settings['email']['address'] ?? '') );

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





# exception-s
# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— 
     /*
     * Manipulador de excecoes
     *
     * Captura excecoes do tipo `Throwable`. Se a excecao for uma `OException`,
     * trata-a de forma personalizada (avisos ou erros fatais com limpeza de buffer).
     * Para outras excecoes, permite que o PHP continue o fluxo padrao de tratamento.
     */
    set_exception_handler( function( Throwable $e ): void {
        if( $e instanceof OpusException ) {
            # se for aviso leve exibe a mensagem sem limpar buffer
            if( strpos($e->getType(), 'e-warning') !== false ) {
                echo $e->warning();
                return;
            }

            # se for erro fatal limpa tudo e exibe pagina de erro
            while( ob_get_level() ) {
                ob_end_clean();
            }

            echo $e->error();
            exit;
        }
        throw $e; # e pra outras excecoes, (se nao for OpusException), deixa o php estourar
    });

# —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— —— ——