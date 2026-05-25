<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php 
	if( INPUT::formSubmitted() ) {
        require_callable('sanitize-validate.php');
		require get_dashboard_path('controller/context.php');
	}
	?>
	<div id="update-context" class="update">
		<?php foreach( Context::mapper() as $show ) : ?>
		<article class="flexbox">

			<div class="cn_75 pr15">
				<label for="section" class="fs12 it">Seção</label>
				<input 
                    id="section" class="lg" type="text" 
                    name="section" value="<?= $show->section ?>" 
                    readonly title="A Seção é definida na criação do contexto" 
                />

				<label for="title" class="mt20 fs12 it">Título</label>
				<input 
                    id="title" class="lg" type="text" 
                    name="title" value="<?= $show->title ?>" 
                    readonly title="O Título é definido na criação do contexto" 
                />

                <p class="txt_center color-danger">
                    A <strong>Secão</strong> e o <strong>Título</strong> fazem parte da identificação estrutural do contexto e só podem ser definidos na criação.
                </p>
			</div>

			<aside class="cn_25 pr15">
		        <button type="submit" class="btn xlg mt20 mb15 ml15 w100" name="action" value="update">ATUALIZAR</button>

                <section class="info txt_right">
                    <label for="basename">Identificador da Seção</label>
                    <input 
                        class="input_false link txt_right ha pt0 mt0 mb15"
                        type="text" name="basename" id="basename" 
                        value="<?= $show->dynamo['basename'] ?>" 
                        readonly 
                    />
                    <label for="name">Identificador do Contexto<br><small>Chave de Exibição</small></label>
                    <input 
                        class="input_false link txt_right ha pt0 mt0 mb15"
                        type="text" name="name" id="name" 
                        value="<?= $show->name ?>" 
                        readonly 
                    />
                </section>

                <button 
                    onclick="javascript: return confirm(`Vai mesmo deletar este contexto?`)" 
                    class="input_false link delete txt_right right mr0 ml mt10" 
                    type="submit" name="action" value="delete">
                    Excluir contexto
                </button>
		    </aside>

		    <div class="cn_100 mt40">
				<?php // render_editor( $show )
                new Punk;
                $content = $_POST['content'] ?? $show->value ?? null;
                echo '<textarea id="editor" name="content">' . $content . '</textarea>'; 
                ?>

                <input type="hidden" id="target_type" name="target_type" value="context" />
                <input type="hidden" id="target_id" name="target_id" value="<?= $show->name ?>" />
		    </div>

		</article>
		
	<?php endforeach; ?>
	</div>
</form>