<div class="menus flexbox">

    <!-- SELECT MENU ------------------------------------------------------- -->
    <?php if( $state['has_menus'] ) : ?>

        <form class="cn_50" method="GET" action="<?= URL::current() ?>">
            <label class="op0" for="select-menu">Selecionar menu:</label><br>
            <select id="select-menu" name="key" onchange="this.form.submit()">
                <option value="" selected disabled hidden>
                    Selecionar menu:
                </option>
                <?php foreach( $menus as $slug => $data ) : ?>
                    <option value="<?= $slug ?>"<?= $selected($slug) ?>>
                        <?= $data['title'] ?? '' ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </form>

    <?php endif; ?>

    <!-- EDIT MENU --------------------------------------------------------- -->
    <?php if( $state['exists'] ) : ?>

        <form class="cn_50" method="article">
            <label for="edit-menu">Editar nome do menu:</label><br>
            <input 
                id="edit-menu" 
                type="text" 
                name="menu_label" 
                value="<?= $state['title'] ?>" 
                required 
            />

            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="key" value="<?= $state['current'] ?>" />

            <button class="btn mt10 ml5" type="submit">Salvar</button>
        </form>

    <?php endif; ?>

    <!-- CREATE MENU ------------------------------------------------------- -->
    <form class="cn_50" method="POST" action="<?= URL::current() ?>">
        <label for="new-menu">Criar<?= $state['exists'] ? ' ' : ' novo ' ?>menu:</label><br>
        <input
            type="text" 
            name="menu_label" 
            placeholder="Nome do menu" 
            required 
        />

        <input type="hidden" name="action" value="insert">

        <button class="btn mt10 ml5" type="submit">Salvar</button>
    </form>

    <?php if( $state['exists'] ) : ?>
        <div class="cn_50">
            <!-- INFO ------------------------------------------------------------ -->
            <div class="menu-info txt_right mr5 mb20">
                <label for="display_key">Chave de exibição do menu</label>
                <input 
                    id="display_key" class="ftmono input_false link pt0 mt0 mr5 txt_right ha" 
                    type="text" value="<?= $state['current'] ?>" 
                />
            </div>

            <!-- DELETE ---------------------------------------------------------- -->
            <form method="POST" action="<?= URL::current() ?>" onsubmit="return confirm('Excluir menu?')">

                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="key" value="<?= $state['current'] ?>">

                <button class="input_false link delete" type="submit">
                    <span icon="trash" size="21" top="2"></span> Excluir menu
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>