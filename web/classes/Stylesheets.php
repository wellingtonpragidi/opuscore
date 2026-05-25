<?php
declare( strict_types = 1 );
/**
 * Classe Responsável pelo carregamento e compressao das folhas de estilo do template.
 * Fornece dois metodos de carregamento por bloco em <style> e linkado via <link>.
 * Inclui: 
 * * suporte a ganchos (hooks)
 * * deteccao condicional de paginas no modo de bloco 
 * * compactacao automatica
 * * geracao de arquivos minificados com divisao em partes sem excederem 100kb no modo linked
 * 
 * block() 
 * Gera codigo minificado das folhas de estilo 
 * Inclui: 
 * * arquivo de fontes
 * * arquivo do editor punk se definido
 * * condicionais por tipo de página (is_*.css) 
 * * arquivos CSS padrao inclusos no diretorio `assets/css/block/`
 * 
 * linked()
 * Le os arquivos CSS da pasta `assets/css/linked/`
 * comprime e agrupa por tamanho ate 100kb
 * salvando como `titulo-do-site.min.css`, `titulo-do-site-part-2.min.css`, etc.
 * 
 * Para garantir o carregamento adequado das fontes, 
 * eh incluido o arquivo CSS com as declaracoes @font-face antes de qualquer outro
 * /assets/fonts/fonts.css se existir 
 * Nao existindo ira incluir nem /assets/css/block/fonts.css na ordem que DirectoryIterator coloca-lo
 * A mesma coisa eh para linked, sera comprimido na folha de estilo sem controle sobre a ordem
 * 
 *
 * Hooks integrados:
 * - Filtro: block_dir_stylesheets
 * - Filtro: run_stylesheets
 * - Acao: stylesheets_override
 *
 * @uses compress_CSS()
 * @uses Hook ::call_filter() ::call_action()
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * @see https://opuscore.dev/classes/stylesheets
 * 
 * @package Output\Assets
 */
class Stylesheets {

    private static array $cache_file = []; # cache para arquivos `.css`

    private static array $cache_printed = []; # impedir de imprimir arquivo + de 1 vez

    private const string ASSETS_DIR = TEMPLATE_PATH . 'assets/';

    public const string BLOCK_DIR = self::ASSETS_DIR . 'css/block/';
   
    public const string LINKED_DIR = self::ASSETS_DIR . 'css/linked/';


    public static function block(): void {
        if( is_dir(self::block_dir()) ) {
            echo "<style>\n";

                self::request_block();

            echo "\n</style>\n";
        }
    }

    /**
     * removido media="print" onload="this.media=\'all\'" de <link>
     * */
    public static function linked(): void {
        if( ! is_dir(self::LINKED_DIR) ) {
            return;
        }
        $files = [];
        foreach( new DirectoryIterator(self::LINKED_DIR) as $file ) {
            if (
                $file->isFile() &&
                $file->getExtension() === 'css' &&
                ! str_contains($file->getFilename(), '.min')
            ) {
                $files[] = $file->getPathname();
            }
        }

        # funcoes de url como template_url limpam barra final
        # portanto mesmo que essa url: template_url( 'assets/css/linked/' ) tenha barra no final
        # linked_url precisa concatenar barra (/) com $filename: $linked_url . '/' . $filename;
        $linked_url = template_url('assets/css/linked');
        $buffer     = '';
        $part       = 1;
        $links      = [];
        # define nome base do arquivo "'linked filename'"
        $basename   = Ensure::slug( site_title() ); 

        foreach( $files as $path ) {
            $content = file_get_contents( $path );
            $compressed = compress_CSS( $content );

            # 102400 bytes = 100 KB
            if( strlen($buffer . $compressed) > 102400 ) { 
                $basename = ($part === 1) ? $basename : "{$basename}-part-{$part}";
                $filename = $basename . '.min.css';

                Ensure::writeLock( 
                    self::LINKED_DIR . $filename, 
                    $buffer, Ensure::FILE_HANDLING_LOCK 
                );

                $links[] = $linked_url . '/' . $filename;

                # Limpa o buffer
                $buffer = '';
                
                if( $part === 1 ) {
                    $part = 2; # comeca particoes a partir do 2
                }
                else {
                    $part++;
                }
            }

            $buffer .= $compressed;
        }

        if( strlen($buffer) > 0 ) {
            $basename = ($part === 1 && empty($links)) ? $basename : "{$basename}-part-{$part}";
            $filename = $basename . '.min.css';

            Ensure::writeLock( 
                self::LINKED_DIR . $filename, 
                $buffer, Ensure::FILE_HANDLING_LOCK 
            );

            $links[] = $linked_url . '/' . $filename;
        }

        foreach( $links as $href ) {
            echo '<link rel="stylesheet" href="' . $href . '" />' . PHP_EOL;
        }
    }

    /**
     * Insere bloco <style> no fim do HTML, 
     *   antes do fechamento do </body> e antes de <script>s
     */
    public static function late_block(): void {
        if( Hook::has_filter('late_load_style') === false ) {
            return;
        }
        
        ob_start('compress_CSS');

        foreach( new DirectoryIterator(self::block_dir() . 'late/') as $file ) {
            if( $file->isFile() && $file->getExtension() === 'css' ) {
                $filepath = str_replace( "\\", "/", $file->getRealPath() );

                self::require_cached_file( $filepath );
            }
        }

        ob_end_flush();
    }


    private static function request_block(): void {
        ob_start('compress_CSS');

        Hook::call_action('prepend_styles');

        # inclusao de folhas de estilos condicionais
        self::require_conditional_files();

        foreach( new DirectoryIterator(self::block_dir()) as $file ) {
            if( $file->isFile() && $file->getExtension() === 'css' ) {

                $filepath = str_replace( "\\", "/", $file->getRealPath() );

                /**
                 * Arquivos iniciados com is- sao incluidos opcionalmente e condicionalmente
                 * essa condicao eh para nao inclui-los novamente no loop de iteracao 
                 * no diretorio block_dir() 
                 */
                if( str_starts_with($file->getFilename(), 'is-') ) {
                    continue;
                }

                self::require_cached_file( $filepath );
            }
        }

        # inclue arquivo css editor punk se definido
        if( editor_is('punk') ) {
            self::require_cached_file( WEB_DIR .'assets/css/punk.css' );
        }

        ob_end_flush();
    }


    private static function block_dir(): string {
        $filter_dir = Hook::call_filter( 'block_styles_dir', self::BLOCK_DIR );

        return $filter_dir ?: self::BLOCK_DIR;
    }


    private static function require_conditional_files(): void {

        $router = Container::call('Router');

        # antes havia duas opcoes pra colocar css de @font-face antes dos outros codigos de estilo, 
        # eram dois: '/fonts/fonts.css' e mais 'fonts.css' dentro do diretorio block_dir()
        # block_dir() . 'fonts.css' foi removido da opcao para diminuir file_exists()
        # nada impede de ter fonts.css em block_dir(), mas nao sera incluido antes de outros estilos
        if( file_exists(self::ASSETS_DIR . 'fonts/fonts.css') ) {
            require self::ASSETS_DIR . 'fonts/fonts.css';
        }

        $stylesheets = [
            'is-404'        => is_404(),
            'is-categories' => is_categories(),
            'is-listing'    => is_listing(),
            'is-posts'      => is_listing(),
            'is-home'       => is_home(),
            'is-page'       => is_page(),
            'is-post'       => is_post(),
            'is-user'       => is_user(),
        ];

        foreach( $stylesheets as $stylesheet => $condition ) {
            if( $condition && file_exists(self::block_dir() . $stylesheet . '.css') ) {

                self::require_cached_file( self::block_dir() . $stylesheet . '.css' );
            }
        }
    }

/**
 * isset(self::$cache_printed[$path])
 * 
 * fluxo real (passo a passo)
->> Primeira vez que entra:
$cache_printed[$path] não existe ainda
isset(...) → false
NÃO entra no return
continua execução
faz o echo
e depois marca:
self::$cache_printed[$path] = true;
->> Segunda vez que entra:
agora $cache_printed[$path] existe
isset(...) → true
cai no return
não imprime de novo

⚠️ detalhe importante
Isso só funciona bem porque você usa o $path como chave.
Se mudar o path (mesmo arquivo com caminho diferente), ele considera outro.
 */
    private static function require_cached_file( string $path ): void {
        if( isset(self::$cache_printed[$path]) ) {
            return;
        }

        if( ! isset(self::$cache_file[$path]) ) {
            ob_start();
            require $path;
            self::$cache_file[$path] = ob_get_clean();
        }

        echo self::$cache_file[$path];

        self::$cache_printed[$path] = true;
    }

}