<?php 
if( INPUT::formSubmitted() ) {
    require get_dashboard_path('controller/comment.php');
}
?>
<form id="comment" class="w70 ml20" method="POST" action="<?= URL::current() ?>">
	<?php foreach( comment_select() as $show ) : ?>
	<div class="flexbox">
		<div class="cn_30 mt10">
            <img src="<?= Image::render($show, 'profile')['show_image'] ?>" alt="" />
        </div>
		<div class="cn_70 mt15">
			<input type="text" name="name" value="<?= $show->name ?>" disabled />
		</div>
		<div class="cn_30 mt20">
			<label>Email:</label>
		</div>
		<div class="cn_70 mt15">
			<input type="text" name="email" value="<?= $show->email ?>" disabled />
		</div>
		<div class="cn_30 mt20">
			<label>Data:</label>
		</div>
		<div class="cn_70 mt15">
			<input type="text" name="date" value="<?= chronos_format($show->date, 2) ?>" disabled />
		</div>
	</div>
	<div class="mx mt25">
		<label>Comentário:</label><br>
		<textarea class="lg" name="content"><?= $show->content ?></textarea>
		<div class="flexbox">
			<p class="cn_60">Comentado em: <a href="<?= site_url($show->related) ?>" target="_blank"><?= site_url($show->related) ?></a></p>
			<p class="cn_40 txt_right">
				<?php if( $comment->approved($show->ID) === 0 ) : ?>
	                <button class="input_false link sm" name="action" value="approve">
                        Marcar como lido
                    </button>
	            <?php else : ?>
	            <span icon="check" size="25" class="op06" title="Comentário aprovado"></span>
	            <?php endif; ?>
	        </p>
        </div>
		<button type="submit" name="action" value="update" class="btn lg mt30">Atualizar comentário</button>

		<button 
            onclick="javascript: return confirm('Vai mesmo deletar o comentário?')" 
            class="input_false link delete txt_right mr0 ml mt30" 
            name="action" value="delete">
            Excluir comentário
        </button>
	</div>
    <input type="hidden" name="target_id" value="<?= $show->ID ?>" />
    <input type="hidden" id="target_type" name="target_type" value="comment" />
	<?php endforeach; ?>
</form>