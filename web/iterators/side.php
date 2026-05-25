<?php
declare( strict_types = 1 );
/**
 * "side" faz parte dos components de templates
 * As funcoes nesse arquivo sao mais usadas em laterais de um template, muito parecido com widgets
 * mas nao podem ser chamados de widgets, pois nao sao bem isso
 * 
 * Existem mais funcoes de components, como shares, relateds (com estrutura HTML em bloco) etc
 * 
 * @todo nomenclatura ainda nao decidida, por esses motivos:
 * pode haver defeitos em templates por mudancas em padroes HTML como valores de atributos class
 * menos provavel, mas pode haver quebra por necessidade de mudar nome de funcoes 
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package Output/Component
 * 
 * @see https://opuscore.dev/functions/components
 */

/**
 * @link https://opuscore.dev/functions/search_form
 */
function search_form( array $args = [] ): void {
    $placeholder = $args['placeholder'] ?? 'Pesquisar no site';
    $btntext     = $args['btntext'] ?? 'Procurar';
    $btnclass    = isset($args['btnclass']) ? "class=\"{$args['btnclass']}\"" : '';

	echo '<form class="query" role="search" method="GET" action="' . URL::root() . '">
		<label for="query" class="sr">' . $placeholder . '</label>
	    <input 
	    	id="query" type="search" placeholder="' . $placeholder . '" minlength="3" 
	    	name="q" value="' . URL::GET('q') . '" required 
	    />
	    <button ' . $btnclass . ' type="submit">' . $btntext . '</button>
	</form>';
}


/**
 * @param $args | array - opcoes: 'except' e 'thumbnail'
 * $args['except'] : slug de categorias que nao quer exibir na lista 
 * $args['thumbnail'] : exibir miniaturas de imagens das categorias | padrao nao exibe
 * @return void | imprime HTML
 * 
 * @link https://opuscore.dev/functions/list_categories
 * */
function list_categories( array $args = [] ): void {
    $category = Container::call('Category');

	echo $category->list( $args );

	if( is_category() && ($args['active_item'] ?? true) ) {
		inline_script("
	        let catLinks = document.querySelectorAll('.listcats a');
	        if( catLinks ) {
	            catLinks.forEach( element => {
	                if( element.href === document.URL ) {
	                    element.parentElement.classList.add('active');
	                }
	            });
	        }
		");
	}
}


/**
 * @link https://opuscore.dev/functions/list_posts_recents
 * */
function list_posts_recents( array $args = [] ): void {

    $class = isset($args['list_class']) ? " class=\"{$args['list_class']}\"" : '';
    $limit = $args['limit'] ?? 6;
    $h     = $args['item_title_tag'] ?? 'strong';

    $post = Container::call('Post');

	$html = "<ul{$class}>";
	foreach( $post->recents($limit) as $show ) {

        $dimensions = Image::dimensions_attrs( $show->attachment->thumb ?? null );
        $title_attr = Ensure::attr($show->title);
		$attrs = "alt=\"$title_attr\" {$dimensions}";

        $filepath  = $show->attachment->thumb->path ?? '';
        $thumbnail = upload_url( $filepath );

		$html .= '<li>
			<span class="thumbnail">';
				if( $filepath !== '' ) {
					$html .= "<img src=\"{$thumbnail}\" {$attrs} />";
				}
		    $html .= 
            "</span>

			<{$h}>{$show->title}</{$h}>
			<a href=\"{$show->URL}\"></a>
		</li>";
	}
	$html .= '</ul>';

	echo $html;
}


/**
 * @link https://opuscore.dev/functions/list_posts_relateds
 */
function list_posts_relateds( array $args = [] ): void {
	if( ! is_post() ) {
        return;
    }

    $class = isset($args['list_class']) ? " class=\"{$args['list_class']}\"" : '';
    $limit = $args['limit'] ?? 6;
    $h     = $args['item_title_tag'] ?? 'strong';

    $post = Container::call('Post');

    $html = "<ul{$class}>";
	foreach( $post->relateds($limit) as $show ) {

		$dimensions = Image::dimensions_attrs( $show->attachment->thumb ?? null );
        $title_attr = Ensure::attr($show->title);
        $attrs = "alt=\"{$title_attr}\" {$dimensions}";

        $filepath  = $show->attachment->thumb->path ?? '';
        $thumbnail = upload_url( $filepath );

		$html .= '<li>
			<span class="thumbnail">';
			if( $filepath !== '' ) {
				$html .= "<img src=\"{$thumbnail}\" {$attrs} />";
			}
			$html .= 
            "</span>
            <{$h} class=\"r-title\">{$show->title}</{$h}>
            <a href=\"{$show->URL}\"></a>
        </li>";
	}
    $html .= '</ul>';

    echo $html;
}
