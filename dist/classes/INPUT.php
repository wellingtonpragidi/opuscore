<?php
declare( strict_types = 1 );
/**
 * Essa classe fornece metodos utilitarios para manipular e validar dados enviados via $_POST
 *
 * Centraliza o acesso e a verificacao de dados do formulario HTTP POST 
 * simplificando operacoes comuns e aumentando a legibilidade do codigo.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 *
 * @package Core\HTTP
 * @subpackage Superglobal\Validate
 */

class INPUT {

    /**
     * Alias para INPUT::isset().
     *
     * Verifica se uma chave existe na superglobal $_POST.
     */
    public static function has( string $key ): bool {
        return isset( $_POST[$key] );
    }


    
    /**
     * Obtem o valor de uma chave na superglobal $_POST.
     * Retorna string vazia se chave nao existir.
     */
    public static function str( string $key ): string {
        return trim($_POST[$key] ?? '');
    }
    /**
     * @deprecated use self::str()
     */
    public static function GET( string $key ): string {
        return trim($_POST[$key] ?? '');
    }



    /**
     * Obtem o valor de uma chave na superglobal $_POST, forcando-o para um inteiro absoluto
     * Retorna `0` se a chave nao existir ou nao contiver um valor numerico valido.
     */
    public static function int( string $key ): int {
        return Ensure::int( $_POST[$key] ?? 0 );
    }



    /**
     * Obtem o valor de uma chave na superglobal $_POST.
     * Retorna uma null se chave nao existir.
     */
    public static function try( string $key ): ?string {
        $input = $_POST[$key] ?? null;

        return ($input === null) ? null : trim((string) $input);
    }

    public static function isset( string $key ): ?string {
        return trim($_POST[$key] ?? '') ?: null;
    }



    public static function bool( string $key ): bool {
        if ( ! isset( $_POST[$key] ) ) {
            return false;
        }
        
        return Ensure::bool( $_POST[$key] );
    }

    


    public static function empty( string $key ): bool {
        $input = trim($_POST[$key]);
        return empty($input);
    }

    /**
     * Verifica se uma chave no array $_POST NAO esta vazia.
     * O oposto de `self::empty()`
     */
    public static function notEmpty( string $key ): bool {
        return ! self::empty($key);
    }

    


    public static function array( string $key ): array {
        return Ensure::array( $_POST[$key] ?? [] );
    }




    /**
     * garante string estrita e segura a partir de $_POST, 
     * return com Ensure::string que remove tags, caracteres, scheme de URL, hosts, '/s+/'
     */
    public static function cleanStr( string $key ): string {
        if( ! self::has($key) ) {
            return '';
        }

        $cleanString = Ensure::string( 
            $_POST[$key], 
            Ensure::STRING_STRICT | Ensure::STRING_REMOVE_HOSTS 
        );

        return $cleanString;
    }



    /**
     * Verifica se existe $key em $_POST e se o valor eh igual a $value sem espacos
     */
    public static function is( string $key, string $value ): bool {
        return isset($_POST[$key]) && trim($_POST[$key]) === $value;
    }




    /**
     * Verifica se existe $key em $_POST e se contem um valor nao vazio apos remover espacos
     */
    public static function full( string $key ): bool {
        return isset($_POST[$key]) && trim($_POST[$key]) !== '';
    }



    public static function action( string $value ): bool {
        $action = $_POST['action'] ?? null;

        return $action === $value;
    }

    /**
     * Verifica se o metodo da requisicao HTTP atual nao eh POST
     * Se nao for POST para o script
     * 
     * Protege contra:
     * - Acesso direto pela URL
     * - Bookmarks acidentais
     * - Crawlers/bots
     * - Usuarios clicando "recarregar"
     * 
     * Não protege contra:
     * - Ataques direcionados
     * - CSRF avancado
     * - Bot sofisticado
     */
    public static function method_request( string $method = 'POST' ): void {
        if( $_SERVER['REQUEST_METHOD'] !== $method ) {

            if( session_status() === PHP_SESSION_ACTIVE ) {
                session_write_close();
            }

            header('HTTP/1.0 405 Method Not Allowed');
            exit;
        }
    }


    public static function formSubmitted( string $name = 'action' ): bool {

        return $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$name]);
    }


    public static function anyFormSubmitted( string ...$keys ): bool {
        if( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return false;
        }
        
        # Verifica todas as chaves passadas
        foreach( $keys as $key ) {
            if( isset($_POST[$key]) ) {
                return true;
            }
        }
        
        return false;
    }

}