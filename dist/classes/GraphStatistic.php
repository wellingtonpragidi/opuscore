<?php
/**
 * Manipula e apresenta dados de estatisticas de visualizacao simples do sistema.
 * Registra acessos unicos diarios a paginas e fornece metodos para consultar e formatar
 * dados para analises em exibicao grafica
 * 
 * @uses get_client_ip() Funcao auxiliar para obter o IP do cliente.
 * @uses date_translate() Funcao auxiliar para traduzir datas.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package Core/Model
 * @subpackage Traffic
 */
class GraphStatistic {

    private PDO $conn;


    private static ?self $instance = null;

    /**
     * Construtor da classe GraphStatistic.
     *
     * Inicializa a conexao com o banco de dados.
     */
    public function __construct() {
        $this->conn = Container::call('Connection');
    }

    # instancia unica
    public static function instance(): self {
        if( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Insere uma nova visualizacao na tabela 'statistics'.
     *
     * Evita duplicatas para a mesma pagina, IP e data.
     *
     * @param string $title O titulo da pagina visualizada.
     * @return int O numero de registros inseridos (0 ou 1). Retorna 0 se o registro ja existir.
     */
    public function insert( string $title ): int {
        // Converte a URL atual para um GUID, removendo o protocolo.
        $URL = str_replace( URL::scheme(), "", URL::current() );
        $clientIp = get_client_ip(); // Assume que esta funcao retorna uma string.
        $currentDate = date('Y-m-d');

        // Verifica se ja existe um registro com o mesmo GUID, IP e data para evitar duplicatas.
        $sql = $this->conn->prepare("
            SELECT URL, created, IP FROM statistics
            WHERE URL = :URL AND IP = :ip AND created = :created
        ");
        $sql->execute([
            ':URL' => $URL,
            ':ip'   => $clientIp,
            ':created' => $currentDate
        ]);

        // Se nenhum registro for encontrado, insere a nova visualizacao.
        if( $sql->rowCount() === 0 ) {
            $sql = $this->conn->prepare("
                INSERT INTO statistics(title, URL, created, period, IP) VALUES(:title, :URL, :created, :period, :ip)
            ");
            $sql->execute([
                ':title' => $title,
                ':URL'  => $URL,
                ':created'  => $currentDate,
                ':period'  => date('H:i:s'), // Hora atual da visualizacao.
                ':ip'    => $clientIp
            ]);

            return $sql->rowCount(); // Retorna 1 se inserido com sucesso, 0 caso contrario.
        }

        return 0; // Retorna 0 se o registro ja existia e nao foi inserido.
    }

    /**
     * Retorna uma lista paginada de entradas de estatisticas
     */
    public function select(): array {
        $pagination = new Pagination( Count::statistics(), per_page('statistics') );

        $sql = $this->conn->prepare("
            SELECT * FROM statistics ORDER BY ID DESC LIMIT :offset, :limit
        ");
        
        $offset = (int) $pagination->offset();
        $limit  = (int) per_page('statistics');

        $sql->bindParam( ':offset', $offset, PDO::PARAM_INT );
        $sql->bindParam( ':limit', $limit, PDO::PARAM_INT );
        $sql->execute();

        while( $row = $sql->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign();
            $bind->title = $row['title'];
            $bind->URL  = $row['URL'];
            $bind->date  = $row['created'];
            $bind->time  = $row['period'];
            $bind->IP    = $row['IP'];
            $list[] = $bind;
        }

        return $list ?? [];
    }

    /**
     * Exibe a data modificada, formatada com o nome do dia da semana (traduzido).
     * @param $modify | Uma string de modificacao de data (ex: '-1 day', '+0 day')
     */
    public function dates( string $modify ): void {
        $datetime = new DateTime( date('Y-m-d') );
        $datetime->modify( $modify );

        echo '"'. chronos_translate( $datetime->format('d/m \- l') ) .'", ';
    }

    /**
     * Conta e exibe o numero de acessos ocorridos em uma data especifica
     */
    public function views( string $modify ): void {
        # Calcula a data alvo aplicando a modificacao.
        $target_date = ( new DateTime(date('Y-m-d')) )->modify($modify)->format('Y-m-d');

        # Conta registros para a data alvo.
        $cmd = $this->conn->prepare("SELECT COUNT(*) FROM statistics WHERE created = :created");
        $cmd->execute([ ':created' => $target_date ]);

        echo $cmd->fetchColumn() . ', '; # Exibe a contagem e uma virgula para formatacao de lista
    }
}