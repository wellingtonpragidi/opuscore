<?php
/**
 * Classe responsavel pela manipulacao de imagens, incluindo insercao, atualizacao e exclusao, 
 * tanto no banco de dados quanto dos arquivos fisicos no servidor.
 *
 * Relacao com `ImageAttachment`: 
 * - `ImageAttachment` foca em consulta e exibicao
 * - `ImageManager` eh responsavel pelas operacoes de escrita "CRUD"
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 *
 * @see https://opuscore.dev/system/imagemanager
 * 
 * @package System\Media\Image
 * @subpackage \Model
 */

class ImageManager extends Image {

    /**
     * Insere uma imagem destacada (featured image) quando uma pagina ou post eh atualizado.
     * O metodo verifica se um arquivo de 'attachment' foi definido no upload.
     * Os caminhos das imagens sao gerados com base no slug da pagina/post e tipo de entidade.
     *
     * @return int O numero de linhas afetadas. Retorna 0 se nenhum arquivo for definido.
     */
    public function insert_page_image(): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO medias(related_type, related_id, related_title, attachment, created) 
            VALUES( ?, ?, ?, ?, ? )
        ");

        $attachment  = Ensure::json_encode( parent::json_attachments() );

        $cmd->execute([ 
            $this->ttype, 
            $this->ID, 
            $this->title, 
            $attachment, 
            date('Y-m-d H:i:s') 
        ]);

        return $this->conn->lastInsertId() > 0;
    }

    /**
     * Insere uma imagem destacada (featured image) para uma categoria.
     * Este metodo eh chamado durante a insercao ou atualizacao de uma categoria.
     * O nome da imagem eh gerado com base no slug da categoria, incluindo hierarquia pai-filho.
     *if( ! FILES::isDefined('attachment') ) {
            return false;
        }
     * @param $lastid O ID da categoria recem-inserida (se for uma operacao de insercao)
     */
    public function insert_category_image( int $bindID ): bool {
        $category = Container::call('Category');

        $cmd = $this->conn->prepare("
            INSERT INTO medias(related_type, related_id, related_title, attachment, created)
            VALUES(?, ?, ?, ?, ?)
        ");

        $attachment = Ensure::json_encode( parent::json_attachments($bindID) );

        $created = date('Y-m-d H:i:s');

        $cmd->execute([ 'category-post', $bindID, $this->title, $attachment, $created ]);

        return $this->conn->lastInsertId() > 0;
    }


    /**
     * Atualiza o campo `related_title` na tabela `medias` quando o titulo de uma pagina eh alterado.
     * Esta operacao eh realizada se o novo titulo for diferente do titulo atual no banco de dados.
     */
    public function update_title_page_image(): bool {
        $table = URL::param(0);
        $cmd = $this->conn->prepare("
            SELECT p.title, m.related_title FROM medias AS m
            LEFT JOIN $table AS p ON m.related_id = p.ID AND m.related_type = '{$this->ttype}' 
            WHERE m.related_type = ? AND m.related_id = ?
        ");
        $cmd->execute([ $this->ttype, $this->ID ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );
        if( ! $row ) {
            return false;
        }

        return $this->update_related_title( 
            $row, $this->ttype, $row['title'], $row['related_title'] 
        );
    }

    /**
     * Atualiza o campo `related_title` na tabela `medias` quando o titulo de uma categoria eh alterado.
     * Funciona de forma similar a `update_image_title_from_pages()`, mas para categorias.
     */
    public function update_title_category_image(): bool {
        $cmd = $this->conn->prepare("
            SELECT c.name, m.related_title FROM medias AS m
            LEFT JOIN categories AS c ON m.related_id = c.ID
            WHERE m.related_type = ? AND m.related_id = ?
        ");
        $cmd->execute([ 'category-post', $this->ID ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );
        if( ! $row ) {
            return false;
        }

        return $this->update_related_title( 
            $row, 'category-post', $row['name'], $row['related_title'] 
        );
    }


    private function update_related_title( 
        array $row, string $media_type, string $current_title, string $related_title ): bool {

        if( $this->title === $current_title && $current_title === $related_title ) { 
            return false;
        }
        try {
            $cmd = $this->conn->prepare("
                UPDATE medias SET related_title = ?
                WHERE related_type = ? AND related_id = ?
            ");
            $cmd->execute([ $this->title, $media_type, $this->ID ]);

            return true;
        }
        catch( Throwable $e ) {
            return false;
        }
    }


    /**
     * Deleta a imagem destacada vinculada a pages=type
     * O metodo remove tanto o arquivo fisico por `parent::delete_file()` quanto o registro no banco de dados.
     * A midia eh ser deletada pode em caso de exclusao da proprio pagina
     * ou se o botao de "excluir imagem" for clicado
     * 
     * @todo renomear para delete_entity_image() esse metodo e todos os outros _page_ para _entity_
     */
    public function delete_page_image(): ?array {
        $deleted_record = false;
        $deleted_file   = [];

        $cmd = $this->conn->prepare("
            SELECT attachment FROM medias WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute([ $this->ttype, $this->ID ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row ) {
            $attachment = Ensure::json_decode( $row['attachment'] );

            $deleted_file = parent::delete_file($attachment);

            $cmd = $this->conn->prepare("DELETE FROM medias WHERE related_type = ? AND related_id = ?");
            $cmd->execute([ $this->ttype, $this->ID ]);

            $deleted_record = true;
        }

        return [
            'deleted_record' => $deleted_record,
            'deleted_file'   => $deleted_file
        ];
    }

    /**
     * Deleta a imagem destacada vinculada a categorias.
     * O metodo remove tanto o arquivo fisico (via `parent::delete_file()`) quanto
     * o registro no banco de dados, similar a `delete_images_from_pages()`
     */
    public function delete_category_image(): array {
        $deleted_record = false;
        $deleted_file   = [];

        $values = [ 'category-post', $this->ID ];

        $cmd = $this->conn->prepare("
            SELECT attachment FROM medias WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute( $values );
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row ) {

            $attachments = Ensure::json_decode( $row['attachment'] );

            # arquivos fisicos
            $deleted_file = parent::delete_file($attachments);

            # registro no banco
            $cmd = $this->conn->prepare("
                DELETE FROM medias WHERE related_type = ? AND related_id = ?
            ");
            $cmd->execute( $values );

            $deleted_record = true;
        }

        return [
            'deleted_record' => $deleted_record,
            'deleted_file'   => $deleted_file,
        ];
    }


    /**
     * Deleta midias relacionadas e seus arquivos fisicos em pagina de visualizacao unica de midia `?id=`
     */
    public function delete( ?int $deleteit = null ): array {
        $deleted_record = false;
        $deleted_file   = [];

        $id = $deleteit ?? $this->ID;
        $cmd = $this->conn->prepare("SELECT attachment FROM medias WHERE ID = ?");
        $cmd->execute([ $id ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row ) {
            $attachments = Ensure::json_decode( $row["attachment"] );
            $deleted_file = parent::delete_file($attachments);

            # Deleta o registro da midia no banco de dados.
            $cmd = $this->conn->prepare("DELETE FROM medias WHERE ID = ?");
            $cmd->execute([ $id ]);

            $deleted_record = true;
        }

        return [
            'deleted_record' => $deleted_record,
            'deleted_file'   => $deleted_file,
        ];
    }

}