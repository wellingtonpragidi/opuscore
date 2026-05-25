<?php
/**
 * dist/core/init.php - Arquivo de Inicializacao do Sistema
 * Esse eh o 3º arquivo a ser carregado pelo sistema, e o 1º do core (/dist) 
 * ficando apos /index.php e /config.php
 * 
 * — Este arquivo eh atualizado pelos marcadores de comentarios entre ## start ## end 
 * usando expressao regular. Nao remova esses comentarios do contrario tera problemas apos updates
 * — O valor da constant VERSION eh atualizada junto com a atualizacao do sistema
 * — Outras constantes podem ser atualizadas pelos marcadores se necessario, estao preparadas para isso
 * Constantes para exibicao de erros eh controlada por OPUS_ERROR_REPORTING em `config.php`
 *
 * Este script eh responsavel por configurar o ambiente PHP para o funcionamento do sistema
 * Ele realiza as seguintes configuracoes essenciais:
 *
 * 1. Definicao de Constantes de Diretorio: Estabelece caminhos absolutos para
 * diretorios chave do sistema, como painel, uploads, web, templates, etc.
 * 2. Localizacao e Codificacao: Define o locale padrao, a codificacao interna
 * de caracteres (UTF-8) e o fuso horario.
 * 3. Output Buffering e Header HTTP: Inicia o buffering de saida e define o
 * cabecalho Content-Type para UTF-8.
 * 4. Inclusao do Loader: Carrega o sistema de inclusao de arquivos no diretorio `dist/loader`
 * 5. Gerenciamento de Sessoes: Configura e inicia a sessao PHP com um nome exclusivo
 * baseado no dominio/caminho, e ajusta parametros de seguranca e duracao
 * dos cookies de sessao.
 * 6. Tratamento de Excecoes: Implementa um manipulador de excecoes para OpusException e outras excecoes do PHP.
 */

/**
 * Definicoes para diretorios absolutos
 * `DIR` caminho raiz definido em `config.php`
 * 
 * @link https://opuscore.dev/constants/diretorios-absolutos
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


# Define localizacao e codificacao
setlocale( LC_ALL, 'pt_BR.UTF-8' ); # define o locale para PT-BR com UTF-8
mb_internal_encoding( 'UTF8' );     # define a codificacao interna de caracteres
mb_regex_encoding( 'UTF8' );        # define a codificacao para expressoes regulares

/**
 * Define fuso horario padrao do sistema.
 * @link https://www.php.net/manual/timezones.america.php
 */
date_default_timezone_set( 'America/Sao_Paulo' );



# Inicia o output buffering
ob_start(); 

# Define o Content-Type para HTML com UTF-8
header( 'Content-Type: text/html; charset=utf-8' ); 



# Carrega definicoes de dados
/**
 * @see https://opuscore.dev/constants/versao-do-sistema
 **/
## start versao do sistema
define('VERSION', '2.8.3');
## end version 

## start versao do banco de dados do sistema
define('DB_VERSION', '1.2.2');
## end db version 

## start URL do servidor que hospeda os pacotes de atualizacoes
define( 'ENGINE_URL', 'https://opuscore.dev');
## end engine_url


## start min php version
/**
 * @see https://opuscore.dev/constants/versao-minima-requerida
 **/
define( 'MIN_PHP_VERSION', '8.1' );
## end min php version



define( 'ENTRY_GUARD', true );


/**
 * O valor da constant OPUS_ERROR_REPORTING é alterado no arquivo config.php
 * 
 * PHP `error_reporting` | `('display_errors')`
 * @link https://www.php.net/manual/pt_BR/function.error-reporting.php
 * @link https://www.php.net/manual/pt_BR/function.ini-set.php
 * 
 * @doc
 * @see https://opuscore.dev/constants/controle-de-exibicao-de-erros
 **/
## start constants errors
if( OPUS_ERROR_REPORTING ) { 
    ini_set( 'display_errors', 1 );
    ini_set( 'display_startup_errors', 1 );
    error_reporting( E_ALL );
    define( 'EXCEPTION_DETAILS', true ); # OpusException
    define( 'MAIL_ERROR_INFO', true ); # PHPMailer
}
else {
    error_reporting( 0 );
    ini_set( 'display_errors', 0 );
    ini_set( 'display_startup_errors', 0 );
    define( 'EXCEPTION_DETAILS', false );
    define( 'MAIL_ERROR_INFO', false );
}
## end constants errors

define( 'MAIL_SMTP_DEBUG', false );


#
#
# Carrega autoloader primeiro apos config.php e declaracoes necessarias do proprio init.php
require DIST_DIR . 'core/loader.php';
#
#
#



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


/**
 * Manipulador de excecoes
 *
 * Captura excecoes do tipo `Throwable`. Se a excecao for uma `OpusException`,
 * trata-a de forma personalizada (avisos ou erros fatais com limpeza de buffer).
 * Para outras excecoes, permite que o PHP continue o fluxo padrao de tratamento.
 *
 * @param A excecao que foi lancada
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
/*
set_error_handler → converte tudo possível

set_exception_handler → último recurso

register_shutdown_function → fatal only
*/