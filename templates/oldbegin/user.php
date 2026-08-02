
<main>
    <h1><?php user_title() ?></h1>

    <div>
        <?php user_image_profile() ?>
    </div>

    <p>
        <?php user_since() ?>
    </p>

    <div>
        <h2><?php user_comment_count() ?></h2>

        <?php 
            user_commented_on([
                'tag'         => 'h2',
                'attrs'       => 'class="commented-on"',
                'date_format' => 2,
            ]);
        ?>
    </div>
    
    <p>
        <?php user_lastupdate('Última atualização: ') ?>
    </p>


    <?php user_form_update() ?>

</main>