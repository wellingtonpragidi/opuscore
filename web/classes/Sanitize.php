<?php
declare( strict_types = 1 );
/**
 * Fornece metodos de higienizacao para areas publicas (Output) "/web/ e /templates/"
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Output\Ensure\Integrity\Purifier
 */

class Sanitize {

    public const ALLOW_LINKS = 1;

    /**
     * Esse metodo geralmente eh usado apos Validate::name fazendo uma higienizacao basica:
     * - Corrigindo imperfeicoes de digitacao e removendo espacos do inicio/fim e consecutivos
     * - Convertendo caracteres especiais para entidades HTML
     */
    public static function name( mixed $name ): string {
        if( ! is_string($name) || $name === '' ) {
            return '';
        }
        # Ensure::squeeze Remove multiplos espaços, quebras de linha e tabulacoes 
        # Usando a flag REMOVE_EDGE_WHITESPACE remove tambem espacos do inicio/fim 
        $name = Ensure::squeeze( $name, Ensure::REMOVE_EDGE_WHITESPACE );

        return Ensure::string( $name );
    }


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
        $allowedTags = [ 'div', 'br', 'code', 's', 'u', 'b', 'i' ];

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

            $name  = strtolower($attr->nodeName);
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
        foreach( $doc->getElementsByTagName('div')->item(0)->childNodes as $child ) {

            $output .= $doc->saveHTML($child);
        }

        return trim($output);
    }

}


