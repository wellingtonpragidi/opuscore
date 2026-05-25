<?php
# get_page('ID');

function page_title(): void {
    echo get_page('title');
}

function page_content(): void {
    echo get_page('content');
}

function page_url(): void {
    echo URL::root( get_page('segment') ?? '' );
}

function page_lastmod(): void {
    echo get_page('lastmod') ?? '';
}

function get_page( string $column ): string {
    $page = Container::call('Page');

    return $page->$column(); 
}