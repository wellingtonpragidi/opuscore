<?php
$dashmenu = new DashboardMenu();
$menu = $dashmenu->menu();

echo '<ul>';
foreach( $menu as $item ) {
    $title  = $item['title'] ?? '';
    $href   = $item['href'] ?? '#';
    $attrs  = $item['attrs'] ?? '';
    $bullet = $item['bullet'] ?? '';
    $icon   = $item['icon'] ?? '';
    $isub   = $item['submenu'] ?? [];

    $hasub = ! empty( $isub );
    echo '<li' . ( $hasub ? ' class="hasub"' : '' ) . '>';
    echo '<a href="' . $href . '" ' . $attrs . '>' . $title . ' ' . $icon . $bullet . '</a>';

    if( $hasub ) {
        echo '<ul class="isub">';
        foreach( $isub as $sub ) {
            $stitle = $sub['title'] ?? '';
            $shref  = $sub['href'] ?? '#';
            $sr     = ( isset($sub['silent']) && $sub['silent'] ) ? ' class="sr"' : '';
            echo "<li{$sr}><a href=\"{$shref}\">". ( $sr ? '&nbsp;' : $stitle ) .'</a></li>';
        }
        echo '</ul>';
    }

    echo '</li>';
}
echo '<ul>';