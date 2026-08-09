<?php
$template = $container->make('TemplateManager');
$template->is_valid();

if( file_exists(TEMPLATE_PATH . 'features.php') ) {
    require TEMPLATE_PATH . 'features.php';
}

$router = $container->make('Router');



$fileannex = match( Router::case() ) {
    'categories' => 'categories.php',
    'page'       => 'page.php',
    'article'    => 'article.php',
    'user'       => 'user.php',
    default      =>  null,
};

if( isset($fileannex) ) {
    require annex_path( $fileannex );
}



# Se append_resource('comment_area') tenha sido registrado, inclui o arquivo annexes/comment.php
if( is_article() && has_resource('comment_area') ) {

    require annex_path('comment.php');
}
# Caso append_resource('comment_area') nao registrado e article.php do template chamou `comment_area()`
else {

    function comment_area(): void {
        if( DISPLAY_ERRORS ) {
            trigger_error(
                'comment_area() foi chamada, mas o recurso "comment_area" não foi registrado.',
                E_USER_WARNING

            );
        }
    }
}
# Um 3º caso: Se 'comment_area' foi registrado e nao chamou funcao o sistema vai carregar comment.php e todo o relacionado a area de comentarios sem necessidade
# Nao da erro, mas e um pequeno disperdicio de recurso