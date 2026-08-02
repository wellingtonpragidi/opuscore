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
    $conn = new Connection;
    return $conn->database();
});


/**
 * -------------------------------------------------------------
 *           SECAO DE INSTANCIAS DO AMBIENTE RESTRITO
 * -------------------------------------------------------------
 *   
 */
if( defined('IS_DASHBOARD') && IS_DASHBOARD ) {

    $container->singleton( 'Admin', function($c) {
        $admin = new Admin;
        $admin->setConn( $c->make('Connection') );

        return $admin;
    });
    

    $container->singleton( 'Media', function($c) {
        return new Media( $c->make('Connection') );
    });

	$container->singleton( 'Image', function($c) {
	    return new Image( $c->make('Connection') );
	});


	$container->singleton( 'Relations', function($c) {
	    return new Relations( $c->make('Connection') );
	});


	$container->singleton( 'Router', fn() => new Router() );

}


/**
 * -------------------------------------------------------------
 *          SECAO DE INSTANCIAS DO AMBIENTE PUBLICO
 * -------------------------------------------------------------
 * 
 */
if( defined('IS_WEB') && IS_WEB ) {
    
    $container->singleton('Access', function($c) {
        $access = new Access;
        $access->setConn( $c->make('Connection') );

        return $access;
    });
    

    $container->singleton('Image', function($c) {
        return new Image(
            $c->make('Connection'),
            $c->make('Category'), 
            $c->make('Article'), 
            $c->make('Page'),
            $c->make('User'),
            $c->make('Auth'),
        );
    });


    $container->singleton('Pagination', function($c) {
        return new Pagination( $c->make('Connection') );
    });


	$container->singleton('Router', function($c) {
	    return new Router(
	        $c->make('Category'),
            $c->make('Article'), 
	        $c->make('Page')
	    );
	});


	$container->singleton('Selection', function($c) {
	    $selection = new Selection(
            $c->make('Router'), 
	        $c->make('Pagination'), 
	        $c->make('Article'),
	        $c->make('Category')
	    );
        $selection->setConn( $c->make('Connection') );

        return $selection;
	});

}


/**
 * -------------------------------------------------------------
 *   SECAO DE INSTANCIAS DE AMBOS AMBIENTES E DO DISTRIBUIDOR
 * -------------------------------------------------------------
 */

$container->singleton('Auth', function($c) {
    return new Auth( $c->make('Connection') );
});


$container->singleton('Comment', function($c) {
    $comment = new Comment;
    $comment->setConn( $c->make('Connection') );

    return $comment;
});


$container->singleton('Context', function($c) {
    return new Context();
});


$container->singleton('Category', function($c) {
    $category = new Category;
    $category->setConn( $c->make('Connection') );

    return $category;
});


$container->singleton('Page', function($c) {
    $page = new Page;
    $page->setConn( $c->make('Connection') );

    return $page;
});


$container->singleton('Article', function($c) {
    $article = new Article;
    $article->setConn( $c->make('Connection') );

    return $article;
});


$container->singleton('Provider', function($c) {
    return new Provider( $c->make('Connection') );
});


$container->singleton('TemplateManager', fn() => new TemplateManager );


$container->singleton('User', function($c) {
    $user = new User;
    $user->setConn( $c->make('Connection') );

    return $user;
});



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
