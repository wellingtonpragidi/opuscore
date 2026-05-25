<?php
$requires = WEB_DIR . 'invoke/';


if( is_categories() ) {

    require $requires . 'categories.php';
}


if( is_page() ) {

    require $requires . 'page.php';
}