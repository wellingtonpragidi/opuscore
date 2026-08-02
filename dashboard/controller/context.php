<?php
defined('ENTRY_GUARD') or die;
INPUT::method_request();


$title = trim($_POST['title'] ?? '');

$section = trim($_POST['section'] ?? '');

$name = Sanitize::to_lower_underscore($title);

$basename = Sanitize::to_lower_underscore($section);

$value = Sanitize::editorContent('content');


$action = $_POST['action'] ?? null;

if( $action === 'insert' ) {

    if( empty($title) ) { 
        alert( 
            'error discard', 
            "O campo <strong>Título</strong> não pode ficar vazio." 
        );
        return;
    }
    if( empty($section) ) {
        alert( 'error discard', 
            "O campo <strong>Sessão</strong> não pode ficar vazio." 
        );
        return;
    }

    # Verifica se o 'name' do contexto ja existe
    if( Context::exists($name) ) {
        alert( 'warning discard', 
            "O nome do Contexto <code>'{$name}'</code> (Chave de Exibição) já existe." 
        );

        return;
    }

    $current_id = Context::increment();

    if( $current_id === 0 ) {
        alert('error', 'Não foi possível obter o ID do contexto' );
        opus_log('Não foi possível travar o arquivo context-increment.txt para leitura');

        return;
    }

    $array_data = [
        'ID'       => $current_id, # obtem o ID atual e incrementa para o proximo
        'section'  => $section,    # section igual como eh digitada no input
        'basename' => $basename,   # section higienizada para usar como a base de nome do arquivo
        'title'    => $title,      # name igual digitada no input para uso de related_title em medias
        'name'     => $name,       # name higienizado para uso como a chave de exibicao
        'value'    => $value
    ];

    if( ArrayExport::apply($name, $array_data, 'contexts/' . $basename) ) {
        redirect( dash_url('contexts/?id=' . $current_id), 1000 );
        preloader(800);
    } 
    else {
        alert('warning discard cn_100', 'Ocorreu algum erro e o contexto não foi adicionado.');
    }
}
    /**
     * @todo retornando true quando superglobals eh usado, isso impede a insercao, deve retornar false 
     */


if( $action === 'update' ) {

    if( empty($title) || empty($section) ) { 
        alert( 'error discard', "Erro." );
        return;
    }

    $array_data = [
        'value' => $value
    ];

    $array_name = $context->ID() ?: INPUT::GET('target_id');
    $local = 'contexts/' . INPUT::GET('basename');

    if( ArrayExport::apply($array_name, $array_data, $local) ) {

        alert_redirect( 'success cn_100', 'Contexto atualizado.', URL::current(), 2800 );
    } 
    else {
        alert('warning discard cn_100', 'Ocorreu algum erro e o contexto não foi atualizado.');
    }
}


if( $action === 'delete' ) {

    $name     = INPUT::GET('name');
    $basename = INPUT::GET('basename');

    $redirect = URL::has('id') ? dash_url('contexts') : URL::current();

    if( Context::delete($name, $basename) ) {
        alert_redirect( 'success cn_100', 
            'Contexto excluído.', $redirect , 2800
        );
        preloader(350);
    }
    else {
        alert('warning discard cn_100', 'Ocorreu algum erro e o contexto não foi excluído.');
    }
}