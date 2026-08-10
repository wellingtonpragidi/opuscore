<?php
/**
 * Configuracoes essenciais
 * Primeiro arquivo a ser carregado, o mesmo eh responsavel por:
 * - Definir configuracao do ambiente de desenvolvimento local
 * - Conexao com o banco de dados, contendo blocos separados para ambientes local vs producao
 * - Define constantes para conexao com banco de dados, contendo blocos separados por ambientes
 * - Define constante para controle e exibicao de erros e excecoes 
 * - Definicao do diretorio raiz `DIR`, servindo como base para todas as outras constantes
 * de diretorio definidas anteriormente em `dist/init.php`
 * 
 * Nao remova marcadores ## start ... e ## end ... 
 * Eles servem para ajudar a localizar blocos para atualizar quando necessario 
 */


## start config env local

$servername  = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$localhost   = in_array( $servername, ['localhost', '127.0.0.1'], true );
$localdomain = str_ends_with( $servername, '.local' );

define( 'IS_LOCAL', $localhost || $localdomain );

## end config env local


/**
 * Definicoes para conexao com o banco de dados
 *
 * Configuracoes de ambiente: localhost ou producao
 * Mantem os blocos separados para facilitar manutencao
 * 
 * @link https://opuscore.dev/constants/conexao-com-o-banco-de-dados
 */
## start define database

$env = IS_LOCAL ? 'local' : 'prod';

$db = [
    # Dados do banco local
    'local' => [
        'host' => '',
        'name' => '',
        'user' => '',
        'pswd' => '',
    ],
    # Dados do banco em producao
    'prod' => [
        'host' => '',
        'name' => '',
        'user' => '',
        'pswd' => '',
    ]
];

define( 'DB_HOST', $db[$env]['host'] );
define( 'DB_NAME', $db[$env]['name'] );
define( 'DB_USER', $db[$env]['user'] );
define( 'DB_PSWD', $db[$env]['pswd'] );

## end define database




/**
 * Controle de excecoes, detalhes e erros
 *
 * Essa constante habilita e desabilita:
 * # error_reporting | display_errors
 * # Detalhes de OpusException
 * # opus.log
 * # PHPMailer
 * 
 * @see https://opuscore.dev/constants/controle-de-exibicao-de-erros
 */
define( 'DISPLAY_ERRORS', true );

define( 'ERROR_LOG', true );




/**
 * Caminho absoluto para o diretorio raiz
 *
 * DIR eh utilizado como base para todas as outras constantes de diretorio
 * 
 * @see https://opuscore.dev/constants/dir
 */
## start define DIR
define( 'DIR', str_replace( '\\', '/', __DIR__ ) . '/' );

/**
 * Caminho absoluto fisico (canonico), resolvendo links simbolicos (symlinks)
 * REAL_DIR nao substitui DIR soh garante a localizacao real do diretorio validando caminhos
 */
define( 'REAL_DIR', str_replace( '\\', '/', realpath(__DIR__) ) . '/' );

## end define DIR




/**
 * define o tempo de sessao
 * @see https://opuscore.dev/constants/session_lifetime 
 **/
## start constant session_lifetime

define( 'SESSION_LIFETIME', 2628000 ); # 2628000 = 30 dias

## end constant session_lifetime




## start require init

require DIR . 'dist/init.php';

## end require init
