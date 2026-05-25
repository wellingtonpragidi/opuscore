<?php
/**
 * Gerencia templates do sistema.
 * Qual template ativos, arquivos necessarios etc
 *
 * @package Template
 */
class TemplateManager {

    public const array REQUIRED_TEMPLATE_FILES = ['header.php', 'footer.php', 'index.php'];

    # O slug (nome) do template atual.
    private string $slug = '';

    # O caminho absoluto para o diretorio do template.
    public string $path = '';


    # Inicializa o slug e o caminho absoluto do template.
    public function __construct() {
        $this->slug = template('slug');
                
        $realpath = Ensure::realpath( TEMPLATE_DIR . $this->slug );
        $this->path = ($realpath !== false) ? $realpath : '';
    }


    /**
     * Retorna o caminho completo para um arquivo dentro do diretorio do template.
     * Se o template for valido, constroi o caminho absoluto para inclusao de arquivos.
     */
    public function path(): string {
        return is_dir( $this->path ) ? $this->path . '/' : '';
    }


    /**
     *    Verifica e valida um template:
     * 1.   existencia do diretorio de template
     * 2.   template usado na raiz - valida true se configuracao de slug for vazio
     * 2.5. existencia de arquivos .php essenciais para funcionamento [/templates/ (raiz)]
     * 3.   o ativo precisa conter o arquivo info.json
     * 4.   arquivo info.json deve contem chave "slug" 
     * 5.   nome do diretorio tem que ser igual o valor da chave "slug"
     * 6.   valor da chave "slug" tem que ser igual o nome do diretorio 
     * 7.   valor da chave "slug" tem que ser igual a valor configurado
     * 8.   existencia de arquivos .php essenciais para funcionamento [/templates/sub-diretorio/]
     */
    public function is_valid(): bool {
        # verifica se diretorio 'templates' ou 'templates/template' existe
        if( ! $this->path() ) {
            throw new OpusException(
                "Diretorio <code>{$this->slug}</code> não localizado."
            );
        }

        $dir_name = basename($this->path);

        # Template na raiz - slug deve ser vazio
        if( $dir_name === 'templates' ) {
            if( $this->slug !== '' ) {
                throw new OpusException('Template na raiz requer <a href="'. dash_url('customize/templates') .'">configuração de slug vazio</a>.');
            } 
            # Verifica arquivos necessarios quando site usa o diretorio /templates/ (raiz "/")
            else {
                foreach( TemplateManager::REQUIRED_TEMPLATE_FILES as $file ) {
                    if( ! file_exists($this->path . '/' . $file) ) {
                        throw new OpusException("Diretório <code>/templates/</code> precisa conter o arquivo <code>{$file}</code>.");
                    }
                }
            }

            return true; # Diretorio /templates/ e arquivos necessarios existe! (finaliza aqui)
        }

        $info_file = $this->path . '/info.json';
        if( ! file_exists($info_file) ) {
            throw new OpusException(
                "Template <code>{$this->slug}</code> não tem o arquivo <code>info.json</code>. "
            );
        }

        $content = file_get_contents($info_file);
        $info = json_decode( $content, true );
        if( ! isset($info['slug']) ) {
            throw new OpusException(
                'Chave <code>"slug"</code> no <code>info.json</code> não existe.'
            );
        }
     
        # compara com o nome real do diretorio com info "slug": "x"
        if( $dir_name !== $info['slug'] ) {
            throw new OpusException(
                'Valor de <code>"slug": "'. $info['slug'] .'"</code> em <code>info.json</code> não corresponde ao diretório ativado <code>'. $this->slug .'</code> na configuração.'
            );
        }

        if( $this->slug !== $dir_name ) {
            throw new OpusException(
                "Template ativado na configuração não corresponde ao diretório <code>{$dir_name}</code>."
            );
        }
        
        if( $this->slug !== $info['slug'] ) {
            throw new OpusException(
                'Valor de <code>"slug": "' . $info['slug'] . '"</code> em <code>info.json</code> não corresponde ao template ativado <code>' . $this->slug . '</code> na configuração.'
            );
        }

        # Verifica arquivos necessarios quando site usa um sub-diretorio /templates/{template-slug}
        foreach( self::REQUIRED_TEMPLATE_FILES as $file ) {
            if( ! file_exists($this->path . '/' . $file) ) {
                throw new OpusException(
                    "O template <code>{$this->slug}</code> não tem o arquivo <code>{$file}</code>."
                );
            }
        }

        return true;
    }

    /**
     * Verifica e valida um template antes de incluir o arquivo features.php
     */
    public function check_features(): bool {
        try {
            return $this->is_valid(); # soh devolve true, nunca exception
        } 
        catch( Exception $e ) {
            return false;
        }
    }

}