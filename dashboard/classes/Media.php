<?php
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\Media
 * @subpackage \Image
 */

class Media {

    protected PDO $conn;

    protected int $ID;
    protected string $title, $slug, $ttype, $ctype;

    public function __construct( PDO $conn ) {
        $this->conn = $conn;

        $this->ID    = URL::int('id') ?: INPUT::int('target_id');
        $this->title = INPUT::GET('title');
        $this->slug  = INPUT::GET('slug') ?: Ensure::slug($this->title);
        $this->ttype = INPUT::GET('target_type') ?: kind();

        $madia_type = singular(URL::param(1)) . '-' . singular(URL::param(0));
        $this->ctype = INPUT::GET('media_type') ?: $madia_type;
    }
    
    /**
     * Esta funcao eh um helper para `move_uploaded_file()`, encapsulando a logica
     * de definicao do diretorio de destino padrao (baseado em ano/mes) e a criacao
     * do mesmo se nao existir. Nao realiza validacoes robustas de upload, que devem
     * ser feitas em camadas superiores do sistema
     */
    public static function save_file( 
        string $input, string $output, string $directory = '' ): bool {

        # Se o diretorio nao foi passado define o padrao Ano/Mes.
        if( empty( $directory ) ) {
            $directory = UPLOAD_DIR . date('Y/m');
        }
        else {
            # previnir duplo separador
            $directory = rtrim( $directory, '/' );
        }

        if( ! is_dir($directory) ) {
            if ( ! mkdir($directory, 0755, true) ) {
                return false;
            }
        }

        $temp = FILES::temp($input);
        $ext  = FILES::ext($input);

        if( ! $temp || ! $ext ) {
            opus_log('Media::save_file falhou', [
                'temp' => $temp,
                'ext'  => $ext,
                'input'=> $input
            ]);
            
            return false;
        }

        return move_uploaded_file( $temp,  "{$directory}/{$output}.{$ext}" );
    }


    /**
     * Insere uma nova imagem no banco de dados que foi carregada via o editor de texto
     * Esta imagem fica com o type 'editor-{target_type}' dependendo do contexto
     */
    public function insert_editor( string $ext, string $tmp ): ?array {
        $related = 'editor-' . $this->ttype;

        # Insere primeiro pra pegar o ID (attachment provisorio vazio)
        $cmd = $this->conn->prepare("
            INSERT INTO medias (
                related_type, related_id, related_title, attachment, created
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $cmd->execute([ $related, $this->ID, $this->title, '{}', date('Y-m-d H:i:s') ]);
        $LastID = $this->conn->lastInsertId();

        if( ! $LastID ) {
            return null;
        }

        $size = FileInfo::imageDimensions($tmp);

        # Monta o nome definitivo usando o ID real
        $filepath = fn($scope) => date('Y/m') . "/{$related}--media-{$LastID}{$scope}.{$ext}";

        if( self::is_bitmap($tmp) ) {
            $attachment = [
                'original' => [
                    'path'   => $filepath(null),
                    'width'  => $size['width'] ?? null,
                    'height' => $size['height'] ?? null
                ],
                'system' => [
                    'path'   => $filepath('-system'),
                    'width'  => system_image_size(),
                    'height' => system_image_size()
                ]
            ];
        }
        else {
            $attachment = [
                'original' => [
                    'path'   => $filepath(null)
                ]
            ];
        }

        # Agora atualiza o registro
        $cmd = $this->conn->prepare("UPDATE medias SET attachment = ? WHERE ID = ?");
        $recorded = $cmd->execute([ Ensure::json_encode($attachment), $LastID ]);

        if( ! $recorded ) {
            return null;
        }

        return [
            'recorded' => true,
            'media_id' => $LastID
        ];
    }


    public static function print( object $show ): array {
        $src = Image::source($show);

        if( ! $src ) {
            return [
                'source' => Image::fallback(),
                'altext' => 'Arquivo físico não encontrado'
            ];
        }

        $filename = FileInfo::filename($src);
        $alt = explode('-', $filename);

        return [
            'source' => Image::source($show, 'url'),
            'altext' => ucwords($alt[0])
        ];
    }
    /**
     * Next update: 
    public static function print(object $show): void {
        $src = Image::source($show);
        $ext = pathinfo($src, PATHINFO_EXTENSION);

        if ($ext === 'mp4') {
            echo "<video src=\"{$src}\" controls></video>";
        }
        elseif ($ext === 'mp3') {
            echo "<audio src=\"{$src}\" controls></audio>";
        }
        elseif ($ext === 'svg') {
            echo "<img src=\"{$src}\" alt=\"...\">";
        }
        elseif (in_array($ext, ['jpeg','jpg','png','webp'])) {
            echo "<img src=\"{$src}\" alt=\"...\">";
        }
        elseif ($ext === 'pdf') {
            echo "<iframe src=\"{$src}\"></iframe>";
        }
        else {
            echo "<img src=\"" . Image::fallback() . "\">";
        }
    }
    */



    /**
     * Exibe inputs type=url de diferentes versoes de arquivos na barra lateral da pagina unica de midia
     */
    public static function input_url( object $show ): array {
        $details = [];
        foreach( ImageHandler::sizes($show->type) as $scope => $data ) {
            $filepath = $show->attachment->{$scope}->path ?? '';

            if( $filepath && file_exists(UPLOAD_DIR . $filepath) ) {
                $fileurl = upload_url($filepath);

                $html = <<<HTML
                <label>
                    <span>$scope</span><br>
                    <input type="url" value="$fileurl" readonly />
                </label>
                HTML;

                $details[$scope] = $html;
            }
        }

        return $details;
    }


    public static function is_bitmap( string $file ): bool {
        $bitmaps = [
            'image/jpeg', 
            'image/png', 
            'image/gif', 
            'image/webp', 
            'image/tiff', 
            'image/bmp', 
        ];
        $finfo = new finfo( FILEINFO_MIME_TYPE );

        return in_array( $finfo->file($file), $bitmaps );
    }


    /**
     * Imprime o URL do dashboard para a edicao da entidade onde um item de midia foi carregado.
     * Utilizado principalmente em 'editor-selected.php' para criar links de navegacao
     * de volta ao item relacionado (post, pagina ou categoria).
     */
    public static function uploaded_in( object $show ): void {
        switch( $show->type ) :
            case 'post':
            case 'editor-post':
                echo dash_url( 'posts/update/?id='. $show->relatedID );
            break;
            case 'category-post':
                echo dash_url( 'posts/category/?id='. $show->relatedID );
            break;
            case 'page':
            case 'editor-page':
                echo dash_url( 'pages/update/?id='. $show->relatedID );
            break;
            case 'context':
            case 'editor-context':
                echo dash_url( 'pages/update/?id='. $show->relatedID );
            break;
        endswitch;
    }


}