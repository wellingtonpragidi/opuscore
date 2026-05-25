<div class="w33 ml mb10">
    <form class="search" method="GET" action="<?= URL::current() ?>">
        <input type="search" placeholder="Procurar por registro de usuários" name="q" value="<?= URL::Get('q') ?>" />
        <button class="btn" type="submit">Procurar</button>
    </form>
</div>
<?php 
if( INPUT::formSubmitted() ) {
    $user->approvedupdated();
    $user->delete();
}
?>
<form method="POST" action="<?= URL::current() ?>">
    <table class="cellspace outline">
        <tr>
            <th class="txt_center"><span icon="image" size="25"></span></th>
            <th>Nome</th>
            <th>Nome de usuário</th>
            <th>E-mail</th>
            <th>Data registro</th>
            <th>Ultima atualização</th>
            <th>Status</th>
            <th class="txt_center w5 approved"><span icon="question" size="25"></span></th>
            <th class="txt_center w5"><span icon="trash" size="26"></span></th>
        </tr>
        <?php exit; foreach($user->select() as $show ) : ?>
        <tr>
            <td><img src="<?= Image::render($show, 'avatar')['show_image'] ?>" alt="" width="40" /></td>
            <td><a href="<?= site_url(user_base() .'/'. $show->username) ?>" target="_blank"><?= $show->name ?></a></td>
            <td><?= $show->username ?></td>
            <td><?= $show->email ?></td>
            <td class="txt_center"><?= chronos_format($show->created) ?></td>
            <td class="txt_center"><?= ( $show->update ) ? chronos_format($show->update, 2) : '<hr>' ?></td>
            <td class="op08"><?= $show->status == 1 ? 'Verificado' : 'Pendente' ?></td>
            <td class="txt_center w5 approved">
                <?php if( $user->approved($show->ID) == 0 ) : ?>
                <button class="input_false link" title="Aprovar" name="action" value="approve">
                    <span icon="check" size="25"></span>
                </button>
                <?php else : ?>
                <span icon="check" size="25" class="op06" title="Usuário aprovado"></span>
                <?php endif; ?>
            </td>
            <td class="txt_center">
                <button 
                    onclick="javascript: return confirm('Vai mesmo deletar este usuário?')" 
                    class="input_false link delete" 
                    name="action" value="delete">
                    <span icon="close" size="26"></span>
                </button>
            </td>
        </tr>
        <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
        <input type="hidden" id="target_type" name="target_type" value="user" />
        <?php endforeach; ?>
    </table>
</form>
<?php
$pagination = new Pagination( Count::users(), per_page('users') );
echo $pagination->render();