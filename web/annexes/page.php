<?php
# page_field('ID');

function page_title(): void {
    echo page_field('title');
}


function page_content(): void {
    echo page_field('content');
}


function page_url(): void {
    $segment = page_field('segment');

    if( str_starts_with($segment, 'external:') ) {
        echo substr($segment, 9);
    }

    echo URL::root($segment);
}


function page_lastmod(): void {
    echo page_field('lastmod');
}



function page_field( string $column ): string {
    $page = Container::call('Page');

    return $page->$column() ?? ''; 
}