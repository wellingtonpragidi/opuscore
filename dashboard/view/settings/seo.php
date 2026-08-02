<form class="w70 px10 mt30" method="POST" action="<?= URL::current() ?>">
	<?php
	if( INPUT::formSubmitted() ) {
		require dashboard_path('controller/settings/seo.php');
	}

	$ensure = fn($key) => Ensure::string( $_POST[$key] ?? SEO($key) );
	?>
	<h2 class="txt_center mt0 mb20 mt25 fs20">Ferramentas de Webmasters</h2>
	<h3 class="txt_center">
		<span class="border-span">Adicionar código de verificação para propriedade do site em mecanismos de busca</span>
	</h3>

	<hr />

	<div class="flexbox">
		<?= $alert['google_verification'] ?? null ?>
		<span class="cn_20 mt10"><label for="g">Google</label></span>
		<input id="g" class="cn_60 mh40" type="text" name="google_verification" value="<?= $ensure('google_verification') ?>" />
		<button type="submit" class="btn cn_20" name="action" value="google_action">SALVAR</button>
		<p class="cn_100 mt5 fs15 mb0 op08" style="padding-left: 20%"><a href="https://search.google.com/search-console" target="_blank" rel="noopener">https://search.google.com/search-console</a></p>

		<hr class="cn_100">

		<?= $alert['bing_verification'] ?? null ?>
		<span class="cn_20 mt10"><label for="b">Bing</label></span>
		<input id="b" class="cn_60 mh40" type="text" name="bing_verification" value="<?= $ensure('bing_verification') ?>" />
		<button type="submit" class="btn cn_20" name="action" value="bing_action">SALVAR</button>
		<p class="cn_100 mt5 fs15 mb0 op08" style="padding-left: 20%"><a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener">https://www.bing.com/webmasters/</a></p>
	</div>

	<hr />

    <h3 class="txt_center mt40 pt20 mb20">
    	<span class="border-span">Meta descrições</span>
    </h3>

    <div class="fs15 mt10 mb30 op08 italic">
	  <p class="my5">Este resumo será usado como a <strong>Descrição</strong> da página para mecanismos de busca e também pode ser reutilizado em buscas internas ou prévias do seu site.</p>
	  Os campos de texto <code>textarea</code> aceita até <strong>220 caracteres</strong>. Este é um limite estendido que permite incluir palavras-chave de contexto adicionais.
	  <p class="my5">O Google e outros buscadores geralmente truncam (cortam e adicionam "&hellip;") ao texto que ultrapassa o limite de visualização. Para garantir que sua descrição apareça completa nos resultados de pesquisa, priorize a informação principal dentro da margem de <strong>155 a 160 caracteres</strong>.</p>
	</div>

	<?= $alert['homepage_description'] ?? null ?>
	<label for="home-dscpt" class="dblock">Descrição da Home Page</label>
	<textarea id="home-dscpt" class="md" name="homepage_description" minlength="50" maxlength="220" placeholder="Mínimo 50 e máximo 220 caracteres"><?= $ensure('homepage_description') ?></textarea>
	<input type="hidden" name="home_page_update" value="<?= date('Y-m-d H:i:s') ?>">
	<button type="submit" class="btn dblock right mt20 w20" name="action" value="home_action">SALVAR</button>

	<hr>

	<?= $alert['articles_description'] ?? null ?>
	<label for="articles-dscpt" class="mt30 dblock">
        Descrição da página de listagem de artigos e artigos por pesquisa
    </label>
	<textarea id="articles-dscpt" class="md" name="articles_description" minlength="50" maxlength="220" placeholder="Mínimo 50 e máximo 220 caracteres"><?= $ensure('articles_description') ?></textarea>
	<button type="submit" class="btn dblock right mt20 w20" name="action" value="articles_action">SALVAR</button>

	<hr>

    <?= $alert['categories_description'] ?? null ?>
    <label for="cats-dscpt" class="mt30 dblock">Descrição da página de listagem de categorias</label>
    <textarea id="cats-dscpt" class="md" name="categories_description" minlength="50" maxlength="220" placeholder="Mínimo 50 e máximo 220 caracteres"><?= $ensure('categories_description') ?></textarea>
    <button type="submit" class="btn dblock right mt20 w20" name="action" value="cats_action">SALVAR</button>

    <hr>

	<?= $alert['user_description'] ?? null ?>
    <label for="user-dscpt" class="mt30 dblock">Descrição da página de usuário</label>
	<textarea id="user-dscpt" class="md" name="user_description" minlength="50" maxlength="220" placeholder="Mínimo 50 e máximo 220 caracteres"><?= $ensure('user_description') ?></textarea>
	<button type="submit" class="btn dblock right mt20 w20" name="action" value="user_action">SALVAR</button>
</form>

<div class="mt35 w70">
	<hr>
	<p class="mt25 fs15 italic">
        Para garantir a melhor exibição da sua marca nos navegadores e redes sociais, 
        configure também o <a href="<?= dash_url('medias/favicons') ?>">Favicon</a> 
        (o sistema gerará os ícones e o arquivo manifest para PWA) 
        e o <a href="<?= dash_url('medias/poster') ?>">Poster</a> de Compartilhamento 
        (imagem fallback para redes sociais)
    </p>
</div>

<script>
// Boquear tecla Enter dos <textarea>
document.querySelectorAll('textarea').forEach( dscpt => {
    dscpt.addEventListener("keydown", event => {
        if( event.key === "Enter" ) {
            event.preventDefault();
        }
    });
});
</script>