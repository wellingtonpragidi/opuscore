<?php 
if( realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ ) {
    http_response_code(403);
    exit('Forbidden');
}

INPUT::method_request();


$action = $_GET["action"] ?? null;

if( $action === 'article' ) {
    $article_size_data = [
        'article' => [
            'wide' => [
                'width' => (int) ($_POST['article_wide_w'] ?? 1600), 
                'height' => (int) ($_POST['article_wide_h'] ?? 550)
            ],
            'larger' => [
                'width' => (int) ($_POST['article_larger_w'] ?? 1000), 
                'height' => (int) ($_POST['article_larger_h'] ?? 400)
            ],
            'minor' => [
                'width' => (int) ($_POST['article_minor_w'] ?? 650), 
                'height' => (int) ($_POST['article_minor_h'] ?? 480)
            ],
            'thumb' => [
                'width' => (int) ($_POST['article_thumb_w'] ?? 70), 
                'height' => (int) ($_POST['article_thumb_h'] ?? 60)
            ]
        ]
    ];

    if( ArrayExport::apply('image', $article_size_data, 'settings') ) {

        alert( 'success', 'Dimensões de imagens para artigos atualizadas.' );
    }
    else {

        alert( 'warning discard', 'Falha ao atualizar as dimensões de imagens para artigos' );
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
        'category-article' => [
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
           'Você certamente alterou o atributo <code>max</code> pelo DOM.<br>
            O tamanho máximo para essa imagem é <strong>250</strong>.<br> 
            Imagens maiores deixa o carregamento do painel pesado e não traz ganho visual.'
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