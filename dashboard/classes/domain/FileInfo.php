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

    public const EXT = [
        'image' => [
            'bitmap' => ['jpeg', 'jpg', 'png', 'gif', 'webp', 'tiff', 'tif', 'bmp'],
            'vector' => ['svg']
        ],
        'video' => [
            'mp4', 'avi', 'mkv', 'wmv', 'rmvb', 'ogv'
        ],
        'audio' => [
            'mp3', 'ogg', 'oga', 'opus'
        ],
        'docs'  => [
            'txt', 'md', 'markdown', 'html', 'pdf', 'rar', 'zip', 'docx', 'rtf', 'odt', 'word'
        ],
    ];

    # @example : array_merge(...FILES::EXT_ALL);
    public const EXT_ALL = [
        self::EXT['image']['bitmap'], 
        self::EXT['image']['vector'], 
        self::EXT['video'], 
        self::EXT['audio'], 
        self::EXT['docs']
    ];



    public const MIMES = [
        'image' => [
            'image/jpeg', 
            'image/png', 
            'image/gif', 
            'image/webp',
            'image/tiff', 
            'image/bmp', 
            'image/svg+xml',
        ],
        'audio' => [
            'audio/mp3', 
            'audio/mpeg', 
            'audio/x-mp3', 
            'audio/mpeg3',
            # OGG
            'audio/ogg', 
            'audio/vorbis', 
            'audio/opus'
        ],
        'video' => [
            'video/mp4', 
            'video/mpeg', 
            'video/x-m4v', 
            'application/mp4', 
            'application/x-mp4', 
            'application/mpeg4-iod', 
            'application/mpeg4-generic',
            ## OGG 
            'video/ogv', 
            'video/ogg',
        ],
        'doc'  => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-zip',
            # TEXTO
            'text/html', 
            // 'text/plain', 
            'text/markdown', 
            'application/rtf', # RTF
            'application/vnd.oasis.opendocument.text'
        ],
        'container' => [
            'application/ogg', 
            'application/x-ogg'
        ]
    ];

    public const MIME = [ 
        self::MIMES['image'], 
        self::MIMES['audio'], 
        self::MIMES['video'], 
        self::MIMES['doc'], 
        self::MIMES['container'], 
    ];



    public static function mimes() {
        $mimes = [];
        foreach( self::MIMES as $values ) {

            foreach( $values as $mime ) {

                $mimes[] = $mime;
            }

        }

        return $mimes;
    }
    
    public static function mimeType( mixed $filedir ): ?string {
        if( ! $filedir || ! is_string($filedir) ) {
            return null;
        }

        $finfo = new finfo( FILEINFO_MIME_TYPE );

        return $finfo->file($filedir) ?: null;
    }


    /**
     * Retorna o tamanho de um arquivo em formato de leitura
     * Se filesize for true, calcula tamanho e formata para uma string mais amigavel
     * @return ex: 1.23 MB, 500 B ou 0 B se o arquivo nao existir
     */
    public static function size( ?string $filedir ): ?string {
        if( $filedir === null ) {
            return null;
        }

        $size = @filesize($filedir);
        if( $size === false ) {
            return '0 B';
        }

        $units = [ 'B', 'KB', 'MB', 'GB' ];

        $power = $size > 0
            ? (int) floor( log($size, 1024) )
            : 0;

        $power = min( $power, count($units) - 1 );

        $size = $size / (1024 ** $power);

        return round($size, 2) . ' ' . $units[$power];
    }



    public static function is_bitmap( ?string $filedir ): bool {
        if( $filedir === null ) {
            return false;
        }

        $is_vector = self::MIMES['image'] === 'image/svg+xml';

        if( ! $is_vector ) {
            $bitmaps = self::MIMES['image'];
        }

        return in_array( self::mimeType($filedir), $bitmaps );
    }




    public static function imageDimensions( ?string $filedir ): ?array {
        if( $filedir === null ) {
            return null;
        }

        $mime = self::mimeType($filedir);

        if( ! $mime || ! str_starts_with($mime, 'image/') ) {
            return null;
        }

        $info = @getimagesize($filedir);

        if( ! $info || empty($info[0]) || empty($info[1]) ) {
            return null;
        }

        return [
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }

    

    # Retorna a extensao de um arquivo com ou sem o ponto da extensao
    public static function extension( string $filename, bool $period = false ): string {

        $ext = pathinfo( $filename, PATHINFO_EXTENSION );

        if( $period ) {

            return ".{$ext}";
        }

        return $ext;
    }


    # nome do arquivo com ou sem a extensao:
    ## basename( 'filename' ); retorna nome do arquivo com a extensao
    ## pathinfo( 'filename', PATHINFO_FILENAME ); retorna nome do arquivo sem a extensao

}