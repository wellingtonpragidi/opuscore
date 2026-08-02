<?php
class Context {

    public static function exists( string $var ): bool {
        $vars = self::datafiles();
        # o nome da variavel que armazena o array de contexto eh uma chave de array
        return array_key_exists( $var, $vars ); 
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


    private static function vars( string $key ): array {
        $contexts = self::datafiles();

        return $contexts[$key] ?? [];
    }


    private static function datafiles(): array {
        require dist_annex('context.php');

        return $context;
    }

}