<?php
if( INPUT::formSubmitted() ) {
    require_annex('deps/sanitize-validate.php');
    require dashboard_path('controller/menu.php');
}

/*-- STATE ------------------------------------------------------- --*/
require view_partial_path('state');

?>
<section id="menu-manager" class="menu-wrapper">

    <!-- MENUS ---------------------------------------------- (storage/menu.php) -->
    <?php require view_partial_path('identify') ?>


    <!-- ITEMS -------------------------------------------------------------- -->
    <hr><h2 class="op03 ml5">ITENS</h2>
    <div class="menu-items flexbox">
        <?php if( $state['exists'] ) : ?>

            <div class="response save-order txt_success"></div>

            <h3 class="cn_100 txt_center ml-15">
                <span class="border-span">Editando o menu: <?= $view['menu'] ?></span>
            </h3>


            <!-- ACTIONS ------------------------------------- ( Adicionar ao menu ) -->
            <?php require view_partial_path('actions') ?>


            <!-- TREE -------------------------------------------------------------- -->
            <div class="menu-tree cn_67">
                <h3 class="ml20">Itens do menu</h3>
                <div id="menu-builder">
                    <?php echo $view['tree'] ?>
                </div>
            </div>

        <?php endif; ?>
    </div>

</section>


<!-- CACHE -------------------------------------------------------------- -->
<?php 
require view_partial_path('cache');
