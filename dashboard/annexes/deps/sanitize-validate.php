<?php
declare( strict_types = 1 );

/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System\Ensure\Sanitize\Validate
 * @subpackage Integrity\Purifier
 */

class Sanitize {

    public static function to_lower_underscore( string $value ): string {
        $string = strip_tags($value);

        if( mb_check_encoding($string, 'UTF-8') ) {
            $string = iconv( "UTF-8", "ASCII//TRANSLIT", $string );
        }
        else {
            $string = Ensure::slug($value);
        }
        $string = preg_replace("/[^\w\s]/", "", $string);
        $string = str_replace([" ", "-"], "_", $string);
        $string = strtolower($string);

        $string = trim($string, "_");
        $string = mb_substr($string, 0, 30);

        return $string;
    }


    /**
     * Sanitizacao de entrada no conteudo HTML nos editores do painel 
     * Com opcao de alteracao por hook filter:
     * - em todo o codigo - ou - remover a sanitizacao de entrada
     * 
     * Obs: Isso nao substitui completamente uma sanitizacao de saida
     *
     * Remove tags perigosas fora de iframes, limpa atributos potencialmente maliciosos (on*, javascript:, etc)
     * 
     * @return :
     * string se nenhum append_filter chamado, ou append_filter substituindo o codigo
     * booleano se append_filter remover a sanitizacao de entrada com `return false;`
     * 
     * @todo remover iframe com URLs desconhecidas
     */
    public static function editorContent( string $name ): bool|string {
        
        $html = INPUT::GET($name);

        if( Hook::call_filter('run_sanitize_editor_content', true) ) {

            $remove_tags = ['script', 'style', 'meta', 'object', 'embed', 'base', 'link', 'body', 'html'];
            $remove_attrs = ['on*', 'xmlns', 'formaction', 'xlink:href'];

            # Suprime warnings de HTML malformado
            libxml_use_internal_errors( true );
            $doc = new DOMDocument( '1.0', 'UTF-8' );

            # Adiciona uma div wrapper temporaria para preservar a estrutura
            $doc->loadHTML( 
                '<?xml encoding="utf-8" ?><div>' . $html . '</div>', 
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD 
            );

            $xpath = new DOMXPath($doc);

            # Remove tags proibidas (exceto dentro de iframes)
            foreach( $remove_tags as $tag ) {
                foreach( $xpath->query('//' . $tag . '[not(ancestor::iframe)]') as $node ) {
                    $node->parentNode->removeChild( $node );
                }
            }

            # Remove atributos perigosos
            foreach( $xpath->query('//*[@*]') as $element ) {
                foreach( iterator_to_array($element->attributes) as $attr ) {
                    $name  = strtolower( $attr->nodeName );
                    $value = trim( $attr->nodeValue );

                    $on_event           = preg_match( '/^on/i', $name );
                    $forbidden_attr     = in_array( $name, $remove_attrs );
                    $protocol_JS_inline = preg_match( '/^\s*javascript:/i', $value );
                    if( $on_event || $forbidden_attr || $protocol_JS_inline ) {
                        $element->removeAttribute( $name );
                        continue;
                    }
                    # Limpa javascript: em src, href e srcset
                    $hasResourceAttr = in_array( $name, ['srcset', 'src', 'href'] );
                    $hasJSprotocol   = preg_match( '/javascript:/i', $value );
                    if( $hasResourceAttr && $hasJSprotocol ) {
                        $element->removeAttribute($name);
                    }
                }
            }
            # Extrai o conteudo SEM a div wrapper
            $content = '';
            foreach( $doc->getElementsByTagName('div')->item(0)->childNodes as $child ) {
                $content .= $doc->saveHTML($child);
            }
            libxml_clear_errors();

            return $content;
        }
        else {
            
            return $html;
        }
    }

}


/**
 * Essa classe fornece metodos de validacoes para controladores do painel
 */
class Validate {

    # Valida host permitidos a partir da URL
    public static function allowedHosts( string $url, array $allowedHosts ): bool { 
        $ihost = parse_url( $url, PHP_URL_HOST ); 
        $ihost = strtolower( preg_replace('/:\d+$/', '', $ihost ?? '') ); 

        return in_array( $ihost, array_map('strtolower', $allowedHosts), true ); 
    }


    public static function hasImageFeatured(string $input = 'attachment'): bool {
        return FILES::isDefined($input) && self::imageUpload($input);
    }

    /**
     * Verifica se o upload de uma imagem eh valido antes de prosseguir com o tratamento.
     *
     * Esta funcao realiza varias checagens para garantir a integridade e o formato
     * correto do arquivo de imagem enviado via formulario.
     * As validacoes incluem:
     * - Se o arquivo foi enviado corretamente e sem erros de upload.
     * - Se o arquivo temporario existe e eh um upload legitimo.
     * - Se o tipo MIME do arquivo eh permitido (JPEG, PNG, GIF, WebP).
     * - Se o arquivo eh de fato uma imagem valida (verificando dimensoes).
     *
     * Se o campo de upload for opcional e nenhum arquivo for enviado, a funcao retorna true.
     * Em caso de falha na validacao, uma mensagem de erro eh exibida e a funcao retorna false.
     */
    public static function imageUpload( string $input = 'attachment' ): bool {
        
        # Campo opcional: Se nao foi enviado arquivo, considera valido
        # Se $_FILES[$input] NAO existir e se o nome do arquivo for vazio, nao ha upload
        # Soh faz validacao se $_FILES[$input] existir para nao atrapalhar | caso edite soh outros campos
        if( FILES::not($input) || FILES::empty($input) ) {
            return true;
        }

        $error = '';

        # Verifica se houve erro no upload
        if( FILES::hasError($input) ) {
            $error .= "
            <p>Erro no upload do arquivo. Código: <code>" . FILES::error($input) . "</code>.</p>
            <p>". FILES::errors() ."</p>";
        }

        # Verifica se o arquivo temporario existe e eh um arquivo enviado via POST HTTP
        elseif( FILES::notTemp($input) || ! FILES::isUploaded($input) ) {
            $error .= "
            <p>O arquivo enviado é inválido ou não foi carregado corretamente.</p>";
        }

        # Se passou ate aqui, valida se o arquivo eh realmente uma imagem
        else {

            # Obter o tipo MIME real do arquivo usando a extensao
            if( FILES::notImageMime($input) ) {
                $error .= "
                <p>O arquivo enviado não é uma imagem com tipo mime válido.</p>";
            }

            # Valida se realmente eh uma imagem, verificando suas dimensoes
            # `getimagesize()` retorna false se o arquivo nao for uma imagem valida
            elseif( FILES::notImageDimensions($input) ) {
                $error .= "
                <p>O arquivo enviado tem o tipo mime válido, 
                porém não tem as dimensões de uma imagem válida.</p>";
            }
        }

        # Se alguma mensagem de erro foi acumulada.
        if( ! empty( $error ) ) {
            alert( "error discard", $error );
            return false;
        }

        return true;
    }
    
}