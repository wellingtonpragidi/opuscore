<?php
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}
# controller/async/menu.php - Controlador para requisicoes assincronas (JS) do gerenciador de menus.
#
# Este arquivo processa operacoes relacionadas a itens de `menus`, como reordenacao, adicao,
# edicao e exclusao, respondendo com dados em formato JSON.


# INPUT::method_request() nao pode ser usado aqui pois existe requisicoes que sao GET
if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

    # Decodifica o JSON do corpo da requisicao (usado para dados raw, como drag-and-drop).
    $json = json_decode( file_get_contents('php://input'), true );

    # Acao: reordenar e atualizar hierarquia de itens de menu.
    if( isset($json['action']) && $json['action'] === 'reorder_menu_items' ) {

        if( ! empty($json['items']) && is_array($json['items']) ) {

            Menu::update_order_and_hierarchy( $json['items'] );

            json_response( true );
        }
        else {
            json_response( false, 'Estrutura invalida' );
        }
        exit;
    }


    # Acao: adicionar varios itens de menu (bulk) a partir de paginas/categorias.
    if( $_POST['action'] === 'add_bulk_items' ) {
        $menu_name = $_POST['menu_name'] ?? ($_COOKIE['last_menu'] ?? null);
        $items_raw = $_POST['items'] ?? '[]';
        $items = json_decode( $items_raw, true );

        if( empty($menu_name) || ! is_array($items) || empty($items) ) {
            json_response( false, 'Nenhum item selecionado ou entrada inválida' );
        }

        $inserted = Menu::add_checked_items( $menu_name, $items );

        $message = ($inserted === 0) 
            ? "Nenhum item adicionado | {$items_raw} |" 
            : (
                ($inserted === 1) 
                    ? " 1 item adicionado" : " {$inserted} itens adicionados"
            );

        json_response( 
            ($inserted === 0) ? false : true, 
            $message,
        );
    }

        /*if( Menu::add_checked_items( $menu_name, $items ) === false ) {
            json_response( false, $items_raw );
        }*/

    # Acao: salvar um item de menu personalizado (custom).
    if( $_POST['action'] === 'add_item_custom' ) {
        $menu_name = $_POST['menu_name'] ?? ($_COOKIE['last_menu'] ?? null);
        $label     = $_POST['custom_label'] ?? '';
        $url       = $_POST['custom_url'] ?? '#';

        if( empty($label) ) {
            echo '<span class="txt_warning">Campo de texto vazio.</span>';
            return;
        }

        $sort = Menu::next_sort( $menu_name );

        $inserted = Menu::add_item_custom([$menu_name, 'custom' , $label, $url, $sort]);

        if( $inserted ) {
            echo '<span class="txt_success">Item de menu adicionado.</span>';
        }
        else {
            echo '<span class="txt_error">Ocorreu um erro ao adicionar o item.</span>';
        }
        exit;
    }


    # Acao: salvar um item de menu usuario (Login/Registro--User::name)
    if( $_POST['action'] === 'add_item_auth' ) {
        $menu_name = $_POST['menu_name'] ?? ( $_COOKIE['last_menu'] ?? null );
        $label     = $_POST['auth_label'] ?: 'Login/Registro';

        $inserted = Menu::add_item_auth( $menu_name, $label );

        if( $inserted ) {
            echo 'Item de menu adicionado.';
        }
        else {
            echo 'Ocorreu um erro ao adicionar o item.';
        }
        exit;
    }


    # Acao: editar um item de menu existente (label e URL).
    if( $_POST['action'] === 'update_menu_item' ) {
        $id = (int) ( $_POST['edit_id'] ?? 0 );
        
        if( ! $id ) {
            json_response( false, 'ID invalido' );
        }
        $data = [
            'label' => $_POST['edit_label'] ?? '',
            'url'   => $_POST['edit_url'] ?? ''
        ];
        
        $run = Menu::update_menu_item([ $data['label'], $data['url'], $id ]);

        json_response( $run, $data['label'] );
    }


    # Acao: deletar um item de menu, garantindo que os filhos herdem o pai do item excluido
    if( $_POST['action'] === 'delete_menu_item' ) {
        $id = (int) ($_POST['delete_id'] ?? 0);

        if( $id <= 0 ) {
            json_response(false, 'ID inválido');
        }

        $deleted = Menu::delete_menu_item( $id );

        if( $deleted === null ) {
            json_response( false, 'Item não encontrado' );
        }

        json_response( $deleted, '' );
    }

}



if( isset($_GET['render_tree']) ) {
    $current_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null);

    if( $current_menu ) {
        echo Menu::render_menu_tree( $current_menu );
    }
    exit;
}
