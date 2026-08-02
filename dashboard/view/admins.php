<table>
    <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Ingressado em:</th>
        <th>Função</th>
        <th class="w5">Status</th>
    </tr>
    <?php foreach( $admin->select() as $show ) : ?>
    <tr>
        <td <?= $auth->logged('ID') === $show->ID ? 'class="bold overline"' : '' ?>>
            <a href="<?= dash_url('admins/update/?id='.$show->ID) ?>"><?= $show->name ?></a>
        </td>
        <td>
            <?= $show->email ?>
        </td>
        <td>
            <?= chronos_format($show->created) ?>
        </td>
        <td>
            <?= $admin->role($show) ?>
        </td>
        <td class="txt_center fs15">
            <?= $admin->status($show) ?>

            <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
            <input type="hidden" id="target_type" name="target_type" value="admin" />
        </td>
    </tr>
    <?php endforeach; ?>
</table>