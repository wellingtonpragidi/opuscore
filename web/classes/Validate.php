<?php
declare( strict_types = 1 );
/**
 * Fornece metodos de validacoes para areas publicas (Output) "/web/ e /templates/"
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Output\Ensure\Integrity\Purifier
 */

class Validate {

    /**
     * validacao usada no upload da imagem de perfil do usuario
     */
    public static function imageUpload( string $input = 'attachment' ): array {    
        # ( ! isset($_FILES[$input]) ) || ( empty($_FILES[$input]['name']) )
        if( FILES::not($input) || FILES::empty($input) ) {
            return [
                'status' => false,
                'alert'  => ''
            ];
        }

        if( FILES::size($input) > 1 *MB ) {
            $error = 'O arquivo excede o limite de <code>1 MB</code> definido pelo site.';
        }

        # Verifica se houve erro no upload
        else if( FILES::hasError($input) ) {
            $error = '
            Erro no upload do arquivo. Código: <code>' . FILES::error($input) . '</code>.
            <p>' . FILES::errors() . '</p>';
        }

        # Verifica se o arquivo temporario existe e eh um arquivo enviado via POST HTTP
        else if( FILES::notTemp($input) || ! FILES::isUploaded($input) ) {
            $error = 'O arquivo enviado é inválido ou não foi carregado corretamente.';
        }

        else {
            # Se passou ate aqui, valida se o arquivo realmente eh uma imagem

            # Obter o tipo MIME real do arquivo usando a extensao
            if( FILES::notImageMime($input) ) {
                $error = 'O arquivo enviado não é uma imagem com tipo mime válido.';
            }

            # Valida se realmente eh uma imagem, verificando suas dimensoes
            # `getimagesize()` retorna false se o arquivo nao for uma imagem valida
            else if( FILES::notImageDimensions($input) ) {
                $error = 'O arquivo enviado não tem as dimensões de uma imagem válida.';
            }
        }


        # return s

        if( isset($error) ) {
            return [
                'status' => false,
                'alert'  => $error
            ];
        }

        return [
            'status' => true,
            'alert'  => null
        ];
    }

    /**
     * projetado especificamente para entradas de "nome" retornando strings de alertas
     * 
     * entradas de NOME e SOBRENOME (nao username)
     * 
     * Aplica diversas validacoes com restricoes, Nao permitindo:
     * - tags HTML
     * - entidades HTML
     * - protocolos (http/s e mailto)
     * Em caso de falha nas validacoes, exibe um alerta e retorna uma string vazia
     * 
     * Sim, exceto nome + sobrenome, poderia sanitizar em vez de uma validacao dessas: 
     * Mas nao, um usuario inserindo essas tipos de dados no campo para entrada de nome 
     * nao merece que tratemos a entrada dele, 
     * melhor que veja que o sistema nao aceita patifarias e va embora
     * - Se foi erro acidental do usuario, ele ira acertar e o sistema vai aceitar
     */
    public static function name( mixed $value ): string|true {
        # Detecta elementos HTML
        if( $value !== strip_tags($value) ) {
            return 'Tags HTML não são permitidas.';
        }

        # Detecta ENTIDADES HTML
        if( preg_match('/&[a-zA-Z0-9#]+;/', $value) ) {
            return 'Entidades HTML não são permitidas.';
        }

        # Detecta http(s) ou mailto
        if( preg_match('/\b(?:https?:\/\/|mailto:)/i', $value) ) {
            return 'Protocolos não são permitidos.';
        }

        # tamanho maximo do nome
        if( mb_strlen($value) > 40 ) {
            return 'Máximo de 40 caracteres.';
        }

        # Verifica se tem Nome + Sobrenome 
        if( substr_count($value, ' ') < 1 ) {
            return 'É necessário informar Nome e Sobrenome.';
        }

        # tamanho minimo do nome + sobrenome
        if( mb_strlen($value) < 5 ) {
            return 'Nome completo precisa ter pelo menos 5 caracteres.';
        }

        return true;
    }
    
}