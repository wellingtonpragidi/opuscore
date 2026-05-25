<?php
/**
 * Classe 'parent' base para gerenciamento de operacoes relacionadas a imagens
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 *
 * Mapa de distribuicao dos metodos relacionados a imagens:
 * ────────────────────────────────────────────────
 * ImageManager - manipulacao de arquivos e registros de midia no banco de dados.
 * ├── insert_editor()                   # Insere imagens do editor.
 * ├── insert_images_from_pages()        # Insere imagens relacionadas a paginas.
 * ├── insert_images_from_categories()   # Insere imagens relacionadas a categorias.
 * ├── update_rename_images_from_pages() # Renomeia imagens de paginas.
 * ├── update_related_image_title_from_pages() # Atualiza titulo relacionado de imagens de paginas.
 * ├── update_image_title_from_pages()   # Atualiza titulo de imagens de paginas.
 * ├── update_image_title_from_categories() # Atualiza titulo de imagens de categorias.
 * ├── delete_images_from_pages()        # Deleta imagens relacionadas a paginas.
 * ├── delete_images_from_categories()   # Deleta imagens relacionadas a categorias.
 * └── delete()                          # Deleta uma midia individual do banco e seus arquivos.
 *
 *
 * ImageAttachment - listagem e exibicao de imagens no front-end ou painel.
 * ├── select()          # Lista todas as imagens.
 * ├── select_last()     # Retorna o ultimo item de midia inserido.
 * ├── navigation()      # Gerencia links de navegacao entre itens de midia.
 * └── thumbnail()       # Exibe uma miniatura para listagens.
 * ────────────────────────────────────────────────
 * @see https://opuscore.dev/system/image
 * 
 * @package System\Image
 * @subpackage \Helper
 */
class Image extends Media {

    /**
     * Gera os caminhos completos e nomes de arquivos padronizados para imagens,
     * considerando o tipo de entidade a qual a imagem esta relacionada para gerar o escopo (chave)
     */
    protected function json_attachments( int $bindID = 0 ): ?array {
        $ext = FILES::ext('attachment');

        $attachments = [];

        if( is_page_update() ) {
            $type['size']  = 'page'; 
            $type['image'] = 'page';
        }
        else if( is_post_update() ) {
            $type['size']  = 'post'; 
            $type['image'] = 'post';
        }
        else if( is_post_categories() || is_post_category() ) {
            $type['size']  = 'category'; 
            $type['image'] = 'category-post';
        }
        else {
            exception('Não é possivel inserir imagem de destaque nesse contexto usando json_attachments()');

            return null;
        }

        foreach( ImageHandler::sizes($type['size']) as $scope => $data ) {
            $id = $bindID ?: $this->ID;
            $path = date('Y/m') . "/{$type['image']}-{$id}-{$scope}.{$ext}";

            $attachments[$scope] = [
                'path'   => $path,
                'width'  => $data['width'],
                'height' => $data['height']
            ];
        }

        return $attachments;
    }


    /**
     * Deleta os arquivos fisicos de imagens destacadas relacionadas
     * Tenta deletar todos os tamanhos de imagens, padroes e adicionadas por append_scope()
     */
    protected function delete_file( ?object $attachment ): array {
        if( ! $attachment ) {
            return [];
        }
        $deleted = [];
        foreach( ImageHandler::sizes($this->ttype) as $scope => $data ) {
            $file = $attachment->{$scope}->path ?? null;

            if( $file && file_exists(UPLOAD_DIR . $file) ) {
                if( unlink(UPLOAD_DIR . $file) ) {
                    $deleted[] = $scope;
                }
            }
        }

        return $deleted;
    }




    # Metodos publicos: mais utilizados em views.

    /**
     * Retorna a URL completa para a pagina de edicao ou visualizacao
     * da entidade a qual uma midia esta relacionada.
     */
    public static function URL( object $show ): string {
        $user = Container::call('User');
        $route = '';
        switch( $show->type ) {
            case 'post':
            case 'editor-post': # Imagens inseridas via editor em posts.
                $route = 'posts/update/?id=';
            break;
            case 'category-post': # Imagens relacionadas a categorias de posts.
                $route = 'posts/category/?id=';
            break;
            case 'page':
            case 'editor-page': # Imagens inseridas via editor em paginas.
                $route = 'pages/update/?id=';
            break;
            case 'context': # Tipo 'scenery' nao tem um caminho base definido aqui.
                $route = 'customize/context/?update=';
            break;
            case 'user':
                # Se a midia esta relacionada a um usuario retorna o metodo URL da classe User
                return $user->URL($show->relatedID);
            break;
        }
        # Concatena o caminho base do dashboard com o caminho relativo e o ID da entidade.
        return dash_url( $route . $show->relatedID );
    }


    /**
     * Retorna o caminho completo (URL ou caminho absoluto no sistema de arquivos)
     * de um arquivo de imagem, priorizando um tamanho especificado ou append_scope()
     */
    public static function source( object $show, string $mode = '' ): string {
        $is_valid = fn($p) => 
            is_string($p) && file_exists(UPLOAD_DIR . $p);

        $output = fn($fp) => 
            ($mode === 'url') ? upload_url($fp) : UPLOAD_DIR . $fp;

        # 1. tenta pela ordem explicita
        foreach( ['original', 'wide', 'plain', 'larger', 'system', 'thumb'] as $scope ) {
            $filepath = $show->attachment->{$scope}->path ?? null;

            if( $is_valid($filepath) ) {

                return $output($filepath);
            }
        }

        # 2. fallback: qualquer custom size `ImageHandler::append_scope()`
        foreach( ImageHandler::sizes($show->type) as $scope => $data ) {
            $filepath = $show->attachment->{$scope}->path ?? null;

            if( $is_valid($filepath) ) {

                return $output($filepath);
            }
        }

        return '';
    }


    public static function render( object $show, string $scope = 'system' ): array {

        $image = [];
        $filepath = $show->attachment->{$scope}->path ?? null;
        if( is_string($filepath) ) {
            $image = [
                'has_image'  => true,
                'show_image' => upload_url($filepath)
            ];
        }
        else {
            $image = [
                'has_image'  => false,
                'show_image' => self::fallback()
            ];
        }

        return $image;
    }


    public static function editor_thumbnail( object $show ): void {
        $system   = $show->attachment->system->path ?? '';
        $original = $show->attachment->original->path ?? '';

        $filepath = $system ?? $original ?? '';

        $ext = pathinfo( $filepath, PATHINFO_EXTENSION ) ?: '';

        if( $ext === FILES::EXT['audio'] ) {
            $source = dash_url('assets/img/audio.svg');
        }
        else if(  in_array( $ext, FILES::EXT['video'] )  ) {
            $source = dash_url('assets/img/video.svg');
        }
        else if(  in_array( $ext, FILES::EXT['docs'] )  ) {
            $source = dash_url('assets/img/document.svg');
        }
        else if( $ext === FILES::EXT['image']['vector'] ) {
            if( is_string($original) && file_exists(UPLOAD_DIR . $original) ) {
                $source = upload_url($original);
            }
            else {
                $source = dash_url('assets/img/image.svg');
            }
        }
        else if(  in_array( $ext, FILES::EXT['image']['bitmap'] )  ) {
            if( is_string($system) && file_exists(UPLOAD_DIR . $system) ) {
                $source = upload_url($system);
            }
            else {
                $source = dash_url('assets/img/image.svg');
            }
        }
        else {
            $source = dash_url('assets/img/generic.svg');
        }

        echo "<img src=\"{$source}\" alt=\"{$show->relatedtitle}\" />";
    }


    public static function fallback( string $figure = 'image' ): string {
        return dash_url("assets/img/{$figure}.svg");
    }

}