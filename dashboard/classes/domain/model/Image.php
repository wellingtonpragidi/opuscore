<?php
declare( strict_types = 1 );
/**
 * Classe responsavel pelo gerenciamento de imagens de destaques 
 *  insercao, selecao, exclusao e atualizacao
 * tanto no banco de dados quanto dos arquivos fisicos hospedados
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 *
 * @see https://opuscore.dev/system/imagemanager
 * 
 * tags
 * @package System\Model\Helper
 * @subpackage \Media\Image
 */

class Image {

    private PDO $conn;

    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }

    /**
     * Insere registro imagem destacada na tabela medias
     * resolve anexos JSON da coluna attachment 
     */
    public function insert( Assign $bind ): bool {

        $cmd = $this->conn->prepare("
            INSERT INTO medias(
                related_type, related_id, related_title, attachment, created) 
            VALUES(   ?,           ?,            ?,           ?,        ? )
        ");

        $cmd->execute([ 
            $bind->media->type, 
            $bind->ID, # ID da entidade em que `medias` esta se relacionando (related_id)
            $bind->title, 
            $this->attachment($bind),
            $bind->created
        ]);

        return (int) $this->conn->lastInsertId() > 0;
    }


    /**
     * Deleta a imagem destacada vinculada a uma tabela/entidade relacionada
     * 
     * O metodo remove o arquivo fisico por $media->delete_file() e registro no banco de dados
     * 
     * A midia pode ser deletada pelo botao "Remover imagem" e exclusao da proprio entidade
     */
    public function delete( Assign $bind ): array {
        $deleted_record = false;
        $deleted_file   = false;

        $cmd = $this->conn->prepare("
            SELECT attachment FROM medias WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute([ $bind->media->type, $bind->ID ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row ) {

            $attachment = Ensure::object( $row['attachment'] );

            $deleted_file = Container::call('Media')->delete_file(
                $attachment, $bind->media->type
            );

            $cmd = $this->conn->prepare("
                DELETE FROM medias WHERE related_type = ? AND related_id = ?
            ");
            
            $cmd->execute([ $bind->media->type, $bind->ID ]);

            $deleted_record = $cmd->rowCount() > 0;
        }

        return [
            'deleted_record' => $deleted_record,
            'deleted_file'   => $deleted_file
        ];
    }


    /**
     * Atualiza o campo `related_title` na tabela `medias` 
     *  quando o titulo de uma relacao eh alterado.
     * E se o novo titulo for diferente do titulo atual no banco de dados
     */
    public function update_title( Assign $bind ) {
        $cmd = $this->conn->prepare("
            SELECT related_title FROM medias  
            WHERE related_type = ? AND related_id = ?
        ");

        # precisa ser $bind->media->type pois categories guarda outro valor em $bind->type
        $cmd->execute([ $bind->media->type, $bind->ID ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( ! $row ) {
            return false;
        }

        # se o titulo do input for igual o valor da coluna related_title 
        # entao nao ha necessidade de UPDATE
        if( $bind->title === $row['related_title'] ) {
            return false;
        }

        $cmd = $this->conn->prepare("
            UPDATE medias SET related_title = ?
            WHERE related_type = ? AND related_id = ?
        ");

        $cmd->execute([ 
            $bind->title, 
            $bind->media->type, 
            $bind->ID 
        ]);

        return $cmd->rowCount() > 0;
    }

    
    /**
     * 
     * 
     * consultas :
     */

    /**
     * Verifica se ja existe outro registro de um ID com o mesmo tipo
     * 
     * Registros na tabela `medias` pode ter mesmo related_id e related_type desde que: 
     *  related_type seja editor-* normalmente ( editor-{nome-entidade} no singular )
     * 
     * Nao pode haver registros em `medias` duplicadas para Imagens Destacadas
     */
    public function exists( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            SELECT 1 FROM medias 
            WHERE related_type = ? AND related_id = ? 
            LIMIT 1
        ");

        $cmd->execute([ 
            $bind->media->type, 
            $bind->ID 
        ]);

        return (bool) $cmd->fetchColumn();
    }


    /**
     * Seleciona registros de midia do banco de dados.
     * Pode selecionar uma midia unica pelo ID se `URL::has('id')`
     * ou uma lista paginada de midias ( por carregamento assincrono com botao "carregar mais" )
     */
    public function select(): array {
        if( URL::has('id') ) {
            $cmd = $this->conn->prepare("SELECT * FROM medias WHERE ID = ?");
            $cmd->execute([ URL::int('id') ]);

            $row = $cmd->fetch( PDO::FETCH_ASSOC );

            if( ! $row ) {
                return [];
            }

            $bind = new Assign;

            $bind->ID         = $row['ID'];
            $bind->type       = $row["related_type"];
            $bind->media->ID  = $row["related_id"];
            $bind->title      = $row["related_title"];
            $bind->attachment = Ensure::object($row["attachment"]);
            $bind->created    = $row['created'];

            return [ $bind ];
        }

        # listagem
        $list = [];

        $cmd = $this->conn->prepare("
            SELECT ID, attachment FROM medias ORDER BY ID DESC LIMIT ? OFFSET ?
        ");

        $cmd->execute([ 
            INPUT::int('limit'), 
            INPUT::int('offset') 
        ]);

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            
            $bind->ID         = $row['ID'];
            $bind->attachment = Ensure::object($row["attachment"]);
            $list[] = $bind;
        }

        return $list;
    }


    /**
     * c.../async/editor-media-selected
     */
    public function select_checked( int $id ): array {
        $cmd = $this->conn->prepare("SELECT * FROM medias WHERE ID = ? LIMIT 1");
        $cmd->execute([ $id ]);
        $row = $cmd->fetch(PDO::FETCH_ASSOC);

        $list = [];
        if( $row ) {
            $bind = new Assign;
            $bind->ID         = $row['ID'];
            $bind->type       = $row["related_type"];
            $bind->media->ID  = $row["related_id"];
            $bind->title      = $row["related_title"];
            $bind->attachment = Ensure::object($row["attachment"]);
            $bind->created    = $row['created'];

            $list[] = $bind;
        }

        return $list;
    }


    /**
     * Busca e retorna o ultimo registro de midia cadastrada no banco de dados
     */
    public function select_last(): array {
        $cmd = $this->conn->prepare("
            SELECT ID, attachment FROM medias ORDER BY ID DESC LIMIT 1"
        );
        $cmd->execute();
        
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            
            $bind->ID         = $row['ID'];
            $bind->attachment = Ensure::object($row["attachment"]);
            $list[] = $bind;
        }

        return $list;
    }


    /**
     * Retorna o ID anterior ou proximo dependendo do valor passado por $direction
     *
     * @param :
     * $direction ID anterior 'prev' ou proximo ID 'next' do ID atual
     * $id O ID de midia atual
     */
    public function navigation( int $id, string $direction ): int {

        if( ! in_array($direction, ['prev', 'next'], true) ) {
            return 0;
        }

        $operator = $direction === 'prev' ? '<' : '>';
        $order    = $direction === 'prev' ? 'DESC' : 'ASC';

        $cmd = $this->conn->prepare("
            SELECT ID FROM medias
            WHERE ID {$operator} ?
            ORDER BY ID {$order}
            LIMIT 1
        ");

        $cmd->execute([ $id ]);

        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        if( $row === false ) {
            return 0;
        }

        return (int) ($row['ID'] ?? 0);
    }


    /**
     * 
     * 
     * helpers :
     */

    /**
     * Retorna a string JSON completa para um arquivo de imagem destacada a ser inserida na coluna `attachment` da tabela `medias`
     * 
     * Gera os caminhos completos e nomes de arquivos padronizados para imagens,
     * considerando o tipo de entidade a qual a imagem esta relacionada para gerar o escopo (chave)
     */
    private function attachment( Assign $bind ): string {
        $ext = FILES::ext('attachment');

        $attachment = [];

        foreach( ImageSize::dimensions($bind->media->type) as $scope => $data ) {

            $path = date('Y/m') . "/{$bind->media->type}-{$bind->ID}-{$scope}.{$ext}";

            $attachment[$scope] = [
                'path'   => $path,
                'width'  => $data['width'],
                'height' => $data['height']
            ];
        }

        $attachment['version'] = random_int(1000, 9999);

        return Ensure::json($attachment);
    }

    /**
     * Esse metodo eh usado pra renderizar imagens de destaque 
     * O padrao do tamanho da imagem eh a quadrada reservada para o sistema
     * Ainda nao ha nenhum modo de alterar isso pelo painel
     * 
     * Em pagina unica geralmente usamos o escopo 'system' pradrao
     * Em listagens passamos o argumento 'thumb' para $scope
     * 
     * @return array com duas opcoes:
     * 1. 'has_image' para verificar se arquivo de imagem existe 
     * 2. 'show_image' a URL da imagem de destaque
     * 
     * @todo 
     * 'show_image' deve retornar o <img /> completo, 
     * render() e 'show_image' nao retrata bem a verdade retornando apenas o URL
     */
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


    /**
     * Retorna URL de miniatura padroes do sistema simbolizando o tipo do arquivo
     * @param $figure | o nome base do arquivo svg referente ao tipo do arquivo
     * Estao disponiveis:
     * 'audio', 'document', 'generic', 'image' (padrao), 'user' e 'video'
     */
    public static function fallback( string $figure = 'image' ): string {
        return dash_url("assets/img/{$figure}.svg");
    }

}