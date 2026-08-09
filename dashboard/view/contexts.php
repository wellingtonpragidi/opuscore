<?php 
if( INPUT::formSubmitted() ) {
    require_annex('deps/sanitize-validate.php');
    require dashboard_path('controller/context.php');
}

$insert = dash_url('contexts/insert');

if( count( get_contexts() ) === 0 ) :

    echo <<<HTML
    <div class="mt40 mb25 ml10">
        <p>Nenhum contexto registrado</p>
        <a href="$insert">Adicionar contexto</a>
    </div>
    HTML;

else :
?>

<div class="mt40 mb25 ml10">
    <?= Context::show_record() ?>
</div>
<table>
	<tr>
        <th>Título</th>
		<th>Seção</th>
        <th>Chave de Exibição</th>
		<th>Chave de Exibição da Seção</th>
        <th class="w5"><span icon="trash" size="25"></span></th>
	</tr>

	<?php foreach( Context::select() as $show ) : ?>
	<tr>
        <td class=" fs19">
            <a href="<?= dash_url('contexts/update/?id=' . $show->context->ID) ?>">
                <?= $show->context->title ?>
            </a>
        </td>
		<td class="fs19">
            <a href="<?= dash_url('contexts/section/?key=' . $show->context->basename) ?>">
                <?= $show->context->section ?>
            </a>
        </td>

        <td class="ftmono"><?= $show->context->name ?></td>

		<td class="ftmono"><?= $show->context->basename ?></td>

        <td class="txt_center">
            <form method="POST" action="<?= URL::current() ?>">
                <button 
                    class="input_false link delete"
                    onclick="javascript: return confirm(`Confirma a exclusão deste contexto? Essa operação é permanente.`)"  
                    name="action" value="delete">
                    <span icon="close" size="28"></span>
                </button>

                <input 
                    type="hidden" id="target_type" name="target_type" value="context" 
                />

                <input 
                    type="hidden" id="target_id" name="target_id" 
                    value="<?= $show->context->ID ?>" 
                />

                <input 
                    type="hidden" id="basename" name="basename" 
                    value="<?= $show->context->basename ?>" 
                />

                <input type="hidden" name="name" value="<?= $show->context->name ?>" />
            </form>
        </td>

	</tr>
	<?php endforeach; ?>
</table>


<?php endif;