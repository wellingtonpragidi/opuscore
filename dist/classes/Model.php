<?php
/**
 * Model de ambos os ambientes
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Core\Model
 * @subpackage \Helper
 */

class Model {

    /**
     * @environment Core
     */
    protected PDO $conn;

    /**
     * @environment Core
     */
    protected static ?PDO $db = null;

    /**
     * @environment Core
     */
    private static array $cache = [];

    /**
     * @environment System
     */
    private static bool $is_category = false;


    # construtor local Model
    public function __construct() {

        self::$is_category = URL::param(1) === 'category';
    }


    /**
     * @environment Core
     */
    public function setConnection( PDO $conn ): void {
        $this->conn = $conn;
    }


    /**
     * @environment Core
     */
    protected static function init(): void {
        if( self::$db === null ) {
            self::$db = Container::call('Connection');
        }
    }


    /**
     * Valida se houve alteração real nos campos informados comparando com o banco
     * Substitui o rowCount() verificando se houve alteracao nos dados antes de persistir
     * @param $table Nome da tabela de entidade a ser atualizada
     * @param $fields Lista de colunas para conferencia
     * @param $bind Objeto contendo ID e os novos valores
     * @return Verdadeiro se houver ao menos uma diferenca detectada
     * 
     * @example
     * if( ! parent::hasChanged('posts', ['title', 'content'], $assign) ) {
     *     return false; // Não houve alteração, interrompe o update
     * }
     * 
     * @environment System
     */
    protected static function hasChanged( 
        string $table, array $fields, Assign $bind, array $ignore = ['updated', 'lastmod'] ): bool {

        self::init();

        # remove campos ignorados da comparacao
        $check_fields = array_values( array_diff($fields, $ignore) );

        if( ! $check_fields ) {
            return false;
        }

        $columns = implode(', ', $check_fields);


        $cmd = self::$db->prepare("SELECT $columns FROM $table WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        $current = $cmd->fetch( PDO::FETCH_ASSOC );

        if( ! $current ) {
            return false;
        }

        $normalize = function($v) {
            if( is_string($v) ) return trim($v);
            if( is_bool($v) ) return $v ? '1' : '0';
            if( is_int($v) || is_float($v) ) return (string) $v;
            return $v;
        };
        
        foreach( $check_fields as $field ) {
            $old = $normalize( $current[$field] ?? null );
            $new = $normalize( $bind->{$field} ?? null );

            if( $old !== $new ) { 
                # achou diferenca - verdadeiro para linhas afetadas
                return true; 
            }
        }

        return false;
    }


    protected static function execute( PDOStatement $cmd, array $values, int $tries = 2 ): bool {

        for( $i = 0; $i < $tries; $i++ ) {
            try {

                return $cmd->execute($values);
            } 
            catch (PDOException $e) {

                $code = $e->getCode();

                # erros classicos de conexao
                if( $i < $tries - 1 && in_array($code, ['2006', '2013']) ) {
                    usleep(100000); # 100ms
                    continue;
                }

                throw $e;
            }
        }

        return false;
    }


    protected static function updater( 
        string $table, array $fields, Assign $bind ): bool {

        self::init();

        # Verifica mudanca real
        if( ! self::hasChanged($table, $fields, $bind) ) {
            return false;
        }

        # Monta SET dinamico seguro
        $set = implode( ', ', array_map( fn($col) => "{$col} = ?", $fields ) );

        $values = [];
        foreach( $fields as $field ) {
            $values[] = $bind->{$field};
        }

        # WHERE
        $values[] = $bind->ID;

        $cmd = self::$db->prepare("UPDATE $table SET $set WHERE ID = ?");

        try {
            self::$db->beginTransaction();

            # Execucao segura
            if( ! self::execute($cmd, $values) ) {

                self::$db->rollBack();

                return false;
            }

            # Confirma persistencia real
            if( self::hasChanged($table, $fields, $bind) ) {

                self::$db->rollBack();

                return false;
            }

            self::$db->commit();

            return true;
        } 
        catch( Throwable $e ) {
            if( self::$db->inTransaction() ) {

                self::$db->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Metodo fetchColumn para PDO::fetchColumn retornando sempre string ou null
     */
    protected static function fetchColumn( PDOStatement $cmd ): ?string {
        $result = $cmd->fetchColumn();
        return ($result === false) ? null : $result;
    }



    /**
     * Obtem o titulo de uma entidade categoria, post, pagina ... fornecido por: 
     *   1º indice [0] do parametro da URL e:  
     *   2º 'ID' pela query string id da URL
     * 
     * Esse metodo eh chamado na classe Router
     * 
     * 
     * @environment System
     */
    public static function get_title_by_id(): string {
        self::init();
        if( ! is_post_category() && ! is_page_update() && ! is_post_update() ) {
            return '';
        }
        $table  = self::$is_category ? 'categories' : URL::param(0);
        $column = self::$is_category ? 'name' : 'title';
        
        $cmd = self::$db->prepare("SELECT $column FROM $table WHERE ID = ? LIMIT 1");
        $cmd->execute([ URL::int('id') ]);

        return $cmd->fetchColumn() ?: '';
    }

}