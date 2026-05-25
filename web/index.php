<?php
$container = Container::instance();

try {

    $template = $container->make('TemplateManager');
    # valida o template ativo
    $template->is_valid();

    # Rota de acesso (login) usuario
    if( URL::param(0) === 'access' ) {

        require_web("access/index");

    }
    else {

        $router = $container->make('Router');
        
        require WEB_DIR . 'loader.php';

        if( file_exists(TEMPLATE_PATH . 'features.php') ) {
            require_template('features');
        }

        # header e footer - agora sao invocados em Router->require
        # require_template('header');

        $router->requires();

        # require_template('footer');
    } 

}
catch( OpusException $e ) {

    echo $e->error();

}

ob_end_flush();