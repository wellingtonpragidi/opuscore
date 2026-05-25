<main>

    <?php master_title() ?>

    <article>

        <?php Image::featured([
            'scope' => 'larger', 
            'class' => 'page-image' 
        ]) ?>

        <div class="entry">
            <?php page_content() ?>
        </div>

    </article>

    <?php admin_edit() ?>

</main>
