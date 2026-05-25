<?php
declare( strict_types = 1 );
/**
 * 
 * Essas funcoes devem ser chamadas dentro de loops para iterar sobre os resultados.
 * 
 * fluxo:
 * row_exists() → Seek::row_exists() → Seek::construct() → Selection->show() → Selection->posts()
 * 
 * 
 */

/**
 * @see https://opuscore.dev/loops/seek_iterator-loop_pattern
 */
function row_exists(): bool {
    return Seek::row_exists();
}

function show_row(): void {
    Seek::show_row();
}




/**
 * @deprecated Use row_exists()
 */
function rows_exists(): bool {
    return Seek::row_exists();
}

/**
 * @deprecated Use show_row()
 */
function show_rows(): void {
    Seek::show_row();
}




/**
 * @see https://opuscore.dev/functions/permalink
 */
function permalink(): void {
    echo get_permalink();
}

/**
 * @see https://opuscore.dev/functions/get_permalink
 */
function get_permalink(): string {
    return URL::root( Seek::segment() );
}



/**
 * @see https://opuscore.dev/functions/title
 */
function title(): void {
    echo Seek::title();
}

/**
 * @see https://opuscore.dev/functions/get_title
 */
function get_title(): string {
    return Seek::title();
}



/**
 * @see https://opuscore.dev/functions/content
 */
function content(): void {
    echo Seek::content();
}
/**
 * @see https://opuscore.dev/functions/get_content
 */
function get_content(): string {
    return Seek::content();
}



/**
 * @see https://opuscore.dev/functions/summary
 */
function summary( int $length = 180, string $hellip = '&hellip;' ): void {
    echo get_summary( $length, $hellip );
}

/**
 * @param $length = tamanho do resumo, numero de letras | padrao 150 caracteres
 * @param $hellip = exibido no fim do resumo de texto | padrao &hellip; (...)
 * @return 1ª tenta pelo campo summary, se vazio cria resumo a partir de content | fallback ''
 * 
 * @see https://opuscore.dev/functions/get_summary
 **/
function get_summary( int $length = 180, string $hellip = '&hellip;' ): string {
    $extract = text_summary( Seek::content(), $length, $hellip );

    return Seek::summary() ?: $extract ?: '';
}



function author(): void {
    echo Seek::author();
}

/**
 * @see https://opuscore.dev/functions/get_author
 */
function get_author(): string {
    return Seek::author();
}



/**
 * 
 * @see https://opuscore.dev/functions/dates
 * @docx
 * 
 **/
/**
 * @see https://opuscore.dev/functions/created
 **/
function created( int|string|null $format = null ): void {
    echo Seek::created( $format );
}

/**
 * @see https://opuscore.dev/functions/get_created
 **/
function get_created( int|string|null $format = null ): string {
    return Seek::created( $format );
}

/**
 * @see https://opuscore.dev/functions/updated
 **/
function updated( int|string|null $format = null ): void {
    echo Seek::updated( $format );
}

/**
 * @see https://opuscore.dev/functions/get_updated
 **/
function get_updated( int|string|null $format = null ): string {
    return Seek::updated( $format );
}



/**
 * @see https://opuscore.dev/functions/page_id
 */
function get_id(): int {
    return Seek::ID();
}



/**
 * @see https://opuscore.dev/functions/featured_image
 **/
function featured_image( array $args = [] ): void {
    echo get_featured_image( $args );
}

/**
 * @see https://opuscore.dev/functions/get_featured_image
 **/
function get_featured_image( array $args = [] ): ?string {

    $attr = [
        'alt'     => '',
        'loading' => '',
        'class'   => '',
        'title'   => ''
    ];

    $scope = $args['scope'] ?? 'larger';

    if( isset($args['alt']) ) {
        $attr['alt'] = ' alt="' . Ensure::attr($args['alt']) . '"';
    }
    else {
        $attr['alt'] = ' alt="' . Ensure::attr(Seek::title()) . '"';
    }

    if( isset($args['loading']) ) {
        if( $args['loading'] === 'lazy' || $args['loading'] === true ) {
            $attr['loading'] = ' loading="lazy"';
        }
    }

    if( isset($args['class']) ) {
        $attr['class'] = ' class="' . Ensure::attr($args['class']) . '"';
    }

    if( isset($args['title']) ) {
        $attr['title'] = ' title="' . Ensure::attr($args['title']) . '"';
    }

    $image = Seek::attachment_data( $scope );

    if( isset($image['path']) ) {

        $attrs = $attr['alt'] 
            . $image['dimensions'] 
            . $attr['loading'] 
            . $attr['class'] 
            . $attr['title'];

        return "<img src=\"{$image['URL']}\"{$attrs} />";
    }

    return null;
}


/**
 * @see https://opuscore.dev/functions/featured_image_url
 **/
function featured_image_url( $scope = 'larger' ): void {
    echo get_featured_image_url($scope);
}

/**
 * @see https://opuscore.dev/functions/get_featured_image_url
 *
 */
function get_featured_image_url( $scope = 'larger' ): ?string {
    $image = Seek::attachment_data( $scope );

    return isset($image['path']) ? $image['URL'] : null;
}



/**
 * @see https://opuscore.dev/functions/post_picture_per_screen
 * @see https://opuscore.dev/functions/page_picture_per_screen
 **/
function picture_per_screen( string $change_alt = '' ): void {
    $minor  = Seek::attachment_data('minor');
    $larger = Seek::attachment_data('larger');
    $wide   = Seek::attachment_data('wide');

    if( ! isset($minor['path'], $larger['path'], $wide['path']) ) {
        return;
    }

    $alt = $change_alt ?: get_title();
    $alt = Ensure::attr( $alt );
    $attrs = "alt=\"{$alt}\" {$minor['dimensions']}";

    echo <<<HTML
    <picture>
        <source media="(min-width: 1366px)" srcset="{$wide['URL']}" />
        <source media="(min-width: 768px)" srcset="{$larger['URL']}" />
        <img src="{$minor['URL']}" {$attrs} />
    </picture>
    HTML;
}



/**
 * @see https://opuscore.dev/functions/title_attr
 * @deprecated use escattr(get_title())
 */
function title_attr(): void {
    echo get_title_attr();
}
/**
 * @see https://opuscore.dev/functions/get_title_attr
 * @deprecated use escattr(get_title())
 */
function get_title_attr(): string {
    return Ensure::attr( Seek::title() );
}