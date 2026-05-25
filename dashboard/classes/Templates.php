<?php
/**
 * Gerencia a listagem e exibicao de templates (templates) disponiveis no sistema.
 * Esta classe e responsavel por escanear o diretorio de templates, ler seus
 * metadados de arquivos `info.json`, e exibir as opcoes de templates para ativacao
 * e visualizacao no painel administrativo.
 *
 * @package System/Customize
 */
class Templates {

    /**
     * Lista todos os templates disponiveis no diretorio `TEMPLATE_DIR`.
     *
     * Escaneia subdiretorios, le o arquivo `info.json` de cada template para
     * obter seus metadados (nome, autor, versao, etc.) e verifica a existencia
     * de uma imagem de preview (`print.png`)
     */
    public static function gallery(): void {
        try {
            $is_templates_available = false;
            $warning_html = '';
            $templates_html = '';
            $is_active = false;
            foreach( new DirectoryIterator( TEMPLATE_DIR ) as $base ) {

                # primeiro remover "ruidos" estrutural do filesystem '.' '..'
                if( $base->isDot() ) {
                    continue;
                }

                # mesmo sendo entradas valida do filesystem, nao serve para o conceito de template
                # soh passa diretorio, nao arquivos
                if( ! $base->isDir() ) {
                    continue;
                }

                # Sim, acima sao duas condicoes `if` e mantenha assim.


                # Checagem de arquivos: Nao depende do loop foreach → DirectoryIterator → $base
                # portanto ( ! $base->isDir() ) nao afeta isso aqui
                #
                # se os arquivos da lista `REQUIRED_TEMPLATE_FILES` forem encontrados na raiz de /templates/
                # e template('slug') for string vazia, consideramos o uso do site direto no diretorio /templates/
                if( template('slug') === '' ) {
                    $missing_files = [];

                    # Ok, template('slug') eh string vazia, agora checamos se os arquivos necessarios existem
                    foreach( TemplateManager::REQUIRED_TEMPLATE_FILES as $file ) {
                        if( ! file_exists(TEMPLATE_DIR . $file) ) {
                            $missing_files[] = $file;
                        }
                    }
                    if( ! empty($missing_files) ) {
                        $files = implode( ', ', $missing_files ) ;

                        $warning_html = "<div class=\"one-exception\">
                            A configuração de slug do template está vazia <code>&apos;&apos;</code>, 
                            isso indica que a interface do site usa o diretório base, 
                            porém o sistema detectou a falta dos seguintes arquivos: <code>{$files}</code>.
                        </div>";
                    } 
                    else {
                        $warning_html = '<div class="one-exception">
                            Template definido na raiz do diretório <code>templates</code>
                        </div>';
                    }
                }

                $dirname = $base->getBasename();


                $template_path = TEMPLATE_DIR . $dirname;
                $pathinfo      = $template_path . '/info.json';

                # Verifica se o arquivo info.json existe
                if( ! is_file($pathinfo) ) {
                    continue;
                }

                $json = file_get_contents( $pathinfo );
                $info = json_decode( $json, true ) ?? '';

                # Garante que info.json contenha a chave 'slug'
                if( ! self::valid_info($info, 'slug') ) {
                    continue;
                }
                if( $info['slug'] !== $dirname ) {
                    continue;
                }

                    # Se chegou ate aqui, existe template valido. .  .
                    $is_templates_available = true;

                $template_name = $info['name'] ?? $info['slug'];

                $base_url   = site_url('templates') . '/' . $dirname;
                # @todo permitir extencoes ['jpg', 'jpeg', 'png', 'webp', 'gif']
                $print_path = $template_path . '/print.png';
                $print_url  = str_replace( $template_path, $base_url, $print_path );

                # Define a URL da imagem de preview do template
                $print = dash_url('assets/img/transparent-background-dark.png'); # Fallback 
                if( file_exists($print_path) ) {
                    $print = $print_url;
                }
                /* 
                @todo Para quando fixar o template ativo:
                 Se for o template ativo for exibido acima (fixo) entao nao repetimos a exibicao
                if( template('slug') === $info['slug'] ) {
                    continue;
                }
                */

                $is_active = template('slug') !== '' 
                    && template('slug') === $dirname 
                    && $info['slug'] === $dirname;
       
                $active = $is_active ? ' active' : '';
                $templates_html .= '<div class="template cn_33'. $active .'">
                    <div class="m15 p10">
                        <div class="print">
                            <img src="'. $print .'" alt="'. $template_name .'" />
                        </div>
                        <div class="action clean">';
                            if( $is_active ) {
                                $templates_html .= "<h3><strong>Ativo:</strong> {$template_name}</h3>";
                            }
                            else {
                                $templates_html .= '<h3>'. $template_name .'</h3>
                                <button class="btn sm" name="action" value="'. $info['slug'] .'">Ativar</button>';
                            }
                        $templates_html .= '</div>
                    </div>
                </div>';
            }

            $missing_files = [];
            # verifica se template ativo contem arquivos necessario, caso falte algum dispara aviso
            if( $is_active ) {
                foreach( TemplateManager::REQUIRED_TEMPLATE_FILES as $file ) {
                    if( ! file_exists(TEMPLATE_PATH . $file) ) {
                        $missing_files[] = $file;
                    }
                }
                if( ! empty($missing_files) ) {
                    $files = implode( ', ', $missing_files ) ;

                    $warning_html = "<div class=\"one-exception\">
                        O sistema detectou a falta dos seguintes arquivos no template ativo: <code>{$files}</code>.<br>
                        O que irá gerar erros na exibição pública.
                    </div>";
                }
            }

            echo $warning_html;

            echo $templates_html;


            # se nenhum template valido foi encontrado, lança aviso
            if( ! $is_templates_available && template('slug') !== '' ) {
                echo '<div class="one-exception">Nenhum template disponível, <small>ou faltando arquivo <code>info.json</code></small></div>' . $b;
            }
        }
        catch( UnexpectedValueException $e ) {
            alert( 'error', 'Erro ao processar templates: ' . $e->getMessage() );
        }
    }

    private static function valid_info( array $info, string $key ): bool {
        $data = $info[$key] ?? false;
        return is_string( $data ) && trim( $data ) !== '';
    }

    /**
     * Verifica se existe diretorio dentro de `/templates/`
     * 1. checa se a configuracao — template('slug') nao esta vazia
     * 2. checa se a configuracao — template('slug') eh igual nome do diretorio
     * 3. checa se a "slug" do arquivo info.json do template eh igual nome do diretorio
    
    private static function template_showcase( string $dirname, ?string $info ): bool {
        $check_1 = template('slug') !== '';
        $check_2 = template('slug') === $dirname;
        $check_3 = $info['slug'] === $dirname;

        $checked = $check_1 && $check_2 && $check_3;

        return $checked;
    } */

}