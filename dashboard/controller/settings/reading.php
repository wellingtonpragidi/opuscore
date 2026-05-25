<?php
INPUT::method_request();

$action = $_POST['form_action'] ?? null;

if( $action === 'public' ) {
    $data['posts_per_page'] = (int) ($_POST['posts_perpage'] ?? 6);

    if( ArrayExport::apply('reading', ['posts_per_page' => $data['posts_per_page']], 'settings') ) {

        alert_redirect( 'success', 'Configurações de leitura pública atualizadas!', URL::current() );
    }
    else {

        alert( 'warning', 'Falha ao atualizar configurações!' );
    }
}


if( $action === 'admin' ) {
    $dashboard_data = [
        'reading' => [
            'pages_per_page'       => (int) ($_POST['pages_perpage'] ?? 20), 
            'posts_per_page'       => (int) ($_POST['posts_perpage'] ?? 40),
            'comments_per_page'    => (int) ($_POST['comments_perpage'] ?? 40),
            'users_per_page'       => (int) ($_POST['users_perpage'] ?? 25)
        ]
    ];

    # So adiciona esses ao array se existir input no form, os mesmos sao condicionais
    if( isset($_POST['statistics_perpage']) ) {
        $dashboard_data['reading']['statistics_per_page'] = (int) $_POST['statistics_perpage'] ?? 100;
    }

    if( isset($_POST['mediapage_perload']) ) {
        $dashboard_data['reading']['page_media_per_load'] = (int) $_POST['mediapage_perload'] ?? 35;
    }
    if( isset($_POST['mediapopup_perload']) ) {
        $dashboard_data['reading']['popup_media_per_load'] = (int) $_POST['mediapopup_perload'] ?? 20;
    }


    if( ArrayExport::apply('dashboard', $dashboard_data, 'settings') ) {

        alert_redirect( 'success', 'Configurações de leitura do painel atualizadas!', URL::current() );
    }
    else {

        alert( 'warning', 'Falha ao atualizar configurações!' );
    }
}