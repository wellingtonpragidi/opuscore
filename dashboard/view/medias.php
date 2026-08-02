<?php 
if( URL::has('id') ) :

    if( INPUT::formSubmitted() ) {
        require annex_path('deps/controller.php');
        require dashboard_path('controller/media.php');
    }


?>
<div id="mediabox">
    <?php
    foreach( select_medias() as $show ) : 

        $print = Media::print($show);

    ?>

    <div class="view">
        <img src="<?= $print['source'] ?>" alt="<?= $print['altext'] ?>" />
    </div>
    <div class="aside">
        <div class="control">
        <?php 
            $direction = (int) $show->ID;
            echo media_navigation( $direction, 'prev' );
            echo media_navigation( $direction, 'next' );
        ?>
        </div>
        <div class="info">
            <h3>Detalhes do arquivo</h3>
            <p><span icon="calendar" top="2"></span> <?= chronos_format($show->created, 2) ?></p>
            <p>
                <strong>Enviado <?= ($show->type === 'user') ? 'por' : 'em' ?> :</strong>
                <a href="<?= url_by_media_type($show) ?>" target="_blank">
                    <?= $show->title ?>
                </a>
            </p>
            <p>
            <?php 
                $data = Media::data_attachment($show);

                $filename = @basename($data['dir']);
                echo $filename ? "<strong>Nome: </strong> {$filename}" : '';
            ?>
            </p>
            <p>
            <?php 
                $mimetype = @FileInfo::mimeType($data['dir']);
                echo $mimetype ? "<strong>Tipo: </strong> {$mimetype}" : '';
            ?>
            </p>
            <p>
            <?php 
                $size = @FileInfo::size($data['dir']);
                echo $size !== '0 B' ? "<strong>Tamanho: </strong> {$size}" : '';
            ?>
            </p>
            <p>
            <?php 
                $dimensions = isset($data['width'], $data['height']);
                echo $dimensions 
                    ? "<strong>Dimensão: </strong> {$data['width']} &times; {$data['height']}" 
                    : '';
            ?>
            </p>

            <?php 
            echo '<p class="purl"><strong>URLs:</strong></p>';
            foreach( Media::inputs_url($show) as $item ) {
                echo $item;
            }
            ?>
            <form method="POST" action="<?= URL::current() ?>">
                <button onclick="javascript: return confirm('Vai mesmo deletar essa mídia?')" type="submit" class="input_false link delete" name="action" value="delete">Excluir</button>

                <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                <input type="hidden" id="upload_id" name="upload_id" value="<?= $show->ID ?>" />
                <input type="hidden" id="upload_type" name="upload_type" value="<?= $show->type ?>" />
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php else : 

    # /controller/async/media-loadmore.php
    ?>
    <section id="gallery" class="clean"></section>

<?php endif;