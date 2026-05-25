<?php
declare( strict_types = 1 );
/**
 * Classe Responsavel por gerenciar arquivos de imagem relacionados a conteudos do sistema:
 * postagens, paginas, categorias e usuarios.
 *
 * Essa classe atua tanto em contexto publico (exibicao de imagens no site)
 * quanto em contexto privado (edicao de perfil do usuario, atualizacao de anexos).
 * 
 * As funcoes auxiliares para exibicao de imagem destacadas 
 * funcionam para todas as entidades que agregam: categorias, usuarios, paginas e posts
 * A classe `Media` resolve o tipo e o ID com base na URL
 * Por esse motivo essa funcoes nao sao para ser usadas dentro de loops como `Seek` para serem exibidas
 * Escopos:
 * categorias: 'plain' e 'thumb' 
 * usuarios: 'profile' e 'avatar' 
 * paginas: 'wide', 'larger' e 'minor' 
 * posts: 'wide', 'larger', 'minor' e 'thumb'
 *
 * @see https://opuscore.dev/classe-media
 *
 * Mapa de distribuicao de responsabilidades:
 * ────────────────────────────────────────────────────────────────
 * Gerenciamento geral de imagens (contexto publico e de perfil)
 *
 * ▸ Post, Pagina, Categoria:
 * - get_featured()        → Retorna a imagem destacada como <img>
 * - get_featured_url()    → Retorna apenas a URL da imagem destacada
 * - featured()            → Echo direto da imagem destacada
 *
 * ▸ Usuario:
 * - user_picture()              → Imagem atual do usuario (JSON direto do banco)
 * - user_image_profile()        → Versao da imagem usada no perfil
 * - insert_user()               → Insere imagem nova no banco
 * - update_user()               → Atualiza imagens anexadas existentes
 * - update_user_related_title() → Atualiza o titulo relacionado a imagem no banco
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Model
 * @subpackage \Helper
 */

class Image {


    private PDO $conn;

    private static PDO $db;

    private static Category $category;
    private static Post $post;
    private static Page $page;
    private static User $user;

    private Router $router;

    /**
     * @var 
     * $id    ID da entidade relacionada (posts, pages, categories, users etc)
     * $type  Tipo da relacao 'post', 'page', 'category-post', 'user' ...
     * $alt   Titulo/Nome da entidade relacionada escapado para uso em atributos HTML
     */
    private static ?int $id = null;
    private static string $type = ''; 
    private static string $alt = '';

    private static string $scope = '';

    private static array $cache = [];


    /**
     * Esse construtor inicializa conexoes e define contexto com base na URL atual
     */
    public function __construct( 
        PDO $conn, Category $category, Post $post, Page $page, User $user ) {

        $this->conn = $conn;
        self::$db   = $conn;

        self::$category = $category;
        self::$post     = $post;
        self::$page     = $page;
        self::$user     = $user;

        $this->resolve();
    }

    private function resolve() {
        # detecta tipo de midia com base na URL
        if( is_category() ) {
            self::$id   = self::$category->id();
            self::$type = 'category-post';
            self::$alt  = Ensure::attr( self::$category->name() );
            self::$scope = 'plain';
        }
        else if( is_post() ) {
            self::$id   = self::$post->id();
            self::$type = 'post';
            self::$alt  = Ensure::attr( self::$post->title() );
            self::$scope = 'larger';
        }
        else if( is_page() ) {
            self::$id   = self::$page->id();
            self::$type = 'page';
            self::$alt  = Ensure::attr( self::$page->title() );
            self::$scope = 'larger';
        }
        else if( is_user() ) {
            self::$id   = User::id();
            self::$type = 'user';
            self::$alt  = Ensure::attr( User::name() );
            self::$scope = 'profile';
        }
    }


    # ========================================================================
    # ======= METODOS RELACIONADOS A EXIBICAO DE IMAGENS DESTACADAS ==========
    #          das entidade: categorias, usuarios, paginas e posts
    # ========================================================================

    public static function featured( array $args = [] ): void {

        echo self::get_featured($args);
    }

    public static function get_featured( array $args = [] ): ?string {

        $scope = $args['scope'] ?? self::$scope;

        $alt = ($args['alt'] ?? '') ? Ensure::attr($args['alt']) : self::$alt;
        $alt = "alt=\"{$alt}\"";

        $v = empty( $args['version'] ?? 0 ) ? '' : "?v={$args['version']}";

        $class = empty( $args['class'] ?? null ) 
            ? '' 
            : 'class="' . Ensure::attr($args['class']) . '"';


        $image = self::attachment_data( $scope );

        if( isset($image['path']) ) {

            return "<img src=\"{$image['URL']}{$v}\" {$alt} {$image['dimensions']} {$class} />";
        }

        return null;
    }


    public static function featured_url( ?string $scope = null ): void {
        $scope = $scope ?? self::$scope;

        echo self::get_featured_url($scope);
    }

    public static function get_featured_url( ?string $scope = null ): ?string {
        $scope = $scope ?? self::$scope;

        $image = self::attachment_data($scope);

        return isset($image['path']) ? $image['URL'] : null;
    }


    private static function attachment_data( ?string $scope = null ): ?array {
        $scope = $scope ?? self::$scope;

        $attachment = self::decoded_attachment();

        if( ! $attachment ) {
            return null;
        }

        $imagepath = $attachment->{$scope}->path ?? null;

        $dimensions = Image::dimensions_attrs( $attachment->{$scope} ?? null );

        if( $imagepath ) {

            return [
                'path' => $imagepath,
                'URL'  => upload_url( $imagepath ),
                'dimensions' => $dimensions
            ];
        }

        return null;
    }


    private static function decoded_attachment(): object {
        $json = self::attachment() ?? '';

        $decoded = json_decode( $json );

        return is_object($decoded) ? $decoded : new stdClass();
    }    


    private static function attachment(): ?string {        
        if( array_key_exists('attachment', self::$cache) ) {
            return self::$cache['attachment'];
        }

        $cmd = self::$db->prepare("
            SELECT attachment FROM medias 
            WHERE related_type = ? AND related_id = ? 
            LIMIT 1
        ");

        $cmd->execute([ self::$type, self::$id ]);

        self::$cache['attachment'] = Provider::fetchColumn($cmd) ?? null;
         
        return self::$cache['attachment'];
    }


    public static function picture_per_screen( string $change_alt = '' ): void {
        $minor  = self::attachment_data('minor');
        $larger = self::attachment_data('larger');
        $wide   = self::attachment_data('wide');

        if( ! isset($minor['path'], $larger['path'], $wide['path']) ) {
            return;
        }

        $alt = ! empty($change_alt) ? Ensure::attr($change_alt) : self::$alt;
        $attrs = "alt=\"{$alt}\" {$minor['dimensions']}";

        echo <<<HTML
        <picture>
            <source media="(min-width: 1366px)" srcset="{$wide['URL']}" />
            <source media="(min-width: 768px)" srcset="{$larger['URL']}" />
            <img src="{$minor['URL']}" {$attrs} />
        </picture>
        HTML;
    }




/* ----------------------------------------------------------------------------------- */

    /**
     * Obtem dimensoes a partir de um objeto de escopo (size)
     * Gera atributos HTML width e height a partir de um objeto de escopo (size)
     * 
     * @param $object | Instância de escopo (stdClass) contendo valor de dimensao
     * 
     * @return Atributos HTML width e height ou string vazia.
     */
    public static function dimensions_attrs( ?object $object ): string {
        $width  = (int) ($object->width ?? 0);
        $height = (int) ($object->height ?? 0);

        $attrs = '';
        if( $width > 0 && $height > 0 ) {
            $attrs = "width=\"{$width}\" height=\"{$height}\"";
        }

        return $attrs;
    }

/* ----------------------------------------------------------------------------------- */



    # ==========================================================
    # ======= METODOS RELACIONADOS A MIDIA DE USUARIO ==========
    # ==========================================================

    public function print_user_picture(): void {
        if( ! get_user_picture_url() ) {
            return;
        }

        $user = Container::call('UserStatus');
        $attr_name = Ensure::attr( $user->logged_name() );
        $alt = 'alt="' . $attr_name . '"';
        $attrs = image_size_attrs( get_user_picture_url() ?? '' );

        echo "<img src=\"" . get_user_picture_url() . "\" $alt {$attrs} />";
    }
    /**
     * funcoes de imagem do usuario logado | retorna a imagem pequena
     * usado em comment-header e comment-form 
     * @see https://opuscore.dev/functions/get_user_picture_url
     **/
    public function get_user_picture_url(): ?string {
        $pic = json_decode( $this->user_picture() ?? '' );

        if( isset($pic->avatar->path) ) {
            return upload_url( $pic->avatar->path );
        }

        return null;
    }

    /**
     * Retorna o JSON da imagem atual do usuario logado (visivel apenas pelo proprio usuario) 
     * pode ser usado na parte da edicao do perfil e acima de formularios de comentarios
     */
    private function user_picture(): ?string {
        return $this->user_attachment( $this->user->logged_id() );
    }

    # Retorna o JSON da imagem atual do perfil do usuario sendo visualizado na pagina de perfil
    private function user_profile(): ?string {
        return $this->user_attachment( $this->user->id() );
    }

    private function user_attachment( int $ref ): ?string {
        $cmd = self::$conn->prepare("
            SELECT attachment FROM medias 
            WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute([ 'user', $ref ]);

        return $cmd->fetchColumn() ?? null;
    }



    /**
     * Insere um novo registro de midia para um usuario no banco de dados.
     *
     * @param Assign $bind Objeto Assign contendo os dados da midia:
     * - relatedID (int): ID do usuario.
     * - relatedtype (string): Tipo da relacao ('user').
     * - relatedtitle (string): Nome do usuario.
     * - attachment (string): JSON com os dados dos anexos.
     * - date (string): Data da insercao.
     * @return int O numero de linhas afetadas pela insercao.
     */
    public function insert_user( Assign $bind ): int {
        $cmd = $this->conn->prepare("
            INSERT INTO media(related_id, related_type, related_title, attachment, created)
            VALUES(?, ?, ?, ?, ?)
        ");
        $cmd->execute([
            $bind->relatedID,
            $bind->relatedtype,
            $bind->relatedtitle,
            $bind->attachment,
            $bind->date
        ]);
        return $cmd->rowCount();
    }

    /**
     * Atualiza os arquivos anexados de midia de um usuario existente.
     *
     * @param Assign $bind Objeto Assign contendo os dados da midia a ser atualizada:
     * - attachment (string): Novo JSON com os dados dos anexos.
     * - relatedID (int): ID do usuario.
     * - relatedtype (string): Tipo da relacao ('user').
     * @return int O numero de linhas afetadas pela atualizacao.
     */
    public function update_user( Assign $bind ): int {
        $cmd = $this->conn->prepare("
            UPDATE medias SET attachment = ? WHERE related_id = ? AND related_type = ?
        ");
        $cmd->execute([
            $bind->attachment,
            $bind->relatedID,
            $bind->relatedtype
        ]);
        return $cmd->rowCount();
    }

    /**
     * Atualiza o nome dos arquivos de imagem do usuario com base no novo username.
     * Renomeia os arquivos fisicamente no sistema de arquivos e atualiza o banco de dados.
     *
     * @param int $user_id O ID do usuario.
     * @param string $username O novo username do usuario.
     * @return int O numero de linhas afetadas na tabela de midia, ou 0 se nao houver anexos.
     */
    public function update_rename_images_user( int $user_id, string $username ): int {
        $cmd = $this->conn->prepare("
            SELECT attachment FROM medias WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute([ 'user', Ensure::int($user_id) ]);
        $row = $cmd->fetch(PDO::FETCH_ASSOC);

        if (isset($row["attachment"])) {
            $attachments = $this->rename_images_user(
                json_decode($row['attachment'], true), $username
            );

            $cmd = $this->conn->prepare("
                UPDATE medias SET attachment = ? WHERE related_type = ? AND related_id = ?
            ");
            $cmd->execute([
                json_encode($attachments),
                'user',
                $user_id
            ]);
            return $cmd->rowCount();
        }
        return 0;
    }

    /**
     * Atualiza o campo "related_title" da midia de um usuario no banco de dados.
     *
     * @param Assign $bind Objeto Assign contendo:
     * - name (string): O novo titulo de relacao (geralmente o nome do usuario).
     * - ID (int): O ID do usuario cuja midia sera atualizada.
     * @return int O numero de linhas afetadas pela atualizacao.
     */
    public function update_user_related_title( Assign $bind ): int {
        $cmd = $this->conn->prepare("
            UPDATE medias SET related_title = ? WHERE related_type = ? AND related_id = ?
        ");
        $cmd->execute([
            $bind->name,
            'user',
            $bind->ID
        ]);
        return $cmd->rowCount();
    }

    /**
     * Renomeia fisicamente os arquivos de imagem do usuario e atualiza os nomes no array de anexos.
     * Usado internamente por `update_rename_images_user()`.
     *
     * @param array<string, string> $attachment Um array associativo com os caminhos dos arquivos de imagem.
     * @param string $username O novo username do usuario a ser usado nos nomes dos arquivos.
     * @return array<string, string> O array de anexos com os nomes dos arquivos atualizados.
     */
    private function rename_images_user( array $attachment, string $username ): array {
        $get_filenames = [
            'medium' => $username .'-sz'. user_md(),
            'small'  => $username .'-sz'. user_sm(),
        ];

        foreach( $get_filenames as $size => $newname ) {
            if( isset($attachment[$size]) && file_exists(UPLOAD_DIR . $attachment[$size]) ) {
                $oldname = pathinfo($attachment[$size], PATHINFO_FILENAME);
                $renamed = str_replace($oldname, $newname, $attachment[$size]);

                rename(UPLOAD_DIR . $attachment[$size], UPLOAD_DIR . $renamed);

                $attachment[$size] = $renamed;
            }
        }

        return $attachment;
    }





    public static function user_fallback( int $dimens = 100, $alt = 'Usuário' ): string {
        return "<img 
            src=\"{$this->user_fallback_url()}\" 
            alt=\"{$alt}\" 
            width=\"$dimens\" height=\"$dimens\" 
        />";
    }

    public static function user_fallback_url(): string {
        return site_url('web/assets/img/user.svg');
    }

}