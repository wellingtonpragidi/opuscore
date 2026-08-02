<?php
declare( strict_types = 1 );
/**
 * Classe responsavel por executar o processo de atualizacao do sistema.
 * Inclui download, extracao, limpeza de arquivos obsoletos e geracao de log.
 *
 * @package System\Upgrades
 */
class Upgrade {

    # diretorios onde tudo eh permitido: atualizar, excluir e adicionar
    private static array $allowed_dirs = [
        'dashboard/', 'dist/', 'web/'
    ];

    # arquivos na raiz sao protegidos, exceto esses
    private static array $allowed_files = [
        'index.php', 'maintenance.html'
    ];

    # diretorios protegido, a atualizacao nao toca em nada deles
    private static array $protected_dirs = [
        'uploads/', 'templates/', 'storage/', 'addons/'
    ];



    /**
     * Verifica se ha uma nova versao do sistema disponivel para atualizacao.
     */
    public static function has(): bool {
        $latest = self::package('latest_version') ?? false;
        return $latest && version_compare( $latest, VERSION, '>' );
    }

    /**
      * Obtém os dados do pacote de atualizacao remoto.
      *
      * $target:
      * Se informado, retorna apenas o valor correspondente a esta chave do JSON
      * Do contrario o arquivo JSON completo decodificado em array associativo
      */
    public static function package( string $target = '' ): array|string|null {
        # Verifica se o arquivo package.json esta acessivel
        # intenamente is_available_file usa read_file que prioriza leitura de arquivo em cache
        if( ! self::is_available_file('package.json') ) {
            return null;
        }

        $response = self::read_file('package.json');

        if( ! $response ) {
            return null;
        }

        $package = json_decode( $response, true );
        
        if( ! $package && ! is_array($package) ) {
            return null;
        }

        # Se um target especifico for solicitado e existir, retorna apenas aquele valor.
        if( $target !== '' ) {
            return $package[$target] ?? null;
        }

        # Caso contrario, retorna o pacote completo 
        return $package;
    }


    /**
     * Obtem a ultima entrada registrada no changelog remoto do sistema.
     * Le o arquivo CHANGELOG.md do servidor remoto e extrai o conteudo
     * da ultima secao de alteracoes.
     * @deprecated use self::read_file() direto
     */
    public static function get_last_changelog_entry(): string {
        # Obtem o conteudo do arquivo CHANGELOG.md.
        $contents = self::read_file('CHANGELOG.md', false);

        # Adiciona um marcador de fim para facilitar a correspondencia da regex.
        $contents .= "\n## END";

        # Regex para extrair o conteudo da ultima entrada (entre "## [Versao] - Data" e "## ").
        $regex = '/## \[.*?\]\s*-\s*\d{2}\/\d{2}\/\d{4}\s*\n(.*?)\n## /s';

        # Se encontrar a correspondencia, retorna o conteudo trimado.
        if( preg_match( $regex, $contents, $matches ) ) {
            return trim( $matches[1] );
        }
        return '';
    }

    /**
     * Executa o processo completo de atualizacao do sistema a partir de um arquivo ZIP.
     * Inclui download, extracao, e remocao de arquivos nao mais presentes na nova versao.
     *
     * @return true em caso de sucesso, em falha string com o tipo de erro.
     */
    public static function update_system( string $zip_file_name ): bool|string {
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '300M');

        $upgrade_dir   = UPLOAD_DIR . 'packages/upgrades/';
        $zip_file_path = $upgrade_dir . trim($zip_file_name);
        $log_path      = $upgrade_dir . 'upgrade.log';

        if( ! is_dir($upgrade_dir) ) {
            mkdir( $upgrade_dir, 0755, true );
        }

        # 1. Baixar o pacote ZIP
        if( ! self::download_zip($zip_file_name) ) {
            return 'Falha no download do arquivo ZIP.';
        }

        # 2. Ler lista de arquivos da nova versao
        $evidence_content = self::read_file('evidence.json');
        if( ! $evidence_content ) {
            return 'Não foi possível carregar o arquivo evidence.json.';
        }

        $evidence = json_decode($evidence_content, true);
        if( ! $evidence || !isset($evidence['files']) ) {
            return 'O arquivo evidence.json está inválido ou corrompido.';
        }

        $log = [
            'atualizados' => [],
            'protegidos'  => [],
            'removidos'   => [],
            'falhas'   => []
        ];

        # 3. Remover arquivos e diretorios obsoletos
        self::cleanup_obsolete_files($evidence['files'], $log);

        # 4. Aplicar atualizacao (extracao do ZIP)
        $zip = new ZipArchive;
        if( $zip->open($zip_file_path) !== true ) {
            if( file_exists($zip_file_path) ) {
                unlink($zip_file_path);
            }
            
            return 'Falha ao abrir o arquivo ZIP.';
        }

        $allowed_root = ['index.php'];

        for( $i = 0; $i < $zip->numFiles; $i++ ) {
            $entry = ltrim( $zip->getNameIndex($i), '/' );

            # Ignorar arquivos fora dos diretorios do sistema
            if( ! self::is_updatable($entry, $allowed_root, self::$allowed_dirs) ) {
                $log['protegidos'][] = $entry;
                continue;
            }
            if( ! is_dir($entry) ) {
                if( $zip->extractTo(DIR, $entry) ) {
                    $log['atualizados'][] = $entry;
                } 
                else {
                    $log['falhas'][] = $entry;
                }
            }
        }

        $zip->close();
        unlink( $zip_file_path );

        # 5. Salvar log final (reescreve sempre o mesmo arquivo)
        $log_details  = "# === Atualizado em " . date('d/m/Y H:i') . " ===\n";
        $log_details .= json_encode( $log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        Ensure::writeLock( $log_path, $log_details );

        return true;
    }


    /**
     * Varre o filesystem e remove arquivos/diretorios extras (nao listados em 'evidence.json')
     * Atua apenas dentro dos diretorios de sistema definidos em `self::$allowed_dirs`
     * 
     * Ignora e protege: 
     * - Arquivos da raiz, exceto `$allowed_files`
     * - Diretorios `$protected_dirs` e seus filhos
     *
     * @param $evidence_files | Lista de caminhos de arquivos e diretorios que devem existir na nova versao.
     * @param &$log | Referencia para um array onde serao registrados os arquivos removidos ou protegidos.
     **/
    private static function cleanup_obsolete_files( array $evidence_files, array &$log ): void {

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( DIR, RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach( $iterator as $file ) {
            $file_path     = str_replace("\\", "/", $file->getRealPath());
            $relative_path = ltrim(str_replace(DIR, '', $file_path), '/');


            # arquivos na raiz sao protegidos, exceto `$allowed_files`
            if( substr_count($relative_path, '/') === 0 ) {
                if( ! in_array($relative_path, self::$allowed_files, true) ) {
                    $log['protegidos'][] = $relative_path;
                    continue;
                }
            }

            # Proteger diretorios reservados `$protected_dirs` (dados dinamicos do usuario)
            # Nao devem ser modificados ou removidos durante a atualização
            foreach( self::$protected_dirs as $dir ) {
                if( strpos($relative_path, $dir) === 0 ) {
                    $log['protegidos'][] = $relative_path;
                    continue 2;
                }
            }

            # Verificar se esta dentro de um dos diretorios do sistema `$allowed_dirs`
            # Se estiver `$is_system_dir` eh `true`, o sistema exclui e atualiza
            $is_system_dir = false;
            foreach( self::$allowed_dirs as $dir ) {
                if( strpos($relative_path, $dir) === 0 ) {
                    $is_system_dir = true; # seguimos com a logica de excluir/atualizar
                    break;
                }
            }
            # Se o path estiver dentro de um diretorio desconhecido `$is_system_dir` eh false
            # O sistema pula e nao toca em nada dos filhos
            if( ! $is_system_dir ) {
                $log['protegidos'][] = $relative_path;
                continue;
            }

            # Neste ponto, $relative_path esta dentro de um dos diretorios principais => `$allowed_dirs` => removemos tudo que nao esta em evidencia
            if( ! in_array($relative_path, $evidence_files, true) ) {
                if( $file->isDir() ) {
                    if( @rmdir($file_path) ) {
                        $log['removidos'][] = $relative_path . '/';
                    } 
                    else {
                        $log['falhas'][] = $relative_path . '/';
                    }
                } 
                else {
                    if( @unlink($file_path) ) {
                        $log['removidos'][] = $relative_path;
                    } 
                    else {
                        $log['falhas'][] = $relative_path;
                    }
                }
            }
        }
    }


    /**
     * Determina se uma entrada do ZIP pode ser atualizada/extraida
     */
    private static function is_updatable( 
        string $entry, array $allowed_root, array $allowed_dirs ): bool {

        if( strpos($entry, '/') === false ) {
            return in_array( $entry, $allowed_root, true );
        }
        foreach( $allowed_dirs as $dir ) {
            if( strpos($entry, $dir) === 0 ) {
                return true;
            }
        }
        return false;
    }


    /**
     * Verifica se dominios sao internos e nao deve passar por update automatico
     **/
    public static function is_internal_domain(): bool {
        return in_array( 
            $_SERVER['SERVER_NAME'] ?? '', 
            ['lab.opuscore.dev'], 
            true 
        );
        # 'opuscore.dev', 'int.opuscore.dev', 
    }


    /**
     * Helper para ocultar o conteudo relacionado a atualizacao na interface
     * utilizado em '/controller/upgrade.php' apos atualizacao bem-sucedida
     */
    public static function hidden_upgrade_content(): void {
        echo '<style>.upgrade-content { display: none }</style>';
    }


    /**
     * -------------- HTTP : https://opuscore.dev/packages/upgrades/
     * -------------- Cache : .../uploads/upgrades/*.cache
     */

    /**
     * Busca o conteudo de URL remota, utilizando um sistema de cache baseado em arquivo.
     *   ↓  ↓  ↓  ↓  ↓          ↓  ↓  ↓  ↓  ↓  ↓
     * Prioriza a leitura do arquivo em cache local se existir e nao estiver expirado.
     * Do contrario, tenta buscar o conteudo remotamente usando `file_get_contents` com um contexto de stream configurado
     */
    public static function read_file( string $filename ): ?string {
        $cache_expires = 172800; # 48h

        $remote_file = ENGINE_URL . '/packages/upgrades/' . $filename;

        $cache_dir = UPLOAD_DIR . 'packages/upgrades/';


        if( ! is_dir($cache_dir) ) {
            mkdir( $cache_dir, 0755, true );
        }

        $cached_file = $cache_dir . $filename . '.cache';

        # Tenta ler do cache se o arquivo existir e nao estiver expirado
        if( file_exists($cached_file) && (filemtime($cached_file) + $cache_expires) > time() ) {

            return file_get_contents($cached_file);
        }

        # Se nao esta em cache ou expirou, tenta buscar o conteudo remoto e gravar em cache

        $stream_context = stream_context_create([
            'http' => [
                'method' => "GET",
                'timeout' => 6, # Timeout em segundos para a requisicao.
                'follow_location' => true, # Segue redirecionamentos HTTP (301, 302).
                'ignore_errors'   => true, # Permite ler o conteudo mesmo se o servidor retornar um erro HTTP (4xx, 5xx).
            ]
        ]);
    
        $content = @file_get_contents( $remote_file, false, $stream_context );

        if( $content === false ) {
            $error = error_get_last();
            $reason = isset($error['message']) 
                ? $error['message'] 
                : 'Timeout ou Falha de Conexão';

            opus_log("Falha ao buscar conteúdo remoto em: {$remote_file} | Motivo: {$reason}");

            return null;
        }

        if( self::is_valid_remote_content($filename, $content) ) {
            Ensure::writeLock( $cached_file, $content );
            return $content;
        }

        return null; # Retorna null se nao for possivel obter o conteudo.
    }

    /**
     * Para verificar disponibilidade de arquivo
     * Esta funcao utiliza `self::read_file` para tentar buscar o conteudo de uma URL
     * e, com base no sucesso da operacao, retorna 200 (OK) ou 0 (erro/indisponivel).
     * Ela eh ideal para checar a acessibilidade de recursos antes de checagem para atualizar arquivos de chache ou tentar um download de atualizacao.
     */
    public static function is_available_file( string $filename ): bool {
        # O caminho eh construida em read_file
        $content = self::read_file( $filename );
        return is_string($content);
    }

    /**
     * Faz o download de um arquivo ZIP de atualizacao do servidor de origem para o diretorio de uploads local.
     *
     * Esta funcao eh otimizada para download de arquivos grandes em ambientes com limitacoes,
     * utilizando `fopen` e `fwrite` para copiar o arquivo em blocos, minimizando o uso de memoria.
     * @return `true` em caso de sucesso, ou `string` com a mensagem de erro em caso de falha
     */
    public static function download_zip( string $zip_name ): string|true {

        $zip_url = ENGINE_URL . '/packages/upgrades/' . $zip_name;

        if( strpos($zip_url, ENGINE_URL) !== 0 ) {
            alert( 'error', 'Tentativa de invasão detectada' );
            exit;
        }

        $dest_file = UPLOAD_DIR . 'packages/upgrades/' . $zip_name;

        if( ! is_dir(dirname($dest_file)) ) {
            mkdir( dirname($dest_file), 0755, true );
        }

        # Tenta abrir o arquivo remoto para leitura como um stream.
        $remote_file = @fopen( $zip_url, 'rb' );
        if( ! $remote_file) {

            return 'Nao foi possivel abrir o arquivo remoto';
        }

        # Tenta criar o arquivo local para escrita como um stream.
        $local_file = @fopen( $dest_file, 'wb' );
        if( ! $local_file ) {
            fclose( $remote_file );

            return 'Nao foi possivel criar o arquivo local';
        }

        # Copia o arquivo em blocos de 8KB (8192 bytes) para economizar memoria
        while( ! feof($remote_file) ) {
            $read_data = fread($remote_file, 8192);
            if( $read_data === false ) {
                fclose( $remote_file );
                fclose( $local_file );

                return 'Erro durante a leitura do arquivo remoto';
            }
            if( fwrite($local_file, $read_data) === false ) {
                fclose( $remote_file );
                fclose( $local_file );

                # Nota: O unlink do $dest_file eh tratado no metodo Upgrade::update_system,

                return 'Erro durante o download';
            }
        }
        fclose( $remote_file );
        fclose( $local_file );

        # valida arquivo apos baixar, se for zip invalido deleta e retorna mensagem
        $fp = fopen($dest_file, 'rb');
        if( $fp === false ) {
            unlink( $dest_file );
            return 'Nao foi possivel validar o arquivo ZIP';
        }
        $signature = fread($fp, 2);
        fclose($fp);
        if( $signature !== 'PK' ) {
            unlink($dest_file);
            return 'Arquivo ZIP invalido';
        }

        return true; # Retorna true em caso de sucesso total.
    }

    /**
     * Forca a atualizacao de arquivo em cache 
     *
     * Apaga o arquivo de cache existente e tenta buscar novamente o conteudo remoto,
     * salvando-o no cache.
     * @example
        if( isset($_GET['refresh']) ) {
            if( http_refresh_cache('versao.json') ) {
                alert('success', 'Cache atualizado com sucesso!');
            } 
            else {
                alert('warning', 'Falha ao atualizar o cache.');
            }
        }
     */
    public static function refresh_cache( string $filename ): bool {

        $remote_file = ENGINE_URL . '/packages/upgrades/' . $filename;
        
        $cache_dir = UPLOAD_DIR . 'packages/upgrades/';

        $tmp_file = $cache_dir . $filename . '.tmp';

        $cached_file = $cache_dir . $filename . '.cache';

        # Forca um novo download do conteudo remoto para um arquivo temporario
        $stream_context = stream_context_create([
            'http' => [
                'method' => "GET",
                'timeout' => 6,
                'follow_location' => true,
                'ignore_errors' => true,
            ]
        ]);

        $content = file_get_contents( $remote_file, false, $stream_context );

        if( $content === false ) {
            $error = error_get_last();
            $reason = isset($error['message']) 
                ? $error['message'] 
                : 'Timeout ou Falha de Conexão';
                
            opus_log("Falha ao buscar conteúdo remoto em: {$remote_file} | Motivo: {$reason}");

            return false;
        }

        # se por acaso ja existir esses mesmos arquivos temporarios writeLock subescreve
        Ensure::writeLock( $tmp_file, $content );

        if( ! file_exists($tmp_file) ) {
            return false;
        }

        $content = file_get_contents($tmp_file) ?: '';

        if( ! self::is_valid_remote_content($filename, $content) ) {
            unlink($tmp_file);
            return false;
        }

        if( rename($tmp_file, $cached_file) === false ) {
            return false;
        }

        return true;
    }

    private static function is_valid_remote_content( string $filename, string $content ): bool {
        $filetype = pathinfo( $filename, PATHINFO_EXTENSION );

        if( $filetype === 'json' ) {

            $json = json_decode($content, true);
            return is_array($json);
        }

        if( $filetype === 'md' ) {

            return stripos($content, '<html') === false;
        }

        if( $filetype === 'zip' ) {

            return str_starts_with($content, 'PK');
        }

        return false;
    }
    // private static function validate_remote_content( string $filename, string $content ): bool
    
}
