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
     * Verifica se uma chave existe na superglobal $_POST.
     */
    public static function isset( string $key ): bool {
        return isset( $_POST[$key] );
    }

    /**
     * Alias para INPUT::isset().
     *
     * Verifica se uma chave existe na superglobal $_POST.
     */
    public static function has( string $key ): bool {
        return isset( $_POST[$key] );
    }

    public static function empty( string $key ): bool {
        return empty( trim($_POST[$key]) );
    }
    public static function isEmpty( string $key ): bool {
        return self::empty($key);
    }

    /**
     * Verifica se uma chave no array $_POST NAO esta vazia.
     *
     * O oposto de `isEmpty()`.
     */
    public static function notEmpty( string $key ): bool {
        return ! self::empty( $key );
    }

    /**
     * Obtem o valor de uma chave na superglobal $_POST.
     * Retorna uma null chave nao existir.
     */
    public static function GET( string $key ): ?string {
        return trim($_POST[$key] ?? '');
    }

    /**
     * garante string estrita e segura a partir de $_POST, 
     * return com Ensure::input que remove tags, caracteres, scheme de URL, hosts, '/s+/'
     */
    public static function str( string $key ): string {
        if( ! isset($_POST[$key]) ) {
            return '';
        }
        $cleanString = Ensure::string( 
            $_POST[$key], 
            Ensure::STRING_STRICT | Ensure::STRING_REMOVE_HOSTS 
        );

        return $cleanString;
    }
    /**
     * @deprecated use: INPUT::str(key)
     */
    public static function GetStr( string $key ): string {
        return self::str($key);
    }

    /**
     * Obtem o valor de uma chave na superglobal $_POST, forcando-o para um inteiro absoluto
     * Retorna `0` se a chave nao existir ou nao contiver um valor numerico valido.
     */
    public static function int( string $key ): int {
        return Ensure::int( $_POST[$key] ?? 0 );
    }

    public static function array( string $key ): array {
        return Ensure::array( $_POST[$key] ?? [] );
    }

    /**
     * Verifica se uma chave existe na superglobal $_POST e contem um valor nao vazio apos remover espacos.
     * Ideal para campos de texto que nao devem ser apenas espacos em branco.
     * returna `true` se a chave existir e o valor nao for uma string vazia (apos `trim()`), `false` caso contrario.
     */
    public static function full( string $key ): bool {
        return isset( $_POST[$key] ) && trim( (string) $_POST[$key] ) !== '';
    }


    public static function is( string $key, string $value ): bool {
        return isset( $_POST[$key] ) && trim( (string) $_POST[$key] ) === $value;
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


    /**
     * helpers com condicional para require controller em views
     * 
    public static function formSubmitted( 
        string $file, string $key = 'action', bool $dispatch = true ): void {

        if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$key]) ) {
            if( $dispatch ) {
                require get_dashboard_path('/controllers/loader/controller-sync.php');
            }

            require get_dashboard_path("controller/{$file}");
        }
    }
    

    public static function formSubmitted( 
        string $file, string $key = 'action', bool $dispatch = true ): bool {

        static $loaded = [];

        if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$key]) ) {
            if( $dispatch && empty($loaded['loader']) ) {
                require get_dashboard_path('/callable/loader/controller-sync.php');
                $loaded['loader'] = true;
            }

            if( empty($loaded[$file]) ) {
                $filepath = get_dashboard_path("controller/{$file}");
                require get_dashboard_path($filepath);
                $loaded[$file] = true;
            }

            return true;
        }

        return false;
    }*/

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