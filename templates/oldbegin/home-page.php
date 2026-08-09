<main>
    <section id="intro">
        <?php
        $pages = $pages = pages_find( 'home-page' );
        foreach( $pages as $page ) : ?>

            <article>
                <!-- <h2><?php // echo $page->title ?></h2> -->

                <div class="image">
                    <img 
                        src="<?= upload_url($page->attachment->larger->path ?? '') ?>" 
                        alt="<?= escattr($page->title) ?>" 
                        width="<?= $page->attachment->larger->width ?? '' ?>" 
                        height="<?= $page->attachment->larger->height ?? '' ?>" 
                    />
                </div>
                
                <div class="summary">
                    <?php echo 
                    word_summary(
                        $page->content, 58, [
                            'url'  => site_url('sobre'),
                            'text' => '<br>Ler conteúdo completo'
                        ]
                    ); 
                    ?>
                </div>
            </article>

        <?php endforeach; ?>
    </section>

    <section id="news">
        <article>
            <h2>Artigos Recentes</h2>
            <?php posts_recents() ?>
        </article>

        <aside>
            <h2>Categorias</h2>
            <?php 
                list_categories([
                    'thumbnail' => true,
                    'attrs' => ['class' => 'unlist'] 
                ]); 
            ?>
        </aside>
    </section>

</main>