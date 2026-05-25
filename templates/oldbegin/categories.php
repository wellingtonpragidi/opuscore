<?php 
/**
 * `categories.php` é o arquivo dedicado a listagem de Categorias, e nao Posts por Categoria
 * O arquivo dedicado a listagem de Posts por Categoria eh `category.php`
 */

    # O argumento passado ao valor da chave 'title' sobrescreve o titulo padrao da rota
    # Se preferir, o mesmo pode ser feito por Hook::append_filter('categories_title')
    master_title( [ 'title' => 'Tópicos' ] );
?>

<small id="cats-count"><?= Category::count() ?> categorias</small>

<main>

    <?php foreach( select_category() as $cat ) : ?>

        <article>
            <a href="<?= cat_url($cat) ?>">

                <?= cat_image($cat, 3) ?>

                <h2><?= $cat->name ?></h2>

            </a>

            <div class="summary">
                <?= cat_summary($cat, 210) ?> 
            </div>

        </article>

    <?php endforeach; ?>

</main>

<aside>
    <section id="list-posts-recents">
        <h2>Posts Recentes</h2>
        <?php 
        list_posts_recents([
           'list_class'     => 'unlist',
           'item_title_tag' => 'h3'
        ]);
        ?>
    </section>
</aside>