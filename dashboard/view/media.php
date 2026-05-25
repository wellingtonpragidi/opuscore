<?php 
if( URL::has('id') ) :

    if( INPUT::formSubmitted() ) {
        require_callable('controller-helpers.php');
        require get_dashboard_path('controller/media.php');
    }
?>
<div id="mediabox">
    <?php
    foreach( media_selection() as $show ) : 
    
        $print = Media::print($show);
        ?>
        <div class="view">
            <img src="<?= $print['source'] ?>" alt="<?= $print['altext'] ?>" />
        </div>
        <div class="aside">
            <div class="control">
                <?php attachment_navigation( $show ) ?>
            </div>
            <div class="info">
                <h3>Detalhes do arquivo</h3>
                <p><span icon="calendar" top="2"></span> <?= chronos_format($show->created, 2) ?></p>
                <p>
                    <strong>Enviado <?= ($show->type === 'user') ? 'por' : 'em' ?> :</strong>
                    <a href="<?= Image::URL($show) ?>" target="_blank">
                        <?= $show->title ?>
                    </a>
                </p>
                <p>
                <?php 
                    $src = Image::source($show);

                    $filename = FileInfo::filename($src);
                    echo $filename ? "<strong>Nome: </strong> {$filename}" : '';
                ?>
                </p>
                <p>
                <?php 
                    $mimetype = FileInfo::mimetype($src);
                    echo $mimetype ? "<strong>Tipo: </strong> {$mimetype}" : '';
                ?>
                </p>
                <p>
                <?php 
                    $size = FileInfo::size($src);
                    echo $size !== '0 B' ? "<strong>Tamanho: </strong> {$size}" : '';
                ?>
                </p>
                <p>
                <?php 
                    $dimension = FileInfo::dimension($src);
                    echo $dimension ? "<strong>Dimensão: </strong> {$dimension}" : '';
                ?>
                </p>

                <?php 
                foreach( Media::input_url($show) as $item ) {
                    echo '<p class="purl"><strong>URLs:</strong></p>';
                    echo $item;
                }
                ?>
                <form method="POST" action="<?= URL::current() ?>">
                    <button onclick="javascript: return confirm('Vai mesmo deletar essa mídia?')" type="submit" class="input_false link delete" name="action" value="delete">Excluir</button>

                    <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                    <input type="hidden" id="target_type" name="target_type" value="<?= $show->type ?>" />
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php else : ?>

    <section id="gallery" class="clean"></section>

<?php endif;