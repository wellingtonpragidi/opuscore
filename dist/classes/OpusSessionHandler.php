<?php
class OpusSessionHandler implements SessionHandlerInterface {

    private string $savepath;

    private int $fragments = 2; # quantos caracteres usar para subdiretorios

    public function __construct() {
        $parts = explode( '/', DIR );

        if( IS_LOCAL ) {
            # Pega o primeiro diretorio servidor local, apos "C:/" Windows, "/" Linux e Mac
            # /wamp/ ou /wamp64/ Windows, /var/ Linux, /Applications/ Mac e /htdocs/ xamp (ambos)
            $userDIR = $parts[0] . '/' . $parts[1];
        }
        else {
            # Pega diretorio apos `/home/` (geralmente eh esse nome)
            $userDIR = $parts[0] . '/' . $parts[1] . '/' . $parts[2];
        }

        $this->savepath = $userDIR . '/session-storage';

        if( ! is_dir($this->savepath) ) {
            # 0700 = so o usuario do PHP tem acesso
            mkdir( $this->savepath, 0700, true );
        }

        if( ! is_writable($this->savepath) ) {
            throw new OpusException('O diretório de armazenamento da sessão não é gravável', 'warn');
        }
    }

    # expoe o savepath para debug/config
    public function getSavePath(): string {
        return $this->savepath;
    }


    public function open( string $savepath, string $sessioname ): bool {
        return true;
    }


    public function close(): bool {
        return true;
    }


    public function read( string $id ): string {
        $file = $this->path_for_id($id);
        # opcional: log temporario para debug
        # error_log("[OpusSessionHandler] read: {$file}");
        if( ! file_exists($file) ) {
            return '';
        }
        $data = @file_get_contents( $file );

        return $data === false ? '' : $data;
    }


    public function write( string $id, string $data ): bool {
        $file = $this->path_for_id($id);
        # error_log("[OpusSessionHandler] write: {$file} len=" . strlen($data));
        $content = Ensure::writeLock( $file, $data, Ensure::FILE_HANDLING_LOCK );
        @chmod( $file, 0600 );

        return $content !== false;
    }


    public function destroy( string $id ): bool {
        $file = $this->path_for_id($id);
        if( file_exists($file) ) {
            @unlink($file);
        }

        return true;
    }


    // PHP GC entrypoint: percorre recursivamente e remove arquivos expirados
    public function gc( int $maxlifetime ): int|false {
        return $this->clean_expired($maxlifetime);
    }


    /**
     * Limpa sessoes expiradas recursivamente no savepath
     * Pode ser chamada via CLI ou agendador. Retorna o numero de arquivos removidos
     *
     * @param $maxlifetime Se null, usa ini_get('session.gc_maxlifetime') ou constante SESSION_LIFETIME
     */
    public function clean_expired( ?int $maxlifetime = null ): int {
        if( $maxlifetime === null ) {
            $maxlifetime = (int) ini_get('session.gc_maxlifetime');
            if( $maxlifetime <= 0 && defined('SESSION_LIFETIME') ) {
                $maxlifetime = (int) SESSION_LIFETIME;
            }
        }

        $now = time();
        $count = 0;

        # RecursiveDirectoryIterator para suportar sharding
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $this->savepath, FilesystemIterator::SKIP_DOTS ),
                RecursiveIteratorIterator::CHILD_FIRST
            );
        } 
        catch( UnexpectedValueException $e ) {
            # diretorio inacessivel ou inexistente
            return 0;
        }

        foreach( $iterator as $fileinfo ) {
            if( ! $fileinfo->isFile() ) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            if( strpos($filename, 'session_') !== 0 ) {
                continue;
            }
            $path = str_replace( '\\', '/', $fileinfo->getPathname() );
            $mtime = $fileinfo->getMTime();
            if( $mtime + $maxlifetime < $now ) {
                if( @unlink($path) ) {
                    $count++;
                }
            }
        }

        return $count;
    }


    # normaliza o id e gera o caminho final (com subdiretorio)
    private function path_for_id(string $id): string {
        # torna o id seguro para filename
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $id);

        $sub = ($this->fragments > 0) ? substr($safe, 0, $this->fragments) : '';
        $dir = '';

        if( $sub === '' ) {
            $dir = $this->savepath;
        } 
        else {
            $dir = $this->savepath . '/' . $sub;
        }
        if( ! is_dir($dir) ) {
            @mkdir( $dir, 0700, true );
        }

        return $dir . '/' . "session_{$safe}";
    }
}
/*
class OpusSessionHandler implements SessionHandlerInterface {
    
    private $savepath;

    public function __construct() {
        $parts = explode( '/', DIR );

        if( IS_LOCAL ) {
            # Pega o primeiro diretorio servidor local, apos "C:/" Windows "/" Linux e Mac
            # /wamp/ ou /wamp64/ Windows, /var/ Linux, /Applications/ Mac e xamp para os tres
            $userDIR = $parts[0] . '/' . $parts[1];
        }
        else {
            # Pega o primeiro diretorio que geralmente se chama `/home/`
            $userDIR = $parts[0] . '/' . $parts[1] . '/' . $parts[2];
        }

        $this->savepath = $userDIR . '/session-storage';

        if( ! is_dir($this->savepath) ) {
            # 0700 = so o usuario do PHP tem acesso
            mkdir( $this->savepath, 0700, true );
        }

        if( ! is_writable($this->savepath) ) {
            throw new OpusException('O diretório de armazenamento da sessão não é gravável', 'warn');
        }

    }


    public function getSavePath(): string {
        return $this->savepath;
    }


    public function open( string $savepath, string $sessioname ): bool {
        return true;
    }


    public function close(): bool {
        return true;
    }


    public function read( string $id ): string {
        $file = "{$this->savepath}/session_{$id}";

        if( ! file_exists($file) ) {
            return '';
        }

        $data = @file_get_contents( $file );

        if( $data === false ) {
            return '';
        }

        return $data === false ? '' : $data;
    }


    public function write( string $id, string $data ): bool {
        $file = "{$this->savepath}/session_{$id}";
        
        $content = Ensure::writeLock( $file, $data, Ensure::FILE_HANDLING_LOCK );

        # permissoes do arquivo: 0600 = escrita e leitura para o proprietario, nada mais para ninguem
        @chmod( $file, 0600 );

        return $content !== false;
    }


    public function destroy( string $id ): bool {
        $file = "{$this->savepath}/session_{$id}";
        if( file_exists($file) ) {
            unlink($file);
        }

        return true;
    }


    public function gc( int $lifetime ): int|false {
        $count = 0;
        foreach( glob("{$this->savepath}/session_*") as $file ) {
            error_log('[gc parametro $lifetime] valor: '.$lifetime);
            if( filemtime($file) + $lifetime < time() ) {
                if( unlink($file) ) {
                    $count++;
                }
            }
        }

        return $count;
    }


        $dir = 'C:/wamp/session-storage';
        $max = 2628000;
        $now = time();
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $file) {
            if ($file->isFile() && str_starts_with($file->getFilename(), 'session_')) {
                if ($file->getMTime() + $max < $now) {
                    @unlink($file->getPathname());
                }
            }
        }

}
*/