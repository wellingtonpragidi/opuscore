<h2 class="txt_center"><span class="border-span">Dimensões de imagens</span></h2>
<div id="media_size" class="flexbox">
	<?php $formaction = fn($act) => dash_url('controller/settings/image.php?action=' . $act); ?>
	<form id="img-post" class="cn_50" method="POST" action="<?= $formaction('post') ?>">
		<div class="response"></div>
		<table>
		<caption class="fs18">Posts</caption>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Notebooka, monitores padrões e tela grande">Grande:</th>
				<td>Largura:</td>
				<td><input type="number" name="post_wide_w" value="<?= post_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="post_wide_h" value="<?= post_h() ?>" /></td>
			</tr>
            <tr>
                <th class="cur_help" title="Indicado para uso em: Tablets e listagens">Maior:</th>
                <td>Largura:</td>
                <td><input type="number" name="post_larger_w" value="<?= post_lg_w() ?>" /></td> 
                <td class="divider"></td>
                <td>Altura:</td>
                <td><input type="number" name="post_larger_h" value="<?= post_lg_h() ?>" /></td>
            </tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Dispositivos móveis, listagens e posts relacionados">Menor:</th>
				<td>Largura:</td>
				<td><input type="number" name="post_minor_w" value="<?= post_md_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="post_minor_h" value="<?= post_md_h() ?>" /></td>
			</tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Componentes como posts recentes e relacionados">Miniatura:</th>
				<td>Largura:</td>
				<td><input type="number" name="post_thumb_w" value="<?= post_sm_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="post_thumb_h" value="<?= post_sm_h() ?>" /></td>
			</tr>
		</table>
		<button class="btn mt10">Salvar alterações</button>
	</form>
	<form id="img-page" class="cn_50" method="POST" action="<?= $formaction('page') ?>">
		<div class="response"></div>
		<table>
		<caption class="fs18">Páginas</caption>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Televisor de tela grande">Grande:</th>
				<td>Largura:</td>
				<td><input type="number" name="page_wide_w" value="<?= page_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="page_wide_h" value="<?= page_h() ?>" /></td>
			</tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Televisor de tela grande">Maior:</th>
				<td>Largura:</td>
				<td><input type="number" name="page_larger_w" value="<?= page_md_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="page_larger_h" value="<?= page_md_h() ?>" /></td>
			</tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Televisor de tela grande">Menor:</th>
				<td>Largura:</td>
				<td><input type="number" name="page_minor_w" value="<?= page_sm_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="page_minor_h" value="<?= page_sm_h() ?>" /></td>
			</tr>
		</table>
		<button class="btn mt10">Salvar alterações</button>
	</form>
	<form id="img-cat" class="cn_50" method="POST" action="<?= $formaction('category') ?>">
		<div class="response"></div>
		<table>
		<caption class="fs18">Categorias</caption>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Listagem e destaque">Plano:</th>
				<td>Largura:</td>
				<td><input type="number" name="cat_w" value="<?= cat_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="cat_h" value="<?= cat_h() ?>" /></td>
			</tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Componente de lista">Miniatura:</th>
				<td>Largura:</td>
				<td><input type="number" name="cat_sm_w" value="<?= cat_sm_w() ?>" /></td> 
				<td class="divider"></td>
				<td>Altura:</td>
				<td><input type="number" name="cat_sm_h" value="<?= cat_sm_h() ?>" /></td>
			</tr>
		</table>
		<button class="btn mt10">Salvar alterações</button>
	</form>
	<form id="img-user" class="cn_50" method="POST" action="<?= $formaction('user') ?>">
		<div class="response"></div>
		<table>
		<caption class="fs18">Usuários<?= file_exists(TEMPLATE_PATH . 'user.php') ? '' : ' <abbr class="fs14" title="O template não tem o arquivo user.php">(não habilitado)</abbr>' ?></caption>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Página de perfil">Perfil:</th>
				<td>Largura e Altura:</td>
				<td><input type="number" name="user_profile" value="<?= user_pic_sz('md') ?>" /></td>
			</tr>
			<tr>
				<th class="cur_help" title="Indicado para uso em: Comentários">Avatar:</th>
				<td>Largura e Altura:</td>
				<td><input type="number" name="user_avatar" value="<?= user_pic_sz('sm') ?>" /></td> 
			</tr>
		</table>
		<button class="btn mt10">Salvar alterações</button>
	</form>
    <form id="img-system" class="cn_50" method="POST" action="<?= $formaction('system') ?>">
        <div class="response"></div>
        <table>
        <caption class="fs18 cur_help" title="Indicado para uso no painel de controle">Sistema</caption>
            <tr>
                <th class="cur_help" title="Tamanho máximo 250">Tamanho único:</th>
                <td>Largura e Altura:</td>
                <td>
                    <input 
                        type="number" name="system_sz" 
                        value="<?= system_image_size() ?>" 
                        max="250" 
                    />
                </td>
            </tr>
        </table>
        <button class="btn mt10">Salvar alteração</button>
    </form>
</div>