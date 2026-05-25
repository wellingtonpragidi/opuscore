<?php
declare( strict_types = 1 );
/**
 * Classe URL: Auxilia na manipulacao, analise e roteamento de URLs.
 *
 * Gerencia a obtencao da URL raiz e atual do sistema, detecta o protocolo (HTTP/HTTPS),
 * e e fundamental para a interpretacao de URLs amigaveis, extraindo e padronizando
 * os parametros da requisicao em formato de slug.
 *
 * Requer configuracoes apropriadas no servidor web (ex: .htaccess para Apache)
 * para o correto funcionamento de URLs amigaveis.
 *
 * @since 1.4.0
 * @global $_SERVER Superglobal para informacoes do servidor e requisicao.
 * @global DIR Constante global para o diretorio raiz do projeto.
 * 
 * @link https://opuscore.dev/classes/url
 * 
 * @package Core/HTTP
 */
class URL {

    # Armazena os parametros da URL em formato de slug apos processamento.
    private static array|null $slug = null;


    public static function root( string $extend = '' ): string {
        $scheme = IS_LOCAL ? 'http://' : 'https://';
        $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        $root = Ensure::realpath( $_SERVER['DOCUMENT_ROOT'] );

        $pathname = str_replace( $root, '', REAL_DIR );

        # remove separador final se alguem passar uma string com barra extra no final
        $pathname = rtrim( $pathname, '/' );

        $pathname = $pathname . '/' . ( $extend ? trim($extend, '/') : '' );

        return $scheme . $host . $pathname;
    }


    /**
     * Retorna a URL completa da requisicao atual incluindo protocolo.
     */
    public static function current( bool $query_string = true, $fragment = '' ): string {
        $root = parse_url( self::root(), PHP_URL_HOST );

        $host = $_SERVER['HTTP_HOST'] ?? $root;

        # Garante que o host eh o mesmo da aplicacao
        # O mesmo que `stripos($host, $root) === false` so que mais safe
        if( strcasecmp($host, $root) !== 0 ) {
            $host = $root;
        }

        $request = strtok( $_SERVER['REQUEST_URI'], '?' );
        
        # Recria query string somente com GETs validos
        $value = http_build_query( array_filter(self::GetAll()) );

        $query = '';

        if( $query_string ) {

            $query = $value ? "?{$value}" : '';
        }

        $current = self::scheme() . $host . $request . $query . $fragment;

        return Ensure::URL( $current );
    }


    /**
     * Verifica se um parametro existe na superglobal $_GET.
     * Returna `true` se o parametro existir, `false` caso contrario.
     * Equivalente a: if( isset($_GET['key']) )
     */
    public static function has( string $key ): bool {
        return isset($_GET[$key]);
    }

    public static function empty( string $key ): bool {
        return empty( trim($_GET[$key]) );
    }

    /**
     * Recupera o valor de um parametro especifico da superglobal $_GET.
     *
     * @param $key Nome do parametro a ser recuperado.
     * @return valor do parametro, ou uma string vazia se nao existir.
     */
    public static function GET( string $key ): string {
        return trim($_GET[$key] ?? '');
    }

    /**
     * Recupera o valor de um parametro especifico da superglobal $_GET, forcando-o para inteiro
     * ou 0 se nao existir ou nao for numerico.
     */
    public static function int( string $key ): int {
        $queryInt = (int) ($_GET[$key] ?? 0);
        return Ensure::int($queryInt);
    }


    public static function escAttr( string $key ): string {
        return Ensure::attr( $_GET[$key] ?? '' );
    }

    /** 
      * @see https://int.opuscore.dev/methods/url_getall
      */
    public static function GetAll(): array {
        $GetParams = filter_input_array( INPUT_GET, FILTER_DEFAULT ) ?? [];
        $GetArray = [
            'id'         => (int)    ( $GetParams['id']         ?? 0  ),
            'q'          => (string) ( $GetParams['q']          ?? '' ),
            'pg'         => (int)    ( $GetParams['pg']         ?? 0  ),
            'name'       => (string) ( $GetParams['name']       ?? '' ),
            'act'        => (string) ( $GetParams['act']        ?? '' ),
            'action'     => (string) ( $GetParams['action']     ?? '' ),
            'activation' => (string) ( $GetParams['activation'] ?? '' ),
            'key'        => (string) ( $GetParams['key']        ?? '' ),
            'insert'     => (string) ( $GetParams['insert']     ?? '' ),
            'update'     => (string) ( $GetParams['update']     ?? '' ),
            'section'    => (string) ( $GetParams['section']    ?? '' ),
            'lang'       => (string) ( $GetParams['lang']       ?? '' )
        ];

        return Ensure::array( $GetArray );
    }


    /**
     * Determina o protocolo HTTP/HTTPS baseado na variavel de ambiente HTTPS
     * 
     * Logica:
     * 1. Verifica se a variavel $_SERVER['HTTPS'] existe (isset)
     * 2. Verifica se a variavel $_SERVER['HTTPS'] nao esta vazia (! empty)
     * 3. Verifica se a variavel $_SERVER['HTTPS'] he for diferente de 'off'
     * 
     * Se existir E nao estiver vazia E for diferente de 'off'
     * Considera como HTTPS, caso contrario HTTP
     * 
     * A verificacao em etapas previne erros quando a variavel nao esta definida
     *  ou tem valores inesperados. 'nao alterar ordem ou logica'
     */
    public static function scheme(): string {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? null;
        if( $scheme !== null ) {
            return $scheme . '://';
        }

        $https = $_SERVER['HTTPS'] ?? null;
        if( $https !== null && ! empty($https) && $https !== 'off' ) {
            return 'https://';
        }
        
        return 'http://';
    }
    /** 
     * @deprecated Use self::scheme()
     */ 
    public static function protocol(): string { 
        return self::scheme(); 
    }

    /**
     * Retorna um parametro de URL amigavel pelo seu indice $index
     *
     * $index baseado no parametro que inicia em zero 
     * (exemplo: 0 para o primeiro, 1 para o segundo).
     */
    public static function param( int $index ): string {
        if( self::$slug === null ) {
            self::extract();
        }

        return self::$slug[$index] ?? '';
    }


    /**
     * Retorna a quantidade de parametros (numero) de parametros encontrados na URL.
     */
    public static function paramCount(): int {
        if( self::$slug === null ) {
            self::extract();
        }

        return count( self::$slug );
    }


    /**
     * Retorna todos os parametros de URL amigaveis (slugs) como um array.
     */
    public static function params(): array {
        if( self::$slug === null ) {
            self::extract();
        }

        return self::$slug;
    }


    # Extrai o pathname da URL
    public static function pathname(): string {
        return implode( '/', self::params() );
    }


    public static function segment(): string {
        $params = self::params();

        $bases = [ category_base(), posts_base(), user_base() ];

        if( isset($params[0]) && in_array($params[0], $bases) ) {
            array_shift( $params );
        }

        return implode( '/', $params );
    }



    /**
     * Processa a URI da requisicao para extrair e armazenar os parametros de URL amigaveis.
     *
     * Remove o caminho do script, a query string e divide o restante da URI em partes,
     * filtrando elementos vazios para gerar uma lista limpa de slugs.
     * 
     * @link int.
     */
    private static function extract(): void {
        # O -9 eh para remover 'index.php' do final
        $_index = substr( $_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"]), -9 );

        $_index = str_replace( '\\', '/', $_index );

        $request = substr( $_SERVER["REQUEST_URI"], strlen( $_index ) );

        $query_pos = explode( "?", $request );
        $request = $query_pos[0];

        $param = explode( "/", $request );

        $process = [];
        for( $i = 0; $i < count($param); $i++ ) {
            if( isset($param[$i]) && $param[$i] != "" ) {
                array_push( $process, $param[$i] );
            }
        }

        self::$slug = $process;
    }


    public static function normalize(): void {
        $current = self::current();
        
        $query_pos = strpos( $current, '?' );
        
        $normalized = $current;
        $query_str  = '';

        if( $query_pos !== false ) {
            $query_str = substr( $current, $query_pos, strlen($current) - $query_pos );
            $normalized = substr( $current, 0, $query_pos );
        }
      
        # Remove barras duplicadas
        $normalized = preg_replace( '#(?<!:)//+#', '/', $normalized );
        
        # Verifica se eh diretorio fisico
        $is_physic_dir = false;
        $parse_url = parse_url( $normalized, PHP_URL_PATH );
        if( $parse_url ) {
            $physic_dir = $_SERVER['DOCUMENT_ROOT'] . strtok( $parse_url, '?#' );
            $is_physic_dir = is_dir($physic_dir);
        }
        
        # Remove barra final apenas se:
        # 1. nao for um diretorio fisico
        # 2. nao for a raiz do site
        if( $parse_url !== '/' && substr($normalized, -1) === '/' && ! $is_physic_dir ) {
            $normalized = rtrim( $normalized, '/' );
        }
                
        # Adiciona query string se existir # Adiciona fragmento se existir
        if( $query_str !== '' ) {
            if( substr($normalized, -1) !== '/' ) {
                $normalized .= '/';
            }
            $normalized .= $query_str;
        }

        if( $current !== $normalized ) {
            header( "Location: {$normalized}", true, 301 );
            exit;
        }
    }

}