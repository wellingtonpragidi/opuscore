<?php
INPUT::method_request();


$bind = new Assign;

# o type dessa categoria eh 'article' nesse caso o name do input eh 'type' e nao 'target_type'
$bind->type    = INPUT::GET('type'); 
$bind->name    = INPUT::GET('title');
$bind->parent  = INPUT::int('parent'); # select > option
$bind->content = Sanitize::editorContent('dscpt');

# midias precisam do type da entidade que no caso eh 'category-article';
$bind->media->type = INPUT::GET('target_type'); 


if( $_POST['action'] === 'insert' ) {

    if( $category->exists($bind) ) {
        alert('warning', 'Já existe uma categoria desse nível com o mesmo nome.');
        return;
    }

    $bind->created = date('Y-m-d'); # categorias nao registram horas
    $bind->slug = Ensure::slug($bind->name);

    # coluna segment de categories eh NOT NULL ( $bind->slug ) como valor temporario
    $bind->segment = $bind->slug;


	if( $category->insert($bind) ) {

        if( Validate::hasImageFeatured() ) {
            if( $image->exists($bind) ) {
                return;
            }

            foreach( ImageSize::dimensions($bind->media->type) as $scope => $size ) {
                ImageHandler::resolve([
                    'input'    => 'attachment',
                    'width'    => $size['width'],
                    'filename' => "{$bind->media->type}-{$bind->LastID}-{$scope}",
                    'height'   => $size['height']
                ]);
            }

            $bind->ID    = $bind->LastID;
            $bind->title = $bind->name; 
            $bind->created = date('Y-m-d H:i:s'); # esse created eh para imagem `medias`

            $image->insert( $bind );
        }

        $image->update_title($bind);
            

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

$bind->ID = URL::int('id') ?: INPUT::int('target_id');

if( $_POST['action'] === 'update' ) {

    if( $category->exists($bind) ) {
        alert('warning', 'Já existe uma categoria desse nível com o mesmo nome.');
        return;
    }
    
    $bind->title = $bind->name; 

    if( Validate::hasImageFeatured() ) {
        if( $image->exists($bind) ) {
            return;
        }

        foreach( ImageSize::dimensions($bind->media->type) as $scope => $size ) {
            ImageHandler::resolve([
                'input'    => 'attachment',
                'filename' => "{$bind->media->type}-{$bind->ID}-{$scope}",
                'width'    => $size['width'],
                'height'   => $size['height']
            ]);
        }

        $bind->created = date('Y-m-d H:i:s'); # esse created eh para imagem `medias`

        $image->insert( $bind );
    }
    

	if( $category->update($bind) ) {
        
        $image->update_title($bind);
        
        $bind->segment = $category->update_rebuild_segment( $bind->ID );

		alert_redirect('success', 'Categoria atualizada ' . $bind->segment, URL::current(), 2000);
    }
	else {

		alert( 'warning', 'Nenhuma linha afetada' );
    }

}



if( $_POST['action'] === 'unlink' ) {
    
    $result = $image->delete($bind);

    $record = $result['deleted_record'];
    $files  = $result['deleted_file'];

    $alert_msg = delete_image_messages( $record, $files );

    if( $record || $files ) {

        alert_redirect( 'success', $alert_msg, URL::current() );
    }
    else {

        alert( 'warning', $alert_msg );
        return;
    }
}



if( $_POST['action'] === 'delete' ) {

    $delete = $category->delete( $bind );

    if( $delete['has_successor'] ) {
        echo "
        <script>
            alert(`Essa categoria possui sucessores.\nPara excluí-la primeiro exclua ou edite as mesmas.`);

            window.history.back();
        </script>";

        exit;
    }
    else if( $delete['has_deleted'] ) {
        $result = $image->delete($bind);

        $record = $result['deleted_record'];
        $files  = $result['deleted_file'];

        $msg_deleted_img = delete_image_messages( $record, $files );

        $relation->delete_category( $bind );

        alert_redirect( 
            'success', 
            '<strong>Categoria excluída</strong>.' . $msg_deleted_img, 
            URL::has('id') ? dash_url('articles/categories') : URL::current(),
            2500
        );

        return;
    }
    else {
        alert( 
            'warning', 
            'Ocorreu algum erro e a categoria não foi excluída.'. $msg_deleted_img  
        );

        return;
    }
}