<?php
URL::normalize();

$auto = new AutoUpdater;
$auto->run();

# Inclui o arquivo de recusos do template, se ele existir
$template = $container->make('TemplateManager');
if( $template->check_features() && file_exists(TEMPLATE_PATH . 'features.php') ) {
    require TEMPLATE_PATH . 'features.php';
}

enum Affected: int {
    case NOT_FOUND = 0;
    case NO_CHANGE = 1;
    case UPDATED   = 2;
}