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
    private static ?array $slug = null;


    public static function root(string $extend = ''): string {

        static $root = null;

        if( $root === null ) {

            $scheme = IS_LOCAL ? 'http://' : 'https://';
            $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

            $document_root = Ensure::realpath($_SERVER['DOCUMENT_ROOT']);

            $pathname = str_replace($document_root, '', REAL_DIR);

            $root = $scheme . $host . $pathname;
        }

        return $root . ltrim($extend, '/');
    }



    /** Determina o protocolo HTTP/HTTPS com verificacao em etapas
     * 
     * Em producao o sistema forca o uso de https, 
     *  http so funciona em ambiente de desenvolvimento
     * scheme() tem menor custo que isso: IS_LOCAL ? 'http://' : 'https://'
     * um custo quase irrelevante, mas ainda o mantemos
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
     * Retorna a URL completa da requisicao atual incluindo protocolo.
     */
    public static function current( bool $appendQuery = true ): string {
        $parse_url = parse_url( self::root(), PHP_URL_HOST );

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? $parse_url;

        # Garante que o host eh o mesmo da aplicacao (site)
        # O mesmo que `stripos($host, $parse_url) === false` so que mais safe
        if( strcasecmp($host, $parse_url) !== 0 ) {
            $host = $parse_url;
        }
        # $host = dominio.ext, localhost, 127.0.0.1 etc

        # Caminho da requisicao (sem query string)
        $request = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

        $query = $_SERVER['QUERY_STRING'] ?? '';

        # montagem da URL final (sem query string)
        $current = self::scheme() . $host . $request;

        if( $appendQuery && $query !== '' ) {

            $current .= '?' . $query;
        }

        return Ensure::URL($current);
    }




# query str > $_GET ⌵ ----------------------------------------------------------------------

    # verifica se existe query string na URL
    public static function hasQuery() {

        return isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '';
    }

    /**
     * Verifica se um parametro existe na superglobal $_GET.
     * Returna `true` se o parametro existir, `false` caso contrario.
     * Equivalente a: if( isset($_GET['key']) )
     */
    public static function has( string $key ): bool {
        return isset($_GET[$key]);
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
        $query = (int) ($_GET[$key] ?? 0);
        
        return Ensure::int($query);
    }

    public static function is( string $key, string $value ): bool {
        return isset($_GET[$key]) && trim($_GET[$key]) === $value;
    }

    public static function empty( string $key ): bool {
        return empty( trim($_GET[$key]) );
    }

# query str > $_GET … ----------------------------------------------------------------------




    /**
     * Retorna um parametro de URL amigavel pelo seu indice $param
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


    /**
     * Extrai o pathname da URL
     * 
     * @todo 
     * No painel de controle esse metodo retorna soh o segment, nao o pathname,
     * pois o metodo extract() remove da URL slugs que vem de diretorio
     * sendo assim esse metodo deixa `dashboard/` de fora
     * 
     * Isso nao eh um problema para o funcionamento, 
     * soh que o nome (pathname) do metodo nao diz a verdade quando no painel
     */
    public static function pathname(): string {
        if( self::$slug === null ) {
            self::extract();
        }

        return implode( '/', self::$slug );
    }

    public static function segment(): string {
        if( self::$slug === null ) {
            self::extract();
        }
        
        $params = self::$slug;

        $bases = [ category_base(), articles_base(), user_base() ];

        if( isset($params[0]) && in_array($params[0], $bases) ) {
            array_shift( $params );
        }

        return implode( '/', $params );
    }



    public static function normalize(): void {
        
        $current = self::scheme() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $parts = parse_url($current);

        if( $parts === false ) {
            return;
        }

        $path = $parts['path'] ?? '/';

        # Remove barras duplicadas do caminho
        $path = preg_replace( '#/+#', '/', '/' . ltrim($path, '/') );


        $is_physical = URL::param(0) === '';
        $has_query   = isset($parts['query']);
        # Remove barra final e adiciona uma unica quando :
        # - o path for apenas um parametro correspondente a diretorio fisico 
        # - o path antecede uma query string 
        if( $is_physical || $has_query ) {
            # garante que a URL sempre termine exatamente com uma unica barra final independentemente de originalmente ter uma, varias ou nenhuma
            $path = rtrim($path, '/') . '/';
        }
        # Remove barra final, Exceto na raiz
        else if( $path !== '/' ) {

            $path = rtrim($path, '/');
        }

        $normalized = '';

        if( isset($parts['scheme']) ) {
            $normalized .= $parts['scheme'] . '://';
        }

        if( isset($parts['host']) ) {
            $normalized .= rtrim( $parts['host'], '/' );
        }

        if( isset($parts['port']) ) {
            $normalized .= ':' . $parts['port'];
        }

        $normalized .= $path;

        if( isset($parts['query']) ) {
            $normalized .= '?' . $parts['query'];
        }

        if( isset($parts['fragment']) ) {
            $normalized .= '#' . $parts['fragment'];
        }


        if( $normalized !== $current ) {

            header( 'Location: ' . $normalized, true, 301 );
            exit;
        }
    }


    /**
     * Processa a URI da requisicao para extrair e armazenar os parametros de URL amigaveis
     *
     * Remove o caminho do script, a query string e divide o restante da URI em partes,
     * filtrando elementos vazios para gerar uma lista limpa de slugs.
     */
    private static function extract(): void {
        # O -9 eh para remover 'index.php' do final
        $_index = substr( $_SERVER["SCRIPT_FILENAME"], strlen($_SERVER["DOCUMENT_ROOT"]), -9 );

        $_index = str_replace( '\\', '/', $_index );

        $request = substr( $_SERVER["REQUEST_URI"], strlen( $_index ) );

        $query_pos = explode( "?", $request );
        $request = $query_pos[0];

        $param = explode( '/', $request );

        $process = [];
        for( $i = 0; $i < count($param); $i++ ) {
            if( isset($param[$i]) && $param[$i] !== '' ) {
                array_push( $process, $param[$i] );
            }
        }

        self::$slug = $process;
    }

}