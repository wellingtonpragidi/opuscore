<main>

    <?php while( row_exists() ) : show_row(); ?>

        <!-- Voce pode usar master_title() fora do loop se preferir -->
        <h1 id="master-title"><?php title() ?></h1>

        <article>

            <?php featured_image([
                'scope' => 'larger', 
                'class' => 'article-image' 
            ]) ?>

            <div class="entry">
                <?php content() ?>
            </div>

        </article>

        <div id="article-info">
            <div class="meta-meta">
                Por <b><?php author() ?></b> em <time><?php created('d F Y') ?></time>
            </div>

            <p class="article-categories">
                <?php echo '<span>Categorias:</span> ' . article_categories() ?>
            </p>

            <?php admin_edit() ?>
        </div>

    <?php endwhile; 
    
    comment_area([ 'tag' => 'h3' ]); 
    ?>

</main>