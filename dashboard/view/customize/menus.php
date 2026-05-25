<?php
if( INPUT::formSubmitted() ) {
    require_callable('sanitize-validate.php');
    require get_dashboard_path('controller/menu.php');
}


/*-- STATE ------------------------------------------------------- --*/
require DASH_DIR . 'callable/view/menus/state.php'; ?>


<section id="menu-manager" class="menu-wrapper">

    <!-- MENUS ---------------------------------------------- (storage/menu.php) -->
    <?php require DASH_DIR . 'callable/view/menus/menu-identify.php'; ?>


    <!-- ITEMS -------------------------------------------------------------- -->
    <hr><h2 class="op03 ml5">ITENS</h2>
    <div class="menu-items flexbox">
        <?php if( $state['exists'] ) : ?>

            <div class="response save-order txt_success"></div>

            <h3 class="cn_100 txt_center ml-15">
                <span class="border-span">Editando o menu: <?= $view['menu'] ?></span>
            </h3>


            <!-- ACTIONS ------------------------------------- ( Adicionar ao menu ) -->
            <?php require DASH_DIR . 'callable/view/menus/actions.php'; ?>


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
require DASH_DIR . 'callable/view/menus/cache.php';
