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

/**
 * Essa classe fornece metodos de higienizacao para dados de entrada em 
 * areas publicas "/web/" e /template/
 */
class Sanitize {

    public const ALLOW_LINKS = 1;

    public static function comment( mixed $comment, int $flags = 0 ): string {
        if( ! is_string($comment) || $comment === '' ) {
            return '';
        }
        # 1. remove scheme/protocolo do texto $comment
        $comment = Ensure::removeScheme($comment);

        # 2. remove qualquer host
        $comment = Ensure::removeHosts($comment);

        # 3. agora texto sem hosts, sem URLs. Tratamento de HTML:

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        # wrapper evitar lixo de html/body
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $comment . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $xpath = new DOMXPath($doc);

        # Tags HTML permitidas
        $allowedTags = ['p', 'br', 'code', 's', 'u', 'b', 'i' ];

        if( $flags & self::ALLOW_LINKS ) {
            $allowedTags[] = 'a';
        }

        # Remove tags nao permitidas (mantem conteudo)
        foreach( $xpath->query('//*') as $node ) {
            if( ! in_array($node->nodeName, $allowedTags, true) ) {
                $node->parentNode->replaceChild(
                    $doc->createTextNode($node->textContent),
                    $node
                );
            }
        }

        # Remove atributos perigosos
        foreach( $xpath->query('//@*') as $attr ) {
            $name = strtolower($attr->nodeName);
            $value = strtolower($attr->nodeValue);

            # remove eventos
            if( str_starts_with($name, 'on') ) {
                $attr->ownerElement->removeAttributeNode($attr);
                continue;
            }

            # remove javascript:
            if( str_contains($value, 'javascript:') ) {
                $attr->ownerElement->removeAttributeNode($attr);
                continue;
            }

            # soh <a> pode ter href
            if( $attr->ownerElement->nodeName !== 'a' ) {
                $attr->ownerElement->removeAttributeNode($attr);
            }
        }

        # Tratamento especial para links
        if( $flags & self::ALLOW_LINKS ) {
            foreach( $xpath->query('//a') as $anc ) {
                $href = $anc->getAttribute('href');

                if( ! $href || ! preg_match('#^https?://#i', $href) ) {
                    # remove link invalido mas mantem texto
                    $anc->parentNode->replaceChild(
                        $doc->createTextNode($anc->textContent),
                        $anc
                    );
                    continue;
                }

                $anc->setAttribute('target', '_blank');
                $anc->setAttribute('rel', 'noopener noreferrer nofollow');
            }
        } 
        else {
            # remove qualquer <a>
            foreach( $xpath->query('//a') as $anc ) {
                $anc->parentNode->replaceChild(
                    $doc->createTextNode($anc->textContent),
                    $anc
                );
            }
        }

        # Extrai conteudo limpo
        $output = '';
        foreach ($doc->getElementsByTagName('div')->item(0)->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }

        return trim($output);
    }

}


/**
 * Essa classe fornece metodos de validacoes para areas publicas "/web/" e /template/
 */
class Validate {

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
    public static function image_upload( string $input = 'attachment' ): bool {
        
        # Campo opcional: Se nao foi enviado arquivo, considera valido
        # Se $_FILES[$input] NAO existir e se o nome do arquivo for vazio, nao ha upload
        # Soh faz validacao se $_FILES[$input] existir para nao atrapalhar| caso edite soh outros campos
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