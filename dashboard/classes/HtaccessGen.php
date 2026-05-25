<?php
/**
 * Gera e atualiza arquivo .htaccess se detectado qualquer alteracao de escrita entre os marcadores
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System/Output/Services
 * @subpackage Generator/Updater
 */
class HtaccessGen {

    public function rewrite(): void {
        $filepath = DIR . '.htaccess';

        $current = file_exists($filepath) ? file_get_contents($filepath) : '';

        $markers = $this->markers();

        $start_marker = '# INICIO das diretivas geradas pelo sistema';
        $end_marker   = '# FIM das diretivas geradas pelo sistema';

        $has_start = str_contains( $current, $start_marker );
        $has_end   = str_contains( $current, $end_marker );

        # Regex que captura tudo entre os marcadores (incluindo os proprios marcadores)
        $start_escaped = preg_quote( $start_marker, '/' );
        $end_escaped   = preg_quote( $end_marker, '/' );

        $regex = "/{$start_escaped}.*?{$end_escaped}/s";

        # Remove todos os blocos antigos (se usuario duplicou ou baguncou)
        $cleaned = preg_replace( $regex, '', $current );
        
        # Remove multiplas quebras de linha consecutivas
        $cleaned = preg_replace( '/\n{3,}/', "\n\n", $cleaned );
        $cleaned = trim( $cleaned );
        
        # Aplica a logica original no conteudo ja limpo
        $has_start = str_contains( $cleaned, $start_marker );
        $has_end   = str_contains( $cleaned, $end_marker );

        # Se ainda tem marcadores soltos (arquivo corrompido)
        if( $has_start XOR $has_end ) {
            # Remove qualquer coisa que comece/termine com o marcador existente
            if( $has_start ) {
                $cleaned = preg_replace('/' . preg_quote($start_marker, '/') . '.*/s', '', $cleaned);
            } 
            else {
                $cleaned = preg_replace('/.*' . preg_quote($end_marker, '/') . '/s', '', $cleaned);
            }

            $cleaned = trim($cleaned);
        }

        # Adiciona o novo bloco no inicio
        $new = $markers . "\n\n" . $cleaned;

        # Novamente remove multiplas quebras de linha consecutivas (por seguranca)
        $new = preg_replace( '/\n{3,}/', "\n\n", $new );
        $new = trim($new);
        $current = trim($current);

        # So grava se algo mudou nos requisitos passados acima
        if( $new !== $current ) {
            Ensure::writeLock( $filepath, $new );
        }
    }


    private function markers(): string {
        # diretivas completas dentro dos marcadores
        return
            '# INICIO das diretivas geradas pelo sistema' . PHP_EOL .
            '# A edição manual entre "# INICIO e # FIM" deste arquivo serão sobrescritas' . PHP_EOL .
            $this->directives() . PHP_EOL .
            '# FIM das diretivas geradas pelo sistema';
    }


    private function directives(): string {
        return IS_LOCAL ? $this->local_directives() : $this->production_directives();
    }


    private function local_directives(): string {
        $mod_mime = $this->mod_mime();

        return <<<HTA
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . index.php [L]
        </IfModule>
        $mod_mime
        HTA;
    }


    private function production_directives(): string {
        $mod_mime = $this->mod_mime();
        
        return <<<HTA
        <IfModule mod_rewrite.c>
            RewriteEngine On
            # Redireciona para https e remove 'www.'
            RewriteCond %{HTTPS} off [OR]
            RewriteCond %{HTTP_HOST} ^www\\.(.+)$ [NC]
            RewriteRule ^(.*)$ https://%1%{REQUEST_URI} [R=301,L]
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . index.php [L]
        </IfModule>
        $mod_mime
        <IfModule mod_expires.c>
            ExpiresActive On
            ExpiresByType text/html                "access plus 0 seconds"
            ExpiresByType application/json         "access plus 0 seconds"
            ExpiresByType application/xml          "access plus 0 seconds"
            ExpiresByType text/xml                 "access plus 0 seconds"
            ExpiresByType text/plain               "access plus 0 seconds"
            ExpiresByType application/x-httpd-php  "access plus 0 seconds"
            ExpiresByType image/jpeg               "access plus 11 months"
            ExpiresByType image/jpg                "access plus 11 months"
            ExpiresByType image/png                "access plus 11 months"
            ExpiresByType image/gif                "access plus 11 months"
            ExpiresByType image/webp               "access plus 11 months"
            ExpiresByType image/avif               "access plus 11 months"
            ExpiresByType image/svg+xml            "access plus 11 months"
            ExpiresByType image/x-icon             "access plus 11 months"
            ExpiresByType text/css                 "access plus 6 months"
            ExpiresByType font/ttf                 "access plus 11 months"
            ExpiresByType font/otf                 "access plus 11 months"
            ExpiresByType font/woff                "access plus 11 months"
            ExpiresByType font/woff2               "access plus 11 months"
            ExpiresByType application/font-woff    "access plus 11 months"
            ExpiresByType application/font-woff2   "access plus 11 months"
            ExpiresByType application/javascript   "access plus 6 months"
            ExpiresByType application/x-javascript "access plus 6 months"
            ExpiresByType text/javascript          "access plus 6 months"
            ExpiresByType video/mp4                "access plus 11 months"
            ExpiresByType video/webm               "access plus 11 months"
            ExpiresByType video/ogg                "access plus 11 months"
            ExpiresByType audio/mp3                "access plus 11 months"
            ExpiresByType audio/mpeg               "access plus 11 months"
            ExpiresByType audio/ogg                "access plus 11 months"
        </IfModule>
        <IfModule mod_headers.c>
            <FilesMatch "\\.(js|css|png|jpg|jpeg|gif|webp|svg|woff2?|ttf|otf|mp4|webm)$">
                Header set Cache-Control "public, max-age=15552000"
            </FilesMatch>
        </IfModule>
        HTA;
    }


    private function mod_mime(): string {
        return <<<HTA
        # Força a codificação UTF-8 para arquivos quando acessados diretamente
        <IfModule mod_mime.c>
            AddDefaultCharset UTF-8
            AddCharset UTF-8 .html .js .json .css .xml .txt .php
        </IfModule>
        HTA;
    }

}