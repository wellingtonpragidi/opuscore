<?php 
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * @author     Wellington Pragidi
 * 
 * @package Core\Media
 * @see https://internal/classes/imagehandler
 * 
 * 
 * ['wide', 'larger', 'minor', 'thumb']
 * ['wide', 'larger', 'minor']
 * ['plain', 'thumb']
 * ['profile', 'avatar']
  */

class ImageHandler {

    # qualidade padrao para imgens .jpg/.jpeg e .webp
    private const QUALITY_JPEG = 100;

    private const COMPRESS_PNG = 9;

    /**
     * Processa imagem enviada via formulario apos serem redimensionadas e cortadas 
     * pelas funcoes imagick() ou GD()
     * Cria diretorio caso nao exista, e os diretorios padroes, todo ano e mes 
     * Verifica se existe extensao imagick e GD e tambem a classe Imagick
     * 
     * - Imagick eh a opcao nº 1 
     * - Biblioteca de imagens GD eh a opcao nº 2
     * - 3ª retorna false; com warning
     * 
     * @param $args 
     * argumentos obrigatorios: 
     * 'input'    => O valor do name de $_FILES
     * 'filename' => O nome final do arquivo 
     * 'width' e 'height' => Largura e altura da imagem a ser redimensionada e cortada
     * 'quality' => qualidade das imagens jpeg e webp 0-100 | padrao 100
     * 
     * argumentos opcionais: 'path' => O caminho relativo do arquivo
     * 
     * 
     * ESSA FUNCAO ASSIM COMO imagick(), GD() e moveupload() NAO FAZ NENHUM TIPO DE VALIDACAO
     * ISSO FICA POR CONTA DO CONTROLADOR — SEJA DIRETAMENTE OU POR MEIO DE validators ou helpers
     */
    public static function resolve( array $args ): bool {
        if( $args['width'] <= 0 || $args['height'] <= 0 ) {

            return false;
        }

        # Usa caminho passado no argumento ou o padrao do ano/mes/ atual
        $relative_path = $args['path'] ?? date('Y/m/');
        

        $abs_path = UPLOAD_DIR . $relative_path;


        if( ! is_dir($abs_path) ) {

            if( ! mkdir($abs_path, 0755, true) ) {

                return false;
            }
        }

        $ext = FILES::ext( $args['input'] );

        $data = [
            'input'    => $args['input'],
            'abs_path' => $abs_path,
            'filename' => $args['filename'] . '.' . $ext,
            'width'    => $args['width'],
            'height'   => $args['height'],
            'quality'  => $args['quality'] ?? self::QUALITY_JPEG
        ];

        # Usa Imagick se disponivel
        if( extension_loaded('imagick') && class_exists('Imagick') ) {

            return self::imagick( $data, $ext );
        } 
        # senao nao encontrado extensao ou classe Imagick, usa GD
        else if( extension_loaded('gd') ) {

            return self::GD( $data, $ext );
        }
        # exibe erro por falta de biblioteca 
        else {
            alert( 'warning', 
                'Nenhum processador de imagem encontrado.
                <p>Seu servidor PHP não tem nenhuma biblioteca para manipular imagens.</p>
                O que impossibilita o sistema de redimensionar e cortar imagens.'
            );

            return false;
        }

        return false;
    }


    /**
     * Processa e redimensiona imagens usando a extensao Imagick.
     *
     * Esta funcao eh projetada para criar versoes redimensionadas de imagens, 
     * mantendo a proporcao e preenchendo as dimensoes alvo atraves de corte (crop to fit)
     * Suporta formatos GIF (incluindo animacoes), PNG, JPEG/JPG e WebP (dependendo da compilacao do Imagick).
     */
    private static function imagick( array $args, string $ext ): bool {
        
        if( $ext === 'webp' ) {
            $formats = array_map( 'strtolower', Imagick::queryFormats() );

            if( ! in_array('webp', $formats, true) ) {

                return self::GD([
                    'input'    => $args['input'],
                    'abs_path' => $args['abs_path'],
                    'filename' => $args['filename'],
                    'width'    => $args['width'],
                    'height'   => $args['height'],
                    'quality'  => $args['quality'] ?? self::QUALITY_JPEG
                ]);
            }
        }

        $tmp = FILES::tmp( $args['input'] );

        # Caminho completo para salvar a imagem
        $filepath = $args['abs_path'] . $args['filename'];

        try {

            $imagick = new Imagick( $tmp );

            self::watermark( $imagick );

            # VERIFICAR SE ARQUIVO TEMPORARIO EXISTE
            if( empty($tmp) || ! file_exists($tmp) ) {
                exception(
                    "Arquivo temporário não encontrado<b><code>: {$tmp}</code></b>", 
                    'ImageHandler::imagick'
                );

                return false;
            }
        }

        catch( ImagickException $e ) {

            exception( 
                "ImagickException: 
                Nao foi possivel criar objeto Imagick de <b><code>: '{$tmp}'</code></b>.<br>
                Erro: <b>{$e->getMessage()}</b>", 
                'ImageHandler::imagick'
            );

            return false;
        }

        # Definir qualidade
        if( $ext === 'webp' || $ext === 'jpeg' || $ext === 'jpg' ) {

            $imagick->setImageCompressionQuality( $args['quality'] ?? self::QUALITY_JPEG );
        }
        /**
         * else if( $ext === 'png' ) 
         * @todo 
         * Avaliar configuracao de controle de compressao para PNG usando os metodos
         * especificos do Imagick ( nao setImageCompressionQuality() )
         * setImageCompression() 
         * setOption()
         * $args['compression'] ?? self::COMPRESS_PNG 
         */
        
        try {

            # TRATAMENTO GIF ANINAMDO
            if( $ext === 'gif' ) {

                $imagick = $imagick->coalesceImages();

                foreach( $imagick as $frame ) {
                    $frame->cropThumbnailImage( $args['width'], $args['height'] );

                    $frame->setImagePage($args['width'], $args['height'], 0, 0);
                }

                $imagick = $imagick->deconstructImages();

                return (bool) $imagick->writeImages( $filepath, true );
            } 

            # TRATAMENTO PARA IMAGENS "NORMAIS" ( JPEG/.JPG, PNG, WEBP )
            else {

                $imagick->cropThumbnailImage( $args['width'], $args['height'] );

                return (bool) $imagick->writeImage( $filepath );
            }
        } 

        catch( Throwable $e ) {

            return false;
        }

        finally {

            if( isset($imagick) ) {

                $imagick->clear();
            }
        }


        return false;
    }

    /**
     * Processa, redimensiona e corta uma imagem enviada usando a biblioteca GD.
     *
     * Esta funcao eh projetada para criar versoes redimensionadas de imagens, mantendo a proporcao 
     * e preenchendo as dimensoes alvo atraves de corte (crop to fit)
     * Suporta formatos GIF, PNG, JPEG/JPG e WEBP.
     */
    private static function GD( array $args, string $ext ): bool {
        $tmp = FILES::tmp( $args['input'] );

        $imagefrom = match( $ext ) {
            'gif'         => imagecreatefromgif($tmp),
            'png'         => self::silent( fn() => imagecreatefrompng($tmp) ),
            'jpeg', 'jpg' => imagecreatefromjpeg($tmp),
            'webp'        => imagecreatefromwebp($tmp),
            'bmp'         => imagecreatefrombmp($tmp),
            default       => false,
        };


        # Dados originais (calculos em construct)
        $original = new class( imagesx($imagefrom), imagesy($imagefrom) ) {

            public float $ratio;

            public function __construct( public int $width, public int $height ) {

                $this->ratio = $this->width / $this->height;
            }
        };


        # Dados alvo 
        $target = new class( $args['width'], $args['height'] ) {

            public float $ratio;

            public function __construct( public int $width, public int $height ) {

                $this->ratio = $this->width / $this->height;
            }
        };


        # Objeto generico para receber area de corte
        $crop = new stdClass;


        # Definir area de corte proporcional
        if( $original->ratio > $target->ratio ) {

            # Imagem original mais larga -> corta nas laterais
            $crop->height = $original->height;
            $crop->width  = $original->height * $target->ratio;
            $crop->x      = ($original->width - $crop->width) / 2;
            $crop->y      = 0;
        } 
        else {

            # Imagem original mais alta -> cortar no topo a abaixo
            $crop->width  = $original->width;
            $crop->height = $original->width / $target->ratio;
            $crop->x      = 0;
            $crop->y      = ($original->height - $crop->height) / 2;
        }

        # Criar nova imagem com o tamanho desejado
        $imagecreate = imagecreatetruecolor( $target->width, $target->height );

        # Preservar transparencia para PNG e GIF
        if( $ext === 'png' || $ext === 'gif' ) {

            imagecolortransparent( 
                $imagecreate, 
                imagecolorallocatealpha( $imagecreate, 0, 0, 0, 127 ) 
            );

            imagealphablending( $imagecreate, false );

            imagesavealpha( $imagecreate, true );
        }

        # Copiar e redimensionar (Passando as propriedades dos objetos)
        imagecopyresampled(
            $imagecreate,

            $imagefrom,

            # posicao destino
            0, 0,         

            # posicao origem encapsulada 
            (int) $crop->x, 
            (int) $crop->y, 

            # tamanho destino encapsulado 
            $target->width, 
            $target->height,

            # tamanho da area cortada encapsulado 
            (int) $crop->width, 
            (int) $crop->height
        );


        $filepath = $args['abs_path'] . $args['filename'];

        $quality  = $args['quality'] ?? self::QUALITY_JPEG;
        $compress = $args['compress'] ?? self::COMPRESS_PNG;

        try {

            return match( $ext ) {
                'gif'         => imagegif( $imagecreate, $filepath ),
                'png'         => imagepng( $imagecreate, $filepath, $compress ),
                'jpeg', 'jpg' => imagejpeg( $imagecreate, $filepath, $quality ),
                'webp'        => imagewebp( $imagecreate, $filepath, $quality ),
                'bmp'         => imagebmp( $imagecreate, $filepath ),
                default       => false,
            };
        }

        catch( Throwable $e ) {

            return false;
        }

        finally {

            if( isset($imagecreate) && is_resource($imagecreate) ) {

                imagedestroy($imagecreate);
            }

            if( isset($imagefrom) && is_resource($imagefrom) ) {

                imagedestroy( $imagefrom );
            }
        }

    }




    /**
     * Aplica uma marca d'agua centralizada em um objeto Imagick existente.
     *
     * A marca d'agua e aplicada somente se a requisicao for POST e se o campo 'watermark'
     * estiver setado e o arquivo 'watermark.png' existir no diretorio de uploads.
     * A marca d'agua eh redimensionada para caber na imagem principal, se for maior.
     */
    private static function watermark( Imagick $imagick ): void {

        # Verificar se o campo 'watermark' foi marcado e se a imagem existe
        if( isset( $_POST['watermark'] ) && file_exists( UPLOAD_DIR . 'watermark.png' ) ) {

            # Criar objeto Imagick para a marca d'água
            $watermark = new Imagick( UPLOAD_DIR . 'watermark.png' );

            # Redimensionar marca d'água se for maior que a imagem
            if(
                $imagick->getImageHeight() < $watermark->getImageHeight() || 
                $imagick->getImageWidth() < $watermark->getImageWidth()
            ) {
                $watermark->scaleImage(
                    $imagick->getImageWidth(), 
                    $imagick->getImageHeight()
                );
            }

            # Calcular posição central
            $x = ( $imagick->getImageWidth() - $watermark->getImageWidth() ) / 2;
            $y = ( $imagick->getImageHeight() - $watermark->getImageHeight() ) / 2;

            # Aplicar marca d'água sobre a imagem
            $imagick->compositeImage( $watermark, Imagick::COMPOSITE_OVER, $x, $y );

        }
    }


    private static function silent( callable $fn ) {
        set_error_handler( function($errno, $errstr) {
            return true;
        });

        try {

            return $fn();
        } 

        finally {

            restore_error_handler();
        }
    }
}