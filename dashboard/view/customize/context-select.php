<?php 
if( URL::param(1) !== 'context' ) {
    header( "Location: " . dash_url('customize/context'), true, 404 );
    exit;
}
    
if( INPUT::formSubmitted() ) {
    require_callable('sanitize-validate.php');
    require get_dashboard_path('controller/context.php');
}

echo Context::show_record();

$insert = dash_url('customize/context/?insert=1');

if( Count::contexts() === 0 ) :
    echo <<<HTML
    <p class="fs21">
        <a href="$insert">Adicionar contexto</a>
    </p>
    HTML;
else :
?>
<div class="mb25">
    <a class="btn" href="<?= $insert ?>">Adicionar contexto</a>
</div>
<table>
	<tr>
        <th>Título</th>
		<th>Seção</th>
        <th>Chave de Exibição</th>
		<th>Identificador da Seção</th>
        <th class="w5"><span icon="trash" size="25"></span></th>
	</tr>
	<?php foreach( Context::mapper() as $show ) : ?>
    	<tr>
            <td class=" fs19">
                <a href="<?= dash_url('customize/context/?update=' . $show->name) ?>">
                    <?= $show->title ?>
                </a>
            </td>
    		<td class="fs19">
                <a href="<?= dash_url('customize/context/?section=' . $show->dynamo['basename']) ?>">
                    <?= $show->section ?>
                </a>
            </td>

            <td class="ftmono"><?= $show->name ?></td>

    		<td class="ftmono"><?= $show->dynamo['basename'] ?></td>

            <td class="txt_center">
                <form method="POST" action="<?= URL::current() ?>">
                    <button 
                        class="input_false link delete"
                        onclick="javascript: return confirm(`Confirma a exclusão deste contexto? Essa operação é permanente.`)"  
                        name="action" value="delete">
                        <span icon="close" size="27"></span>
                    </button>
                    <input type="hidden" id="target_type" name="target_type" value="context" />
                    <input type="hidden" id="target_id" name="target_id" value="<?= $show->name ?>" />
                    <input type="hidden" name="target_basename" value="<?= $show->dynamo['basename'] ?>" />
                </form>
            </td>
    	</tr>
	<?php endforeach; ?>
</table>

<?php endif;