<?php 
if( INPUT::formSubmitted() ) {
    require dashboard_path('controller/comment.php');
}

?>
<form id="comment" class="w70 ml20" method="POST" action="<?= URL::current() ?>">

	<?php foreach( select_comments() as $show ) : ?>

    	<div class="flexbox">
    		<div class="cn_30 mt10">
                <img src="<?= Image::render($show, 'profile')['show_image'] ?>" alt="" />
            </div>
    		<div class="cn_70 mt15">
    			<input type="text" name="name" value="<?= $show->name ?>" />
    		</div>
    		<div class="cn_30 mt20">
    			<label>Email:</label>
    		</div>
    		<div class="cn_70 mt15">
    			<input type="text" name="email" value="<?= $show->email ?>" />
    		</div>
    		<div class="cn_30 mt20">
    			<label>Data:</label>
    		</div>
    		<div class="cn_70 mt15">
    			<input type="text" name="date" value="<?= chronos_format($show->created, 2) ?>" />
    		</div>
    	</div>

    	<div class="mx mt25">

    		<label>Comentário:</label><br>
    		<textarea class="lg" name="content"><?= $show->content ?></textarea>

    		<div class="flexbox">
    			<div class="cn_60">
                    Comentado em: <a href="<?= article_comment_url($show) ?>" 
                        target="_blank"><?= $show->title ?></a>
                </div>
    			<div class="cn_40 txt_right">
    				<?php if( $comment->approved($show) === true ) : ?>

                        <span icon="check" size="25" 
                            class="op06" title="Comentário aprovado">
                        </span>

    	            <?php else : ?>

                        <button class="input_false link sm" name="action" value="approved">
                            Marcar como lido
                        </button>

    	            <?php endif; ?>
    	        </div>
            </div>

    		<button type="submit" name="action" value="update" class="btn lg mt30">
                Atualizar comentário
            </button>

    		<button 
                onclick="javascript: return confirm('Vai mesmo deletar o comentário?')" 
                class="input_false link delete txt_right mr0 ml mt30" 
                name="action" value="delete"
            >
                Excluir comentário
            </button>
    	</div>

        <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
        <input type="hidden" id="target_type" name="target_type" value="comment" />

	<?php endforeach; ?>

</form>