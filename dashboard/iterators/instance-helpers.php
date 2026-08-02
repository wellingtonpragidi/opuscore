<?php
/**
 * arquivo contendo funcoes auxiliares dedicadas a instanciar classes e chamar metodos de consultas
 *
 * @package System\Instance\View\Helper
 */

define( 'ID', URL::int('id') );


function select_admins(): array {
    return valid_class_method( 'Admin', 'select' );
}
function select_admin(): array {
    return valid_class_method( 'Admin', 'select', ID );
}


function select_categories(): array {
	return valid_class_method( 'Category', 'select' );
}


function select_comments(): array {
	return valid_class_method( 'Comment', 'select' );
}



function select_articles(): array {
    return valid_class_method( 'Article', 'select' );
}

/**
 * Retorna todos os registros de paginas
 */
function select_pages(): array {
	return valid_class_method( 'Page', 'select' );
}
function select_page(): array {
    return valid_class_method( 'Page', 'select', ID );
}



function select_users(): array {
    return valid_class_method( 'User', 'select' );
}
/**
 * Retorna todos os registros de configuracoes
 *
 * @return array contendo os dados das configuracoes

function context_select(): array {
	return valid_class_method( 'Context', 'select' );
} */

/**
 * Retorna todos os registros de estatisticas
 * `Statistic` nao eh instanciada pelo `Container`
 */
function select_statistics(): array {
	if( statistics() ) {
		$stats = Statistic::instance();
		return $stats->select() ?: [];
	}
	return [];
}


/**
 * Funcao auxiliar generica para instanciar uma classe registrada no container
 *  e 
 * chamar um metodo, garantindo que o retorno seja um array
 */
function valid_class_method( 
    string $class, string $method, mixed $args = null ): array {

    /*if( ! class_exists($class) ) {
    	if( DISPLAY_ERRORS ) {
	        throw new OpusException("Classe <code class=\"class-name\">{$class}</code> não encontrada.");
	    }

	    return [];
    }*/

    $class = Container::instance()->make( $class );

    /*if( ! method_exists($class, $method) ) {
    	if( DISPLAY_ERRORS ) {
	        throw new OpusException(
	        	"Método <code class=\"class-name\">{$method}</code> não encontrado na classe <code class=\"class-name\">{$class}</code>."
	        );
	    }

	    return [];
    }*/

    $select = $class->$method( $args ); # Chama o metodo na instancia da classe.

    # Garante que o retorno seja sempre um array.
    return is_array( $select ) ? $select : [];
}




#  Funções de Instância para Métodos Gerais das Classes de Imagens
## Estas funcoes auxiliares facilitam o acesso a metodos especificos da classe Image.

/**
 * Retorna todos os registros de anexos de imagem
 */
function select_medias(): array {
    $media = Container::instance()->make('Image');
    $list = $media->select();

    if( $list === [] && URL::has('id') ) {
        redirect( 
            media_navigation(URL::int('id'), 'next', false), 1600 
        );
    }

    return $list ?? [];
}


# retorna os botoes de navegacao (anterior/proximo) para anexos de midia.
function media_navigation( int $id, string $direction, bool $html = true ): string {
	$media = Container::instance()->make('Image');

    # numero inteiro do ID anterior ou proximo ID dependendo do $direction
    $target = $media->navigation( $id, $direction ); 

    $url = ($target === 0) 
        ? dash_url('medias') 
        : dash_url("medias/?id={$target}");


    if( $html === false ) {
        return $url;
    }

    $icon = $direction === 'prev' ? 'chevronleft' : 'chevronright';

    if( $target === 0 ) {
        return "
            <a class=\"{$direction}\" aria-disabled=\"true\">
                <span icon=\"{$icon}\" size=\"27\"></span>
            </a>
        ";
    }
    else {
        return "
            <a href=\"{$url}\" class=\"{$direction}\">
                <span icon=\"{$icon}\" size=\"27\"></span>
            </a>
        ";
    }
}