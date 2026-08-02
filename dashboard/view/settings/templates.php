<?php 
if( INPUT::formSubmitted() ) {
    require dashboard_path('controller/settings/template.php');
}
?>
<form method="POST" action="<?= URL::current() ?>">
	<div class="templates flexbox">

		<?php 
		Templates::gallery();

        $has_required_files = false;
        foreach( TemplateManager::REQUIRED_TEMPLATE_FILES as $file ) {
        	if( template('slug') !== '' && file_exists(TEMPLATE_DIR . $file) ) {
        		$has_required_files = true;
        	}
        }
        if( $has_required_files ) : ?>
        	<hr class="cn_100">
        	<div class="mt40 pt20 ml mr0 cn_50">
				<p>Arquivos <code>header.php</code>, <code>index.php</code> e <code>footer.php</code> detectados na raíz do diretório <code>templates/</code></p>
				<button class="btn" name="action" value="">Limpar template ativo</button>
				<div class="fs15">
					<p>Essa ação desativa o uso padrão para templates do site, sendo possível criar o site na raiz do diretório <code>templates/</code>. Útil para quem customizou o template e deseja evitar a sobrescrita ao receber uma nova versão do autor.</p>
					<strong>Notas:</strong>
					<ul class="mt0 pt0">
						<li>Para voltar a usar o template no método padrão, basta ativar um novo template.</li>
						<li>A ação de desativar é necessária para limpar a configuração de <code>&apos;template&apos; => &apos;slug&apos;</code> para que tudo funcione corretamente.</li>
					</ul>
				</div>
			</div>
        <?php endif; ?>

	</div>
</form>