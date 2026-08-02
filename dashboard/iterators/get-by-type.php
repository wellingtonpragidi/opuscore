<?php
/**
 * Retorna a URL completa para a pagina de edicao ou visualizacao
 * da entidade a qual uma midia esta relacionada.
 */
function url_by_media_type( object $show ): ?string {

    $id = $show->media->ID;

    $dash_url ??= URL::root('dashboard');

    return match( $show->type ) {

        'article', 
        'editor-article'      => $dash_url . 'articles/update/?id=' . $id,

        'category-article'    => $dash_url . 'articles/category/?id=' . $id,

        'page', 'editor-page' => $dash_url . 'pages/update/?id=' . $id,

        'editor-context'      => $dash_url . 'customize/context/?id=' . $id,

        'user'                => User::profile_url($id),

        default => null,
    };
}
