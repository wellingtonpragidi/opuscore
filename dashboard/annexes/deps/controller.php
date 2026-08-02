<?php
/**
 * `articles` - `pages` - `categories` - `contexts` - `medias`
 */


require annex_path('deps/sanitize-validate.php');


/**
 * Resolve qual segment o article deve usar
 *
 * Prioridade:
 * 1. Segmento informado manualmente pelo usuario.
 * 2. Segmento reconstruido automaticamente pela ordem padrao.
 * 3. Mantem o segmento existente quando nenhuma alteracao ocorreu.
 */
function article_segment( Article $article, Assign $bind ): string {
    $segment = $article->field('segment');

    # Reconstroi `segment` quando: 
    # categorias mudaram; 
    # segmento esta vazio; 
    # ainda nao possui slug de categoria;
    $shouldBuild = function( ?string $segment ): bool {
        return INPUT::int('categories_changed') === 1
            || empty($segment)
            || strpos($segment, '/') === false;
    };

    if( INPUT::int('segment_changed') === 1 ) {
        # escolheu manualmente
        return INPUT::GET('segment');
    }
    else if( $shouldBuild($segment) || $article->field('slug') !== $bind->slug ) {
        # nao mexeu no select, mas mexeu nas categorias ou slug do article
        # ou valor do segment na tabela esta vazio ou ainda incorreto 
        return $article->build_segment($bind);
    }
    else {
        
        return $segment;
    }
}


# helper de mensagem para todas as entidades que tem imagem destaque vinculada
# articles, pages, categories
function delete_image_messages( bool $record_deleted, bool $files_deleted ): string {
    $alert_content = '';

    if( $record_deleted ) {
        $alert_content .= '<p class="concat">Imagens de destaque excluídas do registro.</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Imagens de destaque não foram excluídas do registro!</p>';
    }

    if( $files_deleted ) {
        $alert_content .= '<p class="concat">Arquivos físicos de imagens excluídos.</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Os arquivos físicos de imagens não foram excluídos!</p>';
    }

    return $alert_content;
}