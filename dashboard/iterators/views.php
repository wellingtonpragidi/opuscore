<?php
/**
 * Este arquivo contem funcoes auxiliares (helpers) especificas para gerenciar
 * paginas e articles no dashboard. Elas centralizam a logica de apresentacao
 * baseada no 'tipo' (page ou article) e nas acoes, mantendo o HTML das views mais limpo.
 *
 * @package System/Views/Helpers
 */

/**
 * Renderiza o editor padrao ou escolhido na configuracao do sistema
 */
function render_editor( string $type, ?string $content = null ): void {

    $content = $_POST['content'] ?? $content ?? '';

    $html = htmlspecialchars( $content, ENT_QUOTES, 'UTF-8' );

    if( editor_is('punk') ) {
        echo Punk::render( $type, $html );
    }
    if( editor_is('tinymce') ) {
        echo '<textarea id="editor" name="content">' . $html . '</textarea>';
        echo '<input id="upload" class="dnone" type="file" name="upload" />';
    }
    if( editor_is('ace') ) {
        echo '<div id="editor">' . $content . '</div>';
        echo '<textarea id="output" class="dnone" name="content">' . $html . '</textarea>';
    }
    if( editor_is('codemirror') ) {
        echo '<textarea id="editor" name="content">' . $html . '</textarea>';
    }
}


/* URLs direcionados a parte publica > ------------------------------------------------- */
    function category_url( object $show ): string {
        $url ??= URL::root() . category_base() . '/' . $show->segment;

        return $url;
    }

    # URL para o comentario exato (fragmento ID) do article no site (template)
    function article_comment_url( object $show ): string {
        $site_url ??= URL::root();
        $request ??= $show->segment . '/#comment-' . $show->ID;

        return $site_url . $request;
    }

    function user_profile_url( object $show ): string {
        $url ??= URL::root() . user_base() . '/' . $show->username;

        return $url;
    }
/* URLs direcionados a parte publica ... ------------------------------------------------- */



/**
 * Imprime a classe CSS 'select-type' para um elemento principal (main)
 * se a URL nao contiver a acao 'update'
 * Utilizado para estilizar elementos em paginas de insercao ou listagem,
 * diferenciando-as das paginas de edicao
 * @todo encontrar oura maneira de alterar css em paginas de update ""E remover isso"" 
 */
function main_attrs() {
    echo ( URL::param(1) !== 'update' ) ? 'class="select-type"' : '';
}
