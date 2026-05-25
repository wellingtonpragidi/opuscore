<?php
/*
if( is_dir(UPLOAD_DIR.'favicon') ) :
if( file_exists(DIR.'favicon.ico') )
if( file_exists(UPLOAD_DIR.'favicon/card.png') )
if( file_exists(DIR.'manifest.json') )
*/
if( Upgrade::has() ) : 
    $upgrade = Upgrade::package();
    $changelog = Upgrade::read_file('CHANGELOG.md') ?? '';
    $markdown = new Parsedown; ?>
    <div id="op-upgrade" class="one-exception">
        <?php 
        if( INPUT::formSubmitted() ) {
            require get_dashboard_path('controller/upgrade.php');
        } 
        ?>
        <div id="in-upgrade">
            <h3 class="mt0">Nova versão disponível: one <?= $upgrade['latest_version'] ?></h3>
            <p><span icon="calendar"></span> <em class="fs15"> <?= $upgrade['release_date'] ?></em></p>
            <details>
                <summary>Últimas alterações:</summary>
                <div class="changelog"><?= $markdown->text( $changelog ?? '' ) ?></div>
            </details>
            <form method="POST" action="<?= URL::current() ?>">
                <input type="hidden" name="latest_version" value="<?= $upgrade['latest_version'] ?>" />
                <input type="hidden" name="zip_filename" value="<?= basename($upgrade['download_url']) ?>" />
                <div class="my20 mx txt_center">
                    <button class="btn" type="submit" name="action" value="upgrade">
                        Atualizar agora
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
 
<div id="metrics-shortcuts" class="flexbox">
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('posts') ?>">
                <h4>Posts</h4>
                <h3><?= Count::posts() ?></h3>
                <span icon="clipboard" size="55"></span>
            </a>
        </div>
    </div>
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('posts/categories') ?>">
                <h4>Categorias</h4>
                <h3><?= Count::categories() ?></h3>
                <span icon="tags" size="55"></span>
            </a>
        </div>
    </div>
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('pages') ?>">
                <h4>Páginas</h4>
                <h3><?= Count::pages() ?></h3>
                <span icon="info" size="55"></span>
            </a>
        </div>
    </div>
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('users') ?>">
                <h4>Usuários</h4>
                <h3><?= Count::users() ?></h3>
                <span icon="user" size="55"></span>
            </a>
        </div>
    </div>
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('comments') ?>">
                <h4>Comentários</h4>
                <h3><?= Count::comments() ?></h3>
                <span icon="textarea" size="55"></span>
            </a>
        </div>
    </div>
    <div class="cn_33">
        <div>
            <a href="<?= dash_url('admins') ?>">
                <h4>Administradores</h4>
                <h3><?= Count::admins() ?></h3>
                <span icon="edit" size="55"></span>
            </a>
        </div>
    </div>
</div>

<div class="flexbox">
    <div class="cn_60">
        <?php if( statistics() ) : ?>
        <div id="stats-chart" class="chart-panel">
            <div>
                <h2 class="pl10 mt0">Estatísticas</h2>
                <canvas id="chart-statistic" width="400" height="150"></canvas>

                <p class="txt_center mt20"><a href="<?= dash_url('statistics') ?>">Ver tabela de estatística</a></p>
            </div>
        </div>
        <?php endif; ?>
        <div id="maintenance">
            <?php 
            $flag = DIR . '/.maintenance';
            $maintenance = $_GET['maintenance'] ?? '';
            $state = '';
            if( $maintenance === 'ON' ) {
                Ensure::writeLock( $flag, date('Y-m-d H:i:s') );
                alert( 'warning', 'Modo manutenção ATIVADO' );
                $state = 'Desativar modo manutenção';
                $switch = 'OFF';
            } 
            elseif( $maintenance === 'OFF' ) {
                alert( 'warning', 'Modo manutenção DESATIVADO' );
                $state = 'Ativar modo manutenção';
                $switch = 'ON';
                if( file_exists($flag) ) {
                    unlink( $flag );
                }
            } 
            else {
                if( file_exists($flag) ) {
                    $state = 'Desativar modo manutenção';
                    $switch = 'ON';
                }
                else {
                    $state = 'Ativar modo manutenção';
                    $switch = 'OFF';
                }
            }
            ?>
            <form method="GET" action="<?= URL::current() ?>#maintenance">
                <input type="hidden" name="maintenance" value="<?= $switch ?>">
                <button class="btn"><?= $state ?></button>
            </form>
        </div>
    </div>

    <div id="server-report" class="cn_40">
        <div>
            <hr>
            <form id="has_update" class="txt_center mt40" method="POST" action="<?= URL::current() ?>">
                <button type="submit" class="btn" name="action" value="refresh_cache">
                    Verificar atualizações
                </button>
            </form>
            <div id="response_update"></div>
        </div>
    </div>

</div>