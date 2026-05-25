<?php
if( ! is_admin() ) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php head() ?>
</head>
<body <?php html_class('ob-') ?>>
<a href="#content" class="sr">Ir para o conteúdo principal</a>

<header><!-- um unico elemento header por documento -->

    <nav id="navbar">

    	<?php 
        site_logo([ 
            'width'  => 480, 
            'height' => 115 
        ]);
        ?>

    	<div class="toggle">
            <button class="switch" type="button" aria-label="Alternar navegação"></button>
        </div>
    	<?php 
        Menu::list([ 
            'name' => 'main_menu' 
        ]);
        ?>

    </nav>
    
    <?php search_form() ?>

</header>

<div id="content">