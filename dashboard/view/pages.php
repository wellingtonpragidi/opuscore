<div class="clean">
    <div class="w50 mb10 floatleft">
        <p class="ml10"><?= Count::selects() ?></p>
    </div>
    <div class="w50 mb10 floatright">
        <form class="search w400 floatright" method="GET" action="<?= URL::current() ?>">
            <input 
                type="search" placeholder="Procurar por registro de páginas" 
                name="q" value="<?= URL::GET('q') ?>" 
            />
            <button class="btn" type="submit">Procurar</button>
        </form>
    </div>
</div>
<?php
if( INPUT::formSubmitted() ) {
    require_callable('controller-helpers.php');
    require get_dashboard_path('controller/page.php');
}
?>
<table>
    <tr>
        <th><span icon="image" size="25"></span></th>
        <th>Título</th>
        <th>Template</th>
        <th>Caminho</th>
        <th class="w10">Status</th>
        <th>Modificado</th>
        <th class="txt_center w5"><span icon="trash" size="26"></span></th>
    </tr>
    <?php foreach( page_select() as $show ) : ?>
    <form method="POST" action="<?= URL::current() ?>">
    <tr>
        <td class="thumb">
            <div>
                <a href="<?= dash_url( 'pages/update/?id=' . $show->ID ) ?>">
                    <img src="<?= Image::render($show, 'thumb')['show_image'] ?>" alt="" />
                </a>
            </div>
        </td>
        <td class="show">
            <a class="dblock ellps" href="<?= dash_url('pages/update/?id=' . $show->ID ) ?>">
                <?= $show->title ?>
            </a>
        </td>
        <td><?= $show->template ?></td>
        <td><?= empty($show->segment) ? '<hr>' : '/' . $show->segment; ?></td>        
        <td class="txt_center ft18"><?= ($show->status === 0) ? 'Rascunho' : 'Publicado' ?></td>
        <td class="ft14"><?= chronos_format($show->lastmod) ?: '<hr>' ?></td>
        <td class="txt_center">
            <button 
                onclick="javascript: return confirm(`Vai mesmo deletar esta página?`)" 
                class="input_false link delete" 
                name="action" value="delete">
                <span icon="close" size="26"></span>
            </button>
        </td>
    </tr>
        <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
        <input type="hidden" id="target_type" name="target_type" value="page" />
    </form>
    <?php endforeach; ?>
</table>
<?php
$pagination = new Pagination( Count::pages(), per_page('pages') );
echo $pagination->render();