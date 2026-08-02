<?php
declare( strict_types = 1 );
/**
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package System\File\Model
 * @subpackage \Image
 */

class Media {

    protected PDO $conn;

    public const ADMIN_SCOPES = [
        'original', 'larger', 'minor', 'wide', 'plain', 'thumb', 'system'
    ];
    
    
    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }
    

    # isso eh INSERT para uso em popup de editor do sistema e pagina /media
    public function insert( Assign $bind ): bool {
        
        $cmd = $this->conn->prepare("
            INSERT INTO medias (
                related_type, related_id, related_title, attachment, created
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $cmd->execute([ 
            $bind->type, 
            $bind->ID, 
            $bind->title, 
            '{}', 
             $bind->created
        ]);
        
        $bind->LastID = (int) $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    /**
     * isso eh UPDATE para uso em popup de editor do sistema
     * esse update acontece imediatamente seguido pelo insert
     */
    public function update( Assign $bind ): bool {
        $cmd = $this->conn->prepare("UPDATE medias SET attachment = ? WHERE ID = ?");

        $executed = $cmd->execute([ 
            $bind->attachment, 
            $bind->media->ID 
        ]);

        return $executed && $cmd->rowCount() > 0;
    }


    /**
     * Deleta qualquer registro de midias e arquivos fisicos em:
     * Popup de midias em editor de texto quando o editor Punk habilitado
     * Pagina de visualizacao unica de midia `medias/?id=`
     */
    public function delete( ?int $id, string $type ): array {
        $deleted_record = false;
        $deleted_file   = false;

        $cmd = $this->conn->prepare("SELECT attachment FROM medias WHERE ID = ?");
        $cmd->execute([ $id ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row ) {
            $attachments = Ensure::object( $row["attachment"] );

            $deleted_file = $this->delete_file($attachments, $type);

            $cmd = $this->conn->prepare("DELETE FROM medias WHERE ID = ?");
            $cmd->execute([ $id ]);

            $deleted_record = $cmd->rowCount() > 0;
        }

        return [
            'deleted_record' => $deleted_record,
            'deleted_file'   => $deleted_file,
        ];
    }



    /**
     * Esta funcao eh um helper para `move_uploaded_file()`, encapsulando a logica
     * de definicao do diretorio de destino padrao (baseado em ano/mes) e a criacao
     * do mesmo se nao existir
     */
    public function save_file( 
        string $input, string $basename, string $dir = '' ): bool {

        # Se o diretorio nao foi passado define o padrao Ano/Mes.
        if( empty( $dir ) ) {
            $dir = UPLOAD_DIR . date('Y/m');
        }
        else {
            # previnir duplo separador
            $dir = rtrim( $dir, '/' );
        }

        if( ! is_dir($dir) ) {
            if ( ! mkdir($dir, 0755, true) ) {
                return false;
            }
        }

        $tmp = FILES::tmp($input);
        $ext = FILES::ext($input);

        if( ! $tmp || ! $ext ) {
            opus_log('Media::save_file falhou', [
                'tmp'  => $tmp,
                'ext'  => $ext,
                'input'=> $input
            ]);
            
            return false;
        }

        return move_uploaded_file( $tmp, "{$dir}/{$basename}.{$ext}" );
    }


    /**
     * Deleta arquivos fisicos 
     * 
     * imagens  de destaque:
     * Tenta deletar todos os tamanhos de imagens, padroes e adicionadas por append_scope()
     * 
     * O parametro $type precisa ser igual a uma das chaves no array ImageHandler::$registered 
     * com valores adicionados em ImageSize::dimensions()
     * Valores padroes incluem:
     * 'article', 'page', 'category', 'user', 'editor', 'favicon' 
     * entre outros possiveis adicionados por ImageHandler::append_size()
     * 
     * Esse metodo nao identifica a media a ser deletada, para isso se faz necessario um SELECT
     *  com (WHERE related_type AND related_id)
     * Por isso deve ser chamado em um metodo de **modelo** e na condicao verdadeira de $row,  `delete_file` eh chamado antes de DELETE
     */
    public function delete_file( ?object $attachment, string $type ): bool {
        if( ! $attachment ) {
            return false;
        }

        $deleted = [];

        foreach( ImageSize::dimensions($type) as $scope => $data ) {
            $filepath = $attachment->{$scope}->path ?? null;

            $file = UPLOAD_DIR . $filepath;

            if( isset($filepath) && file_exists($file) ) {
                if( unlink($file) ) {

                    $deleted[] = $scope;
                }
            }
        }

        return ! empty($deleted);
    }


    /**
     * Retorna o caminho completo (URL ou caminho absoluto no sistema de arquivos)
     * de um arquivo de imagem, priorizando um tamanho especificado ou append_scope()
     */
    public static function data_attachment( object $show ): ?array {
        # escolhe escopo pela ordem do array
        foreach( self::ADMIN_SCOPES as $scope ) {
            $path   = $show->attachment->{$scope}->path ?? null;
            $width  = $show->attachment->{$scope}->width ?? 0;
            $height = $show->attachment->{$scope}->height ?? 0;

            if( ! is_string($path) || $path === '' ) {
                continue;
            }

            return [
                'url'    => upload_url($path),
                'dir'    => UPLOAD_DIR . $path,
                'width'  => $width,
                'height' => $height
            ];
        }

        return null;
    }



    public static function system_thumbnails( object $show ): string {

        # system para imagens bitmap
        $system   = $show->attachment->system->path ?? '';

        # original para imagens vetor .svg
        $original = $show->attachment->original->path ?? '';

        # todos os outros tipos de arquivos usam miniaturas padroes simbolizando o arquivo


        $filepath = $system ?? $original ?? '';

        $ext = pathinfo( $filepath, PATHINFO_EXTENSION ) ?: '';


        if( $ext === FileInfo::EXT['audio'] ) {

            $source = dash_url('assets/img/audio.svg');
        }

        else if(  in_array( $ext, FileInfo::EXT['video'] )  ) {

            $source = dash_url('assets/img/video.svg');
        }

        else if(  in_array( $ext, FileInfo::EXT['docs'] )  ) {

            $source = dash_url('assets/img/document.svg');
        }

        else if( $ext === FileInfo::EXT['image']['vector'] ) {
            if( $original !== '' ) {

                $source = upload_url($original);
            }
            else {

                $source = dash_url('assets/img/image.svg');
            }
        }

        else if(  in_array( $ext, FileInfo::EXT['image']['bitmap'] )  ) {
            if( $system !== '' ) {

                $source = upload_url($system);
            }
            else {

                $source = dash_url('assets/img/image.svg');
            }
        }

        else {

            $source = dash_url('assets/img/generic.svg');
        }

        $size  = system_image_size();

        return "
            <img 
                src=\"{$source}\" alt=\"{$show->media->title}\" 
                width=\"{$size}\" height=\"{$size}\" 
            />";
    }



    /**
     * retorna array com texto alternativo e URL da midia para visualizacao em pagina unica
     * 
     * se o arquivo for um imagem o tamanho eh exibido pela ordem do array da constante ADMIN_SCOPES
     * nao existindo a midia de imagem um texto generico e imagem fallback eh usado para substituir
     * 
     * @todo Para outras midias nada aida eh eh exibido, mas estamos trabalhando nisso
     * ( Next update )
     */
    public static function print( object $show ): array {
        $data = self::data_attachment($show);

        if( ! $data ) {
            return [
                'source' => Image::fallback(),
                'altext' => 'Arquivo físico não encontrado'
            ];
        }

        $alt = explode( '-', basename($data['dir']) );

        return [
            'source' => $data['url'],
            'altext' => ucwords( $alt[0] )
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
     * Exibe inputs type=url de diferentes versoes de arquivos na barra lateral 
     * - pagina unica de midia
     */
    public static function inputs_url( object $show ): array {
        $details = [];
        foreach( ImageSize::dimensions($show->type) as $scope => $data ) {
            $filepath = $show->attachment->{$scope}->path ?? '';

            if( $filepath ) {
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


    public static function title(): string {
        $title = '';
        
        foreach( select_medias() as $show ) {
            $path = null;
            
            foreach( self::ADMIN_SCOPES as $scope ) {
                $filepath = $show->attachment->{$scope}->path ?? null;
                if( isset($filepath) ) {
                    $path = $filepath;
                    break;
                }
            }
            
            if( isset($path) ) {
                $ext = strtolower( pathinfo($path, PATHINFO_EXTENSION) );
                $title = self::title_by_ext( $ext );
            }
        }
        
        return $title;
    }


    private static function title_by_ext( string $ext ): string {
        foreach( FileInfo::EXT as $groups => $types ) {
            # Se array o bidimensional
            if( is_array($types) && isset($types['bitmap'], $types['vector']) ) {
                foreach( $types as $subgroups => $exts ) {
                    if (in_array($ext, $exts, true)) {
                        return 
                        match( $subgroups ) {
                            'bitmap' => 'Imagem',
                            'vector' => 'Imagem vetor',
                        };

                    }
                }
            } 
            else {
                # array simples
                if( in_array($ext, $types, true) ) {
                    return 
                    match( $groups ) {
                        'audio' => 'Audio',
                        'video' => 'Vídeo',
                        'audio' => 'Áudio',
                        'docs' => 'Documento',
                    };
                }
            }
        }
        
        return 'Arquivo'; # fb
    }

}