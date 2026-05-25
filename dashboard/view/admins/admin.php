<?php 
/**
 * verificacao 1: admin com funcao 1 pode acessar independente do param id da URL
 * verificacao 2: o admin com a session igual param id da URL pode acessar
 */
if( is_admin_manager() || $admin->logged_id() === URL::int('id') ) : 
    if( INPUT::formSubmitted() ) {
        require get_dashboard_path('controller/admin.php');
    }
?>
	<form method="POST" action="<?= URL::current() ?>" class="mt30">
		<?php foreach($admin->select() as $show) : ?>
		<div class="flexbox mb20">
			<div class="cn_30 mr5">
				<label for="name" class="op07 fs14">Nome</label><br>
				<input id="name" type="text" name="name" value="<?= $show->name ?>" />
			</div>
			<div class="cn_30 ml5">
				<label for="mail" class="op07 fs14">E-mail</label><br>
				<input id="mail" type="text" name="mail" readonly disabled value="<?= $show->email ?>" /><br>
				<div class="color-danger fs13 txt_center mt5 op08">Ainda não é possível alterar o e-mail.</div>
			</div>
		</div>

		<div class="w30">
			<label for="role" class="op07 fs14">Função</label><br>
			<select id="role" name="role" <?php select_options_role_attributes() ?>>
				<option value="1" <?= is_admin_manager() ? 'selected' : '' ?>>Administrador</option>
				<option value="2" <?= $show->role == 2 ? 'selected' : '' ?>>Editor</option>
				<option value="3" <?= $show->role == 3 ? 'selected' : '' ?>>Autor</option>
			</select>
		</div>

		<?php echo ( $admin->role() == 1 ) ? '<div class="cn_30 ml15 mt30 color-danger fs13 op08">Só é possível alterar a fução de Editor e Autor</div>' : ''; ?>

		<div class="w60 mt40 txt_right">
			<button type="submit" class="btn mt15 w20" name="action" value="update">Atualizar</button>
		</div>

        <input type="hidden" id="target_id" name="target_id" value="<?= $show->ID ?>" />
        <input type="hidden" id="target_type" name="target_type" value="admin" />

		<?php endforeach; ?>
	</form>
	
<?php else :

	echo '<p>Sem autorização para editar conta de outro administrador.</p>';

endif;