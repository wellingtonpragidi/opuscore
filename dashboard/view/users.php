<div class="w33 ml mb10">
    <form class="search" method="GET" action="<?= URL::current() ?>">
        <input type="search" placeholder="Procurar por registro de usuários" name="q" value="<?= URL::Get('q') ?>" />
        <button class="btn" type="submit">Procurar</button>
    </form>
</div>
<?php 

if( INPUT::formSubmitted() ) {
    require dashboard_path('controller/user.php');
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
        <?php foreach( select_users() as $show ) : ?>
            <tr>
                <td>
                    <img 
                        src="<?= Image::render($show, 'avatar')['show_image'] ?>" 
                        alt="" width="40" 
                    />
                </td>

                <td>
                    <a href="<?= user_profile_url($show) ?>" target="_blank" rel="noopener">
                        <?= $show->name ?>
                    </a>
                </td>

                <td><small><?= $show->username ?></small></td>

                <td><?= $show->email ?></td>

                <td class="txt_center"><?= chronos_format($show->created) ?></td>

                <td class="txt_center">
                    <?= $show->updated ? chronos_format($show->updated, 2) : '<hr>' ?>
                </td>

                <td>
                    <?php echo ($show->status === 1) 
                        ? '<span class="op08">Verificado</span>' 
                        : '<b>Pendente</b>';
                    ?>
                </td>

                <td class="txt_center w5 approved">
                    <?php if( $user->approved($show) === true ) : ?>

                        <span icon="check" size="25" 
                            class="op06" title="Usuário aprovado">
                        </span>

                    <?php else : ?>

                        <form method="POST" action="<?= URL::current() ?>">
                            <button class="input_false link" name="action" value="approved">
                                <span icon="check" size="25" title="Aprovar usuário"></span>
                            </button>
                            <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                        </form>

                    <?php endif; ?>
                </td>

                <td class="txt_center">
                    <form method="POST" action="<?= URL::current() ?>">
                        <button 
                            onclick="javascript: return confirm('Vai mesmo deletar este usuário?')" 
                            class="input_false link delete" 
                            name="action" value="delete"
                        >
                            <span icon="close" size="26"></span>
                        </button>
                        <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                    </form>
                </td>
            </tr>

        <?php endforeach; ?>

    </table>
    
    <input type="hidden" id="target_type" name="target_type" value="user" />

</form>
<?php
$pagination = new Pagination( Count::users(), per_page('users') );
echo $pagination->render();