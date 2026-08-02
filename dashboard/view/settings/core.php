<?php
if( INPUT::formSubmitted() ) {
	require dashboard_path( 'controller/settings/core.php' );
}


# retora o array com formato de datas para valores validos disponiveis
$values = function(): array {
    return [
        'd/m/Y',
        'd \d\e F \d\e Y',
        'd/m/Y \a\s H:i',
        'd/m/Y \a\s H:i:s',
        'd \d\e F \d\e Y \a\s H:i',
        'd \d\e F \d\e Y \a\s H:i:s',
        'inblock'
    ];
};
?>
<hr class="w60">
<form class="flexbox w60 mt20" method="POST" action="<?= URL::current() ?>">
	<span class="cn_30 mt5"><label>Título do site:</label></span>
    <input class="cn_70" type="text" name="site_title" value="<?= $_POST['site_title'] ?? site_title() ?>">

	<hr class="cn_100">

	<div class="cn_30 mt40">Formato da data:</div>
	<div class="cn_70 rad mb20">
		<?php foreach( $values() as $key => $value ) : ?>
		<div class="mt10">
			<input 
			    id="date<?= $key ?>" 
			    type="radio" name="dateformat" 
				<?= ($_POST['dateformat'] ?? chronos_setting()) === $value ? 'checked' : '' ?> 
				value="<?= $value ?>" 
			/>
			<label for="date<?= $key ?>" <?= $key === 6 ? ' class="mt20"' : '' ?>>
				<span <?= $key === 6 ? ' class="txt_center mt-15"' : '' ?>>
					<?= chronos_format( date('Y-m-d H:i:s'), $key ) ?>
				</span>
			</label>
		</div>
		<?php endforeach; ?>
	</div>

    <hr class="cn_100">

	<p class="cn_100 mt40" style="padding-left: 30%;">
		<button type="submit" class="btn" name="action">Salvar alteração</button>
	</p>
</form>