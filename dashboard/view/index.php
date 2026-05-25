<?php
if( isset( $_SESSION["admin_redirect"]) ) {
    header( "location: " . $_SESSION["admin_redirect"] ); 
}
unset( $_SESSION["admin_redirect"] );
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow" />
    <?php if( file_exists(UPLOAD_DIR.'favicon/48x48.png') ) : ?>
    <link rel="shortcut icon" href="<?= upload_url('favicon/48x48.png') ?>">
    <?php else : ?>
    <link rel="shortcut icon" href="<?= dash_url('assets/img/favicon.png') ?>">
    <?php endif; ?>
    <title><?php echo $router->title_tag() ?></title>
    <?php if( editor_is('codemirror') ) : ?>
    <!-- CSS base -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
    <!-- CSS autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css" />
    <!-- CSS theme monokai -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.css" />
    <?php endif; ?>
    <style><?php stylesheets() ?></style>
</head>
<body <?php echo $router->body_id() ?>>

    <div id="content">

        <?php require_dashboard(DASH_DIR . 'invoke/views.php') ?>

        <div id="nav">
            <?php require DASH_DIR . 'callable/dashboard-menu.php' ?>
        </div>

        <div id="main" <?php main_attrs() ?>>
            <h1><?php echo $router->master_title() ?></h1>
            <?php $router->includes() ?>
        </div><!-- #main -->

    </div>

    <footer id="footer">
        <?php
        printf(
            '<p class="op05 fs14">CMS %1s Powered by %2s</p>',
            '<a href="'. ENGINE_URL .'" target="_blank">one</a>',
            '<a href="https://webship.com.br" target="_blank">webship</a>'
        );
        ?>
        <div class="version"><p class="op05 fs14"><?= VERSION ?></p></div>
    </footer>

<?php action_footer() ?>
</body>
</html>