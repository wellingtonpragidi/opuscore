<form class="flexbox w70 px20" method="POST" action="<?= URL::current() ?>">

<?php 
	if( INPUT::formSubmitted() ) {
		require dashboard_path('controller/settings/urls.php');
	}
?>

	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="cat">Base categorias</label></span>
	<span class="bar">/</span>
	<input id="cat" class="cn_70" type="text" name="category_base" value="<?= category_base() ?>" />

	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="pt">Base articles</label></span>
	<span class="bar">/</span>
	<input id="pt" class="cn_70" type="text" name="articles_base" value="<?= articles_base() ?>" />

	<hr class="cn_100" />

	<span class="cn_30 mt10"><label for="us">Base perfil de usuário</label></span>
	<span class="bar">/</span>
	<input id="us" class="cn_70" type="text" name="user_base" value="<?= user_base() ?>" />

	<hr class="cn_100" />

	<div class="cn_100">
	    <button class="btn xlg mt20" name="action">ATUALIZAR</button>
	</div>
</form>