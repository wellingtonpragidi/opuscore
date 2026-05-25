<?php
/**
 * Este arquivo contem funcoes auxiliares (helpers) especificas para gerenciar
 * paginas e posts no dashboard. Elas centralizam a logica de apresentacao
 * baseada no 'tipo' (page ou post) e nas acoes, mantendo o HTML das views mais limpo.
 *
 * @package System/Views/Helpers
 */

/**
 * Renderiza o editor padrao ou escolhido na configuracao do sistema
 */
function render_editor( ?object $show = null ): void {
    # Prioriza o conteudo enviado via POST; 
    # caso contrario, usa o conteudo armazenado no db com $show->content
    # fallback string vazia
    $content = $_POST['content'] ?? $show->content ?? '';

    $html = htmlspecialchars( $content, ENT_QUOTES, 'UTF-8' );

    if( editor_is('punk') ) {
        new Punk; # Instancia a classe Punk, ela que inicializa o editor.
        echo '<textarea id="editor" name="content">' . $html . '</textarea>';
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


function category_url( object $show ): string {
    $pathname = category_base() . '/' . $show->segment;
    return site_url($pathname);
}

/**
 * Imprime atributos HTML para desativar a interacao em seletores de funcao
 * se o usuario logado nao for um administrador gerente.
 * Utilizado para prevenir alteracoes nao autorizadas de funcoes de administradores.
 */
function select_options_role_attributes(): void {
    if( ! is_admin_manager() ) {
        # Adiciona um alerta JS e previne a acao se o usuario nao tiver permissao.
        echo 'onmousedown="alert(`Somente um administrador com função 1 pode alterar a função de outro administrador.`); this.blur(); return false;"';
    }
}


/**
 * Imprime a classe CSS 'select-type' para um elemento principal (main)
 * se a URL nao contiver a acao 'update'
 * Utilizado para estilizar elementos em paginas de insercao ou listagem,
 * diferenciando-as das paginas de edicao
 * @todo encontrar oura maneira de alterar css em paginas de update ""E remover isso"" 
 */
function main_attrs() {
    echo ( URL::param(1) != 'update' ) ? 'class="select-type"' : '';
}