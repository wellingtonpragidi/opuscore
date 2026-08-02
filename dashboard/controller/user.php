<?php 
INPUT::method_request();



$bind = new Assign;

$bind->ID          = INPUT::int('target_id') ?: GET::int('id');
$bind->media->type = 'user';


# apenas referencia
# ainda nao existe um users/update/?id=X no ambiente do dashbd
if( INPUT::action('update') ) {

    return;

    if( $user->update($bind) ) {

        alert_redirect( 'success', 'Usuário atualizado.', URL::current() );
    } 
    else {

        alert('warning', 'Nenhuma alteração foi feita.');
    }
}


if( INPUT::action('delete') ) {

    # exclui imagem de perfil e avatar (registro e arquivo fisico) se existir
    $result = $image->delete($bind);

    $record  = $result['deleted_record'];
    $files   = $result['deleted_file'];

    $msg_deleted_img = delete_image_messages( $record, $files );


    if( $user->delete($bind) ) {

        alert_redirect( 'success', 'Usuário excluído.', dash_url('users') );
    }
    else {

        alert('warning', 'Usuário não excluído.');
    }
}


if( INPUT::action('approved') ) {

    $bind->approved = 1;

    if( $user->update_approved($bind) ) {

        alert_redirect( 'success', 'Usuário aprovado', URL::current() ); # 3300ms
    }
    else {

        alert_time('warning', 'Usuário não aprovado');
    }
}


function delete_image_messages( bool $record_deleted, bool $files_deleted ): string {
    $alert_content = '';

    if( $record_deleted ) {
        $alert_content .= '<p class="concat">Imagens do usuário excluídas do registro.</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Imagens do usuário não foram excluídas do registro.</p>';
    }

    if( $files_deleted ) {
        $alert_content .= '<p class="concat">Arquivos físicos de imagens excluídos.</p>';
    }
    else {
        $alert_content .= '<p class="concat warn">Os arquivos físicos de imagens não foram excluídos.</p>';
    }


    if( ! $record_deleted && ! $files_deleted ) {
        $alert_content .= '<br>Isso não indica um erro, pode ser que esse usuário nunca adicionou uma imagem vinculada.';
    }


    return $alert_content;
}