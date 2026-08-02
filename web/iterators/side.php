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
 * @see https://opuscore.dev/functions/search_form
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
 * @param $args :
 * 'except' => slug de categorias que nao quer exibir na lista 
 * 'thumbnail' =: exibir miniaturas de imagens das categorias | padrao nao exibe
 * 
 * @see https://opuscore.dev/functions/list_categories
 * */
function list_categories( array $args = [] ): void {
    $category = Container::call('Category');

	echo $category->list( $args );

	if( is_category() && ($args['active_item'] ?? true) ) {
		block_script("
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
 * @see https://opuscore.dev/functions/list_articles_recents
 * */
function list_articles_recents( array $args = [] ): void {

    $class = isset($args['list_class']) ? " class=\"{$args['list_class']}\"" : '';
    $limit = $args['limit'] ?? 6;
    $h     = $args['item_title_tag'] ?? 'strong';

    $article = Container::call('Article');

	$html = "<ul{$class}>";
	foreach( $article->recents($limit) as $show ) {

        $dimensions = Image::dimension_attrs( $show->attachment->thumb ?? null );
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
 * @see https://opuscore.dev/functions/list_articles_relateds
 */
function list_articles_relateds( array $args = [] ): void {
	if( ! is_article() ) {
        return;
    }

    $class = isset($args['list_class']) ? " class=\"{$args['list_class']}\"" : '';
    $limit = $args['limit'] ?? 6;
    $h     = $args['item_title_tag'] ?? 'strong';

    $article = Container::call('Article');

    $html = "<ul{$class}>";
	foreach( $article->relateds($limit) as $show ) {

		$dimensions = Image::dimension_attrs( $show->attachment->thumb ?? null );
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
