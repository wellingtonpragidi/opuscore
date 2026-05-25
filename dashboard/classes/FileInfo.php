<?php
declare( strict_types = 1 );
/**
 * Classe utilitaria para obter informacoes detalhadas sobre arquivos.
 *
 * Esta classe estatica oferece metodos para extrair diversas propriedades de arquivos,
 * como nome, extensao, tipo MIME, tamanho formatado, dimensoes de imagem
 * e data da ultima modificacao.
 *
 * @package System\Utils
 */
class FileInfo {

    /**
     * Retorna o nome do arquivo, com ou sem a extensao.
     *
     * Este metodo extrai o nome base do arquivo de um caminho completo.
     * Por padrao, inclui a extensao. Se o parâmetro $with_ext for `false`,
     * apenas o nome do arquivo sem a extensao sera retornado.
     *
     * @param string $file_name O caminho completo ou nome do arquivo.
     * @param bool $with_ext Se `true` (padrao), retorna o nome com a extensao;
     * se `false`, retorna apenas o nome sem extensao.
     * @return string O nome do arquivo (com ou sem extensao).
     */
    public static function filename( string $file_name, bool $with_ext = true ): string {
        if( $with_ext ) {
            return basename( $file_name );
        }
        return pathinfo( $file_name, PATHINFO_FILENAME );
    }

    /**
     * Retorna a extensao de um arquivo.
     *
     * Extrai a extensao do arquivo a partir de um caminho.
     * Opcionalmente, pode incluir o ponto inicial (e.g., ".txt" em vez de "txt").
     *
     * @param string $file_name O caminho completo ou nome do arquivo.
     * @param bool $period Se `true`, retorna a extensao precedida por um ponto (ex: ".txt");
     * se `false` (padrao), retorna apenas a extensao (ex: "txt").
     * @return string A extensao do arquivo.
     */
    public static function extension( string $file_name, bool $period = false ): string {
        if( $period == true ) {
            return "." . pathinfo( $file_name, PATHINFO_EXTENSION );
        }
        return pathinfo( $file_name, PATHINFO_EXTENSION );
    }

    /**
     * Retorna o tipo MIME de um arquivo.
     *
     * Utiliza a extensao Fileinfo para determinar o tipo MIME do arquivo.
     * Retorna `null` se o arquivo nao existir.
     *
     * @param string $file_dir O caminho completo do arquivo.
     * @return string|null O tipo MIME do arquivo (ex: "image/jpeg", "application/pdf") ou `null` se o arquivo nao existir.
     * @link https://www.php.net/manual/pt_BR/function.finfo-file.php Para mais detalhes sobre finfo.
     */
    public static function mimetype( string $file_dir ): ?string {
        if( file_exists($file_dir) ) {
            $finfo = new finfo( FILEINFO_MIME_TYPE );
            return $finfo->file( $file_dir );
        }

        return null;
    }

    /**
     * Retorna o tamanho de um arquivo formatado em unidades legíveis (B, KB, MB, GB, TB).
     *
     * Verifica a existência do arquivo e, se encontrado, calcula seu tamanho
     * e o formata para uma string mais amigavel.
     *
     * @param string $file_dir O caminho completo do arquivo.
     * @return string O tamanho do arquivo formatado (ex: "1.23 MB", "500 B") ou "0 B" se o arquivo nao existir.
     */
    public static function size( string $file_dir ): string {
        $size = '0 B';
        if( file_exists($file_dir) ) {
            $size =  filesize( $file_dir );
            $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
            $power = ( $size > 0 ) ? (int) floor( log($size, 1024) ) : 0;
            $power = min( $power, count($units) - 1 );
            $size = $size / (1024 ** $power);
            return round($size, 2) . ' ' . $units[$power];
        }

        return $size;
    }

    /**
     * Retorna as dimensoes (largura x altura) de uma imagem.
     *
     * Verifica se o arquivo existe e se e uma extensao de imagem suportada
     * (JPG, JPEG, PNG, WEBP, GIF, SVG). Retorna as dimensoes formatadas
     * ou `null` se a imagem nao puder ser processada ou nao for encontrada.
     */
    public static function dimension( ?string $file_dir ): ?string {
        if( ! file_exists($file_dir) ) {
            return null;
        }
        
        $valid_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = pathinfo( $file_dir, PATHINFO_EXTENSION );
        if( in_array($ext, $valid_ext) ) {
            
            $size   = getimagesize( $file_dir );
            $width  = number_format( $size[0], 0, '.', '.' );
            $height = number_format( $size[1], 0, '.', '.' );

            if( ! empty($width) && ! empty($height) ) {

                return $width . ' &times; ' . $height;
            }
        }
        
        return null;
    }

    /**
     * @param $file | pode ser por path (filesystem), arquivo temporario por $_FILES ou a URL
    */
    public static function imageDimensions( ?string $file ): ?array {
        if( ! $file || ! file_exists($file) ) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file);

        if( ! $mime || ! str_starts_with($mime, 'image/') ) {
            return null;
        }

        $info = @getimagesize($file);

        if( ! $info || empty($info[0]) || empty($info[1]) ) {
            return null;
        }

        return [
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }

    /**
     * Retorna a data e hora da ultima modificacao de um arquivo.
     *
     * Verifica a existência do arquivo e, se encontrado, retorna a data e hora
     * da ultima modificacao formatada como "dd/mm/AAAA às HH:MM:SS".
     */
    public static function fileupdated( string $file_dir ): ?string {
        if( file_exists($file_dir) ) {
            return date( "d/m/Y \à\s H:i:s", filemtime($file_dir) );
        }

        return null;
    }

}