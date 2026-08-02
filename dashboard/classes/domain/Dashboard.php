<?php
declare( strict_types = 1 );
/**
 * Permite registrar paginas e subpaginas do dashboard
 * Cada pagina possui: path (arquivo php), title (titulo do header), body (id do body)
 * Subpaginas sao opcionais e verificadas antes da pagina principal
 * Usa hooks do sistema (Hook::append_filter e Hook::append_action)
 * Gerencia as operacoes CRUD para categorias incluindo funcionalidade hierarquica 
 * e geracao de HTML para exibicao
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package System\Hook
*/

class Dashboard {

    # Adiciona uma pagina do dashboard
    public static function route( array $args ): void {

        $subpages = $args['subpages'] ?? [];

        if( URL::param(0) === $args['page'] ) {

            # filtro unico para desativar a rota padrao do dashboard
            Hook::append_filter( 'run_dashboard', fn($run) => false );

            # verifica subpages primeiro
            # $slug  = chave da subpagina (geralmente usado em URL::param(1))
            # $sub   = array com os dados da subpagina: 'path', 'title' e 'body'
            foreach( $subpages as $slug => $sub ) {
                if( URL::param(1) === $slug ) {
                    self::appendHooks($sub);
                    return;
                }
            }

            # fallback para pagina principal
            self::appendHooks([
                'path'  => $args['path']  ?? '',
                'title' => $args['title'] ?? '',
                'body'  => $args['body']  ?? ''
            ]);
            return;
        }
    }

    # registra os hooks de conteudo, titulo e body
    private static function appendHooks( array $args ): void {
        Hook::append_action( 'dashboard_page', function() use ($args) {
            require $args['path'];
        });

        Hook::append_action( 'dashboard_title', function() use ($args) {
            echo $args['title'];
        });

        Hook::append_action( 'dashboard_body', function() use ($args) {
            echo "id=\"{$args['body']}\"";
        });
    }

}