<?php
declare( strict_types = 1 );
/**
 * Conversor de imagens para `.ico` multi-tamanho
 * a partir de PNG, JPEG, GIF, BMP ou WEBP, preservando transparencia.
 *
 * Suporta multiplos tamanhos
 * 
 * Uso:
 * $ico = new ConvertIco( 'file', [64, 64] );
 * $ico->save( __DIR__ . '/favicon.ico' );
 *
 * - PHP 8+
 * - Não utiliza extensoes externas alem de GD
 * - Não possui dependencias
 * 
 * @todo Essa classe redimensiona a imagem distorcendo-a –
 *       precisa primeiro redimensionar depois cortar caso aja sobra apos o redimensionamento
 *
 * @see https://github.com/wellingtonpragidi/ConvertIco
 * @version 1.2.0
 * @author  Wellington Pragidi
 * @license MIT
 */
class ConvertIco {

    private array $images = [];


    public function __construct( 
        string $input, array $sizes, bool $is_uploaded = true ) {

        $file = $is_uploaded ? $_FILES[$input]["tmp_name"] : $input;

        $this->add_image( $file, $sizes );
    }

    /**
     * escreve os dados do arquivo ICO em um caminho de arquivo.
     */
    public function save( string $filepath ): bool {
        if( ! extension_loaded('gd') ) {
            return false;
        }

        $path = dirname( $filepath ) . '/';

        if( ! is_dir($path) ) {
            mkdir( $path, 0775, true);
        }

        if( false === ($data = $this->get_ico_data()) ) {
            return false;
        }

        if( false === ( $fp = fopen($filepath, 'w')) ) {
            return false;
        }

        if( false === ( fwrite($fp, $data)) ) {
            fclose( $fp );
            return false;
        }

        fclose( $fp );

        return true;
    }

    /**
     * Adicionar uma imagem ao gerador
    */
    private function add_image( string $file, array $sizes = [] ): bool { 
        if( ! extension_loaded('gd') ) {
            return false;
        }

        if( false === ($img = $this->read_source_file($file)) ) {
            return false;
        }

        if( empty($sizes) ) {
            $sizes = array( imagesx($img), imagesy($img) );
        }

        # se apenas um tamanho foi passado, coloque-o no array X
        if( ! is_array( $sizes[0] ) ) {
            $sizes = array( $sizes );
        }

        foreach( $sizes as $size ) {
            list( $width, $height ) = $size;

            $new_image = imagecreatetruecolor( (int) $width, (int) $height );

            # fundo transparente completo
            imagesavealpha( $new_image, true );
            $transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127 );
            imagefill( $new_image, 0, 0, $transparent );
            imagecolortransparent( $new_image, $transparent );

            $source_width = imagesx( $img );
            $source_height = imagesy( $img );

            $imagecopyresampled = imagecopyresampled(
                $new_image, 
                $img, 0, 0, 0, 0, 
                (int) $width, (int) $height, 
                $source_width, $source_height
            );

            if( false === $imagecopyresampled ) {
                continue;
            }

            $this->add_image_data( $new_image );
            imagedestroy( $new_image );
        }

        imagedestroy( $img );
        return true;
    }

    /**
     * gera os dados ICO finais criando um cabecalho de arquivo e adicionando os dados da imagem
     */
    private function get_ico_data(): string|bool {
        if ( empty( $this->images ) ) {
            return false;
        }

        $data = pack( 'vvv', 0, 1, count($this->images) );

        $pixel_data = '';

        $icon_dir_entry_size = 16;

        $offset = 6 + ( $icon_dir_entry_size * count($this->images) );

        foreach( $this->images as $image ) {
            $data .= pack( 
                'CCCCvvVV', 
                $image['width'], 
                $image['height'], 
                $image['color_palette_colors'], 
                0, 
                1, 
                $image['bits_per_pixel'], 
                $image['size'], 
                $offset 
            );
            $pixel_data .= $image['data'];

            $offset += $image['size'];
        }

        $data .= $pixel_data;

        unset( $pixel_data );

        return $data;
    }

    /**
     * pega uma imagem GD e converte para o formato BMP bruto
     */
    private function add_image_data( $img ): void {
        $width = imagesx( $img );
        $height = imagesy( $img );

        $pixel_data = [];
        $opacity_data = [];
        $current_opacity = 0;

        for( $y = $height - 1; $y >= 0; $y-- ) {
            for( $x = 0; $x < $width; $x++ ) {
                $color = imagecolorat( $img, $x, $y );

                $alpha = ( $color >> 24 ) & 0x7F;
                $alpha_normalized = 127 - $alpha; # Inverte: 0 = transparente, 127 = opaco
                $alpha_byte = (int) ( ($alpha_normalized / 127) * 255 );

                $rgb = $color & 0xFFFFFF;
                $pixel_color = $rgb | ( $alpha_byte << 24 );
                $pixel_data[] = $pixel_color;

                $opacity = ( $alpha_byte < 128 ) ? 1 : 0; # 1 = transparente no bitmap mask

                $current_opacity = ( $current_opacity << 1 ) | $opacity;

                if( (($x + 1) % 32) == 0 ) {
                    $opacity_data[] = $current_opacity;
                    $current_opacity = 0;
                }
            }

            if( ($x % 32) > 0) {
                while( ($x % 32) > 0 ) {
                    $current_opacity = $current_opacity << 1;
                    $x++;
                }

                $opacity_data[] = $current_opacity;
                $current_opacity = 0;
            }
        }

        $image_header_size = 40;
        $color_mask_size = $width * $height * 4;
        $opacity_mask_size = ( ceil($width / 32) * 4 ) * $height;

        # cabecalho BMP com altura dobrada (imagem + mask)
        $data = pack( 'VVVvvVVVVVV', 
            40,                  # Tamanho do cabeçalho
            $width,              # Largura
            $height * 2,         # Altura total (imagem + máscara)
            1,                   # Planos
            32,                  # Bits por pixel
            0,                   # Compressão (BI_RGB = 0)
            $color_mask_size + $opacity_mask_size, # Tamanho imagem
            0, 0, 0, 0           # Reservados
        );

        foreach( $pixel_data as $color ) {
            $data .= pack( 'V', $color );
        }

        foreach( $opacity_data as $opacity ) {
            $data .= pack( 'N', $opacity );
        }

        $image = [
            'width'                => $width,
            'height'               => $height,
            'color_palette_colors' => 0,
            'bits_per_pixel'       => 32,
            'size'                 => $image_header_size + $color_mask_size + $opacity_mask_size,
            'data'                 => $data,
        ];

        $this->images[] = $image;
    }

    /**
     * Le o arquivo de imagem de origem e converte em um recurso de imagem GD
     */
    private function read_source_file( string $file ): GdImage|false { 
        if( false === ($type = exif_imagetype($file)) ) {
            return false;
        }

        # detectar tipo de imagem entrada
        switch( $type ) {
            case IMAGETYPE_PNG:
                $image = @imagecreatefrompng( $file );
            break;
            case IMAGETYPE_JPEG:
                $image = @imagecreatefromjpeg( $file );
            break;
            case IMAGETYPE_GIF:
                $image = @imagecreatefromgif( $file );
            break;
            case IMAGETYPE_BMP:
                $image = @imagecreatefrombmp( $file );
            break;
            case IMAGETYPE_WEBP:
                $image = @imagecreatefromwebp( $file );
            break;
            default:
                return false;
            break;
        }

        if( $image === false ) {
            return false;
        }

        return $image;
    }

}
