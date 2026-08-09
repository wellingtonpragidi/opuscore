</div><!-- #content -->

<footer><!-- um unico elemento footer por documento -->

    <section>
        <div>
            <?php context('resumo_template') ?>
        </div>
        <div>
            <h2><?php context_title('recursos') ?></h2>
            <?php context('recursos') ?>
        </div>
        <div>
            <h2>Legal</h2>
            <nav>
                <?php 
                Menu::list([ 
                    'name' => 'legal_menu',
                    'list_class' => null
                ]); 
                ?>
            </nav>
        </div>
    </section>

    <small id="copyright">
        &copy; <?= site_title() ?>. 
        Feito com 🖤 usando 
        <a class="inverse" href="<?= ENGINE_URL ?>" target="_blank" rel="noopener">Opus Core</a>
    </small>

</footer>

<?php foot() ?>
</body>
</html>