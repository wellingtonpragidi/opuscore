<form method="POST" action="<?= URL::current() ?>" enctype="multipart/form-data">
	<?php 

	if( INPUT::formSubmitted() ) {
        require_annex('deps/sanitize-validate.php');
		require dashboard_path('controller/context.php');
	}

	?>
	<div id="update-context" class="update">
		<?php foreach( Context::select() as $show ) : ?>
		<article class="flexbox">

			<div class="cn_70 pr15">
				<label for="section" class="fs12 it">Seção</label>
				<input 
                    id="section" class="lg disabled" type="text" 
                    name="section" value="<?= $show->context->section ?>" 
                    readonly title="A Seção é definida na criação do contexto" 
                />

				<label for="title" class="mt20 fs12 it">Título</label>
				<input 
                    id="title" class="xlg disabled" type="text" 
                    name="title" value="<?= $show->context->title ?>" 
                    readonly title="O Título é definido na criação do contexto" 
                />

                <p class="txt_center color-danger">
                    A <strong>Secão</strong> e o <strong>Título</strong> fazem parte da identificação estrutural do contexto e só podem ser definidos na criação.
                </p>
			</div>

			<aside class="cn_30 pr15 mt15">
		        <button type="submit" class="btn xlg mx mt5 w200 h50" name="action" value="update">
                    ATUALIZAR
                </button>

                <section class="info txt_right mt15">
                    <!-- <label for="basename" class="fs14">Chave de Exibição da Seção</label>
                    <input 
                        class="input_false link txt_right ha pt0 mt0 mb15"
                        type="text" name="basename" id="basename" 
                        value="<?php // $show->context->basename ?>" 
                        readonly 
                    />
                    <hr> -->
                    <label for="name" class="fs14">Chave de Exibição do contexto</label>
                    <input 
                        class="input_false link txt_right ha pt0 mt0 mb5"
                        type="text" name="name" id="name" 
                        value="<?= $show->context->name ?>" 
                        readonly 
                    />
                    <!-- <div>
                        <b>ID: </b> 
                        <span class="color-link fs17"><?php // $show->context->ID ?></span>
                    </div>  -->
                </section>
                    <hr>
                <button 
                    onclick="javascript: return confirm(`Vai mesmo deletar este contexto?`)" 
                    class="input_false link delete txt_right right mr0 ml mt10" 
                    type="submit" name="action" value="delete">
                    Excluir contexto
                </button>
		    </aside>

		    <div class="cn_100 mt40">
                
				<?php render_editor( 'context', $show->context->value ) ?>

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

		    </div>

		</article>
		
	<?php endforeach; ?>
	</div>
</form>