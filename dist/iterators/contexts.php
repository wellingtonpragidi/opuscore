<?php
function get_contexts() {

    static $contexts = null;

    if( $contexts !== null ) {
        return $contexts;
    }

    $contexts = [];

    try {
        foreach( new DirectoryIterator(STORAGE_DIR . 'contexts') as $file ) {

            if( $file->isDot() || $file->getExtension() !== 'php' ) {
                continue;
            }

            $realpath = $file->getRealPath();

            if( $realpath === false ) {
                continue;
            }

            $filepath = str_replace( '\\', '/', $realpath );

            $vars = Provider::include_file_vars($filepath);

            # Cada variavel dentro dos arquivos sao uma chave com o array de um `context`
            foreach( $vars as $key => $value ) {

                if( is_array($value) ) {

                    $contexts[$key] = $value;
                }
            }
        }
    } 
    catch( UnexpectedValueException $e ) {
        exception('
            Diretório <code>contexts</code> para caminho fornecido não encontrado ou sem permissão.'
        );
    }
    
    return $contexts;
}  
/**
 * $contexts : 
 * retorna todos os arrays de contextos adicionados
 * 
 * $contexts[$key] $key :
 * retorna o array do contexto passado pelo argumento $name
 */

/**
 * $contexts[$name]['value'] : 
 * retorna o conteudo do array passado pelo argumento $name
 */

if( defined('IS_WEB') && IS_WEB ) {
    function context( string $name ): void {
        $context = get_contexts();

        echo $context[$name]['value'] ?? '';
    }


    function context_title( string $name ): void {
        $context = get_contexts();

        echo $context[$name]['title'] ?? '';
    }


    function has_context( string $name ): bool {
        $context = get_contexts();

        # o nome da variavel que armazena o array de contextos eh o mesmo que o valor da chave 'name' do array de um contexto
        return array_key_exists( $name, $context ); 
    }


    function context_section( string $name ): string {
        $context = get_contexts();

        return $context[$name]['section'] ?? '';
    }

    function context_basename( string $name ): string {
        $context = get_contexts();

        return $context[$name]['basename'] ?? '';
    }

    function context_filename( string $name ): string {
        $basename = context_basename($name);

        return $basename . '.php';
    }

    function context_filepath( string $name ): string {
        $filename = context_filename($name);
        $path = STORAGE_DIR . 'contexts/';

        return $path . $filename;
    }
}