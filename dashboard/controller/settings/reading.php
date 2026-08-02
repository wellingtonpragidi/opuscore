<?php
INPUT::method_request();

$action = $_POST['form_action'] ?? null;

if( $action === 'public' ) {
    $limit_data = [ 'articles_per_page' => (int) ($_POST['articles_per_page'] ?? 6) ];

    if( ArrayExport::apply('reading', $limit_data, 'settings') ) {

        alert_redirect( 'success', 'Configurações de leitura pública atualizadas!', URL::current() );
    }
    else {

        alert( 'warning', 'Falha ao atualizar configurações!' );
    }
}


if( $action === 'admin' ) {
    $limit_data = [
        'reading' => [
            'pages_per_page'    => (int) ($_POST['pages_per_page'] ?? 20), 
            'articles_per_page'    => (int) ($_POST['articles_per_page'] ?? 40),
            'comments_per_page' => (int) ($_POST['comments_per_page'] ?? 40),
            'users_per_page'    => (int) ($_POST['users_per_page'] ?? 25)
        ]
    ];

    # So adiciona esses ao array se existir input no form, os mesmos sao condicionais
    if( isset($_POST['statistics_per_page']) ) {
        $limit_data['reading']['statistics_per_page'] = (int) ($_POST['statistics_per_page'] ?? 100);
    }

    if( isset($_POST['media_manager_perload']) ) {
        $limit_data['reading']['media_manager_perload'] = (int) ($_POST['media_manager_perload'] ?? 35);
    }
    if( isset($_POST['media_popup_perload']) ) {
        $limit_data['reading']['media_popup_perload'] = (int) ($_POST['media_popup_perload'] ?? 20);
    }


    if( ArrayExport::apply('dashboard', $limit_data, 'settings') ) {

        alert_redirect( 'success', 
            'Configurações de leitura do painel atualizadas!', URL::current() );
    }
    else {

        alert( 'warning', 'Falha ao atualizar configurações!' );
    }
}