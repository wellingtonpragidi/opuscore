<?php 
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';


if(  ! empty( media_selection() )  ) :

    ob_start();

	foreach( media_selection() as $show ) : ?>
        <label for="file-<?= $show->ID ?>" id="thumb-<?= $show->ID ?>" class="thumb">
            <input 
                id="file-<?= $show->ID ?>" type="radio" class="dnone datafile" 
                name="datafile" value="<?= $show->ID ?>" 
            />

            <?php Image::editor_thumbnail($show) ?>
        </label>
    <?php endforeach;

    $HTML = ob_get_clean();

    echo json_encode([
        'content' => $HTML,
        'button'  => true
    ]);

else : 

    echo json_encode([
        'content' => '<p class="loadmore-end">Parece que você chegou ao fim.</p>',
        'button'  => false
    ]);

endif;