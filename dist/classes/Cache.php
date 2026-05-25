<?php
class Cache {

#################################################################
         ### sessao de cache armazenado em arquivo ###


    # gera o caminho do arquivo de cache com base em cache_file
    private static function filepath( string $cache_pathfile ): string {

        return STORAGE_DIR . 'cache/' . $cache_pathfile;
    }


    /**
     * armazena em arquivo:  
     *      se nao existir writeLock cria novo arquivo
     *      se existir writeLock reescreve o arquivo
     */
    public static function setFile( string $cache_file, string $content ): void {
        $filepath = self::filepath( $cache_file );

        $dir = dirname( $filepath );
        if( ! is_dir($dir) ) {
            mkdir( $dir, 0755, true );
        }

        Ensure::writeLock( $filepath, $content );
    }


    # Le o cache, retorna null se nao existir
    public static function getFile( string $cache_file ): ?string {
        $filepath = self::filepath( $cache_file );
        if( ! file_exists($filepath) ) {
            return null;
        }

        return file_get_contents( $filepath );
    }


    # Deleta o arquivo em cache
    public static function deleteFile( string $cache_file ): string {
        $filepath = self::filepath( $cache_file );
        if( file_exists($filepath) ) {
            if( unlink($filepath) ) {
                return true;
            }
        }

        return self::filepath( $cache_file );
    }














###########################################################
    ### sessao de cache armazenado em variavel estatica ###


    # static cache so serve pra ser usado se houver muitas requisicoes por chamada


    /**
     * @var $store: armazena em memoria durante a requisicao
     * padrao para file_exists/get_contents/json_decode etc
     */
    private static array $store = [];


    /**
     * Verifica se existe valor em memoria no keyspace especificado.
     * Retorna true mesmo se o valor for false/null (usa array_key_exists).
     */
    public static function hasMemory( string $keyspace, string $key ): bool {
        $has_keyspace = array_key_exists( $keyspace, self::$store );

        if( ! $has_keyspace ) {
            return false;
        }

        $has_uniquekey = array_key_exists( $key, self::$store[$keyspace] );

        return $has_uniquekey;
    }

    /**
     * Ler valor em memoria no keyspace
     * Retorna null se nao existir (use hasMemory para diferenciar)
     */
    public static function getMemory( string $keyspace, string $key ): mixed {
        if( ! array_key_exists($keyspace, self::$store) ) {
            return null;
        }
        if( ! array_key_exists($key, self::$store[$keyspace]) ) {
            return null;
        }

        return self::$store[$keyspace][$key];
    }

    /**
     * Define valor em memoria no keyspace.
     */
    public static function setMemory( 
        string $keyspace, string $key, mixed $value ): void {

        if( ! array_key_exists($keyspace, self::$store) ) {
            self::$store[$keyspace] = [];
        }
        self::$store[$keyspace][$key] = $value;
    }


    /**
     * Obtem um valor da memoria estatica ou, se nao existir executa o $cached
     * 
     * @example : 
     * $features = TEMPLATE_PATH . 'features.php';
     * $has_features = Cache::memory( 'file_exists', $features, fn() => file_exists($features) );
     * if( $cache_features ) {
     *     require $features;
     * }
     */
    public static function memory( string $keyspace, string $key, callable $cached ): mixed {

        if( ! array_key_exists($keyspace, self::$store) ) {
            self::$store[$keyspace] = [];
        }

        if( array_key_exists($key, self::$store[$keyspace]) ) {
            return self::$store[$keyspace][$key];
        }

        $value = $cached();

        self::$store[$keyspace][$key] = $value;

        return $value;
    }

    /**
     * Le o valor de um cache em estatico memoria
     * @example : 
     * echo = Cache::read( 'key-space', 'section-key' );
     */
    public static function read(string $keyspace, string $key): mixed {
        return self::$store[$keyspace][$key] ?? null;
    }

    /**
     * @example
     * $data = OpusCache::json('config.json');
     * if( $data === null ) {
     *     // tratar
     * }
     */
    public static function json( string $file, bool $assoc = true ): mixed {
        if( ! is_file($file) ) {
            return null;
        }

        $key = $file . '|' . filemtime($file) . '|' . filesize($file) . '|' . ($assoc ? '1' : '0');

        if( self::hasMemory('json', $key) ) {
            return self::getMemory( 'json', $key );
        }

        $data = json_decode( file_get_contents($file), $assoc );
        self::setMemory( 'json', $key, $data );

        return $data;
    }

}