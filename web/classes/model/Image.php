<?php
declare( strict_types = 1 );
/**
 * Classe Responsavel por gerenciar arquivos de imagem relacionados a conteudos do sistema:
 * articleagens, paginas, categorias e usuarios.
 *
 * Essa classe atua tanto em contexto publico (exibicao de imagens no site)
 * quanto em contexto privado (edicao de perfil do usuario, atualizacao de anexos).
 * 
 * As funcoes auxiliares para exibicao de imagem destacadas 
 * funcionam para todas as entidades que agregam: categorias, usuarios, paginas e articles
 * A classe `Media` resolve o tipo e o ID com base na URL
 * Por esse motivo essa funcoes nao sao para ser usadas dentro de loops como `Seek` para serem exibidas
 * Escopos:
 * categorias: 'plain' e 'thumb' 
 * usuarios: 'profile' e 'avatar' 
 * paginas: 'wide', 'larger' e 'minor' 
 * articles: 'wide', 'larger', 'minor' e 'thumb'
 *
 * @see https://opuscore.dev/classe-media
 *
 * Mapa de distribuicao de responsabilidades:
 * ────────────────────────────────────────────────────────────────
 * Gerenciamento geral de imagens (contexto publico e de perfil)
 *
 * ▸ Article, Pagina, Categoria:
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
 * @package Output\Model\Helper
 */

class Image {


    private PDO $conn;

    private static PDO $db;


    private Auth $auth;

    private static Category $category;
    private static Article $article;
    private static Page $page;
    private static User $user;

    /**
     * @var 
     * $id    ID da entidade relacionada (articles, pages, categories, users etc)
     * $type  Tipo da relacao 'article', 'page', 'category-article', 'user' ...
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
        PDO $conn, Category $category, Article $article, Page $page, User $user, Auth $auth ) {

        $this->conn = $conn;
        self::$db   = $conn;

        self::$category = $category;
        self::$article     = $article;
        self::$page     = $page;
        self::$user     = $user;

        $this->auth = $auth;

        $this->resolve();
    }

    private function resolve() {
        # detecta tipo de midia com base na URL
        if( is_category() ) {
            self::$id   = self::$category->id();
            self::$type = 'category-article';
            self::$alt  = Ensure::attr( self::$category->name() );
            self::$scope = 'plain';
        }
        else if( is_article() ) {
            self::$id   = self::$article->target()->ID;
            self::$type = 'article';
            self::$alt  = Ensure::attr( self::$article->target()->title );
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
    #          das entidade: categorias, usuarios, paginas e articles
    # ========================================================================

    public static function featured( array $args = [] ): void {

        echo self::get_featured($args);
    }

    # args: 'scope', 'alt', 'class'
    public static function get_featured( array $args = [] ): ?string {

        $scope = $args['scope'] ?? self::$scope;

        $alt = ($args['alt'] ?? '') ? Ensure::attr($args['alt']) : self::$alt;

        $class = empty($args['class'] ?? null) 
            ? '' 
            : 'class="' . Ensure::attr($args['class']) . '"';

        $img = self::attachment_data( $scope );

        if( isset($img['path']) ) {

            return "<img src=\"{$img['URL']}\" alt=\"{$alt}\" {$img['dimension']} {$class} />";
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


    public static function picture_per_screen( string $change_alt = '' ): void {
        $minor  = self::attachment_data('minor');
        $larger = self::attachment_data('larger');
        $wide   = self::attachment_data('wide');

        if( ! isset($minor['path'], $larger['path'], $wide['path']) ) {
            return;
        }

        $alt = ! empty($change_alt) ? Ensure::attr($change_alt) : self::$alt;
        $attrs = "alt=\"{$alt}\" {$minor['dimension']}";

        echo <<<HTML
        <picture>
            <source media="(min-width: 1366px)" srcset="{$wide['URL']}" />
            <source media="(min-width: 768px)" srcset="{$larger['URL']}" />
            <img src="{$minor['URL']}" {$attrs} />
        </picture>
        HTML;
    }


    private static function attachment_data( ?string $scope = null ): ?array {
        $attachment = Ensure::object( self::attachment() );

        if( empty($attachment) ) {
            return null;
        }

        $scope = $scope ?? self::$scope;

        $fillpath = $attachment->{$scope}->path ?? null;

        if( ! $fillpath ) {
            return null;
        }

        # o numero para versao de cache eh filho direto de attachment, fora de path e/ou scope
        $version = '?v=' . ($attachment->version ?? 0);

        $dimension = self::dimension_attrs( $attachment->{$scope} ?? null );

        return [
            'path' => $fillpath,
            'URL'  => upload_url($fillpath . $version),
            'dimension' => $dimension
        ];
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

        $value = $cmd->fetchColumn();

        self::$cache['attachment'] = ($value === false) ? null : $value;
         
        return self::$cache['attachment'];
    }





/* dimension_attrs ⌵ ------------------------------------------------------------------ */

    /**
     * Obtem dimensoes a partir de um objeto de escopo (size)
     * Gera atributos HTML width e height a partir de um objeto de escopo (size)
     * 
     * @param $object | Instancia de escopo (stdClass) contendo valor de dimensao
     */
    public static function dimension_attrs( ?object $object ): string {
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
    
    public function has_user_picture(): bool {
        $cmd = $this->conn->prepare("
            SELECT 1 FROM medias WHERE related_type = ? AND related_id = ? LIMIT 1
        ");

        $cmd->execute([ 'user', $this->auth->id() ]);

        return (bool) $cmd->fetchColumn();
    }

    /**
     * (visivel apenas pelo proprio usuario logado) 
     * 
     * Imprime o elemento img da foto/imagem de avatar atual do usuario logado 
     */
    public function user_avatar(): string {
        $avatar_url = self::user_url() ?? dist_thumbnail('user.svg');

        $logged = $this->auth->logged();

        $size      = user_pic_sz('avatar');
        $dimension = 'width="' . $size . '" height="' . $size . '"';


        return "<img src=\"{$avatar_url}\" alt=\"{$logged->name}\" {$dimension} />";
    }

    /**
     * (visivel apenas pelo proprio usuario logado)
     * 
     * Retorna a URL da foto/imagem de perfil/avatar do usuario || null
     */
    public function user_url( string $scope = 'avatar' ): ?string { 
        $cmd = self::$db->prepare("
            SELECT attachment FROM medias 
            WHERE related_type = ? AND related_id = ? 
            LIMIT 1
        ");
        $cmd->execute([ 'user', $this->auth->id() ]);

        $attachment = Ensure::object( $cmd->fetchColumn() );

        $path = $attachment->{$scope}->path ?? null;
        if( $attachment === '{}' || $path === null ) {
            return null;
        }

        return upload_url( $path . '?v=' . ($attachment->version ?? 0) );
    }


    # Insere novo registro de midia para um usuario no banco de dados
    public function user_insert(): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO medias (related_id, related_type, related_title, attachment, created)
            VALUES (?, ?, ?, ?, ?)
        ");

        $cmd->execute([
            $this->auth->id(),
            'user',
            $this->auth->title,
            $this->json_attachment(),
            $this->auth->created
        ]);

        return $cmd->rowCount() > 0;
    }


    # Atualiza anexo de midia de um usuario existente, substituindo o arquivo fisico anterior
    public function user_update(): bool {
        $cmd = $this->conn->prepare("
            UPDATE medias SET attachment = ? WHERE related_id = ? AND related_type = ?
        ");

        $cmd->execute([
            $this->json_attachment(),
            $this->auth->id(),
            'user'
        ]);

        return $cmd->rowCount() > 0;
    }

    # monta o valor para a coluna `attachment`
    private function json_attachment(): string {
        $ext  = FILES::ext(); # 'attachment'

        $attachment = [];

        foreach( ImageSize::dimensions('user') as $scope => $data ) {
            $datepath = date( 'Y/m', strtotime($this->auth->logged()->created ?? '') );

            $fillpath = $datepath . '/user-' . $this->auth->id() . '-' . $scope . '.' . $ext;

            $attachment[$scope] = [
                'path'   => $fillpath,
                'width'  => $data['width'],
                'height' => $data['height']
            ];
        }

        $attachment['version'] = random_int(1000, 9999);

        return Ensure::json($attachment);
    }


    # Atualiza o campo "related_title" da midia de um usuario no banco de dados
    public function update_related_title(): bool {
        $cmd = $this->conn->prepare("
            UPDATE medias SET related_title = ? WHERE related_type = ? AND related_id = ?
        ");

        $cmd->execute([ $this->auth->logged()->name, 'user', $this->auth->id() ]);

        return $cmd->rowCount() > 0;
    }

}