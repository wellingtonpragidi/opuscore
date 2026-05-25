<?php
INPUT::method_request();

/*
if( $admin->logged_role() !== 1 || $admin->logged_role() !== 2 ) {
    alert('Sem autorização para adicionar ou excluir categoria.');
}
*/

$bind = new Assign;

$bind->ID      = URL::int('id') ?: INPUT::int('target_id');
$bind->type    = singular(URL::param(0)) ?: INPUT::int('target_type');
$bind->name    = INPUT::GET('title');
$bind->parent  = INPUT::int('parent');
$bind->content = Sanitize::editorContent('dscpt');

if( $_POST['action'] === 'insert' ) {

    if( $category->exists($bind) ) {
        alert('warning', 'Já existe uma categoria desse nível com o mesmo nome.');
        return;
    }

    $bind->date = date('Y-m-d');
    $bind->slug = Ensure::slug($bind->name);

    # coluna segment eh NOT NULL ( $bind->slug ) como valor temporario
    $bind->segment = $bind->slug;

	if( $category->insert($bind) ) {

        if( Validate::hasImageFeatured() ) {
            foreach( ImageHandler::sizes('category') as $scope => $size ) {
                ImageHandler::resolve([
                    'input'    => 'attachment',
                    'width'    => $size['width'],
                    'filename' => "category-post-{$bind->LastID}-{$scope}",
                    'height'   => $size['height']
                ]);
            }
        }

        if( FILES::isDefined('attachment') ) {
            $imanager->insert_category_image( $bind->LastID );
        }

        # Caucula o segmento hierarquico completo
        # Ex: se categoria for "tecnologia/php", monta essa string
        # Ainda nao salvou no banco - so preparou o valor
        # Esse calculo he armazenado na propriedade $segment do objeto Assign
        $bind->segment = $category->build_segment( $bind->LastID );

        # Agora com o segmento calculado e armazenado, o valor he salvo no banco de dados
        # Salva o valor usando `UPDATE` pois `INSERT` ja aconteceu
        # Atualizando a coluna `segment` de `categories` com o valor calculado
        # Sem isso o valor nao persistiria no DB
        $category->update_segment( $bind->segment, $bind->LastID );

		alert_redirect('success', 'Categoria adicionada.', URL::current(), 2000);
	}

}


if( $_POST['action'] === 'update' ) {
    if( $category->exists($bind) ) {
        alert('warning', 'Já existe uma categoria desse nível com o mesmo nome.');
        return;
    }

	$bind->date    = INPUT::GET('date');
    $bind->slug    = Ensure::slug($bind->name);

    if( Validate::hasImageFeatured() ) {
        foreach( ImageHandler::sizes('category') as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "category-post-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        $imanager->insert_category_image($bind->ID);
    }
    

	if( $category->update($bind) ) {
        
        $imanager->update_title_category_image();
        
        $bind->segment = $category->update_rebuild_segment( $bind->ID );

		alert_redirect('success', 'Categoria atualizada ' . $bind->segment, URL::current(), 2000);
    }
	else {

		alert( 'warning', 'Nenhuma linha afetada' );
    }

}



if( $_POST['action'] === 'unlink' ) {
    
    $result = $imanager->delete_category_image();

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


if( $_POST['action'] === 'delete' ) {

    $delete = $category->delete( $bind );

    if( $delete === true ) {
        $result = $imanager->delete_category_image();
        $record_deleted = $result['deleted_record'];
        $files_deleted  = ! empty($result['deleted_file']);
        $fileAlert = images_deletion_message($record_deleted, $files_deleted);

        $relation->delete_category( $bind );

        $redirect = URL::has('id') ? dash_url('posts/categories') : URL::current();
        alert_redirect( 'success', "Categoria excluída. {$fileAlert}", $redirect, 2500 );
        return;
    }
    else if( $delete === null ) {
        echo "<script>
            alert('Essa categoria possui sucessores, primeiro exclua-os ou edite-os.');
            window.history.back();
        </script>";
        exit;
    }
    else {
        alert( 'warning', "Ocorreu algum erro e a categoria não foi excluída." );
        return;
    }
}