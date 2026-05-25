<?php
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
            "O campo <strong>Título</strong> não pode ficar vazio. Ele irá definir o identificador do contexto." 
        );
        return;
    }
    if( empty($section) ) {
        alert( 'error discard', 
            "O campo <strong>Sessão</strong> não pode ficar vazio. Ele irá definir um grupo de contextos, necessário para dar nome ao arquivo onde os dados agrupados são armazenados" 
        );
        return;
    }

    # Verifica se o 'name' do contexto ja existe
    if( Context::exists($name) ) {
        alert( 'warning discard', 
            "O Identificador do Contexto <code>'{$name}'</code> (Chave de Exibição) já existe." );
        return;
    }

    $array_data = [
        'section'  => $section,  # section igual como eh digitada no input
        'basename' => $basename, # section higienizada para usar como a base de nome do arquivo
        'title'    => $title,    # name igual digitada no input para uso de related_title em medias
        'name'     => $name,     # name higienizada para usar como a base de nome do arquivo
        'value'    => $value
    ];

    $array_name = $name;
    $pathname   = "contexts/{$basename}";

    /**
     * @todo retornando true quando superglobals eh usado, isso impede a insercao, deve retornar false 
     */
    if( ArrayExport::apply($array_name, $array_data, $pathname) ) {

        alert_redirect( 'success cn_100', 
            'Novo contexto adicionado.', 
            dash_url('customize/context/?update=' . $name), 3000
        );
    } 
    else {
        alert('warning discard cn_100', 'Ocorreu algum erro e o contexto não foi adicionado.');
    }
}


if( $action === 'update' ) {

    if( empty($title) || empty($section) ) { 
        alert( 'error discard', "Erro." );
        return;
    }

    $array_data = [
        'value'    => $value
    ];

    $array_name = INPUT::GET('name');
    $pathname   = 'contexts/' . INPUT::GET('basename');

    if( ArrayExport::apply($array_name, $array_data, $pathname) ) {

        alert_redirect( 'success cn_100', 'Contexto atualizado.', URL::current(), 2800 );
    } 
    else {
        alert('warning discard cn_100', 'Ocorreu algum erro e o contexto não foi atualizado.');
    }
}


if( $action === 'delete' ) {

    $name = INPUT::has('name') ? INPUT::GET('name') : INPUT::GET('target_id');
    $basename = INPUT::has('basename') ? INPUT::GET('basename') : INPUT::GET('target_basename');

    $redirect = URL::has('update') ? dash_url('customize/context') : URL::current();

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