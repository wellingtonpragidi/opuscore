<?php
$display_errors = DISPLAY_ERRORS ? 'true' : 'false';
$has_upgrade    = Upgrade::has() ? 'true' : 'false';

$menu_name = $_GET['key'] ?? $_COOKIE['last_menu'] ?? null;
$current_menu_name = (is_menus() && $menu_name !== null) 
    ? 'current_menu: "' . $menu_name . '",' 
    : null;

$assets = new Assets;
echo $assets->block_script('
    window.OpusCore = window.OpusCore || {};
    window.OpusCore.config = {
        base_url:    "' . URL::root() . '",
        current_url: "' . URL::current() . '",
        display_log: ' . $display_errors . ',
        limit: {
            media:  ' . media_manager_limit() . ',
            editor: ' . media_popup_limit() . ',
        },
        has_upgrade: ' . $has_upgrade . ',
        ' . $current_menu_name . '
    };
');