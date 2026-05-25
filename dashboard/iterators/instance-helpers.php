<?php
/**
 * Este arquivo contem funcoes auxiliares (helpers) dedicadas a instanciar
 * classes e chamar metodos de selecao/
 * Elas centralizam a logica de acesso a dados para garantir consistencia e reuso
 *
 * @package Dashboard/Helpers
 */

/**
 * Retorna todos os registros de categorias
 */
function category_select(): array {
	return valid_class_method( 'Category', 'select' );
}

/**
 * Retorna todos os registros de comentarios
 */
function comment_select(): array {
	return valid_class_method( 'Comment', 'select' );
}

/**
 * Retorna todos os registros de posts
 */
function post_select(): array {
    return valid_class_method( 'Post', 'select' );
}

/**
 * Retorna todos os registros de paginas
 */
function page_select(): array {
	return valid_class_method( 'Page', 'select' );
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
 * `GraphStatistic` nao eh instanciada pelo `Container`
 */
function statistic_select(): array {
	if( statistics() ) {
		$statistic = GraphStatistic::instance();
		return $statistic->select();
	}
	return [];
}

/**
 * Retorna o ultimo registro de imagem anexada
 */
function select_last_image(): array {
	return valid_class_method( 'ImageAttachment', 'select_last' );
}

/**
 * Funcao auxiliar generica para instanciar uma classe e 
 * chamar um metodo, garantindo que o retorno seja um array
 */
function valid_class_method( string $class, string $method ): array {
    if( ! class_exists($class) ) {
    	if( OPUS_ERROR_REPORTING ) {
	        throw new OpusException("Classe <code class=\"class-name\">{$class}</code> não encontrada.");
	    }
	    return [];
    }

    $classCall = Container::call( $class );

    if( ! method_exists($classCall, $method) ) {
    	if( OPUS_ERROR_REPORTING ) {
	        throw new OpusException(
	        	"Método <code class=\"class-name\">{$method}</code> não encontrado na classe <code class=\"class-name\">{$class}</code>."
	        );
	    }
	    return [];
    }

    $select = $classCall->$method(); # Chama o metodo na instancia da classe.

    # Garante que o retorno seja sempre um array.
    return is_array( $select ) ? $select : [];
}




#  Funções de Instância para Métodos Gerais das Classes de Imagens
## Estas funcoes auxiliares facilitam o acesso a metodos especificos da classe ImageAttachment.

/**
 * Retorna todos os registros de anexos de imagem
 */
function media_selection(): array {
    $media = Container::call('ImageAttachment');
    $list = $media->select();
    if( ! $list && URL::has('id') ) {
        header( 'Location: ' . $media->navigation('next', URL::int('id'), true) );
        exit;
    }

    return $list ?? [];
}


/**
 * Imprime os botoes de navegacao (anterior/proximo) para anexos de imagem.
 * @param $show Objeto de iteracao para colulas de `media` ->ID : Da midia atual
 */
function attachment_navigation( object $show ): void {
	$media = Container::call('ImageAttachment');
	echo $media->navigation( 'prev', $show->ID ); # Imprime o link para a imagem anterior
	echo $media->navigation( 'next', $show->ID ); # Imprime o link para a proxima imagem
}