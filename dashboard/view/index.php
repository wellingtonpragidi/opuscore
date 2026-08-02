<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <?php if( file_exists(UPLOAD_DIR . 'favicon/48x48.png') ) : ?>
    <link rel="shortcut icon" href="<?= upload_url('favicon/48x48.png') ?>">
    <?php else : ?>
    <link rel="shortcut icon" href="<?= dash_url('assets/img/favicon.png') ?>">
    <?php endif; ?>
    <title><?php echo $router->title_tag() ?></title>
    <style><?php stylesheets() ?></style>
</head>
<body <?= $router->body_id() ?>>

    <div id="content">

        <div id="nav">
        <?php 
            annex_class('NavMenu');
            echo (new NavMenu)->list();
        ?>
        </div>

        <div id="main" <?php main_attrs() ?>>
            <h1><?php echo $router->master_title() ?></h1>

            <?php $router->requires() ?>
        </div>

    </div>

    <footer id="footer">
        <?php
        printf(
            '<p class="op05 fs14">Gerenciador web/CMS %1s Um projeto de %2s</p>',
            
            '<a href="' . ENGINE_URL . '" target="_blank" rel="noopener">opuscore</a>',
            '<a href="https://webship.com.br" target="_blank" rel="noopener">webship</a>'
        );
        ?>
        <p class="op05 fs14">
            Gerenciador web/CMS 
            <a href="<?= ENGINE_URL ?>" target="_blank" rel="noopener">opuscore</a> 
            Um projeto de 
            <a href="https://webship.com.br" target="_blank" rel="noopener">webship</a>
        </p>

        <div class="version"><p class="op05 fs14"><?= VERSION ?></p></div>

    </footer>


<?php 
annex_class('Assets');
echo (new Assets)
    ->scripts()
    ->resolve()
    ->dispatcher()
; ?>

</body>
</html>