<?php 
declare( strict_types = 1 );
/**
 * Gera e atualiza sitemap.xml se detectado qualquer alteracao no site
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System\Output\SEO
 * @subpackage \Generator\Updater
 */
class SitemapXML {

    private PDO $conn;

    private DateTimeZone $tzSystem, $tzUTC;

    private static ?string $siteurl = null;


    public function __construct() {
        $container = Container::instance();

        $this->conn = $container->make('Connection');

        $this->tzSystem = $container->make('TimeZone.System');
        $this->tzUTC    = $container->make('TimeZone.UTC');

        if( self::$siteurl === null ) {
            self::$siteurl = URL::root();
        }
    }

    public function rewrite(): void {
        $filepath = DIR . 'sitemap.xml';

        $xml = $this->render();

        # Verifica se precisa atualizar
        $needupdated = true;

        if( file_exists($filepath) ) {
            $current = file_get_contents( $filepath );

            if( trim($current) === trim($xml) ) {
                $needupdated = false;
            }
        }

        if( $needupdated ) {
            Ensure::writeLock( $filepath, $xml, Ensure::FILE_HANDLING_LOCK );
        }
    }


    private function render() {
        $xsl_url = URL::root('web/annexes/sitemap.xsl');

        $xml_head = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <?xml-stylesheet type="text/xsl" href="$xsl_url"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        XML;

        $xml_body = [
            $this->home(),
            $this->categories(),
            $this->articles(),
            $this->pages(),
            $this->users()
        ];

        $xml_body = implode( '', $xml_body );
        
        $xml_foot = PHP_EOL . '</urlset>';

        return $xml_head . $xml_body . $xml_foot;
    }


    private function home(): string {
        return $this->build_URL( '', SEO('homepage_lastmod') );
    }

    private function categories(): string {
        $cmd = $this->conn->query("
            SELECT segment, created FROM categories ORDER BY ID DESC
        ");
        $row = $cmd->fetch( PDO::FETCH_ASSOC );
        $xml = '';
        if( $row ) {
        	# a pagina da listagem de categorias ex: 'https://domain.ext/categorias'
            # fora do loop
        	$xml .= $this->build_URL( 
                category_base(), 
                $row['created'] . ' 00:00:00' 
            );

        	# todas as categorias 
            # as mesmas que listam articles ex: 'https://domain.ext/categorias/1/2/max3'
        	do {
        		$xml .= $this->build_URL( 
                    category_base() . '/' . $row['segment'], 
                    $row['created'] . ' 00:00:00' 
                );
        	}
            while( $row = $cmd->fetch() );
        }

        return $xml;
    }

    private function articles(): string {
        $cmd = $this->conn->prepare("
            SELECT ID, updated FROM articles WHERE status = ? ORDER BY ID DESC
        ");
        $cmd->execute([ 1 ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        $xml = '';
        if( $row ) {
            # pagina da listagem de artigos 
            # fora do loop, mesmo sem `LIMIT 1` sho pega o ultimo registro do updated/lastmod
            $xml .= $this->build_URL( 
                articles_base(), 
                $row['updated'] 
            );

            # todos os artigos
            $article = Container::call('Article');
            do {
                $xml .= $this->build_URL( 
                    $article->field('segment', $row['ID']), 
                    $row['updated'] 
                );
            }
            while( $row = $cmd->fetch() );
        }

        return $xml;
    }

    private function pages(): string {
        $cmd = $this->conn->query("SELECT slug, lastmod FROM pages WHERE status = 1 ORDER BY ID DESC");
        $row = $cmd->fetch( PDO::FETCH_ASSOC );
        $xml = '';
        if( $row ) {
            do {
                $xml .= $this->build_URL( $row['slug'], $row['lastmod'] );
            }
            while( $row = $cmd->fetch() );
        }

        return $xml;
    }

    private function users(): string {
        $cmd = $this->conn->query("
            SELECT username, updated FROM users 
            WHERE approved = 1 AND status = 1 ORDER BY ID DESC
        ");
        $row = $cmd->fetch( PDO::FETCH_ASSOC );
        $xml = '';
        if( $row ) {
            do {
                $xml .= $this->build_URL( 
                    user_base() . '/' . $row['username'], 
                    $row['updated'] 
                );
            }
            while( $row = $cmd->fetch() );
        }

        return $xml;
    }


    private function build_URL( ?string $loc, ?string $date ): string {
        $location = self::$siteurl . $loc;
        $block = "
        <url>
            <loc>{$location}</loc>
            <lastmod>{$this->LastModified($date)}</lastmod>
        </url>";

        return str_replace( '        ', '    ', $block );
    }


    private function LastModified( ?string $date ): string {

        if( empty($date) || $date === '0000-00-00 00:00:00' ) {
            return '';
        }

        try {
            # DateTime nao pode ser singleton, isso faria retornar sempre a mesma
            $lastmod = new DateTime( $date, $this->tzSystem );
        } 
        catch( Exception $e ) {
            if( DISPLAY_ERRORS ) {
                throw new Exception(
                    "Erro no parâmetro de entrada do DateTime na classe SitemapXML: " . 
                    $e->getMessage()
                );
            }
            return '';
        }
        
        $lastmod->setTimezone( $this->tzUTC );

        return $lastmod->format('Y-m-d\TH:i:s\Z');
    }

}