<?php
declare( strict_types = 1 );
/**
 * Classe de suporte as classes User: Profile, Auth e Status
 *   e funcoes presentes no arquivo users.php
 * 
 * colunas:
 * ID, name, username, email, pswd, created, updated, content, token, nonce, status, approved
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @link https://opuscore.dev/classes/user
 * 
 * @package Output\User
 */
class User {

    private static UserAuth $auth;
    private static UserProfile $profile;
    private static UserStatus $status;


    public function __construct( UserAuth $auth, UserProfile $profile, UserStatus $status ) {
        self::$auth    = $auth;
        self::$profile = $profile;
        self::$status  = $status;
    }


    /**
     * Acessores para campos do usuario via URL
     * !!! Nao use fora do arquivo `user.php` do template
     */
    public static function id(): ?int {
        return (int) self::$profile->field('ID') ?? null;
    }

    public static function name(): ?string {
        return self::$profile->field('name') ?? null;
    }

    public static function username(): ?string {
        return self::$profile->field('username') ?? null;
    }

    public static function created(): ?string {
        return self::$profile->field('created') ?? null;
    }

    public static function update(): ?string {
        return self::$profile->field('updated') ?? null;
    }

    public static function description(): ?string {
        return self::$profile->field('content') ?? null;
    }

    # Gera resumo para meta tags
     public static function meta_description(): string {
        $description = self::$profile->field('content') ?? '';
        if( ! $description ) {
            $description = seo('user_description');
        }

        return text_summary_attr( $description );
    }

}