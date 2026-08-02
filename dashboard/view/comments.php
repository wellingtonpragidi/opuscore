<div class="clean">
    <div class="w50 mb10 floatleft">
        <p class="ml10"><?= Count::selects() ?></p>
    </div>
    <div class="w50 mb10 floatright">
        <form class="search w400 floatright" method="GET" action="<?= URL::current() ?>">
            <input 
                type="search" placeholder="Procurar por registro de comentários" 
                name="q" value="<?= Ensure::attr(URL::GET('q')) ?>" 
            />
            <button class="btn" type="submit">Procurar</button>
        </form>
    </div>
</div>
<?php 


if( INPUT::formSubmitted() ) {
    require dashboard_path( 'controller/comment.php' );
}

?>
<form method="POST" action="<?= URL::current() ?>">
    <table class="cellspace outline">
        <tr>
            <th class="txt_center">
            <span icon="image" size="25"></span></th>
            <th>Comentarista</th><!-- Nome --> <!-- Comentado em: -->
            <th>E-mail</th>
            <th>Comentou em:</th>
            <th>Data</th>
            <th class="txt_center w5">
                <span icon="edit" size="26" title="Editar"></span>
            </th>
            <th class="txt_center w5 approved">
                <span icon="question" size="25" title="Visibilidade pública"></span>
            </th>
            <th class="txt_center w5">
                <span icon="trash" size="26" title="Excluir"></span>
            </th>
        </tr>

        <?php foreach( select_comments() as $show ) : ?>
            <tr>
                <td>
                    <img src="<?= Image::render($show, 'avatar')['show_image'] ?>" alt="" width="38" />
                </td>

                <td>
                    <a href="<?= user_profile_url($show) ?>" target="_blank" rel="noopener">
                        <?= $show->name ?>
                    </a>
                </td>

                <td><?= $show->email ?></td>

                <td>
                    <a href="<?= article_comment_url($show) ?>" target="_blank" rel="noopener">
                        <?= $show->title ?>
                    </a>
                </td>

                <td><?= chronos_format($show->created, 2) ?></td>

                <td class="txt_center">
                    <a href="<?= $show->URL ?>"><span icon="newtab" size="22"></span></a>
                </td>

                <td class="txt_center w5 approved">
                    <?php if( $comment->approved($show) === true ) : ?>

                        <span icon="check" size="25" 
                            class="op06" title="Comentário aprovado">
                        </span>

                    <?php else : ?>

                        <form method="POST" action="<?= URL::current() ?>">
                            <button name="action" value="approved" 
                                class="input_false link" title="Aprovar comentário"
                            >
                                <span icon="check" size="25"></span>
                            </button>
                            <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                        </form>

                    <?php endif; ?>
                </td>

                <td class="txt_center">
                    <form method="POST" action="<?= URL::current() ?>">
                        <button 
                            onclick="javascript: return confirm('Vai mesmo deletar o comentário?')" 
                            class="input_false link delete" 
                            name="action" value="delete"
                        >
                            <span icon="close" size="26"></span>
                        </button>
                        <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
                    </form>
                </td>
            </tr>

        <?php endforeach; ?>

    </table>
    <input type="hidden" name="target_type" value="comment" />
</form>
<?php
$pagination = new Pagination( Count::comments(), per_page('comments') );
echo $pagination->render();