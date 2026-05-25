<?php 
require dirname( __DIR__, 2 ) . '/callable/loader/controller-async.php';
INPUT::method_request();

$action = $_GET["action"] ?? null;

if( $action === 'post' ) {
    $post_size_data = [
        'post' => [
            'wide' => [
                'width' => (int) ($_POST['post_wide_w'] ?? 1600), 
                'height' => (int) ($_POST['post_wide_h'] ?? 550)
            ],
            'larger' => [
                'width' => (int) ($_POST['post_larger_w'] ?? 1000), 
                'height' => (int) ($_POST['post_larger_h'] ?? 400)
            ],
            'minor' => [
                'width' => (int) ($_POST['post_minor_w'] ?? 650), 
                'height' => (int) ($_POST['post_minor_h'] ?? 480)
            ],
            'thumb' => [
                'width' => (int) ($_POST['post_thumb_w'] ?? 70), 
                'height' => (int) ($_POST['post_thumb_h'] ?? 60)
            ]
        ]
    ];

    if( ArrayExport::apply('image', $post_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens para posts atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens para posts' );
    }
}


if( $action === 'page' ) {
    $page_size_data = array (
        'page' => [
            'wide' => [
                'width' => (int) ($_POST['page_wide_w'] ?? 1700), 
                'height' => (int) ($_POST['page_wide_h'] ?? 600)
            ],
            'larger' => [
                'width' => (int) ($_POST['page_larger_w'] ?? 1400), 
                'height' => (int) ($_POST['page_larger_h'] ?? 500)
            ],
            'minor' => [
                'width' => (int) ($_POST['page_minor_w'] ?? 650), 
                'height' => (int) ($_POST['page_minor_h'] ?? 600)
            ]
        ]
    );

    if( ArrayExport::apply('image', $page_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens para páginas atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens para páginas.' );
    }
}


if( $action === 'category' ) {
    $category_size_data = [
        'category' => [
            'plain' => [
                'width' => (int) ($_POST['cat_w'] ?? 500), 
                'height' => (int) ($_POST['cat_h'] ?? 350)
            ],
            'thumb' => [
                'width' => (int) ($_POST['cat_sm_w'] ?? 70), 
                'height' => (int) ($_POST['cat_sm_h'] ?? 60)
            ]
        ]
    ];
    
    if( ArrayExport::apply('image', $category_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens para categorias atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens para categorias.' );
    }
}


if( $action === 'user' ) {
    $user_size_data = [
        'user' => [
            'profile' => (int) ($_POST['user_profile'] ?? 100),
            'avatar' => (int) ($_POST['user_avatar'] ?? 60)
        ]
    ];
    
    if( ArrayExport::apply('image', $user_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens para usuários atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens para usuários.' );
    }
}


if( $action === 'system' ) {
    $system_size_data = (int) ($_POST['system_size_data'] ?? 180);

    if( $system_size_data > 250 ) {
        alert(
           'warning discard',
           'Você alterou o atributo <code>max</code> no DOM.<br>
            O tamanho máximo para essa imagem é <strong>250</strong>.  
            Imagens maiores não trazem ganho visual e só pesar o carregamento no painel.'
        );
        return;
    }
    
    $system_size_data = [
        'system' => $system_size_data,
    ];
    
    if( ArrayExport::apply('image', $system_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens do sistema atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens do sistema.' );
    }
}