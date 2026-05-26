<?php 
/**
 * Arquivo central de registro para as instancias Singleton 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 */

# `Container` eh o pai de todas as instancias, entao precisa ser singleton por si sho
$container = Container::instance();


$container->singleton('Connection', function() {
    $connect = new Connection;
    return $connect->database();
});
// $container->singleton('Connection', fn() => ( new Connection )->database());


/**
 * -------------------------------------------------------------
 *           SECAO DE INSTANCIAS DO AMBIENTE RESTRITO
 * -------------------------------------------------------------
 *   
 */
if( defined('IS_DASHBOARD') && IS_DASHBOARD ) {

    $container->singleton('Media', function($c) {
        return new Media( $c->make('Connection') );
    });

	$container->singleton('ImageAttachment', function($c) {
	    return new ImageAttachment( $c->make('Connection') );
	});

	$container->singleton('ImageManager', function($c) {
	    return new ImageManager( $c->make('Connection') );
	});


	$container->singleton('Relations', function($c) {
	    return new Relations( $c->make('Connection') );
	});


	$container->singleton( 'Router', fn() => new Router() );


	$container->singleton('User', function($c) {
	    return new User( $c->make('Connection') );
	});

}



/**
 * -------------------------------------------------------------
 *          SECAO DE INSTANCIAS DO AMBIENTE PUBLICO
 * -------------------------------------------------------------
 * 
 */
if( defined('IS_WEB') && IS_WEB ) {

    $container->singleton('Image', function($c) {
        return new Image(
            $c->make('Connection'),
            $c->make('Category'), 
            $c->make('Post'), 
            $c->make('Page'),
            $c->make('User')
        );
    });


    $container->singleton('Pagination', function($c) {
        return new Pagination( $c->make('Connection') );
    });


	$container->singleton('Router', function($c) {
	    return new Router(
	        $c->make('Category'),
            $c->make('Post'), 
	        $c->make('Page'),
	        $c->make('User')
	    );
	});


	$container->singleton('Selection', function($c) {
	    $selection = new Selection(
            $c->make('Router'), 
	        $c->make('Pagination'), 
	        $c->make('Post'),
	        $c->make('Category')
	    );
        $selection->setConnection( $c->make('Connection') );

        return $selection;
	});


	$container->singleton('User', function($c) {
	    return new User(
	        $c->make('UserAuth'), 
	        $c->make('UserProfile'), 
	        $c->make('UserStatus')
	    );
	});
    
	$container->singleton('UserAuth', function($c) {
        $auth = new UserAuth;
        $auth->setConnection( $c->make('Connection') );

        return $auth;
	});
    
	$container->singleton('UserProfile', function($c) {
        $profile = new UserProfile;
        $profile->setConnection( $c->make('Connection') );

        return $profile;
	});
    
	$container->singleton('UserStatus', function($c) {
	    return new UserStatus( $c->make('Connection') );
	});

}


/**
 * -------------------------------------------------------------
 *   SECAO DE INSTANCIAS DE AMBOS AMBIENTES E DO DISTRIBUIDOR
 * -------------------------------------------------------------
 * Ambientes publico (web/) e restrito (dashboard/) nao se misturam 
 * e ambos tem classes com mesmo nomes, portanto esses sao instanciados aqui 
 * junto com as classes do distribidor (dist/) 
 * sem usar as constantes `IS_WEB` e `IS_DASHBOARD` que separam ambientes
 */
$container->singleton('Admin', function($c) {
    return new Admin( $c->make('Connection') );
});


$container->singleton('Comment', function($c) {
    return new Comment( $c->make('Connection') );
});


$container->singleton('Context', function($c) {
    return new Context();
});


$container->singleton('Category', function($c) {
    $category = new Category();
    $category->setConnection( $c->make('Connection') );

    return $category;
});


$container->singleton('Page', function($c) {
    $page = new Page();
    $page->setConnection( $c->make('Connection') );

    return $page;
});


$container->singleton('Post', function($c) {
    $post = new Post();
    $post->setConnection( $c->make('Connection') );

    return $post;
});


$container->singleton('Provider', function($c) {
    return new Provider( $c->make('Connection') );
});


$container->singleton('TemplateManager', fn() => new TemplateManager );




/**
 * -------------------------------------------------------------
 *       SECAO DE INSTANCIAS PARA CLASSES PHP RESERVADAS
 * -------------------------------------------------------------
 */

$container->singleton('TimeZone.System', function() {
    $core = Provider::settings('core');
    $timezone = $core['timezone'] ?: date_default_timezone_get() ?: 'America/Sao_Paulo';
    return new DateTimeZone( $timezone );
});

$container->singleton('TimeZone.UTC', function() {
    return new DateTimeZone('UTC');
});
