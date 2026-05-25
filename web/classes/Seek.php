<?php
declare( strict_types = 1 );
/**
 * Classe de saida do sistema Seek, responsavel por consultar registros de banco de dados
 *   funcionando em conjunto com `SeekPreparer` e `Selection`
 * e fornecer acesso aos campos da linha atual
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Iterator\Seek
 */

class Seek {

    /**
     * @var $seek | Armazena a instancia de SeekPreparer retornada por Selection::resolve
     */
    private static ?SeekPreparer $seek = null;

    private static array $valid_chronos = [
        'created' => true,
        'updated' => true,
        'lastmod' => true,
    ];

    /**
     * Metodo interno para inicializar $seek apenas uma vez.
     * Garante que a instancia de SeekPreparer seja criada somente se ainda nao existir.
     */
    private static function init(): void {
        $selection = Container::call('Selection');
        if( self::$seek === null ) {
            self::$seek = $selection->resolve();
        }
    }


    /**
     * Verifica se ainda existem registros para iterar.
     * Chama init para garantir que $seek foi inicializado.
     */
    public static function row_exists(): bool {
        self::init();

        if( ! self::$seek instanceof SeekPreparer ) {
            return false;
        }
        
        return self::$seek->hasNext();
    }

    /**
     * @deprecated Use Seek::row_exists()
    public static function rows_exists(): bool {
        return self::row_exists();
    } */

   /**
    * Avança para a proxima linha de resultados e atualiza os campos se self::$seek for `SeekPreparer`
    * 1. So funciona em SeekPreparer, que contem multiplas linhas de resultados.
    * 2. O metodo next() de SeekPreparer he void: atualiza self::$seek->row com a linha atual.
    * 3. Apos a execucao self::$seek->row contem a linha atual pronta para uso.
    * 4. Se self::$seek nao for SeekPreparer, nada he feito, evitando erros de chamada de metodo inexistente.
    */
    public static function show_row(): void {
        if( self::$seek instanceof SeekPreparer ) {
            self::$seek->next();
        }
    }
    /**
     * @deprecated Use Seek::show_row()
    public static function show_rows(): void {
        self::show_row();
    } */


    public static function ID(): int {
        self::init();
        return (int) (self::$seek->row['ID'] ?? 0);
    }

    public static function slug(): string {
        self::init();
        return self::$seek->row['slug'] ?? '';
    }

    public static function segment(): string {
        self::init();
        return self::$seek->row['segment'] ?? '';
    }

    public static function title(): string {
        self::init();
        return self::$seek->row['title'] ?? '';
    }

    public static function content(): string {
        self::init();
        return self::$seek->row['content'] ?? '';
    }

    /**
     * Retorna o campo 'summary' do registro de atual
     * A insercao dessa campo fica no update, em um textarea simples abaixo do conteudo
     */
    public static function summary(): string {
        self::init();
        return self::$seek->row['summary'] ?? '';
    }

    public static function author(): string {
        self::init();
        return self::$seek->row['author'] ?? '';
    }



    public static function attachment_data( string $scope = 'larger' ): ?array {

        $attachment = self::attachment();

        $filepath = $attachment->{$scope}->path ?? null;

        if( $filepath === null ) {
            return null;
        }

        $dimensions = Image::dimensions_attrs( $attachment->{$scope} );

        return [
            'path'       => $filepath,
            'URL'        => upload_url($filepath),
            'dimensions' => $dimensions
        ];
    }


    /**
     * A coluna 'attachment' he da tabela 'media' que faz relacionamento com a tabela 'posts'
     * E toda coluna de tabela selecionada fazendo relacao com 'posts' pode ser incluido no "seek loop"
     */
    private static function attachment(): object {
        self::init();
        $json = self::$seek->row['attachment'] ?? '';

        $decoded = json_decode( $json );

        return is_object($decoded) ? $decoded : new stdClass();
    }


    /**
     * 
     * Datas : 
     */

    public static function created( int|string|null $format = null ): string {

        return self::chronos( 'created', $format );
    }


    public static function updated( int|string|null $format = null ): string {

        return self::chronos( 'updated', $format );
    }

    /**
     * Retorna campos de data especificado do registro atual
     */
    private static function chronos( string $column, int|string|null $format = null ): string {

        if( isset(self::$valid_chronos[$column]) === false ) {
            return '';
        }

        self::init();

        $datecol = self::$seek->row[$column] ?? '';

        if( $format !== null ) {
            return chronos_format($datecol, $format);
        }

        $def_date = chronos_setting();

        if( $def_date === 'inblock' ) {
            return chronos_format($datecol, 6);
        }

        return chronos_translate(
            date($def_date, strtotime($datecol))
        );
    }

}