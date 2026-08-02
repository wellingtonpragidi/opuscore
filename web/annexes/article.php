<?php
declare( strict_types = 1 );
/**
 * 
 * @usage article.php
 */


/**
 * exibe as categorias marcadas em uma publicacao 
 * @see https://opuscore.dev/functions/article_categories
 */
function article_categories( string $separator = ', ' ): string {
    $category = Container::call('Category');

    $rows = $category->article_cats();

    if( empty($rows) ) {
        return '';
    }

    $links = [];
    foreach( $rows as $row ) {
        $href = URL::root( category_base() . '/' . $row['segment'] );

        $links[] = "<a href=\"{$href}\">{$row['name']}</a>";
    }

    return implode( $separator, $links );
}


/**
 * 
 * @todo Essa funcao ainda não funciona com a URL de articles hierarquica configurada
 * @see https://opuscore.dev/functions/articles_relateds
 */
function articles_relateds( array $args = [] ): string {
    $article = Container::call('Article');

    $scope = $args['scope'] ?? 'minor';
    $h     = $args['item_title_tag'] ?? 'strong';
    $limit = (int) ($args['limit'] ?? 4);

    $itens = '';
    foreach( $article->relateds($limit) as $show ) {
        $filepath   = $show->attachment->{$scope}->path ?? null;
        $alt        = escattr($show->title);
        $dimensions = Image::dimension_attrs($show->attachment->{$scope} ?? null);

        $image = '';
        if( $filepath ) {
            $imageurl = upload_url($filepath);
            $image = "<img src=\"{$imageurl}\" alt=\"{$alt}\" {$dimensions} />";
        }

        $itens .= "
        <li>
            <a href=\"{$show->URL}\">
                {$image}
                <{$h}>{$show->title}</{$h}>
            </a>
        </li>";
    }

    return "<ul>{$itens}</ul>";
}
