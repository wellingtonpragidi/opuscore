<?php
/**
 * Classe responsavel por consultar, listar, exibir e navegar
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @see https://opuscore.dev/system/imageattachment
 * 
 * @package System\Media\Image
 * @subpackage \Model
 */
class ImageAttachment extends Image {

    /**
     * Seleciona registros de midia do banco de dados.
     * Pode selecionar uma midia unica pelo ID se `URL::has('id')`
     * ou uma lista paginada de midias ( por carregamento assincrono com botao "carregar mais" )
     */
    public function select(): array|false {
        if( URL::has('id') ) {
            $id = URL::int('id');

            $cmd = $this->conn->prepare("SELECT * FROM medias WHERE ID = ?");
            $cmd->execute([ $id ]);
            $row = $cmd->fetch(PDO::FETCH_ASSOC);

            if( ! $row ) {
                return false;
            }

            $bind = new Assign;
            $bind->ID         = $row['ID'];
            $bind->type       = $row["related_type"];
            $bind->relatedID  = $row["related_id"];
            $bind->title      = $row["related_title"];
            $bind->attachment = json_decode($row["attachment"] ?? '');
            $bind->date       = $row["created"]; # @obsolete
            $bind->created    = $row['created'];

            return [ $bind ];
        }

        # listagem
        $list = [];
        $cmd = $this->conn->prepare("
            SELECT ID, attachment FROM medias ORDER BY ID DESC LIMIT ? OFFSET ?
        ");
        $cmd->execute([ INPUT::int('limit'), INPUT::int('offset') ]);

        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            $bind->ID         = $row['ID'];
            $bind->attachment = json_decode($row["attachment"] ?? '');
            $list[] = $bind;
        }

        return $list;
    }


    /**
     * Busca o ultimo registro de midia cadastrada no banco de dados
     * saida: helper select_last_image() em /iterators/instance-helpers.php
     */
    public function select_last(): array {
        $cmd = $this->conn->prepare("SELECT ID, attachment FROM medias ORDER BY ID DESC LIMIT 1");
        $cmd->execute();
        $list = [];
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;
            
            $bind->ID         = $row['ID'];
            $bind->attachment = json_decode( $row["attachment"] ?? '' );
            $list[] = $bind;
        }

        return $list;
    }

    /**
     * Gera links HTML para navegacao com icone entre itens de midia ou um link desabilitado se nao houver  (anterior/proximo)
     *
     * @param $direction A direcao da navegacao ('prev' para anterior, 'next' para proximo)
     * @param $id O ID do item de midia atual
     * @param $is_html Define se retorna sho a URL ou HTML contendo <a attrs><span icon>
     */
    public function navigation( 
        string $direction, int $media_id, bool $return_url = false ): string {

        if( ! in_array($direction, ['prev', 'next'], true) ) {
            return '';
        }

        $operator = $direction === 'prev' ? '<' : '>';
        $order    = $direction === 'prev' ? 'DESC' : 'ASC';
        $icon     = $direction === 'prev' ? 'chevronleft' : 'chevronright';

        $cmd = $this->conn->prepare("
            SELECT ID FROM medias
            WHERE ID {$operator} ?
            ORDER BY ID {$order}
            LIMIT 1
        ");
        $cmd->execute([ $media_id ]);
        $row = $cmd->fetch(PDO::FETCH_ASSOC);

        $base_url = dash_url('media');
        $target_id = $row['ID'] ?? null;

        $url = $target_id
            ? dash_url("media/?id={$target_id}")
            : $base_url;

        if( $return_url ) {
            return $url;
        }

        if( $target_id ) {
            return "
                <a href=\"{$url}\" class=\"{$direction}\">
                    <span icon=\"{$icon}\" size=\"27\"></span>
                </a>
            ";
        }

        return "
            <a class=\"{$direction}\" aria-disabled=\"true\">
                <span icon=\"{$icon}\" size=\"27\"></span>
            </a>
        ";
    }

}