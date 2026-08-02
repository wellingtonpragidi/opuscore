<?php
declare( strict_types = 1 );
/**
 * Fornece metodos utilitarios para manipular e validar dados enviados via $_FILES
 *
 * Centraliza o acesso e a verificacao de dados de upload de arquivos 
 * simplificando operacoes comuns e aumentando a legibilidade do codigo.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 *
 * @package Core\HTTP
 * @subpackage Superglobal\Validate
 */
class FILES {


    private static string $error = '';


    public static function has( string $name = 'attachment' ): bool {
        return isset( $_FILES[$name] );
    }

    public static function not( string $name = 'attachment' ): bool {
        return ! isset( $_FILES[$name] );
    }


    public static function GET( string $name = 'attachment' ): array {
        return $_FILES[$name] ?? [];
    }


    public static function name( string $name = 'attachment' ): string {
        return $_FILES[$name]["name"] ?? '';
    }

    # caminho temporario onde o arquivo foi armazenado no servidor
    public static function tmp( string $name = 'attachment' ): string {
        return $_FILES[$name]["tmp_name"] ?? '';
    }


    # retorna o tamanho do arquivo enviado em bytes
    public static function size( string $name = 'attachment' ): int {
        return $_FILES[$name]["size"] ?? 0;
    }


    # retorna o codigo de erro numerico do upload
    public static function error( string $name = 'attachment' ): int {
        return $_FILES[$name]["error"] ?? UPLOAD_ERR_NO_FILE; # 4 = UPLOAD_ERR_NO_FILE
    }


    
    # ----            ----           ----             ----           ---- #
    #                                                                     #
    #                                                                     #
    # ------------------------------------------------------------------- #
    #                                                                     #
    #                                                                     #
    # ----            ----           ----             ----           ---- #
    
    public static function empty( string $name = 'attachment' ): bool {
        return empty( self::name($name) );
    }
    public static function isEmpty( string $name = 'attachment' ): bool {
        return self::empty($name);
    }

    public static function notEmpty( string $name = 'attachment' ): bool {
        return ! empty( self::name($name) );
    }


    public static function notTemp( string $name = 'attachment' ): bool {
        return empty( self::tmp($name) );
    }
    
    
    /**
     * verifica se o arquivo esta definido e possui um nome valido
     * isset( $_FILES[$name] ) &&  ! empty( $_FILES[$name]["name"] )
     */
    public static function isDefined( string $name = 'attachment' ): bool {
        return self::has($name) && ! self::empty($name);
    }


    public static function isUploaded( string $name = 'attachment' ): bool {
        return is_uploaded_file( self::tmp($name) );
    }



    public static function isValid( string $name = 'attachment' ): bool {
        return self::notError($name) && ! self::empty($name) && self::isUploaded($name);
    }

    # o mesmo que: return self::hasError($name) || self::empty($name) || ! self::isUploaded($name);
    public static function isInvalid( string $name = 'attachment' ): bool {
        return ! self::isValid($name);
    }


    /**
     * Retorna a extensao do arquivo enviado
     * antes de tentar obter a extensao verifica se o arquivo foi de fato um upload POST HTTP valido
     */
    public static function ext( string $name = 'attachment' ): string {
        if( self::isDefined($name) && self::isUploaded($name) ) {
            $extension = pathinfo( self::name($name), PATHINFO_EXTENSION ) ?: '';

            return strtolower( $extension );
        }

        return '';
    }


    # $_FILES[$name]["error"] !== UPLOAD_ERR_OK
    public static function hasError( string $name = 'attachment' ): bool {
        return self::error($name) !== 0;
    }


    # $_FILES[$name]["error"] === UPLOAD_ERR_OK
    public static function notError( string $name = 'attachment' ): bool {
        return self::error($name) === 0;
    }


    # retorna descricao textual do codigo de erro de upload
    public static function errors( string $name = 'attachment' ): string {
        switch( self::error($name) ) {
            case UPLOAD_ERR_OK:
            case 0:
                self::$error = 'O arquivo foi carregado com sucesso.'; # Nao ha erro
            break;
            case UPLOAD_ERR_INI_SIZE:
            case 1:
                self::$error = 'O arquivo excede o limite <code>upload_max_filesize</code> no <code>php.ini</code>.';
            break;
            case UPLOAD_ERR_FORM_SIZE:
            case 2:
                self::$error = 'O arquivo excede o limite <code>MAX_FILE_SIZE</code> definido no formulário.';
            break;
            case UPLOAD_ERR_PARTIAL:
            case 3:
                self::$error = 'O upload foi feito parcialmente.';
            break;
            case UPLOAD_ERR_NO_FILE:
            case 4:
                self::$error = 'Nenhum arquivo foi enviado.';
            break;
            case UPLOAD_ERR_NO_TMP_DIR:
            case 6:
                self::$error = 'Pasta temporária ausente.';
            break;
            case UPLOAD_ERR_CANT_WRITE:
            case 7:
                self::$error = 'Falha ao escrever o arquivo no disco.';
            break;
            case UPLOAD_ERR_EXTENSION:
            case 8:
                self::$error = 'Uma extensão do PHP interrompeu o upload. Use <code>phpinfo()</code> para investigar.';
            break;
            default:
                self::$error = 'Erro de upload desconhecido.';
            break;
        }

        return self::$error;
    }

    public static function mime( string $name, string $mtype ): bool {
        $tmp = self::tmp($name);
        return mime_content_type($tmp) === $mtype;
    }

    # verifica se o arquivo eh uma imagem por: mime_content_type() 'image/*' e getimagesize()
    public static function isImage( string $name = 'attachment' ): bool {
        return self::isImageMime($name) && self::hasImageDimensions($name);
    }

    # verifica se o arquivo NAO eh uma imagem por: mime_content_type() 'image/*' e getimagesize()
    public static function notImage( string $name = 'attachment' ): bool {
        return self::notImageMime($name) && self::notImageDimensions($name);
    }


    # verifica se o arquivo eh uma imagem: mime_content_type 'image/*'
    public static function isImageMime( string $name = 'attachment' ): bool {
        $mime_type = mime_content_type( self::tmp($name) );
        return str_starts_with( $mime_type, 'image/' );
    }
    
    # verifica se o arquivo NAO eh uma imagem: mime_content_type 'image/*'
    public static function notImageMime( string $name = 'attachment' ): bool {
        return ! self::isImageMime($name);
    }

    # Verifica se o arquivo eh realmente uma imagem verificando suas dimensoes
    public static function hasImageDimensions( string $name = 'attachment' ): bool {
        return (bool) getimagesize( self::tmp($name) );
    }

    # Verifica se o arquivo NAO eh uma imagem real verificando suas dimensoes
    public static function notImageDimensions( string $name = 'attachment' ): bool {
        return ! self::hasImageDimensions($name);
    }
    
}