<main>

    <?php while( row_exists() ) : show_row(); ?>

        <!-- Voce pode usar master_title() fora do loop se preferir -->
        <h1 id="master-title"><?php title() ?></h1>

        <article>

            <?php featured_image([
                'scope' => 'larger', 
                'class' => 'post-image' 
            ]) ?>

            <div class="entry">
                <?php content() ?>
            </div>

        </article>

        <div id="post-info">
            <div class="meta-meta">
                Por <b><?php author() ?></b> em <time><?php created('d F Y') ?></time>
            </div>

            <p class="post-categories">
                <?php echo '<span>Categorias:</span> ' . post_categories() ?>
            </p>

            <?php admin_edit() ?>
        </div>

    <?php endwhile; ?>

</main>