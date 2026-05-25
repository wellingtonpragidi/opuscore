<?php 
/**
 * `index.php` eh o arquivo dedicado a listagem de todos os Posts
 * 
 * Se queres arquivos dedicados as listagens, crie: 
 * `category.php` Posts por Categoria
 * `search.php` Posts por Busca
 * 
 * Porem como esse template usa o mesmo padrao visual, e tambem para simplificar:
 * Na falta de arquivos dedicados a rota usa `index.php` como fallback
 */

    # padroes das funcoes *_title() : tag <h1> e seletor id=master-title 
    master_title( [ 'title' => 'Últimas Notícias' ] );

    search_title();

    category_title([ 'prefix' => '<span class="category-prefix">Notícias:</span> ' ]);


    category_description(); # seletor padrao id=category-description
    # para exibir imagem destacada de uma categoria, 
    # use o metodo Image::featured() passando o argumento ['scope' => 'plain']
?>

<small id="post-count">
    <?php 
    post_count([
        'singular'   => 'noticia',
        'plural'     => 'noticias',
        'no_results' => 'Nenhum noticia publicada.'
    ]);
    ?>  
</small>
<main>

    <?php while( row_exists() ) : show_row(); ?>

        <article>

            <a href="<?php permalink() ?>" title="<?= escattr(get_title()) ?>">

                <?php featured_image( [ 'scope' => 'minor' ] ) ?>

                <h2><?php title() ?></h2>

            </a>

            <time><?php created('d \d\e M') ?></time>

            <div class="summary">
                <?php summary() ?>
            </div>

        </article>

    <?php endwhile ?>
    
    <?php posts_paginator() ?>

</main>

<aside>

    <section id="list-categories">
        <h2>Tópicos</h2>
        <?php 
        list_categories([
            'thumbnail' => true,
            'attrs' => ['class' => 'unlist'] 
        ]); 
        ?>
    </section>

    <section id="list-posts-recents">
        <h2>Posts Recentes</h2>
        <?php  
        list_posts_recents([
           'list_class'      => 'unlist',
           'item_title_tag'  => 'h3'
        ]);
        ?>
    </section>

</aside>