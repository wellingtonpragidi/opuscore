<?php
static $context = null;

if( $context !== null ) {
    return $context;
}

$context = [];

foreach( new DirectoryIterator(STORAGE_DIR . 'contexts') as $file ) {

    if( $file->isDot() || $file->isFile() === false || $file->getExtension() !== 'php' ) {
        continue;
    }

    $realpath = $file->getRealPath();

    if( $realpath === false ) {
        continue;
    }
    $filepath = str_replace( "\\", "/", $realpath );

    $vars = Provider::include_file_vars($filepath);

    # Cada variavel do arquivo vira uma chave em $context,
    # e o valor dessa chave e o proprio array definido no arquivo
    foreach( $vars as $name => $data ) {
        if( is_array($data) ) {

            $context[$name] = $data;
        }
    }
}

return $context;