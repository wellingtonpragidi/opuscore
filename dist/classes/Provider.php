<?php
/**
 * @package Core/Helper
 **/
class Provider {

    private $conn;

    private static array $cached = [];


    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }


    public static function settings( string $key ): array {
        if( ! array_key_exists('settings', self::$cached) ) {
            $file = STORAGE_DIR . 'settings.php';

            self::$cached['settings'] = self::include_file_vars($file);
        }

        return self::$cached['settings'][$key] ?? [];
    }


    /**
     * Carrega um arquivo PHP isoladamente e retorna todas as variaveis definidas dentro dele.
     *
     * O arquivo eh incluído dentro de uma closure para evitar vazamento de escopo externo.
     *
     * A variavel interna utilizada pela closure ($file) eh removida do resultado antes do retorno.
     *
     * @param $path Caminho absoluto do arquivo PHP.
     * @return Variaveis declaradas no arquivo, indexadas pelo nome.
     *
     * @example
     * Se no arquivo existir:
     * $options = ['a' => 1];
     *
     * O retorno sera:
     * ['options' => ['a' => 1]]
     */
    public static function include_file_vars( string $path ): array {
        $key = basename( $path, '.php' );

        if( ! array_key_exists($key, self::$cached) ) {
            $file_vars = ( function($file) {
                    include $file;
                    return get_defined_vars();
                } 
            )($path);

            unset( $file_vars['file'] );

            self::$cached[$key] = $file_vars;
        }

        return self::$cached[$key];
    }


    /**
     * auxiliar para envio de e-mails
     * @param $args {
     *     string | 'email'
     *     string | 'name'
     *     string | 'subject' | site_title() – 'subject'
     *     string | 'body'    | HTML
     * }
     */
    public static function send_email( array $args ): bool { 
        if( ! isset($args['email'], $args['name'], $args['subject'], $args['body']) ) {
            return false;
        }

        $sitename = site_title();

        $mailer = new PHPMailer;

        $mailer->isHTML(true);
        $mailer->CharSet     = 'UTF-8';
        $mailer->IsSMTP();
        $mailer->SMTPAuth    = true;
        $mailer->SMTPAutoTLS = true;

        $mailer->Host       = email_host();
        $mailer->Username   = email_user();
        $mailer->Password   = email_pswd();
        $mailer->Port       = email_port();

        if( $mailer->Port === 587 ) {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        else if( $mailer->Port === 465 ) {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        else {
            $mailer->SMTPSecure = false;
        }

        $mailer->From       = email_address();
        $mailer->FromName   = $sitename;
        $mailer->addReplyTo( email_address(), $sitename );
        $mailer->addAddress( $args['email'], $args['name'] );

        $mailer->Subject = $sitename . ' – ' . $args['subject'];

        $mailer->Body = '<div style="font-family: system-ui, sans-serif; font-size: 1.15rem">'
            . $args['body'] . 
        '</div>';

        if( MAIL_SMTP_DEBUG ) {
            $mailer->SMTPDebug = 2;
            $mailer->Debugoutput = 'html';
        }

        if( ! $mailer->send() ) {
            if( MAIL_ERROR_INFO ) {
                echo '<div class="opus-exception">' . $mailer->ErrorInfo . '</div>';
            }
            return false;
        }

        return true;
    }

    public static function email_body( array $args = [] ): string {
        $h2 = ($args['h2'] ?? false)  
            ? '<h2 style="font-size: 1.25rem; font-weight: 500">' . $args['h2'] . '</h2>' 
            : null;

        $p1 = ($args['p1'] ?? false) ? '<p>' . $args['p1'] . '</p>' : null;

        $p2 = ($args['p2'] ?? false) ? '<p>' . $args['p2'] . '</p>' : null;

        $link = '';
        if( isset($args['link']) ) {
            $link = '<p>
                <a href=\"' . $args['link'] . '\" target="_blank" rel="noopener">'
                    . $args['link'] . 
                '</a>
            </p>';
        }

        return $h2 . $p1 . $p2 . $link 
        . '<p style="font-size: 0.95rem; margin-top: 30px; opacity: 0.85">
            Caso isso seja um engano, você pode responder a: ' . SYSTEM_EMAIL_ADDRESS . '
        </p>';
    }
    
}