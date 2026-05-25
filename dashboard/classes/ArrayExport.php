<?php
/**
 * Cria e atualiza dados persistidos em arquivos PHP organizados por arrays com variaveis declaradas
 * 
 * 
 * --------- Implementacao estavel: evitar refactors sem necessidade real ---------
 * 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\Output
 * @subpackage \...
 */

class ArrayExport {

    public static function apply( 
        string $array_name, array $array_data, string $basename ): bool {

        $file = DIR . 'storage/' . $basename . '.php';

        $vars = is_file($file) ? Provider::include_file_vars($file) : [];
        
        # Atualiza ou cria o array alvo
        if( isset($vars[$array_name]) && is_array($vars[$array_name]) ) {
            $vars[$array_name] = self::array_merge_deep( $vars[$array_name], $array_data );
        } 
        else {
            $vars[$array_name] = $array_data;
        }
        
        # Remove variaveis internas
        $vars = self::filter_superglobals( $vars );
        
        # Regera o arquivo
        return self::write( $file, $vars );
    }


    public static function rewrite( array $vars, string $file ): bool {

        return self::write( $file, $vars );
    }


    private static function write( string $file, array $vars ): bool {
        # Escreve arquivo de forma atomica (segura)
        $content = "<?php\n\n";
        
        foreach( $vars as $name => $value ) {
            try {
                $content .= '$' . $name . ' = ' . self::array_export_bracket($value) . ";\n\n";
            } 
            catch( RuntimeException ) {
                return false; # Tipo nao suportado
            }
        }
        
        # Escrita atomica com arquivo temporario
        $tmp_file = tempnam( dirname($file), 'opus_' );
        if( $tmp_file === false ) {
            return false;
        }

        if( Ensure::writeLock( $tmp_file, $content ) === false ) {
            unlink( $tmp_file );
            return false;
        }
        
        # Move para o arquivo final (operacao atomica)
        $success = rename( $tmp_file, $file );
        if( ! $success ) {
            unlink( $tmp_file );
        }

        clearstatcache( true, $file );

        if( function_exists('opcache_invalidate') ) {
            @opcache_invalidate($file, true);
        }
        
        
        return $success;
    }

    /**
     * @todo soh filtrar superglobals nao undescore ( _ ) antes de qualquer variavel 
     */
    private static function filter_superglobals( array $vars ): array {
        $globals = ['_GET', '_POST', '_SERVER', '_COOKIE', '_FILES', '_SESSION', 'GLOBALS'];

        $filter = function($name) use ($globals) {
            return ! in_array( $name, $globals );
        };

        return array_filter( $vars, $filter, ARRAY_FILTER_USE_KEY );
    }


    private static function array_export_bracket( mixed $var, int $indent = 0 ): string {
        # Exporta array com syntax [] ao inves de array()
        if( is_array($var) ) {

            if( empty($var) ) {
                return '[]';
            }
            
            $is_list = array_is_list($var); # PHP 8.1+
            $lines = [];
            $current_indent = str_repeat('    ', $indent);
            $next_indent = str_repeat('    ', $indent + 1);
            
            foreach( $var as $key => $value ) {
                $value_export = self::array_export_bracket( $value, $indent + 1 );
                
                if( $is_list ) {
                    $lines[] = $next_indent . $value_export . ',';
                }
                else {
                    $key_export = is_int($key) ? $key : var_export( (string) $key, true );
                    $lines[] = $next_indent . $key_export . ' => ' . $value_export . ',';
                }
            }
            
            return "[\n" . implode("\n", $lines) . "\n" . $current_indent . "]";
        }
        
        # Para outros tipos, usa var_export normal
        if( is_scalar($var) || is_null($var) ) {
            return var_export( $var, true );
        }
        
        throw new RuntimeException( 'Tipo não suportado: ' . gettype($var) );
    }


    private static function array_merge_deep( array $defined_var, array $array_data ): array {
        # Faz merge recursivo de arrays
        # Se algum nao for array, retorna $array_data (substituicao total)  
        if( ! is_array($array_data) || ! is_array($defined_var) ) {
            return $array_data;
        }
        foreach( $array_data as $key => $value ) {
            $merge_deep_condition = array_key_exists( $key, $defined_var ) 
                && is_array( $defined_var[$key] ) 
                && is_array( $value );

            if( $merge_deep_condition ) {
                # Recursao para sub-arrays
                $defined_var[$key] = self::array_merge_deep( $defined_var[$key], $value );
            } 
            else {
                # Substitui valor (escalar ou troca de tipo)
                $defined_var[$key] = $value;
            }
        }
        
        return $defined_var;
    }






    /**
     * metodo para atualizar valor da contant DB_PSWD de config.php
     * essa funcionalidade ainda nao foi implementada
     */
    private static function db_pswd_export( string $pswd ): bool {
        $filepath = DIR . 'config.php';

        if( ! self::isFilePath( $filepath ) ) {
            alert('error', "Caminho inválido ou tentativa de path traversal!");
            return false;
        }

        $pswd = var_export( $pswd, true );
        $markers = "## start define database" . PHP_EOL .
                   "define( 'DB_PSWD', $pswd );" . PHP_EOL .
                   "## end define database";
        $markers = trim( $markers );
        $current = file_get_contents( $filepath );
        $regex = '/## start define database.*?## end define database/s';
        if( preg_match($regex, $current) ) {
            $current = preg_replace( $regex, $markers, $current );
            $file = fopen( $filepath, 'c+' );
            if( $file && flock($file, LOCK_EX ) ) {
                fseek( $file, 0 );
                fwrite( $file, $current );
                fflush( $file );
                flock( $file, LOCK_UN );
                fclose( $file );
                return true;
            } 
            else {
                echo alert('error', "Não foi possível obter a trava!");
                return false;
            }
        }
        else {
            alert('warning', 'A constant <code>DB_PSWD</code> não foi atualizada');
        }
        return false;
    }

}