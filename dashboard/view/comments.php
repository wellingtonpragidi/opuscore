<div class="w33 ml mb10">
    <form class="search" method="GET" action="<?= URL::current() ?>">
        <input type="search" placeholder="Procurar por registro de comentários" name="q" value="<?= URL::Get('q') ?>" />
        <button class="btn" type="submit">Procurar</button>
    </form>
</div>
<?php 
if( INPUT::formSubmitted() ) {
    require get_dashboard_path( 'controller/settings/comment.php' );
}

?>
<form method="POST" action="<?= URL::current() ?>">
<table class="cellspace outline">
    <tr>
        <th class="txt_center"><span icon="image" size="25"></span></th>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Resumo</th>
        <th>Data</th>
        <th>Comentado em:</th>
        <th class="txt_center w5"><span icon="link" size="26"></span></th>
        <th class="txt_center w5 approved"><span icon="question" size="25"></span></th>
        <th class="txt_center w5"><span icon="trash" size="26"></span></th>
    </tr>
    <?php exit; foreach( comment_select() as $show ) : ?>
    <tr>
        <td><img src="<?= Image::render($show, 'avatar')['show_image'] ?>" alt="" width="45" /></td>
        <td><a href="<?= site_url(user_base().$show->username) ?>" target="_blank"><?= $show->name ?></a></td>
        <td><?= $show->email ?></td>
        <td><?= text_summary($show->content, 50) ?></td>
        <td><?= chronos_format($show->date, 2) ?></td>
        <td><a href="<?= site_url($show->related) ?>" target="_blank"><?= site_url($show->related) ?></a></td>
        <td class="txt_center"><a href="<?= dash_url('comments/comment/?id='.$show->ID) ?>"><span icon="edit" size="22"></span></a></td>
        <td class="txt_center w5 approved">
            <?php if( $comment->approved($show->ID) == 0 ) : ?>
            <button class="input_false link" name="action" value="approve" title="Marcar como lido"><span icon="check" size="25"></span></button>
            <?php else : ?>
            <span icon="check" size="25" class="op06" title="Comentário aprovado"></span>
            <?php endif; ?>
        </td>
        <td class="txt_center">
            <button 
                onclick="javascript: return confirm('Vai mesmo deletar o comentário?')" 
                class="input_false link delete" 
                name="action" value="delete">
                <span icon="close" size="26"></span>
            </button>
        </td>
    </tr>
    <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
    <input type="hidden" id="target_type" name="target_type" value="comment" />
    <?php endforeach; ?>
</table>
</form>
<?php
$pagination = new Pagination( Count::comments(), per_page('comments') );
echo $pagination->render();