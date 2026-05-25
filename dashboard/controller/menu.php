<?php

# Controlador para acoes principais de gerenciamento de menus.
#
# Criar, atualizar e ( exclui menus inteiros )
# ( nao cria, nem atualiza itens individuais de um menu, que sao tratados pelo controlador assincrono ).

INPUT::method_request();

$current_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null);


$action = $_POST['action'] ?? null;

if( $action === 'insert' ) {

    $label = trim($_POST['menu_label'] ?? '');

    if( empty($label) ) {
        alert('warning discard', 'Insira o nome do menu.');
        return;
    }

    $array_name = Sanitize::to_lower_underscore($label);

    $array_data = [
        'title' => $label,
        'key'   => $array_name,
    ];

    if( ArrayExport::apply( $array_name, $array_data, 'menus') ) {
        alert_redirect(
            'success', "Menu <strong>{$label}</strong> adicionado.",
            dash_url("customize/menus/?key={$array_data['key']}"), 3000
        );
    }
    else {
        alert( 'success discard', 
            "Ocorreu algum erro e o menu <strong>{$label}</strong> não foi adicionado.",
        );
    }
}


if( $action === 'update' ) {
    $label = trim($_POST['menu_label'] ?? '');

    if( empty($label) ) {
        alert('warning discard', 'Insira o nome do menu.');
        return;
    }

    $new_var = Sanitize::to_lower_underscore($label);

    $array_data = [
        'title' => $label,
        'key'   => $new_var,
    ];

    # $current_menu = $var = $current_menu
    $updated = Menu::update($current_menu, $new_var, $array_data);

    if( $updated ) {
        alert_redirect(
            'success', "Menu atualizado para <strong>{$label}</strong>.",
            dash_url("customize/menus/?key={$new_var}"), 3000
        );
        exit;
    }
    else {
        alert( 'warning discard', 
            "Ocorreu algum erro e o menu não foi atualizado para <strong>{$label}</strong>.",
        );
        exit;
    }
}


if( $action === 'delete' ) {

    if( Menu::delete($current_menu) ) {
        alert_redirect( 'success', 
            "Menu <strong>{$current_menu}</strong> excluído", dash_url("customize/menus") 
        );

        # remove $_COOKIE last_menu
        setcookie( 'last_menu', '', time() - 3600, '/' );
    }
    else {
        alert( 'warning discard', 
            "Ocorreu um erro e o menu <strong>{$current_menu}</strong> não foi excluído" 
        );
    }
}


# save menu = deleta cache
if( $action === 'delete_cache' ) {

    if( Cache::deleteFile("menus/{$current_menu}.html") ) {
        alert_redirect( 'success', 'Exibição do menu atualizado', URL::current(), 3500 );
    }
    else {
        alert(
            'warning discard',
            sprintf(
                'A exibição do menu é criada quando o <a href="%s" target="_blank" rel="noopener">Site</a> é carregado, após clicar em <a href="%s#save-changes">Atualizar menu</a>.',
                site_url(),
                dash_url("customize/menus/?key={$current_menu}" )
            ),
            URL::current()
        );
    }
}
