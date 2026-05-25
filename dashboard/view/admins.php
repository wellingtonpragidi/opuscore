<?php 
if( INPUT::formSubmitted() ) {
    require get_dashboard_path( 'controller/settings/common.php' );
}
?>
<form method="POST" action="<?= URL::current() ?>">
<table class="cellspace outline">
    <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Ingressado em:</th>
        <th>Função</th>
        <th class="w5">Status</th>
        <th class="txt_center w5"><span icon="trash" size="26"></span></th>
    </tr>
    <?php foreach( $admin->select() as $show ) : ?>
    <tr>
        <td <?= $admin->logged_ID() == $show->ID ? 'class="bold overline"' : '' ?>>
            <a href="<?= dash_url('admins/admin/?id='.$show->ID) ?>"><?= $show->name ?></a>
        </td>
        <td><?= $show->email ?></td>
        <td><?= chronos_format($show->created) ?></td>
        <td>
        <?php 
            switch($show->role) {
                case 1:
                    echo 'Administrador';
                break;
                case 2:
                    echo 'Editor';
                break;
                case 3:
                    echo 'Autor';
                break;
            }
        ?>
        </td>
        <td class="txt_center fs15"><?= ( $show->status == 0 ) ? 'Pendente' : 'Confirmado' ?></td>
        <td class="txt_center">
            <?php if( $show->role == 1 ) : ?>
            <button 
                onclick="javascript: return confirm('Vai mesmo deletar este administrador?')" 
                class="input_false link delete" name="action" value="delete">
                <span icon="close" size="26"></span>
            </button>
            <?php endif; ?>
        </td>
    </tr>
    <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
    <input type="hidden" id="target_type" name="target_type" value="admin" />
    <?php endforeach; ?>
</table>
</form>