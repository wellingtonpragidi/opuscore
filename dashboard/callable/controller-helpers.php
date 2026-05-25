<?php

/**
 * 
 * helpers para todas as entidades que tem imagem destaque vinculada
 */
function images_deletion_message( string $record_deleted, string $files_deleted ): string {
    $alert_content = '';

    if( $record_deleted ) {
        $alert_content .= '<p class="concat">Imagens de destaque excluídas do registro</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Imagens de destaque não excluídas do registro</p>';
    }

    if( $files_deleted ) {
        $alert_content .= '<p class="concat">Arquivos físicos de imagens excluídos!</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Os arquivos físicos de imagens não foram excluídos!</p>';
    }

    return $alert_content;
}


/**
 * 
 * helpers para entidades tipo: `posts`, `pages`, `videos` `products` etc
 */
function unlink_entity_image( ImageManager $imanager ): void {
    $result = $imanager->delete_page_image();

    $record_deleted = $result['deleted_record'];
    $files_deleted  = ! empty( $result['deleted_file'] );

    $fileAlert = images_deletion_message( $record_deleted, $files_deleted );

    if( $record_deleted || $files_deleted ) {

        alert_redirect( 'success', $fileAlert, URL::current() );
    }
    else {
        alert( 'warning', $fileAlert );
    }
}


/**
 * 
 * helpers para controller da entidade `posts`
 */
function post_segment( Post $post, Assign $bind ): string {
    $segment = $post->segment();

    $shouldBuild = function( ?string $segment ): bool {
        return INPUT::int('categories_changed') === 1
            || empty($segment)
            || strpos($segment, '/') === false;
    };

    if( INPUT::int('segment_changed') === 1 ) {
        # escolheu manualmente
        return INPUT::GET('segment');
    }
    else if( $shouldBuild($segment) ) {
        # nao mexeu no select, mas mexeu nas categorias
        # ou valo do segmento na tabela esta vazio ou ainda incorreto 
        return $post->build_segment($bind);
    }
    else {
        
        return $segment;
    }
}

function delete_post( 
    ImageManager $imanager, Post $post, Relations $relation, Assign $bind ): void {

    $result = $imanager->delete_page_image();

    $record_deleted = $result['deleted_record'];
    $files_deleted  = ! empty($result['deleted_file']);

    $fileAlert = images_deletion_message( $record_deleted, $files_deleted );

    if( $post->delete($bind) ) {

        $relation->delete_type( $bind );

        $redirect = URL::has('id') ? dash_url('posts') : URL::current();
        alert_redirect( 'success', "Post excluído. {$fileAlert}", $redirect );
        preloader();
    }
    else {
        alert( 'warning', "Ocorreu algum erro e o post não foi excluído. {$fileAlert}" );
    }
}


/**
 * 
 * funcoes helpers para controller da entidade `pages`
 */
function delete_page( ImageManager $imanager, Page $page, Assign $bind ): void {
    $result = $imanager->delete_page_image();

    $record_deleted = $result['deleted_record'];
    $files_deleted  = ! empty($result['deleted_file']);

    $fileAlert = images_deletion_message( $record_deleted, $files_deleted );

    if( $page->delete($bind) ) {
        $redirect = URL::has('id') ? dash_url('pages') : URL::current();
        alert_redirect( 'success', "Página excluída. {$fileAlert}", $redirect );
        preloader();
    }
    else {
        alert( 'warning', "Ocorreu algum erro e a página não foi excluída. {$fileAlert}" );
    }
}