<?php
/**
 * Comprime o codigo HTML removendo comentarios, quebras de linha, tabs e espacos extras.
 *
 * Esta funcao eh projetada para reduzir o tamanho do HTML, mas tenta preservar
 * a funcionalidade de scripts e estilos. Eh crucial que um unico espaco seja
 * mantido entre tags HTML quando elas nao tem conteudo entre si, para evitar
 * quebra de layout e a necessidade de usar &nbsp; manualmente no codigo PHP.
 *
 * @param string $html O codigo HTML a ser comprimido.
 * @return string O codigo HTML comprimido.
 */
function compress_HTML( string $html ): string {
    # Protege o conteudo de blocos <script> e <style> para evitar que sejam comprimidos.
    # Isso eh crucial para nao quebrar o JavaScript ou CSS inline.
    preg_match_all( '#<(script|style)\b[^>]*>.*?</\1>#is', $html, $matches );
    $placeholders = [];
    foreach( $matches[0] as $i => $original ) {
        $placeholder = "___HTMLCOMPRESSOR_PLACEHOLDER_$i___";
        $placeholders[$placeholder] = $original;
        $html = str_replace( $original, $placeholder, $html );
    }

    /**
     * Remove comentarios HTML ().
     * Tambem inclui a remocao de comentarios condicionais do IE ().
     */
    $html = preg_replace( '//', '', $html );

    # Remove quebras de linha (CRLF, CR, LF) e caracteres de tabulacao.
    $html = str_replace( ["\r\n", "\r", "\n", "\t"], '', $html );

    # Remove multiplos espacos em branco consecutivos, substituindo-os por um unico espaco.
    $html = preg_replace( '/\s{2,}/', ' ', $html );

    /**
     * Remove espacos ENTRE as tags, mas mantem um unico espaco.
     * Isso eh feito para evitar que elementos HTML renderizados via PHP
     * fiquem colados, o que forcaria o desenvolvedor a usar &nbsp;
     * Por exemplo: `</p> <span>` permanece assim, mas `>    </` vira `> <`.
     */
    $html = preg_replace( '/>\s+</', '> <', $html );

    # Restaura o conteudo original dos blocos <script> e <style> que foram protegidos.
    foreach( $placeholders as $placeholder => $original ) {
        $html = str_replace( $placeholder, $original, $html );
    }

    # Remove espacos em branco do inicio e fim da string HTML final.
    return trim( $html );
}

/**
 * Comprime o codigo HTML com diferentes niveis de intensidade.
 *
 * Permite controlar o grau de compressao aplicado ao HTML, preservando
 * certos elementos (como comentarios) dependendo do nivel escolhido.
 * Eh crucial que um unico espaco seja mantido entre tags HTML quando elas
 * nao tem conteudo entre si, para evitar quebra de layout.
 *
 * @param string $html O codigo HTML a ser comprimido.
 * @param string $level O nivel de compressao: 'high', 'medium' ou 'low'.
 * - 'high': maxima compressao (remove comentarios, quebras de linha, tabs e espacos extras).
 * - 'medium': remove quebras de linha e espacos, mas mantem comentarios.
 * - 'low': apenas protecao de script/style e remocao de multiplos espacos.
 * @return string O codigo HTML comprimido.
 *
 * @todo Testar e refinar a logica de compressao para os niveis 'high', 'medium' e 'low'.
 * Verificar se a remocao de espacos entre tags e comentarios esta de acordo
 * com a agressividade desejada para cada nivel. Atualmente, a regra
 * `/>\s+</` para `> <` eh aplicada para 'high' e 'medium', e o bloco `if ($level === 'high')`
 * precisaria de uma logica de compressao distinta para ter efeito adicional.
 */
function level_compress_HTML( string $html, string $level = 'high' ): string {
    # Protege o conteudo de blocos <script> e <style> temporariamente.
    preg_match_all( '#<(script|style)\b[^>]*>.*?</\1>#is', $html, $matches );
    $placeholders = [];
    foreach( $matches[0] as $i => $original ) {
        $placeholder = "___HTMLCOMPRESSOR_PLACEHOLDER_$i___";
        $placeholders[$placeholder] = $original;
        $html = str_replace( $original, $placeholder, $html );
    }

    # Remove comentarios HTML so se o nivel de compressao nao for 'low'.
    if( $level !== 'low' ) {
        $html = preg_replace( '//', '', $html );
    }

    # Aplica compressao 'high' ou 'medium'.
    if( $level === 'high' || $level === 'medium' ) {
        # Remove quebras de linha e tabs.
        $html = str_replace( ["\r\n", "\r", "\n", "\t"], '', $html );
        
        # Remove multiplos espacos em branco consecutivos, substituindo-os por um unico espaco.
        $html = preg_replace( '/\s{2,}/', ' ', $html );

        /**
         * Remove espacos ENTRE as tags, mas mantem um unico espaco (`> <`).
         * Esta regra se aplica a ambos os niveis 'high' e 'medium'.
         * Isso evita quebra de layout em HTML gerado por PHP sem uso de &nbsp;.
         */
        $html = preg_replace( '/>\s+</', '> <', $html );
        
        # Se a intencao eh que 'high' tenha uma remocao ainda mais agressiva de espacos entre tags
        # (ex: resultando em `><`), a regex abaixo precisaria ser diferente da anterior.
        # Caso contrario, esta linha e redundante.
        if( $level === 'high' ) {
            $html = preg_replace( '/>\s+</', '> <', $html );
        }
    }

    # Restaura o conteudo original dos blocos <script> e <style>.
    foreach( $placeholders as $placeholder => $original ) {
        $html = str_replace( $placeholder, $original, $html );
    }

    # Remove espacos em branco do inicio e fim da string HTML final.
    return trim( $html );
}


/**
 * Comprime o codigo JavaScript removendo comentarios, quebras de linha,
 * espacos extras e espacos em torno de operadores e delimitadores.
 *
 * Protege strings para garantir que seu conteudo nao seja alterado.
 */
function compress_JS( string $code ): string {

    # 1. Remove comentários multi-line /* */ (Isso é seguro)
    $code = preg_replace( '#/\*.*?\*/#s', '', $code );

    # 2. Protege Strings E Expressões Regulares (Regex)
    # A mágica está em esconder tudo que está entre /.../ que não seja divisão
    $protected = [];
    $code = preg_replace_callback(
        '/("(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|(?<=[\s\(\[\{\=\,\!\&\|])\/(?![*\/])(?:\\\\\/|[^\/\n])+\/(?=[gimuy\s\)\;\,\{\}\[\]]|$))/',
        function ($m) use (&$protected) {
            $key = '__OPUS_PROT_' . count($protected) . '__';
            $protected[$key] = $m[0];
            return $key;
        },
        $code
    );

    # 3. Agora sim: Remove comentários de linha única // 
    # Como as strings e regex (que têm //) estão escondidas, aqui é seguro!
    $code = preg_replace('/(?m)^\s*\/\/.*$/', '', $code); // Linhas que só tem comentário
    $code = preg_replace('/\/\/.*$/m', '', $code);      // Comentários no fim da linha

    # 4. Remove quebras e excesso de espaço
    $code = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $code);
    $code = preg_replace('/\s+/', ' ', $code);

    # 5. Remove espaços ao redor de tokens
    $code = preg_replace('/\s*([{}[\]();,:=<>+\-*\/&|!])\s*/', '$1', $code);

    # 6. Restaura tudo (Strings e Regex)
    if (!empty($protected)) {
        $code = str_replace(array_keys($protected), array_values($protected), $code);
    }

    return trim($code);
}


/**
 * Comprime o codigo CSS removendo comentarios, quebras de linha,
 * tabs e espacos extras para reduzir o tamanho do arquivo.
 *
 * @param string $css O codigo CSS a ser comprimido.
 * @return string O codigo CSS comprimido.
 */
function compress_CSS( string $css ): string {
    # Remove comentarios CSS de multiplas linhas: /* ... */
    $css = preg_replace( "!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $css );

    # Remove espacos apos dois pontos
    $css = str_replace( ": ", ":", $css );

    # Remove espacos antes e depois de chaves de abertura
    $css = str_replace( [" {", "{ "], "{", $css );

    # Remove espacos antes e depois de chaves de fechamento e ponto e virgula seguido imediatamente por chave de fechamento
    $css = str_replace( [" }", "} ", "; }", ";}"], "}", $css );

    # Remove espacos antes e depois de parenteses de abertura 
    $css = str_replace( [" (", "( "], "(", $css );

    # Remove espacos antes e depois de parenteses de fechamento
    $css = str_replace( [" )", ") "], ")", $css );

    # Remove espacos antes de pontos em seletores (ex: ", .class").
    $css = str_replace( ", .", ",.", $css );

    # Remove espacos antes de cerquilhas em seletores
    $css = str_replace( ", #", ",#", $css );

    # Remove espacos apos virgulas (usado em listas de seletores ou valores).
    $css = str_replace( ", ", ",", $css );

    # Remove espacos apos ponto e virgula
    $css = str_replace( "; ", ";", $css );

    # Remove multiplos espacos em branco consecutivos.
    $css = preg_replace( '/\s{2,}/', "", $css );

    # Remove quebras de linha (CRLF, CR, LF) e caracteres de tabulacao.
    $css = str_replace( ["\r\n", "\r", "\n", "\t"], "", $css );

    return $css;
}