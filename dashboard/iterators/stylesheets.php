<?php
/**
 * Este arquivo e responsavel por incluir e injetar dinamicamente todos os estilos CSS
 * do dashboard diretamente no HTML, dentro de uma tag <style>. Ele le os arquivos CSS
 * de diretorios especificos e os combina, aplicando compressao.
 *
 * @package Dashboard/Styles
 */

/**
 * Carrega e injeta todos os arquivos CSS do dashboard diretamente no HTML.
 * A funcao utiliza output buffering para capturar o conteudo dos arquivos CSS,
 * aplicar compressao e, em seguida, descarregar o CSS processado.
 *
 * @return void
 *
 */
function stylesheets(): void {
    ob_start( 'compress_CSS' );

    require DASH_DIR . 'assets/fonts/fonts.css';

    $iterator = new DirectoryIterator( DASH_DIR . 'assets/css/' );
    foreach( $iterator as $file ) {
        if( $file->isFile() && $file->getExtension() === 'css' ) {

            require str_replace( "\\", "/", $file->getRealPath() );
        }
    }

    # Bloco condicionais:

    $slug[0] = URL::param(0);
    $slug[1] = URL::param(1);

    $routes_path = DASH_DIR . 'assets/css/routes/';


    if( $slug[0] === 'admins' ) {
        require $routes_path . 'admins.css';
    }


    # inclui folhas de estilos especificos de editores, dependendo da rota e do editor configurado
    # A condicao verifica se e uma pagina de 'update' article, page ou context
    if( is_document_update() ) {
        # Inclui os estilos do editor Punk, se configurado.
        if( editor_is('punk') ) {
            $iterator = new DirectoryIterator( DASH_DIR . 'assets/css/punk/' );
            foreach( $iterator as $file ) {
                if( $file->isFile() && $file->getExtension() === 'css' ) {
                    require str_replace( "\\", "/", $file->getRealPath() );
                }
            }
        }
        # Inclui os estilos do editor TinyMCE, se configurado.
        if( editor_is('tinymce') ) {
            require DASH_DIR . 'assets/css/tinymce/tinymce.css';
        }
        if( editor_is('ace') ) {
            echo "
            #editor.ace_editor {
                width: 100%;
                height: 90vh;
            }
            .ace_selected-word {
                border: 2px solid rgba(255, 255, 255, 0.80);
                background-color: #111;
                border-radius: 2.5px
            } ";
        }
        if( editor_is('codemirror') ) {
            echo "
            .CodeMirror, .cm-s-monokai.CodeMirror {
                color: #bbb;
                height: 90vh;
            }
            .CodeMirror-lines {}
            .CodeMirror-code > div {
                padding-left: 8px !important;
            }
            .CodeMirror-gutter {
                background: rgba(255, 255, 255, 0.03) !important;
                border-right: 1px solid rgba(0, 0, 0, 0.15) !important;
            }
            .CodeMirror-gutter.CodeMirror-linenumbers, .CodeMirror-linenumber {
                color: #777 !important;
                text-align: center !important;
            }
            .CodeMirror-activeline-gutter {
                background: rgba(255, 255, 255, 0.10) !important;
            } ";
        }
    }

    ob_end_flush();
}