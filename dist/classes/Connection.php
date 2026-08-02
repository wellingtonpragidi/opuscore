<?php
/**
 * Gerencia a conexao unica com o banco de dados
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Core\DAO
 * @subpackage \Single
 */

class Connection {

    private PDO $conn;

    /**
     * DSN (Data Source Name) para a conexao PDO.
     *
     * Contem as informacoes essenciais para conectar ao banco de dados,
     * como tipo de banco, host, nome do banco e charset.
     */
    private string $db_dsn;

    /**
     * Opcoes de configuracao para a conexao PDO.
     *
     * Inclui definicoes como o modo de emulacao de prepared statements,
     * o modo de tratamento de erros (lancando excecoes) e o modo padrao de fetch.
     */
    private array $db_options;

    /**
     * Construtor da classe Connection.
     *
     * Inicializa o DSN e as opcoes da conexao PDO utilizando constantes globais
     * Em seguida tenta estabelecer a conexao chamando o metodo `connect()`.
     */
    public function __construct() {

        $this->db_dsn  = 'mysql:dbname='. DB_NAME .'; host='. DB_HOST .'; charset=utf8mb4';


        $this->db_options = [
            # Desativa emulacao de prepared statements
            PDO::ATTR_EMULATE_PREPARES => false, 

            # Define para lancar excecoes em caso de erro
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 

            # Define o modo de fetch padrao para array associativo
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 

            # garantir que modos estejam habilitados
            PDO::MYSQL_ATTR_INIT_COMMAND => "
                SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE'
            "
        ];

        $this->connect();
    }

    /**
     * Estabelece a conexao com o banco de dados utilizando PDO.
     *
     * Este metodo tenta criar uma nova instancia PDO com as configuracoes
     * definidas no construtor. Em caso de falha na conexao (PDOException),
     * registra o erro e encerra a execucao do script com uma mensagem.
     */
    private function connect(): void {
        try {

            $this->conn = new PDO( $this->db_dsn, DB_USER, DB_PSWD, $this->db_options );

        }
        catch( PDOException $e ) {

            error_log( "Falha na comunicacao com o banco de dados: ( {$e->getMessage()} )" );

            $errorMessage = DISPLAY_ERRORS ? "( {$e->getMessage()} )" : '';
            throw new OpusException( 
                "<b>Falha na comunicação com o banco de dados: </b>{$errorMessage}", 
                'error', 503
            );
        }
    }

    /**
     * Retorna a instancia da conexao PDO ativa.
     *
     * Este metodo permite que outras partes do sistema obtenham o objeto PDO
     * para realizar operacoes de banco de dados (prepared statements, queries, etc.)
     */
    public function database(): PDO {

        return $this->conn;
    }

}