<?php
/**
 * Model de ambos os ambientes
 * 
 * Conexao de instancia com o banco de dados `$this-conn` dessa classe 
 *  eh resolvida pelo container com o metodo setConn() 
 *  - metodos herdeiros precisam instanciar esse dentro da funcao de callback
 * @example : 
 * $container->singleton('Category', function($c) {
 *     $category = new Category;
 *     $category->setConn( $c->make('Connection') );
 * 
 *     return $category;
 * });
 * 
 * Para a conexao de instancia estatica `self::db`, `self::container` e outros 
 *  eh necessario sempre antes instanciar o metodo `self::init()` 
 *  quando precisar dessas propriedades
 *  pois metodos estaticos podem ser executados sem passar pelo `__construct()` 
 *  dando o erro de propriedade nao inicializada
 * @example : 
 * public function delete() {
 *     self::init();
 *     $page = self::$container->make('Page');
 *     self::$db->prepare("DELETE x FROM y WHERE x = ?");
 * }
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause – @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Core\Model
 * @subpackage \Model\Helper
 */

class Model {

    protected PDO $conn;

    protected static PDO $db;

    protected static Container $container;

    private static array $cache = [];


    protected static function init(): void {
        self::$container ??= Container::instance();
        
        self::$db ??= self::$container->make('Connection');
    }

    public function setConn( PDO $conn ): void {
        $this->conn = $conn;
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
     * if( ! parent::hasChanged('articles', ['title', 'content'], $assign) ) {
     *     return false; // Não houve alteração, interrompe o update
     * }
     * 
     * @env Core
     */
    protected function hasChanged( 
        string $table, array $fields, Assign $bind, array $ignore = [] ): bool {

        $ignore = $ignore ?: ['updated', 'lastmod'];

        # remove campos ignorados da comparacao
        $check_fields = array_values( array_diff($fields, $ignore) );

        if( ! $check_fields ) {
            return false;
        }

        $columns = implode(', ', $check_fields);


        $cmd = $this->conn->prepare("SELECT $columns FROM $table WHERE ID = ?");
        $cmd->execute([ $bind->ID ]);

        $current = $cmd->fetch( PDO::FETCH_ASSOC );

        if( ! $current ) {
            return false;
        }

        $normalize = function($v) {
            if( is_string($v) ) {
                return trim($v);
            }
            if( is_bool($v) ) {
                return $v ? '1' : '0';
            }
            if( is_int($v) || is_float($v) ) {
                return (string) $v;
            }
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

    # @env System | @system
    protected function updater( 
        string $table, array $fields, Assign $bind ): bool {

        # Verifica mudanca real
        if( ! $this->hasChanged($table, $fields, $bind) ) {
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

        $cmd = $this->conn->prepare("UPDATE $table SET $set WHERE ID = ?");

        try {
            $this->conn->beginTransaction();

            # Execucao segura
            if( ! $this->executeRetry($cmd, $values) ) {

                $this->conn->rollBack();

                return false;
            }

            # Confirma persistencia real
            if( $this->hasChanged($table, $fields, $bind) ) {

                $this->conn->rollBack();

                return false;
            }

            $this->conn->commit();

            return true;
        } 
        catch( Throwable $e ) {
            if( $this->conn->inTransaction() ) {

                $this->conn->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Executa PDOStatement
     * Tenta novamente em erro 2006
     * Tenta novamente em erro 2013
     */
    private function executeRetry( PDOStatement $cmd, array $values ): bool {
        
        $tries = 2;

        for( $i = 0; $i < $tries; $i++ ) {
            try {

                return $cmd->execute($values);
            } 
            catch( PDOException $e ) {

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



    /**
     * Metodo fetchColumn para PDO::fetchColumn retornando sempre string ou null
     */
    protected static function fetchColumn( 
        PDOStatement $cmd, mixed $fallback = null ): int|bool|string|null {

        $value = $cmd->fetchColumn();
        
        return ($value === false) ? $fallback : $value;
    }



    # @env Output
    protected function sql_set( string ...$columns ): string {
        return implode( ' = ?, ', [...$columns] ) . ' = ?';
    }



    /**
     * 
     * 
     * @method static
     **/

    # @env Output
    protected function sql_into_values( string $columns, int $nc ): string {
        $values = rtrim( str_repeat('?,', $nc), ',' );

        return '(' . $columns . ') VALUES (' . $values . ')';
    }




    /**
     * 
     * 
     * @access public
     **/

    /**
     * Obtem o titulo de uma entidade categoria, article, pagina ... fornecido por: 
     *   1º indice [0] do parametro da URL e:  
     *   2º 'ID' pela query string id da URL
     * 
     * Esse metodo eh chamado na classe Router
     * 
     * 
     * @env System
     */
    // public static function getTitleById(): ?string {
    public static function get_title_by_id(): ?string {
        self::init();

        $is_category = URL::param(1) === 'category';

        if( ! in_array(URL::param(0), ['admins', 'menus', 'pages', 'articles', 'users']) ) {
            return null;
        }
        
        $table  = is_category() ? 'categories' : URL::param(0);
        $column = is_article() || is_page() ? 'title' : 'name';
        
        $cmd = self::$db->prepare("SELECT $column FROM $table WHERE ID = ? LIMIT 1");
        $cmd->execute([ URL::int('id') ]);

        return self::fetchColumn($cmd);
    }

}