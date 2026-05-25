<?php
/**
 * Registro ativo baseado em arquivo
 * Central para o gerenciamento de Contexto
 *
 * - Gerenciar entradas dinamicas chamadas Contexto/Contexts, que sao blocos de conteudo
 * personalizavel (com suporte a editor rich-text, uploads e embeds)
 * para criar e exibir conteudos dinamicamente na parte publica (template) 
 * chamando a funcao `get_context('name')`
 *
 * @package Core\Repository\Model
 */

class Context {

    private static ?array $cache = null;


    public static function join(): array {

        if( self::$cache !== null ) {
            return self::$cache;
        }

        self::$cache = [];

        foreach( new DirectoryIterator(STORAGE_DIR . 'contexts') as $file ) {

            if( $file->isDot() ) {
                continue;
            }

            if( $file->isFile() && $file->getExtension() === 'php' ) {

                $realpath = $file->getRealPath();

                if( $realpath === false ) {
                    continue;
                }
                $filepath = str_replace( "\\", "/", $realpath );

                $vars = Provider::include_file_vars($filepath);

                # Cada variavel do arquivo vira uma chave em self::$cache,
                # e o valor dessa chave e o proprio array definido no arquivo
                foreach( $vars as $name => $data ) {
                    if( is_array($data) ) {
                        self::$cache[$name] = $data;
                    }
                }
            }
        }

        return self::$cache;
    }


    public static function mapper(): array {
        $keys = ['title', 'section', 'value', 'name', 'basename'];

        $update = URL::GET('update');

        $list = [];

        foreach( self::join() as $data ) {

            if( ! is_array($data) ) {
                continue;
            }

            $bind = new Assign;

            # filtra para retornar apenas o registro (array) com mesmo 'name' da query update
            if( $update && ($data['name'] ?? null) !== $update ) {
                continue;
            }

            foreach( $keys as $key ) {
                if( $key === 'basename' ) {
                    $bind->dynamo['basename'] = $data[$key] ?? null;
                    continue;
                } 

                $bind->$key = $data[$key] ?? null;
            }

            $list[] = $bind;
        }

        return $list;
    }


    public static function sections(): array {

        $sections = [];

        foreach( self::join() as $data ) {

            if( ! is_array($data) ) {
                continue;
            }

            $section  = $data['section'] ?? null;

            if( $section ) {
                $sections[$section] = $section;
            }
        }

        return array_values($sections);
    }


    public static function map_section(): array {
        $is_section = fn($assign) => $assign->dynamo['basename'] === URL::GET('section');
        $filtered   = array_filter( self::mapper(), $is_section );

        return array_values($filtered);
    }

    public static function section_title(): string {
        $group = self::map_section();
    
        if( ! empty($group) ) {
            return $group[0]->section;
        }
    }


    public static function delete( string $name, string $basename ): bool {
        $file = STORAGE_DIR . 'contexts/' . $basename . '.php';

        $vars = Provider::include_file_vars($file);

        if( ! isset($vars[$name]) ) {
            return false;
        }
        
        unset($vars[$name]);

        # deleta o arquivo caso nao encontre variavel de array
        if( empty($vars) ) {
            unlink($file);
            self::clear();
            return true;
        }

        $result = ArrayExport::rewrite($vars, $file);

        if( $result ) {
            self::clear();
        }

        return $result;
    }


    public static function exists( string $var ): bool {
        $vars = self::join();
        # o nome da variavel que armazena o array de contexto eh uma chave de array
        return array_key_exists($var, $vars); 
    }

    private static function vars( string $key ): array {
        $contexts = self::join();
        return $contexts[$key] ?? [];
    }


    public static function value( string $var ): string {
        $context = self::vars($var);
        return $context['value'] ?? '';
    }

    public static function title( string $var ): string {
        $context = self::vars($var);
        return $context['title'] ?? '';
    }

    public static function section( string $var ): string {
        $context = self::vars($var);
        return $context['section'] ?? '';
    }

    public static function basename( string $var ): string {
        $context = self::vars($var);
        return $context['basename'] ?? '';
    }


    public static function filename( string $var ): string {
        $basename = self::basename($var);
        return $basename . '.php';
    }

    public static function filepath( string $var ): string {
        $filename = self::filename($var);
        $path = STORAGE_DIR . 'contexts/';
        $filepath = $path . $filename;

        return $filepath;
    }
    

    public static function show_record(): string {
        $count = Count::contexts();
        switch($count) {
            case 0:
                return "<p>Nenhum Contexto</p>";
            break;
            case 1:
                return "<p>1 Contexto</p>";
            break;
            default:
                return "<p>{$count} Contextos</p>";
            break;
        }
    }


    public static function clear(): void {
        self::$cache = null;
    }

}
