<?php
function access_router(): array {
    $action = $_GET['action'] ?? null;

    return match( $action ) {
        'login' => [
            'filename' => 'login.php', 
            'title'    => 'Login'
        ], 
        'register' => [
            'filename' => 'register.php', 
            'title'    => 'Criar conta'
        ], 
        'activate' => [
            'filename' => 'activate.php', 
            'title'    => 'Ativar conta',
            'view-validate' => 'view/partial/validates.php'
        ], 
        'lost-password' => [
            'filename'  => 'lost.php', 
            'title'     => 'Recuperar senha'
        ], 
        'reset-password' => [
            'filename' => 'reset.php', 
            'title'    => 'Redefinir senha',
            'view-validate' => 'view/partial/validates.php'
        ], 
        'logout' => [
            'filename' => 'logout.php', 
            'title'    => 'Logout'
        ],
        default => header_location('access/?action=login')
    };
}


URL::normalize();



function view_router(): void {
    $route = access_router();

    extract( Container::scope(), EXTR_SKIP );


    if( INPUT::formSubmitted() ) {
        require access_path( 'controller/' . $route['filename'] );
    }

    if( isset($route['view-validate']) ) {
        $show_form = true;
        require access_path( $route['view-validate'] );
    }

    require access_path( 'view/' . $route['filename'] );
}


function title_router(): void {
    $route = access_router();

    echo $route['title'] . ' – ' . site_title();
}



function stylesheets() {
    ob_start('compress_CSS');

    require access_path('assets/style.css');

    ob_end_flush();
}



function access_shell(): void {
    require access_path('view/index.php');
}