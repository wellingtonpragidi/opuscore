<?php
declare( strict_types = 1 );
/**/
class Context {

    public static function select(): array {
        $keys = ['ID', 'name', 'title', 'value', 'section', 'basename'];

        $query_id = URL::int('id');

        $list = [];

        foreach( get_contexts() as $data ) {

            if( ! is_array($data) ) {
                continue;
            }

            $data_id = (int) ($data['ID'] ?? 0);

            # filtra para retornar apenas o registro (array) com mesmo 'ID' da query id
            if( $query_id && $data_id !== $query_id ) {
                continue;
            }

            $bind = new Assign;

            foreach( $keys as $key ) {

                $bind->context->$key = $data[$key] ?? null;
            }

            $list[] = $bind;
        }

        return $list;
    }



    public function ID(): ?int {
        foreach( self::select() as $show ) {
            $id = (int) $show->context->ID;
        }

        return $id ?? null;
    }

    public function title(): ?string {
        foreach( self::select() as $show ) {
            $title = $show->context->title;
        }

        return $title ?? null;
    }

    # slug do titulo | chave de exibicao
    public function name(): ?string {
        foreach( self::select() as $show ) {
            $name = $show->context->name;
        }

        return $name ?? null;
    }

    # slug da secao | que eh a mesmo que o nome base do arquivo de dados do grupo de contextos
    public function basename(): ?string {
        foreach( self::select() as $show ) {
            $basename = $show->context->basename;
        }

        return $basename ?? null;
    }

    

    public static function section_title(): ?string {
        if( URL::param(0) !== 'section' && ! URL::has('key') ) {
            return null;
        }
        $group = self::select_sections();

        if( empty($group) ) {
            return null;
        }

        return $group[0]->context->section;
    }


    public static function select_sections(): array {
        # para selecionar context por secao em tabela HTML quando query id = 'basename'
        if( URL::has('key') ) {
            $is_section = fn($assign) => $assign->context->basename === URL::GET('key');
            $filtered   = array_filter( self::select(), $is_section );

            return array_values($filtered);
        }

        # para selecionar context em select > options quando em insert
        $sections = [];

        foreach( get_contexts() as $data ) {

            if( ! is_array($data) ) {
                continue;
            }

            $section = $data['section'] ?? null;

            if( $section ) {
                $sections[$section] = $section;
            }
        }

        return array_values( $sections );
    }


    public static function exists( string $name ): bool {
        $context = get_contexts();
        # o nome da variavel que armazena o array de contexto eh uma chave de array
        return array_key_exists( $name, $context );
    }


    /**
     * delete pelo name -- nao pelo ID
     * nao complica ->> deixa assim
     */
    public static function delete( string $name, string $basename ): bool {
        $file = STORAGE_DIR . 'contexts/' . $basename . '.php';

        $context = Provider::include_file_vars($file);

        if( ! isset($context[$name]) ) {
            return false;
        }
        
        unset( $context[$name] );

        # deleta o arquivo caso nao encontre variavel de array
        if( empty($context) ) {
            unlink($file);

            return true;
        }

        return ArrayExport::rewrite( $context, $file );
    }


    public static function show_record(): string {
        $count = count( get_contexts() );

        return match($count) {
            0 => "<p>Nenhum Contexto</p>",

            1 => "<p>1 Contexto</p>",

            default => "<p>{$count} Contextos</p>",
        };
    }



    public static function increment(): int {
        $file = STORAGE_DIR . 'contexts/context-increment.txt';

        $fp = fopen( $file, 'c+' );

        if( ! $fp ) {
            return 0;
        }

        try {

            if( ! flock($fp, LOCK_EX) ) {
                return 0;
            }

            rewind( $fp );

            $ID = (int) trim( stream_get_contents($fp) );

            if( $ID < 1 ) {
                $ID = 1;
            }

            $increment = (int) ($ID + 1);

            ftruncate( $fp, 0 );
            rewind( $fp );

            fwrite( $fp, (string) $increment );

            fflush( $fp );

            return $ID;
        }
        finally {

            flock( $fp, LOCK_UN );
            fclose( $fp );
        }
    }

}