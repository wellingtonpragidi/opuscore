<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

_POST::method_request();


if(  ! empty( select_medias() )  ) {

    ob_start();

	foreach( select_medias() as $show ) : ?>
        <div class="thumb">
            <a href="<?= dash_url('medias/?id=' . $show->ID) ?>" target="_blank" rel="noopener">
                <?php echo Media::system_thumbnails( $show ) ?>
            </a>
        </div>
    <?php 
    endforeach;

    $HTML = ob_get_clean();

    echo json_encode([
        'content' => $HTML,
        'button'  => true
    ]);

}
else {

    echo json_encode([
        'content' => '<p class="loadmore-end">Parece que você chegou ao fim.</p>',
        'button'  => false
    ]);

}