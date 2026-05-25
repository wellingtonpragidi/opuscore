<?php
declare( strict_types = 1 );
/**
 * classe responsavel pela higienizacao e validação de dados (principal)
 * A maioria dos parametros de entrada `$value` dos metodos de sanitizacao sao mixed, 
 * pois quem deve garantir o retorno apropriado eh o script do metodo e nao o tipo do parametro
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Core\Sanitize\Validate
 * @subpackage Integrity\Purifier
 */

class Ensure {

    # FLAGS
    public const int ABSOLUTE_INT = 1;
    public const int ONLY_VALIDATE_INT = 0;

    public const int STRING_STRICT       = 1 << 0; # 1
    public const int STRING_REMOVE_HOSTS = 1 << 1; # 2

    public const int REMOVE_EDGE_WHITESPACE = 1;

    public const int FILENAME_EXTENSION = 1;
    public const int ONLY_SLUG          = 0;

    public const int FILE_PUT_LOCK      = 1;
    public const int FILE_HANDLING_LOCK = 2;
    public const int USE_REAL_FILEPATH  = 4;

    /**
     * Por padrao ($abs = true) retorna a versao absoluta de um numero inteiro validos e positivos
     * @example
     * Ensure::int('123');   # 123
     * Ensure::int('-45');   # 45
     * Ensure::int('3.14');  # 0
     * Ensure::int('abc');   # 0
     * Ensure::int(100);     # 100
     * 
     * Se ($abs = false) — verifica e retorna um numero inteiro caso seja valido
     * 
     * Independente de $abs true ou false se o valor passado nao for um inteiro valido retorna 0
     */
    public static function int( mixed $value, int $flags = self::ABSOLUTE_INT ): int {
        if( ! is_int($value) && ! is_string($value) ) {
            return 0;
        }

        $integer = filter_var( $value, FILTER_VALIDATE_INT );

        if( $integer === false ) {
            return 0;
        }

        if( $flags & self::ONLY_VALIDATE_INT ) {

            return $integer;
        }

        return abs($integer);
    }


    public static function tryInt( mixed $value, int $flags = self::ABSOLUTE_INT ): ?int {
        if( ! is_int($value) && ! is_string($value) ) {
            return null;
        }

        $integer = filter_var( $value, FILTER_VALIDATE_INT );
        
        if( $integer === false ) {
            return null;
        }

        if( $flags & self::ONLY_VALIDATE_INT ) {
            return $integer;
        }

        return abs($integer);
    }

    /**
     * Higieniza e garante que um valor seja um numero real de ponto flutuante
     * Converte o valor diretamente para float
     * @todo passar flag em $implicit
     */
    public static function float( mixed $value, bool $implicit = false ): float {
        if( $implicit ) {
            $number = filter_var( 
                $value, 
                FILTER_SANITIZE_NUMBER_FLOAT, 
                FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC 
            );

            return ($number !== false && $number !== '') ? (float) $number : 0.0;
        }

        $number = filter_var( $value, FILTER_VALIDATE_FLOAT );

        return is_float($number) ? $number : 0.0;
    }

    /**
     * valida/higieniza garantindo valor booleano
     *
     * Utiliza FILTER_VALIDATE_BOOLEAN com a flag FILTER_NULL_ON_FAILURE
     * Se a validacao resultar em null (falha), retorna false
     */
    public static function bool( mixed $value ): bool {

        return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false;
    }

    /**
     * Higieniza e garante que um valor seja uma string segura para exibicao HTML.
     * Por padrao ($flags = 0) soh escapa caracteres do valor de entrada
     * 
     */
    public static function string( mixed $value, int $flags = 0 ): string {
        if( ! is_string($value) ) {
            return '';
        }

        $string = $value;

        if( $flags & self::STRING_STRICT ) {

            $string = strip_tags($string);

            $string = self::squeeze($string);

            $string = self::removeScheme($string);
            
            # somente letras minusculas, numeros, hifen e underscore
            # $string = preg_replace( '/[^a-z0-9_-]/', '', $string );

            if( $flags & self::STRING_REMOVE_HOSTS ) {
                $string = self::removeHosts($string);
            }
        }

        $string = htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' );

        return trim($string);
    }

    
    public static function strConv( mixed $value ): string {
        return is_scalar($value) ? trim($value) : '';
    }

    /**
     * retorna string vazia pra tudo o que nao for string 
     */
    public static function abstr( mixed $value ): string {
        if( ! is_string($value) ) {
            return '';
        }

        return trim($value);
    }

    /**
     * Higieniza string para uso em atributos HTML
     * 1. Remove elementos(tags) HTML
     * 2. Converte caracteres especiais para entidades HTML (incluindo aspas duplas e simples)
     * @uses squeeze :
     * - Remove multiplos espacos consecutivos por um 
     *   e remove do inicio/fim passando a flag REMOVE_EDGE_WHITESPACE que eh trim()
     */
    public static function attr( mixed $value ): string {
        if( ! is_string($value) ) {
            exception('Ensure::attr : argumento $value inválido.');
            return '';
        }
        if( $value === '' ) {
            return '';
        }

        $attribute = strip_tags( $value );
        $attribute = htmlspecialchars( $attribute, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
        $attribute = self::squeeze( $attribute, self::REMOVE_EDGE_WHITESPACE );

        return $attribute;
    }


    /**
     * higieniza e valida um endereco de e-mail
     *
     * Primeiro remove caracteres que nao sao validos em emails, depois valida
     * se a string resultante tem o formato de um e-mail valido
     * 
     * Isso nao garante que o e-mail exista, soh verifica se o formato eh igual a um e-mail
     */
    public static function email( mixed $value ): string {
        if( ! is_string($value) ) {
            return '';
        }
        $email = filter_var( $value, FILTER_SANITIZE_EMAIL );   
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }


    /**
     * Higieniza e valida uma URL
     *
     * Primeiro remove caracteres que nao sao validos em URLs, depois valida
     * se a string resultante tem o formato de uma URL valida
     */
    public static function URL( mixed $value ): string {
        if( ! is_string($value) ) {
            return '';
        }
        $URL = filter_var( $value, FILTER_SANITIZE_URL );   
        return filter_var($URL, FILTER_VALIDATE_URL) ? $URL : '';
    }
    

    /**
     * Limpa uma string, removendo caracteres especiais e acentos, e formata-a para uso em slugs
     * ou nomes de arquivos, preservando extensoes $ext_allowed
     *
     * A funcao converte caracteres acentuados para suas equivalentes sem acento, remove
     * pontuacoes e simbolos indesejados, e garante que extensoes de arquivo populares sejam mantidas.
     * 
     * @todo Substituir a funcao str_clean() por esse metodo
     */
    public static function slug( 
        mixed $value, int $flags = self::ONLY_SLUG, array $ext_allowed = [] ): string {

        if( ! is_string($value) ) {
            return '';
        }

        $string = $value;
        
        if( mb_check_encoding($string, 'UTF-8') ) {
            # primeiro tenta iconv 
            $converted = iconv( 'UTF-8', 'ASCII//TRANSLIT', $string );
            if( $converted !== false && is_string($converted) ) {
                $string = $converted;
            }
        }
        else {
            # fallback manual
            $string = self::replaceChars($string); 
        }

        $string = str_replace( '/', '-', $string );
        
        $string = preg_replace( '/[ -]+/', '-', $string ); # multiplos espacos e hifens → um hifen
        $string = preg_replace( '/_+/', '_', $string ); # multiplos underscores → um underscore

        # necessario aqui pois preg_replace abaixo remove letras maiusculas
        $string = strtolower( $string );

        # somente letras minusculas, numeros, hifen e underscore, assim nenhum outro caractere passa
        $string = preg_replace( '/[^a-z0-9_-]/', '', $string );

        # remove espaco - hifen - underscore do inicio/fim
        $slug = trim( $string, ' -_' );

        # se extensao de arquivo permitido 
        if( $flags & self::FILENAME_EXTENSION ) {

            # caso parametro $ext_allowed seja o padrao vazio, 
            # preserva estencoes contidas na propriedade $ext_allowed
            $extensions = $ext_allowed ?: array_merge(...FILES::EXT_ALL);

            foreach( $extensions as $ext ) {
                # Verifica se slug TERMINA com a extensao (sem ponto)
                if( str_ends_with($slug, $ext) ) {
                    # Remove a extensão do final e adiciona com ponto
                    $basename = substr( $slug, 0, -strlen($ext) );

                    $slug = "{$basename}.{$ext}";
                }
            }

        }

        return $slug;
    }


    /**
     * Substitui caracteres acentuados por suas versoes sem acentos
     * Metodo fallback para iconv() com substituicoes manuais que cobre varios caracteres latinos
     * 
     * `iconv`: Internationalization Conversion (Conversao de Internacionalizacao)
     * UTF-8: Unicode Transformation Format (Formato de Transformacao Unicode) — 8-bit
     * - padrao universal que suporta todos os caracteres do mundo
     * - media de 95% da web moderna usa UTF-8
     * ASCII: → American Standard Code for Information Interchange
     * Codificacao antiga dos EUA de (1963)
     * Só tem 128 caracteres: A-Z, a-z, 0-9, pontuacao basica. Nao tem acentos ou cedilha
     */
    private static function replaceChars( string $string ): string {
        $replacements = [
            'a' => ['À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å','æ','ā','ă','ą','ǟ','ǡ','ǻ','ȁ','ȃ','ḁ','ẚ','ạ','ả','ấ','ầ','ẩ','ẫ','ậ','ắ','ằ','ẳ','ẵ','ặ','ᶏ','ᶐ'],
            'c' => ['Ç','ç','ć','ĉ','ċ','č','ƈ','ḉ','ᶜ'],
            'd' => ['đ','ď','ḋ','ḍ','ḏ','ḑ','ḓ','ᶁ'],
            'e' => ['È','É','Ê','Ë','è','é','ê','ë','ē','ĕ','ė','ę','ě','ȅ','ȇ','ȩ','ḕ','ḗ','ḙ','ḛ','ḝ','ẹ','ẻ','ẽ','ế','ề','ể','ễ','ệ','ᶒ','ᶓ','ᶔ'],
            'g' => ['ğ','ĝ','ġ','ģ','ǧ','ǵ','ḡ','ᶃ'],
            'h' => ['ĥ','ħ','ḣ','ḥ','ḧ','ḩ','ḫ','ẖ','ᶣ'],
            'i' => ['Ì','Í','Î','Ï','ì','í','î','ï','ī','ĭ','į','ǐ','ȉ','ȋ','ḭ','ḯ','ỉ','ị','ᶖ','ᶤ'],
            'j' => ['ĵ','ǰ','ɉ','ᶨ'],
            'k' => ['ķ','ǩ','ḱ','ḳ','ḵ','ᶄ'],
            'l' => ['ĺ','ļ','ľ','ŀ','ł','ḷ','ḹ','ḻ','ḽ','ᶅ'],
            'n' => ['Ñ','ñ','ń','ņ','ň','ŉ','ŋ','ƞ','ǹ','ȵ','Ṅ','ṅ','Ṇ','ṇ','Ṉ','ṉ','Ṋ','ṋ','ᶇ'],
            'o' => ['Ò','Ó','Ô','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø','ð','ō','ŏ','ő','ƍ','ơ','ǒ','ǫ','ǭ','ǿ','ȍ','ȏ','ȫ','ȭ','ȯ','ȱ','ṍ','ṏ','ṑ','ṓ','ọ','ỏ','ố','ồ','ổ','ỗ','ộ','ớ','ờ','ở','ỡ','ợ','ᴑ'],
            'r' => ['ŕ','ŗ','ř','ȑ','ȓ','ṛ','ṝ','ṟ','ᶉ'],
            's' => ['ß','ś','ŝ','ş','š','ș','ṣ','ṥ','ṧ','ṩ','ᶊ'],
            't' => ['ţ','ť','ŧ','ƫ','ƭ','ț','ṫ','ṭ','ṯ','ṱ','ẗ','ᶵ'],
            'u' => ['Ù','Ú','Û','Ü','ù','ú','û','ü','ū','ŭ','ů','ű','ų','ư','ǔ','ǖ','ǘ','ǚ','ǜ','ȕ','ȗ','ṳ','ṵ','ṷ','ṹ','ṻ','ụ','ủ','ứ','ừ','ử','ữ','ự','ᶙ'],
            'w' => ['ŵ','ẁ','ẃ','ẅ','ẇ','ẉ','ẘ','ᶌ'],
            'y' => ['Ý','ý','ÿ','ŷ','ƴ','ȳ','ẏ','ỳ','ỵ','ỷ','ỹ','ᶌ'],
            'z' => ['ż','ž','ź','ƶ','ẑ','ẓ','ẕ','ᶎ'],
        ];

        $converted = $string;
        foreach( $replacements as $key => $chars ) {
            $converted = str_replace( $chars, $key, $converted );
        }

        return $converted;
    }


    /**
     * valida um valor para o formato de data e hora (DateTime).
     * 
     * Tenta criar um objeto `DateTime` a partir da string ($value) 
     * e do formato fornecido por $format
     * Retorna o objeto `DateTime` se a string corresponder exatamente ao formato,
     * caso contrario, retorna `null`
     */
    public static function date( mixed $value, string $format = 'Y-m-d H:i:s' ): ?DateTime {
        if( ! is_string($value) || $value === '' ) {
            return null;
        }

        $date = DateTime::createFromFormat( $format, $value );

        return ( $date && $date->format($format) === $value ) ? $date : null;
    }


    /**
     * Higieniza todos os valores de um array,
     * 
     * Fas higienizacao de array aplicando Ensure::string em chaves e valores do tipo string
     */
    public static function array( mixed $array ): array {
        if( ! is_array($array) || $array === [] ) {
            return [];
        }

        $clean = [];

        foreach( $array as $key => $value ) {
            # Chave: Se for string higienizar com Ensure::string
            $key = is_string($key) ? self::string($key) : $key;

            # Valor: respeita o tipo
            if( is_string($value) ) {
                $clean[$key] = self::string($value);
            }
            elseif( is_int($value) || is_float($value) || is_bool($value) ) {
                $clean[$key] = $value;
            }
            elseif( is_array($value) ) {
                $clean[$key] = self::array($value);
            }
            elseif( $value === null ) {
                $clean[$key] = null;
            }
            # ignora objetos, resources, callable etc
        }

        return $clean;
    }

    
    /**
     * Higieniza JSON: decodifica e valida uma string JSON.
     *
     * Tenta decodificar a string JSON. Se houver qualquer erro na decodificacao,
     * retorna `null`. Caso contrario, retorna o objeto ou array decodificado.
     *
     * @param $value A string JSON a ser higienizada e decodificada.
     * @param $assoc Se `true`, retorna arrays associativos; caso contrario, retorna objetos
     */
    public static function json( mixed $value, bool $assoc = true ): array|object|null {
        if( ! is_array($value) && ! is_object($value) ) {
            return null;
        }
        
        try {
            return json_decode( $value, $assoc, flags: JSON_THROW_ON_ERROR );
        }
        catch( JsonException ) {
            return null;
        }
    }



    public static function json_encode( mixed $value ): string {
        if( is_string($value) ) {
            # Para saber se JSON eh valido precisa de json_last_error()
            # E esse erro sho eh atualizado chamando json_decode()
            json_decode( $value );
            
            return json_last_error() === JSON_ERROR_NONE ? $value : '{}';
        }

        if( is_array($value) || is_object($value) ) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '{}';
        }

        return '{}';
    }


    public static function json_decode( mixed $value ): stdClass {
        if( is_string($value) ) {
            $data = json_decode($value);
        }
        elseif( is_array($value) || is_object($value) ) {
            $data = json_decode(json_encode($value));
        }
        else {
            return new stdClass();
        }

        return $data instanceof stdClass ? $data : new stdClass();
    }


    /**
     * Higieniza JSON: codifica dados PHP em string JSON formatada
     *
     * Converte valores PHP (array, objeto) em string JSON com indentacao
     * para melhor legibilidade. Se houver qualquer erro na codificacao,
     * retorna `null`. Caso contrario, retorna a string JSON formatada.
     *
     * @param $value O valor PHP a ser codificado (array, objeto, etc)
     */
    public static function json_pretty( mixed $value ): ?string {
        if( ! is_array($value) && ! is_object($value) ) {
            return null;
        }

        try {
            return json_encode(
                $value, 
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        }
        catch( JsonException ) {
            return null;
        }
    }


    # Valida host (dominio) de origem a partir da URL
    public static function sameHost( mixed $url ): bool { 
        if( ! is_string($value) || $value === '' ) {
            return false;
        }
        
        $current_host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''; 
        $current_host = strtolower( preg_replace('/:\d+$/', '', $current_host) ); 
        $input_host   = parse_url( $url, PHP_URL_HOST ); 
        $input_host   = strtolower( $input_host ?? '' ); 

        return $input_host === $current_host; 
    }


    # Substitui multiplos espaços, quebras de linha e tabulacoes (tabs) consecutivos em um unico espaco
    # e parametro com flag Ensure::REMOVE_EDGE_WHITESPACE opcional para adicionar trim()
    public static function squeeze( string $string, int $flags = 0 ): string {
        $string = preg_replace( '/\s+/', ' ', $string );

        if( $flags & self::REMOVE_EDGE_WHITESPACE ) {
            $string = trim($string);
        }

        return $string;
    }


    public static function removeScheme( string $string ): string {
        return preg_replace( '~https?:\/\/~i', '', $string );
    }


    # remove nome de hosts (dominios) da entrada
    public static function removeHosts( string $string ): string {
        $parts = preg_split( '/\s+/', $string );

        foreach( $parts as &$part ) {

            # remove pontuacao das bordas para validacao - ex: .. dominio.ext, ...
            $clean = trim( $part, " \t\n\r\0\x0B.,!?;:" );

            $host = filter_var( $clean, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME );

            # regra minima: host precisa ter ponto
            if( str_contains($clean, '.') && $host ) {

                # remove soh o host, preservando o restante
                $part = str_replace( $clean, '', $part );
            }
        }
        $normalize = self::squeeze( implode(' ', $parts) );

        return trim($normalize);
    }

    public static function removeURL( string $string ): string {
        return self::removeScheme($string) . self::removeHosts();
    }


    public static function writeLock( 
        mixed $filepath, mixed $write = null, $flags = self::FILE_PUT_LOCK ): bool {

        if( ! is_string($filepath) || $filepath === '' ) {
            return false;
        }

        if( $flags & self::USE_REAL_FILEPATH ) {
            if( ! self::isFilePath($filepath) ) {
                exception("Caminho inválido ou tentativa de path traversal!", 'Ensure::writeLock');
                return false;
            }
        }
        else {
            $dirs = dirname( $filepath );
            if( realpath($dirs) === false ) {
                return false;
            }
        }

        $content = '';
        if( $write === null ) {
            $content = file_get_contents( $filepath );
        }
        else {
            $content = $write;
        }

        $filename = '<code>' . basename($filepath) . '</code>';

        if( $flags & self::FILE_PUT_LOCK ) {
            if( file_put_contents($filepath, $content, LOCK_EX ) === false ) {
                exception("Não foi possível obter a trava para gravar no arquivo {$filename} com <code>file_put_contents</code>", 'Ensure::writeLock');
                return false;
            }
            return true;
        }
        if( $flags & self::FILE_HANDLING_LOCK ) {
            $file = fopen( $filepath, 'c+' );
            if( ! $file ) {
                exception("Não foi possível abrir o arquivo {$filename}", 'Ensure::writeLock');
                return false;
            }
            try {
                if( flock($file, LOCK_EX) === false ) {
                    exception("Não foi possível obter a trava para gravar no arquivo {$filename} com <code>flock</code>", 'Ensure::writeLock');
                    return false;
                }
                fseek( $file, 0 );
                # Escreve e pega bytes escritos
                $bytesWritten = fwrite( $file, $content );
                if( ! fflush($file) ) {
                    exception("O conteúdo foi escrito, mas o sistema não confirmou se o flush foi realmente descarregado no arquivo {$filename}", 'Ensure::writeLock');
                    # Continua mesmo assim, nao eh critico
                }
                if( $bytesWritten === false ) {
                    exception("Falha na escrita do arquivo {$filename}", 'Ensure::writeLock');
                    return false;
                }
                $length = strlen( $content );
                # Verificacao dupla
                if( $bytesWritten !== $length ) {
                    exception("O arquivo $filename foi escrito parcialmente: $bytesWritten bytes / $length caracteres", 'Ensure::writeLock');
                    return false;
                }
                # Corta no tamanho REAL escrito
                ftruncate( $file, $bytesWritten ); 
                
                return true;
            } 
            finally {
                # garante que sempre libera trava e fecha arquivo
                if( is_resource($file) ) {
                    flock( $file, LOCK_UN );
                    fclose( $file );
                }
            }
        }
        
        exception("Flag de escrita inválido: <code>{$flags}</code>", 'Ensure::writeLock');
        return false;
    }


    public static function isFilePath( mixed $filepath ): bool {
        if( ! is_string($filepath) || $filepath === '' ) {
            return false;
        }
        $realDIR = self::realpath( dirname($filepath) );

        if( $realDIR === false ) {
            return false;
        }
        # garante que o diretorio eh permitido
        if( strpos($realDIR, REAL_DIR) !== 0 ) {
            return false;
        }
        # se o arquivo existe, valida ele tambem
        if( ! file_exists($filepath) ) {
            return false;
        }
        
        $realFile = self::realpath($filepath);

        return $realFile !== false && is_file($realFile);
    }


    # normalize directory separator para padrao unix quando necessario usar realpath()
    public static function realpath( mixed $path ): string|false {
        static $cache = [];

        if( ! is_string($path) ) {
            exception('O argumento passado não é uma string.', 'Ensure::realpath');
            return false;
        }
        if( $path === '' ) {
            exception('O argumento passado é uma string vazia.', 'Ensure::realpath');
            return false;
        }

        if( isset($cache[$path]) ) {
            return $cache[$path];
        }

        $realpath = @realpath($path);

        if( $realpath === false ) {
            $cache[$path] = false;
            return false;
        }

        $realpath = str_replace('\\', '/', $realpath);

        $cache[$path] = $realpath;

        return $realpath;
    }

}