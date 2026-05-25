<aside id="menu-actions" class="cn_33">
    <h3>Adicionar ao menu</h3>
    <div class="accordion collapse">
        <?php echo $view['pages']; ?>

        <?php echo $view['categories']; ?>

        <button class="acc_btn" data-collapse="collapse_itens_custom">Personalizado</button>
        <div id="source-custom" class="menu-source acc_panel">
            <div class="acc_content">
                <form id="item-form-custom" method="POST" action="<?= URL::current() ?>">
                    <label for="custom_label">Texto a ser exibido</label>
                    <input type="text" id="custom_label" name="custom_label" required />
                    <label for="custom_url" class="mt15">URL:</label>
                    <input 
                        id="custom_url" class="sm" placeholder="https://" 
                        type="url" name="custom_url" 
                    />
                    <button class="btn sm right mt25" type="button" name="add_item_custom">
                        Adicionar ao menu
                    </button>
                    <div class="response mt10 mb5 fs15 txt_center"></div>
                </form>
            </div>
        </div>

        <button class="acc_btn" data-collapse="collapse_itens_auth">Usuário</button>
        <div id="source-auth" class="menu-source acc_panel">
            <div class="acc_content">
                <form id="item-form-auth" method="POST" action="<?= URL::current() ?>">
                    <label for="auth_label">
                        Texto padrão se nenhum gancho for acrescentado
                    </label>
                    <input 
                        id="auth_label" placeholder="Login/Registro" 
                        type="text" name="auth_label" 
                    />
                    <button class="btn sm right mt25" type="button" name="add_item_auth">
                        Adicionar ao menu
                    </button>
                    <div class="response mt10 mb5 fs15 txt_center"></div>
                </form>
            </div>
        </div>
    </div>
</aside>