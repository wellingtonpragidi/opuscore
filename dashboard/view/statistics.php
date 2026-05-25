<?php if( statistics() ) : ?>
    <p><?= Count::statistics() ?> visitas.</p>
    <table class="cellspace outline">
        <tr>
            <th>Pagina</th>
            <th>URL</th>
            <th>Data</th>
            <th>Hora</th>
            <th>IP</th>
        </tr>
        <?php foreach( statistic_select() as $show ) : ?>
        <tr>
            <td><?= $show->title ?></td>
            <td><?= $show->URL ?></td>
            <td class="ft14"><?= chronos_format($show->date) ?></td>
            <td><?= substr($show->time, 0, -3) ?></td>
            <td><?= $show->IP ?></td>
        </tr>
        <?php 
        endforeach; ?>
    </table>
    </form>
    <?php
    $pagination = new Pagination( Count::statistics(), per_page('statistics') );
    echo $pagination->render();
else :
    $href = dash_url('settings/options#form_sts')
    echo <<<HTML
    <div class="one-exception">
        <h2 class="mt0">Desabilitadas</h2>
        <p>Para habilitar vá até <a href="$href">Configurações opcionais</a>.</p>
    </div>
    HTML;
endif;