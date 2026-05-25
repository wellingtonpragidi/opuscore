<?php
switch( URL::Get('act') ) :
    case 'login':
        $title =  'Acessar';
    break;
    case 'lost-password':
        $title =  'Recuperar senha';
    break;
    case 'reset-password':
        $title =  'Redefinir senha';
    break;
    case 'activation':
        $title =  'Ativação';
    break;
    default:
        $title =  'Acessar';
    break;
endswitch
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex, nofollow" />
<title><?= $title . ' – ' . site_title() ?></title>
<style><?php require get_dashboard_path('access/assets/css/style.css') ?></style>
</head>
<body>
<div id="logotipo">
    <a href="https://opuscore.dev">
        <img src="<?= dash_url('access/assets/img/opuscore.svg') ?>" alt="opuscore" />
    </a>
</div>
