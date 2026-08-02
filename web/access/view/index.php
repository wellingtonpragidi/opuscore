<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php title_router() ?></title>
<style><?php stylesheets() ?></style>
</head>
<body>
    <div id="logo">
        <a href="<?= URL::root() ?>" target="_blank" rel="noopener noreferrer nofollow">
            <img src="<?= dist_img_url('opuscore-logo-v.svg') ?>" alt="opuscore" />
        </a>
    </div>


    <div id="wrapper">

        <?php view_router() ?>

    </div>
    

<script src="<?= dist_js() ?>"></script>
<script>
OpusCore.dist.passwordToggle();

if( document.getElementById('btn-pswd-generator') ) {
    OpusCore.dist.passwordGenerator({
        limit:  12,
        input:  'pswd'
    });
}
</script>
<script src="<?= URL::root('web/access/assets/basic.js') ?>"></script>
</body>
</html>