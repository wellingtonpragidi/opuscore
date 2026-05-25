<?php
/**
 * config.php - Configuracoes Essenciais do Sistema.
 *
 * Este eh o primeiro arquivo a ser carregado, o mesmo eh responsavel por:
 * - definir as configuracoes de ambiente
 * - Conexao com o Banco de Dados, contendo blocos separados para ambientes de desenvolvimento (localhost) e producao.
 * - Controle de Erros e Excecoes: Define uma constante para controlar exibicao de erros e excecoes 
 * - Definicao do diretorio raiz `DIR`, servindo como base para todas as outras constantes
 * de diretorio definidas posteriormente em `dist/^/init.php`. Inclui tratamento
 * para compatibilidade com diferentes sistemas operacionais (Windows/Linux).
 * - Inclusao do Arquivo de Inicializacao `dist/init.php`,
 * que continua a configuracao e definicoes de outras constantes.
 */

$servername  = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$localhost   = in_array( $servername, ['localhost', '127.0.0.1'], true );
$localdomain = str_ends_with( $servername, '.local' );

define( 'IS_LOCAL', $localhost || $localdomain );

/**
 * Definicoes para conexao com o banco de dados
 *
 * Configuracoes de ambiente: localhost ou producao
 * Mantem os blocos separados para facilitar manutencao
 * 
 * Aconselhavel nao remover ## start define database — e — ## end define database
 * pois essas podem ser necessarias em futuras atualizacoes
 * 
 * @link https://opuscore.dev/constants/conexao-com-o-banco-de-dados
 */
## start define database
if( IS_LOCAL ) {
    # Dados do banco local
    define( 'DB_HOST', '' );
    define( 'DB_NAME', '' );
    define( 'DB_USER', '' );
    define( 'DB_PSWD', '' );
}
else {
    # Dados do banco em producao
    define( "DB_HOST", '' );
    define( "DB_NAME", '' );
    define( "DB_USER", '' );
    define( "DB_PSWD", '' );
}
## end define database


/**
 * Caminho absoluto para o diretorio raiz
 *
 * DIR eh utilizado como base para todas as outras constantes de diretorio.
 * outras definicoes de constants para caminhos absolutos estao no arquivo dist/init.php
 *
 * no servidor windows o separador eh invertida "\" isso causa problemas, entao invertemos o invertido
 * @see https://opuscore.dev/constants/dir
 */
define( 'DIR', str_replace( '\\', '/', __DIR__ ) . '/' );

/**
 * Caminho absoluto fisico (canonico), resolvendo links simbolicos (symlinks)
 * REAL_DIR nao substitui DIR soh garante a localizacao real do diretorio validando caminhos
 */
define( 'REAL_DIR', str_replace( '\\', '/', realpath(__DIR__) ) . '/' );


/**
 * Controle de excecoes, detalhes e erros
 *
 * Essa constante habilita e desabilita:
 * # error_reporting | display_errors
 * # Detalhes de OpusException
 * # PHPMailer (ErrorInfo)
 *
 * Se preferir, mova essa constant para condicional IS_LOCAL:
 * exibilas em modo de desenvolvimento e nao em producao sem precisar habilita/desabilita
 * 
 * @see https://opuscore.dev/constants/controle-de-exibicao-de-erros
 */
define( 'OPUS_ERROR_REPORTING', true );


/**
 * habilita e desabilita arquivo log do sistema
 * util quando em producao OPUS_ERROR_REPORTING estiver desabilitado
 */
define( 'OPUS_DEBUG', false );


/**
 * define o tempo de sessao: `2628000` eh equivalente a 30 dias
 * @see https://opuscore.dev/constants/session_lifetime 
 **/
define( 'SESSION_LIFETIME', 2628000 ); 


## start init
# inclui o arquivo inicial do distribuidor
require DIR . 'dist/core/init.php';
## end init