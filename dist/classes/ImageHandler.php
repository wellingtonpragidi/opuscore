<?php 
declare( strict_types = 1 );
/**
 * 
 * ['wide', 'larger', 'minor', 'thumb']
 * ['wide', 'larger', 'minor']
 * ['plain', 'thumb']
 * ['profile', 'avatar']
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * @author     Wellington Pragidi
 * 
 * @package Core/Media/Image
 * @see https://opuscore.dev/classes/imagehandler
 */

class ImageHandler {

    /**
     * Escopos reservados (jah eh uso padrao do sistema)
     * 
     * page:     wide     / larger / minor  
     * post:     wide     / larger / minor / thumb  
     * category: plain    / thumb  
     * user:     profile  / avatar  
     * editor:   original
     * E todos com excecao a user tem um scope 'system'
     */
    private static array $reserved_scopes = [
        'wide', 'larger', 'minor', 'thumb', 'plain', 'profile', 'avatar', 'original', 'system'
    ];


    # tipos
    private static array $registered = [
        'post'     => [],
        'page'     => [],
        'category' => [],
        'user'     => [],
    ];


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
     * 
     * ESSA FUNCAO ASSIM COMO imagick(), GD() e moveupload() NAO FAZ NENHUM TIPO DE VALIDACAO
     * ISSO FICA POR CONTA DO CONTROLADOR — SEJA DIRETAMENTE OU POR MEIO DE helpers
     */
    public static function resolve( array $args ): bool {
        # Dados da imagem
        $input    = $args['input'];
        $path     = $args['path'] ?? '';
        $filename = $args['filename'];
        $width    = $args['width'];
        $height   = $args['height'];

        if( $width <= 0 || $height <= 0 ) {
            return false;
        }

        # Define diretorio de destino, caso nao tenha sido passado
        if( empty($path) ) {
            $path = UPLOAD_DIR . date('Y/m/');
        }

        if( ! is_dir($path) ) {
            if( ! mkdir($path, 0755, true) ) {
                return false;
            }
        }

        $ext = FILES::ext($input);

        $data = [
            'input'    => $input,
            'path'     => $path,
            'filename' => $filename . '.' . $ext,
            'width'    => $width,
            'height'   => $height
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
     * Adiciona novo escopo de imagem
     * @param $type : Nome da entidade singular
     * @param $scope : Identificador unico para o escopo de redimencionamento e corte
     * @param $width e $height : Largura e Altura de redimencionamento e corte do novo escopo
     * 
     * @todo Quebra: nome do metodo append_size -> append_scope
     */
    public static function append_scope( 
        string $type, string $scope, int $width, int $height ): void {

        # escopos reservados do nucleo
        if( in_array($scope, self::$reserved_scopes, true) ) {
            return;
        }

        # tipo invalido
        if( ! isset(self::$registered[$type]) || $type === 'favicon' ) {
            return;
        }

        # tamanho invalido
        if( $width <= 0 || $height <= 0 ) {
            return;
        }

        self::$registered[$type][$scope] = [
            'width'  => $width,
            'height' => $height
        ];
    }

    public static function sizes( string $type ): array {

        $sizes = self::default_sizes($type);

        if( isset(self::$registered[$type]) ) {
            foreach( self::$registered[$type] as $scope => $data ) {
                $sizes[$scope] = $data;
            }
        }

        # remove desativados width ou height = 0 (como em todos os casos)
        foreach( $sizes as $scope => $data ) {
            if( $data['width'] <= 0 || $data['height'] <= 0 ) {
                unset( $sizes[$scope] );
            }
        }

        return $sizes;
    }

    private static function default_sizes( string $type ): array {
        $system_sz = system_image_size();
        return match( $type ) {
            # !!! IMPORTANT
            # Precisa de size adicional em 'page' e 'post' post_md_w() 'larger' 'minor'
            'post' => [
                'wide'   => ['width' => post_w(),    'height' => post_h()],

                'larger' => ['width' => post_lg_w(), 'height' => post_lg_h()],
                'minor'  => ['width' => post_md_w(), 'height' => post_md_h()],

                'thumb'  => ['width' => post_sm_w(), 'height' => post_sm_h()],
                
                'system' => ['width' => $system_sz,  'height' => $system_sz],
            ],
            'page' => [
                'wide'   => ['width' => page_w(),    'height' => page_h()],

                'larger' => ['width' => page_md_w(), 'height' => page_md_h()],
                'minor'  => ['width' => post_md_w(), 'height' => post_md_h()],

                'thumb'  => ['width' => page_sm_w(), 'height' => page_sm_h()],
                
                'system' => ['width' => $system_sz,  'height' => $system_sz],
            ],
            'category' => [
                'plain'   => ['width' => cat_w(),    'height' => cat_h()],
                'thumb'  => ['width' => cat_sm_w(), 'height' => cat_sm_h()],
                
                'system' => ['width' => $system_sz, 'height' => $system_sz],
            ],
            'user' => [
                'profile' => ['width' => user_pic_sz('profile'), 'height' => user_pic_sz('profile')],
                'avatar'  => ['width' => user_pic_sz('thumb'),   'height' => user_pic_sz('thumb')],
            ],

            default => [],
        };
    }


    /**
     * Processa e redimensiona imagens usando a extensao Imagick.
     *
     * Esta funcao eh projetada para criar versoes redimensionadas de imagens, 
     * mantendo a proporcao e preenchendo as dimensoes alvo atraves de corte (crop to fit)
     * Suporta formatos GIF (incluindo animacoes), PNG, JPEG/JPG e WebP (dependendo da compilacao do Imagick).
     */
    private static function imagick( array $args, string $ext ): bool {
        $input    = $args['input'];
        $path     = $args['path'] ?? '';
        $filename = $args['filename'];
        $width    = $args['width'];
        $height   = $args['height'];

        $temp = FILES::temp($input);

        # Caminho completo para salvar a imagem
        $filepath = $path . $filename;
        
        if( $ext === 'webp' ) {
            $formats = Imagick::queryFormats();
            $supports_webp = in_array('WEBP', $formats) || in_array('webp', $formats);
            
            if( ! $supports_webp ) {
                return self::GD([
                    'input'    => $input,
                    'path'     => $path,
                    'filename' => $filename,
                    'width'    => $width,
                    'height'   => $height
                ]);
            }
        }

        try {
            $imagick = new Imagick( $temp );

            self::watermark( $imagick );

            # VERIFICAR SE ARQUIVO TEMPORARIO EXISTE
            if( empty($temp) || ! file_exists($temp) ) {
                exception(
                    "Arquivo temporário não encontrado<b><code>: {$temp}</code></b>", 
                    'ImageHandler::imagick'
                );

                return false;
            }
        }
        catch( ImagickException $e ) {
            exception( 
                "ImagickException: 
                Nao foi possivel criar objeto Imagick de <b><code>: '{$temp}'</code></b>.<br>
                Erro: <b>{$e->getMessage()}</b>", 
                'ImageHandler::imagick'
            );

            return false;
        }

        # Definir qualidade
        /*if( $ext === 'webp' || $ext === 'jpeg' || $ext === 'jpg' ) {
            $imagick->setImageCompressionQuality(90);
        }
        else if($ext === 'png') {
            $imagick->setImageCompressionQuality(9);
        }*/

        try {
            # TRATAMENTO GIF ANINAMDO
            if( $ext === 'gif' ) {
                $imagick = $imagick->coalesceImages();
                foreach( $imagick as $frame ) {
                    $frame->cropThumbnailImage( $width, $height );
                    $frame->setImagePage($width, $height, 0, 0);
                }
                $imagick = $imagick->deconstructImages();

                return (bool) $imagick->writeImages( $filepath, true );
            } 
            # TRATAMENTO PARA IMAGENS "NORMAIS" (JPEG/.JPG, PNG, WEBP)
            else {
                $imagick->cropThumbnailImage( $width, $height );

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
        $input    = $args['input'];
        $path     = $args['path'] ?? '';
        $filename = $args['filename'];
        $width    = $args['width'];
        $height   = $args['height'];
 
        $temp = FILES::temp($input);
        //$name = FILES::name($input);

        # Caminho completo para salvar a imagem
        $filepath = $path . $filename;

        # Criar imagem de origem baseada na extensao
        switch( $ext ) {
            case 'gif':
                $origin = imagecreatefromgif($temp);
            break;
            case 'png':
                $origin = self::silent( fn() => imagecreatefrompng($temp) );
            break;
            case 'jpeg': case 'jpg':
                $origin = imagecreatefromjpeg($temp);
            break;
            case 'webp':
                $origin = imagecreatefromwebp($temp);
            break;
            case 'bmp':
                $origin = imagecreatefrombmp($temp);
            break;
            default:
                return false; # Extensao nao suportada
            break;
        }

        # Obter largura e altura da imagem original
        $orig_width = imagesx( $origin );
        $orig_height = imagesy( $origin );

        # Calcular proporção da imagem original e da imagem de destino
        $orig_ratio = $orig_width / $orig_height;
        $target_ratio = $width / $height;

        # Definir area de corte proporcional
        if( $orig_ratio > $target_ratio ) {
            # Imagem original mais larga que a proporcao alvo -> cortar nas laterais
            $new_height = $orig_height;
            $new_width = $orig_height * $target_ratio;
            $src_x = ( $orig_width - $new_width ) / 2;
            $src_y = 0;
        } 
        else {
            # Imagem original mais alta que a proporção alvo -> cortar no topo/baixo
            $new_width = $orig_width;
            $new_height = $orig_width / $target_ratio;
            $src_x = 0;
            $src_y = ( $orig_height - $new_height ) / 2;
        }

        # Criar nova imagem com o tamanho desejado
        $imagecreate = imagecreatetruecolor( $width, $height );

        # Preservar transparencia para PNG e GIF
        if( $ext === 'png' || $ext === 'gif' ) {
            imagecolortransparent(
                $imagecreate, 
                imagecolorallocatealpha( $imagecreate, 0, 0, 0, 127 )
            );
            imagealphablending( $imagecreate, false );
            imagesavealpha( $imagecreate, true );
        }

        # Copiar e redimensionar a imagem com corte proporcional
        imagecopyresampled(
            $imagecreate,
            $origin,
            0, 0, # Posicao destino (canto superior esquerdo)
            (int) $src_x, (int) $src_y, # Posicao origem (corte central)
            $width, $height, # Tamanho destino
            (int) $new_width, (int) $new_height # Tamanho da area cortada
        );

        try {
            switch ($ext) {
                case 'gif':
                    return imagegif($imagecreate, $filepath);
                break;
                case 'png':
                    return imagepng($imagecreate, $filepath);
                break;
                case 'jpeg':
                case 'jpg':
                    return imagejpeg($imagecreate, $filepath, 90);
                break;
                case 'webp':
                    return imagewebp($imagecreate, $filepath, 90);
                break;
                case 'bmp':
                    return imagebmp($imagecreate, $filepath);
                break;
            }
        }
        catch( Throwable $e ) {
            return false;
        }
        finally {
            if( isset($imagecreate) && is_resource($imagecreate) ) {
                imagedestroy($imagecreate);
            }
            if( isset($origin) && is_resource($origin) ) {
                imagedestroy($origin);
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