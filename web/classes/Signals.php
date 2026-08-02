<?php
/**
 * Document Signals:
 * Sao sinais que o documento emite para o mundo, consumidos por maquinas — nao por humanos.
 *
 * Classe dedicada a geracao de tags <meta> e <link> relevantes para indexacao,
 * interpretacao e relacionamento do documento.
 * A classe tambem gera titulo dinamico para a tag <title>
 *
 * Esta classe e responsavel por inserir:
 * - <meta> essenciais (SEO, indexacao e identificacao)
 * - <link rel="canonical">, icon e manifest
 *
 * Relacoes alternativas como <link rel="alternate"> (hreflang)
 * devem ser adicionadas via gancho de acao 'document_signals'.
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship®
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 *
 * @package Output\SEO
 * @subpackage \Generator
 */

class Signals {

    private Category $category;
    private Page $page;
    private Article $article;
    private Router $router;

    public function __construct() {
        $container = Container::instance();
        $this->category = $container->make('Category');
        $this->page     = $container->make('Page');
        $this->article     = $container->make('Article');
        $this->router   = $container->make('Router');
    }

    public function routes(): ?string {
        if( is_home() ) {

            return $this->indexed( SEO('homepage_description'), null );
        }
        else if( is_query() ) {

            return $this->indexed( SEO('homepage_description'), null );
        }
        else if( is_categories() ) {

            return $this->indexed( SEO('categories_description'), null );
        }
        else if( is_category() ) {

            return $this->indexed( $this->category->meta_description(), 'plain' );
        }
        else if( is_page() ) {
            $description = $this->page->meta_description();
            # subscreve $description se o filtro for chamado, geralmente para traducoes
            $description = Hook::call_filter('page_description', $description);
            
            return $this->indexed( $description, 'wide' );
        }
        else if( is_article() ) {

            return $this->indexed( $this->article->meta_description(), 'wide' );
        }
        else if( is_articles() ) {

            return $this->indexed( SEO('articles_description'), null );
        }
        else if( is_user() ) {

            return $this->indexed( User::meta_description(), 'profile' );
        }
        else if( is_404() || http_response_code() === 404 ) {

            return $this->unindexed();
        }
        else {

            return $this->unindexed();
        }
    }


    private function indexed( string $description, ?string $image_scope ): string {
        $emit = '<meta charset="UTF-8" />' . PHP_EOL;

        $emit .= '<meta name="viewport" content="width=device-width, initial-scale=1" />' . PHP_EOL;

        $emit .= '<title>' . $this->router->title('tag') . '</title>' . PHP_EOL;

        $emit .= '<!-- Search Engines -->' . PHP_EOL;
        if( ! empty($description) ) {
            $emit .= "<meta name=\"description\" content=\"{$description}\" />" . PHP_EOL;
        }
        if( SEO('bing_verification') ) {
            $emit .= '<meta name="msvalidate.01" content="' . SEO('bing_verification') . '" />' . PHP_EOL;
        }
        if( SEO('google_verification') ) {
            $emit .= '<meta name="google-site-verification" content="' . SEO('google_verification') . '" />' . PHP_EOL;
        }

        $emit .= '<link rel="canonical" href="' . URL::current() . '" />' . PHP_EOL;

        $emit .= $this->favicons();
  
        if( is_policy_pages() ) {
            $emit .= '<meta name="robots" content="noarchive">' . PHP_EOL;
        } 
        else {
            $emit .= $this->meta_twitter( $description, $image_scope );

            $emit .= $this->meta_og( $description, $image_scope );
        }

        $emit .= $this->manager_signature();
        
        return $emit;
    }


    private function favicons(): ?string {
        if(  empty( glob(UPLOAD_DIR . 'favicons/*.png') )  ) {
            return null;
        }
        $v = ''; # '?v=' . random('version');

        $emit = '<!-- .Icons -->' . PHP_EOL;
        $emit .= '<link rel="apple-touch-icon" href="' . upload_url('favicons/180x180.png' . $v) . '" sizes="180x180" type="image/png" />' . PHP_EOL;

        foreach( ["144x144", "96x96", "48x48", "32x32", "16x16"] as $scope ) {
            $favicons = upload_url("favicons/{$scope}.png{$v}");
            $emit .= "<link rel=\"icon\" href=\"{$favicons}\" sizes=\"{$scope}\" type=\"image/png\" />" . PHP_EOL;
        }

        $emit .= '<link rel="shortcut icon" href="' . site_url("favicon.ico{$v}") . '" sizes="32x32" type="image/ico" />' . PHP_EOL;

        $emit .= '<!-- .PWA -->' . PHP_EOL;
        $emit .= '<link rel="manifest" href="' . site_url('manifest.json') . '" />' . PHP_EOL;

        return $emit;
    }


    private function meta_twitter( string $description, ?string $scope ): string {
        $v = ''; // rand_v()
        $emit = '<!-- Twitter Cards -->' . PHP_EOL;

        $emit .= '<meta name="twitter:title" content="' . site_title() . '" />' . PHP_EOL;
        if( ! empty($description) ) {
            $emit .= "<meta name=\"twitter:description\" content=\"{$description}\" />" . PHP_EOL;
        }
        $poster = Image::get_featured_url( $scope );
        if( $poster && $scope !== null ) {
            $emit .= '<meta name="twitter:card" content="summary_large_image" />' . PHP_EOL;
            $emit .= "<meta name=\"twitter:image\" content=\"{$poster}{$v}\" />" . PHP_EOL;
        }
        else if( file_exists(UPLOAD_DIR . 'favicon/poster.png') ) {
            $emit .= '<meta name="twitter:card" content="summary_large_image" />' . PHP_EOL;
            $poster = upload_url('favicon/poster.png');
            $emit .= "<meta name=\"twitter:image\" content=\"{$poster}{$v}\" />" . PHP_EOL;
        }

        return $emit;
    }

    private function meta_og( string $description, ?string $scope ): string {
        $v = ''; // rand_n();
        $emit = '<!-- Open Graph -->' . PHP_EOL;

        $emit .= '<meta property="og:locale" content="pt-BR" />' . PHP_EOL;
        $emit .= '<meta property="og:site_name" content="' . site_title()  . '" />' . PHP_EOL;
        $emit .= '<meta property="og:url" content="'. URL::current()  . '" />' . PHP_EOL;
        $emit .= "<meta property=\"og:title\" content=\"{$this->router->title('tag')}\" />" . PHP_EOL;
        $ogtype = is_article() ? 'article' : ( 
            is_page() ? 'profile' : 'website' 
        );

        $emit .= "<meta property=\"og:type\" content=\"{$ogtype}\" />" . PHP_EOL;

        if( ! empty($description) ) {
            $emit .= "<meta property=\"og:description\" content=\"{$description}\" />" . PHP_EOL;
        }
        $featuredimage = Image::get_featured_url( $scope );
        if( $featuredimage && $scope != null ) {
            $emit .= "<meta property=\"og:image\" content=\"{$featuredimage}{$v}\" />" . PHP_EOL;
        }
        else if( file_exists(UPLOAD_DIR . 'favicon/poster.png') ) {
            $poster = upload_url('favicon/poster.png');
            $emit .= "<meta property=\"og:image\" content=\"{$poster}{$v}\" />" . PHP_EOL;
        }

        return $emit;
    }

    private function manager_signature(): string {
        $emit = '<!-- Assinatura do Gerenciador -->' . PHP_EOL;
        $emit .= '<meta name="author" content="webship" />' . PHP_EOL;
        $emit .= '<meta name="generator" content="Opus Core" />' . PHP_EOL;
        $emit .= '<meta name="reply-to" content="https://opuscore.dev/contact">' . PHP_EOL;

        return $emit;
    }

    private function unindexed(): string {
        $emit  = '<meta charset="utf-8" />' . PHP_EOL;
        $emit .= '<meta name="viewport" content="width=device-width, initial-scale=1" />' . PHP_EOL;
        $emit .= '<title>' . $this->router->title('tag') . '</title>' . PHP_EOL;

        $emit .= '<!-- Página não indexada -->' . PHP_EOL;
        $emit .= '<meta name="robots" content="noindex, follow" />' . PHP_EOL;
        $emit .= '<meta name="googlebot" content="noindex, follow">' . PHP_EOL;
        $emit .= $this->manager_signature();

        return $emit;
    }

}