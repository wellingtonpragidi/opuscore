<?php
if( URL::has('key') ) {
    setcookie('last_menu', URL::GET('key'), [
        'expires' => time() + (180 * 24 * 60 * 60),
        'path' => '/',
    ]);
}

$menus = Menu::load();


$state = [ 
    'current' => $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null)
];


$state['has_menus'] = ! empty($menus);
$state['exists']    = $state['current'] && isset($menus[$state['current']]);

$state['title'] = $state['exists'] ? ($menus[$state['current']]['title'] ?? '') : '';



$state['menu_has_item'] = Menu::items_exists($state['current']);

// $state['tree'] = $state['exists'] ? Menu::render_menu_tree($state['current']) : '';

$selected = fn($slug) => ($slug === $state['current']) ? ' selected' : '';


$view = [
    'pages'      => Menu::list_page_items(),
    'categories' => Menu::list_category_items(),
    'menu'       => $state['title'],
    'tree'       => Menu::render_menu_tree($state['current']),
];

