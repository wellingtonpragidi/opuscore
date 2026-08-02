<form 
    id="admin-update" 
    class="ml20 mb10 w50 floatleft" 
    method="POST" 
    action="<?= URL::current() ?>"
>
	<?php 
    foreach( select_admin() as $show ) :


        require view_param_path( 'updates/name' );
        

            echo '<hr class="hl">';


        require view_param_path( 'updates/email' );


            echo '<hr class="hl">';


        require view_param_path( 'updates/status' );


            echo '<hr class="hl">';


        require view_param_path('updates/role');


            echo '<hr class="hl">';


        require view_param_path('updates/pswd');


?>
        <input type="hidden" id="target_id" name="target_id" value="<?= $show->ID ?>" />
        <input type="hidden" id="target_type" name="target_type" value="admin" />

	<?php endforeach ?>
</form>


<form id="admin-delete" class="w35 floatright mt40" method="POST" action="<?= URL::current() ?>">
    <?php 
    if( $auth->is_authorized() ) :

        require view_param_path('updates/delete');

    endif; 
    ?>
</form>