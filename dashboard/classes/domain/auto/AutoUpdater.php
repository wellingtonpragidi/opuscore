<?php
declare( strict_types = 1 );
/**
 * Classe dedicada para automatizacao de processos
 * - Pequenas automatizacoes em metodos @access private na propria classe AutoUpdater
 * - Automacoes "maiores" com mais codigos e metodos eh instanciado aqui com saida no metodo run()
 * - O metodo run() dessa classe eh instanciada no construtor da classe Router;
 * - Classes de automatizacoes nao usam singleton pois sao instanciadas uma unica vez.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System\Output\Services
 * @subpackage \Generator\Updater
 */
class AutoUpdater {

    private SitemapXML $sitemap;

    private Htaccess $htaccess;


	public function __construct() {
        $this->sitemap = new SitemapXML;
        $this->htaccess = new Htaccess;
    }


    public function run(): void {
        $this->homepage_check_update();

        $this->sitemap->rewrite();
        $this->htaccess->rewrite();    
    }

	/** 
	 * Esse metodo verifica se existir o arquivo 'home-page.php' no template, 
     * atualiza a configuracao $SEO = ['homepage_lastmod' => 'x'] 
     * cujo valor contem a data da ultima alteracao no arquivo.
     * 
	 * Entao quando `filemtime` detectar alguma alteracao no arquivo 
     * essa configuracao eh automaticamente atualizada.
	 */
    private function homepage_check_update(): void {

        $homepage = TEMPLATE_PATH . 'home-page.php';

        if( ! file_exists($homepage) ) {
            return;
        }

        $stored = SEO('homepage_lastmod');

        # inicializa se estiver vazio
        if( empty($stored) ) {
            ArrayExport::apply('SEO', [
                'homepage_lastmod' => date('Y-m-d H:i:s')
            ], 'settings' );
            return;
        }

        $filemtime  = filemtime( $homepage );
        $lastmod    = date( 'Y-m-d H:i:s', $filemtime );
        $lastupdate = strtotime( $stored );

        if( $lastupdate < $filemtime ) {
            ArrayExport::apply('SEO', [
                'homepage_lastmod' => $lastmod
            ], 'settings' );
        }
    }

}