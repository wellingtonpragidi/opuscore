<?php
# define o ambiente
define( 'IS_WEB', false );
define( 'IS_DASHBOARD', true );

require str_replace( '\\', '/', dirname(__DIR__, 1) ) . '/config.php';


URL::normalize();


(new AutoUpdater)
->run();


# disponibiliza as variaveis de instancia de classes registradas no Container
extract( Container::scope(), EXTR_SKIP );


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

enum Status: int {
    case DRAFT     = 0;
    case PUBLISHED = 1;
    case TRASH     = 2;
}