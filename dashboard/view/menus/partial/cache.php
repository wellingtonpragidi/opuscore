<?php if( $state['exists'] ) : ?>
    <form id="save-changes" class="mt25" method="POST" action="<?= URL::current() ?>">
        <button class="btn center" name="action" value="delete_cache" />
            Salvar atualizações
        </button>
    </form>

    <div class="guide my40 pt40 fs14 op08 cn_50 ml">
        <p>O menu é atualizado automaticamente a cada edição, mas para que as mudanças apareçam no site é preciso clicar em Atualizar alterações, o que recria a exibição pública.</p>
        <p>Se não vir as mudanças no menu do site, não se preocupe, tudo continua salvo, basta voltar e clicar no botão novamente.</p>
        <p>Esse processo garante o máximo de desempenho na parte pública do seu site.</p>
    </div>

<?php 
endif;

# Exibe mensagem fixa com a quantidade de menus registrado
$qtys = Count::Menus();
$message = match($qtys) {
    0 => "Este site não tem nenhum menu de navegação registrado",
    1 => "Este site contém 1 menu de navegação registrado",
    default => "Este site contém {$qtys} menus de navegação registrados"
};
echo "<div class=\"mt40 pt40 txt_right op07 fs13\">{$message}</div><hr>";