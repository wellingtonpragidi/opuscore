<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();


if(  empty( select_medias() )  ) :

    echo json_encode([
        'content' => '<p class="loadmore-end">Parece que você chegou ao fim.</p>',
        'button'  => false
    ]);

else : 

    ob_start();

	foreach( select_medias() as $show ) {
        echo 
        "<label for=\"file-{$show->ID}\" id=\"thumb-{$show->ID}\" class=\"thumb\">

            <input 
                type=\"radio\" 
                id=\"file-{$show->ID}\" 
                class=\"dnone datafile\" 
                name=\"datafile\" 
                value=\"{$show->ID}\" 
            />"

            . Media::system_thumbnails( $show ) . 

        "</label>";
        
    }

    $HTML = ob_get_clean();

    echo json_encode([
        'content' => $HTML,
        'button'  => true
    ]);

endif;