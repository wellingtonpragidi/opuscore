<?php
declare( strict_types = 1 );
/**
 * 
 * @usage categories.php
 */

function select_category( array $except = [] ): array {
    $container = Container::instance();
    $category = $container->make('Category');

    return $category->select($except) ?? [];
}

function cat_url( object $show ): string {
    $base = get_settings('URL');
    $base = $base['category_base'] ?? 'categoria';

    return URL::root("{$base}/{$show->segment}");
}

function cat_summary( object $show, int $length = 240, string $hellip = '&hellip;' ): string {
    $summary = text_summary( $show->content, $length, $hellip );
    $summary = empty($show->content) ? '': $summary;

    return $summary;
}

function cat_class( object $show, string $moreClass = '' ): string {
    $value = ($moreClass !== '')
        ? $show->html->class 
        : $moreClass . ' ' . $show->html->class;

    return 'class="' . trim($value) . '"';
}

function cat_image( object $show, array $args = [] ): ?string {
    $scope   = $args['scope'] ?? 'plain';

    $loading = null;

    if( isset($args['loading']) && ($args['loading'] === 'lazy' || $args['loading'] === true) ) {
        $loading = ' loading="lazy"';
    }

    $alt        = 'alt="' . Ensure::attr($show->name) . '" ';
    $dimensions = Image::dimension_attrs( $show->attachment->{$scope} ?? null );

    $attachment = $show->attachment->{$scope}->path ?? null;
    $version    = '?v=' . ($show->attachment->version ?? 0);


    if( isset($attachment) ) {
        $attrs = $alt . $dimensions . $loading;

        return '<img src="' . upload_url($attachment . $version) . '" ' . $attrs . ' />';
    }

    return null;
}