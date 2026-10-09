<div class="mb10">
    <span class="ml10"><?= Count::selects() ?></span>
</div>

<div class="flexbox">
    <div class="cn_33 pr5"></div>

    <div class="cn_33 px5">
        <label for="category" class="sr">Categoria</label>
        <select id="category" name="category">
            <option value="0" selected>Todas</option>
            <?php echo $category->select_option() ?>
        </select>
    </div>

    <div class="cn_33 pl5">
        <form class="search" method="GET" action="<?= URL::current() ?>">
            <input 
                type="search" placeholder="Procurar por registro de articles" 
                name="q" value="<?= Ensure::attr(URL::GET('q')) ?>" 
            />
            <button class="btn" type="submit">Procurar</button>
        </form>
    </div>
</div>

<?php

if( INPUT::formSubmitted() ) {
    require annex_path('deps/controller.php');
    require dashboard_path('controller/article.php');
}


?>
<table>
    <tr>
        <th><span icon="image" size="25"></span></th>
        <th>Título</th>
        <th>Publicado</th>
        <th>Atualizado</th>
        <th>Slug</th>
        <th class="w10">Status</th>
        <th class="txt_center w5"><span icon="trash" size="26"></span></th>
    </tr>
    <?php foreach( select_articles() as $show ) : ?>
    
        <tr>
            <td class="thumb">
                <div>
                    <a href="<?= dash_url( "articles/update/?id={$show->ID}" ) ?>">
                        <img src="<?= Image::render($show, 'thumb')['show_image'] ?>" alt="" />
                    </a>
                </div>
            </td>
            <td class="show">
                <a class="dblock ellps" href="<?= dash_url('articles/update/?id=' . $show->ID ) ?>">
                    <?= $show->title ?>
                </a>
            </td>
            <td class="ft14"><?= chronos_format($show->created) ?></td>
            <td class="ft14"><?= chronos_format($show->updated) ?></td>
            <td><?= $show->slug ?></td>
            <td class="txt_center ft18"><?= ( $show->status == 0 ) ? 'Rascunho' : 'Publicado' ?></td>
            <td class="txt_center">
                <form method="POST" action="<?= URL::current() ?>">
                    <button 
                        onclick="javascript: return confirm(`Vai mesmo deletar esse article?`)" 
                        class="input_false link delete" 
                        name="action" value="delete"
                    >
                        <span icon="close" size="26"></span>
                    </button>
                    <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                    <input type="hidden" id="target_type" name="target_type" value="article" />
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php
echo paginator()->render();