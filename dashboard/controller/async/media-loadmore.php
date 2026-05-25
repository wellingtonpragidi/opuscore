<?php 
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';

if(  ! empty( media_selection() )  ) {

    ob_start();

	foreach( media_selection() as $show ) : ?>
        <div class="thumb">
            <a href="<?= dash_url('media/?id=' . $show->ID) ?>" target="_blank" rel="noopener">
                <img src="<?= Image::render($show)['show_image'] ?>" alt="" />
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