<?php 
if( INPUT::formSubmitted() ) {
    require_annex('deps/sanitize-validate.php');
    require dashboard_path('controller/context.php');
}


$select = Context::select_sections();

$count  = count($select);
$insert = dash_url('contexts/insert');

if( $count === 0 ) :

    echo <<<HTML
    <div class="mt40 mb25 ml10">
        <p>Nenhum contexto registrado nesta seção</p>
        <a href="$insert">Adicionar contexto</a>
    </div>
    HTML;

else :
?>

<div class="mt40 mb25 ml10">
    <?= ($count === 1) ? '1 Contexto' : "{$count} Contextos" ?> nesta seção.
</div>
<table>
    <tr>
        <th>Título</th>
        <th>Seção</th>
        <th>Chave de Exibição</th>
        <th>Identificador da Seção</th>
        <th class="w5"><span icon="trash" size="25"></span></th>
    </tr>

    <?php foreach( $select as $show ) : ?>
    <tr>
        <td class=" fs19">
            <a href="<?= dash_url('contexts/update/?id=' . $show->context->ID) ?>">
                <?= $show->context->title ?>
            </a>
        </td>
        <td class="fs19"><?= $show->context->section ?></td>

        <td class="ftmono"><?= $show->context->name ?></td>

        <td class="ftmono"><?= $show->context->basename ?></td>

        <td class="txt_center">
            <form method="POST" action="<?= URL::current() ?>">
                <button 
                    class="input_false link delete"
                    onclick="javascript: return confirm(`Confirma a exclusão deste contexto? Essa operação é permanente.`)"  
                    name="action" value="delete">
                    <span icon="close" size="27"></span>
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