<?php
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

function cat_class( object $show, string $more_class = '' ): string {
    $html_class = trim($more_class . ' ' . $show->dynamo['class']);
    $html_class = empty($more_class) ? $show->dynamo['class'] : $html_class;

    return "class=\"{$html_class}\"";
}

function cat_image( object $show, $version = 0, string $scope = 'plain' ): string {
    $alt        = 'alt="' . Ensure::attr($show->name) . '"';
    $attachment = $show->attachment->{$scope} ?? null;
    $dimensions = Image::dimensions_attrs( $attachment );

    $v = ($version !== 0) ? "?v={$version}" : '';

    $source = isset($attachment->path) ? upload_url($attachment->path) : false;

    return $source ? "<img src=\"{$source}{$v}\" {$alt} {$dimensions} />" : '';
}