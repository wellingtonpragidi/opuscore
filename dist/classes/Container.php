<?php
/**
 * Mecanismo de gerenciamento de instancias (Container de Inversao de Controle - IoC).
 *
 * Esta classe atua como um container de servicos, responsavel por gerenciar e resolver dependencias de 
 * forma automatica, flexivel e desacoplada.
 * Implementa o padrao Singleton para garantir instancia unica do proprio Container em toda a aplicacao. 
 * Ele permite registrar:
 * - 'singletons' (que criam uma unica instancia cacheada) 
 * - 'bindings' (que sempre criam novas instancias)
 * - 'factories' (forcando criacao de nova instancia, ignora cache de singletons)
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Core
 * @uses OpusException Para lancar excecoes em caso de servicos nao registrados.
 */

class Container {

    /**
     * Instancia unica do container (implementacao do padrao Singleton).
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Mapeamento de 'bindings'.
     *
     * Armazena funcoes de criacao (resolvers) para servicos que sempre devem
     * retornar uma **nova instancia** a cada requisicao.
     *
     * @var callable[] Um array associativo onde a chave e o nome do binding e o valor e uma funcao callable.
     */
    private $bindings = [];

    /**
     * Mapeamento de 'singletons'.
     *
     * Armazena funcoes de criacao (resolvers) para servicos que devem ter
     * uma **unica instancia** em toda a vida util da aplicacao, sendo
     * cacheada apos a primeira criacao.
     *
     * @var callable[] Um array associativo onde a chave e o nome do singleton e o valor e uma funcao callable.
     */
    private $singletons = [];

    /**
     * Construtor privado.
     *
     * Impede a criacao direta de instancias do Container de fora da classe,
     * garantindo a aplicacao do padrao Singleton para o proprio container.
     */
    private function __construct() {}

    /**
     * Obtem a instancia unica do container.
     *
     * Implementa o padrao Singleton para garantir que haja apenas uma instancia
     * do Container em toda a aplicacao. Se a instancia ainda nao existir, ela
     * e criada. Caso contrario, a instancia existente e retornada.
     */
    public static function instance(): self {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registra uma 'binding' (ligacao) no container.
     *
     * Um binding e uma definicao de como resolver uma dependencia.
     * Cada vez que esta binding for solicitada, uma **nova instancia**
     * do servico sera criada e retornada.
     *
     * @param string $name O nome unico (geralmente o nome da classe ou uma string identificadora) da binding.
     * @param callable $resolver A funcao anonima (closure) que contem a logica para criar a instancia do servico.
     * Ela recebe a propria instancia do container como argumento, permitindo resolver outras dependencias internas.
     * @return void
     */
    public function bind( string $name, callable $resolver ): void {
        $this->bindings[$name] = $resolver;
    }

    /**
     * Registra uma 'singleton' no container.
     *
     * Um singleton e uma definicao de um servico que deve ter **apenas uma instancia**
     * em toda a aplicacao. A instancia e criada apenas na primeira vez que e solicitada
     * e, em chamadas subsequentes, a mesma instancia cacheada e reutilizada.
     *
     * @param string $name O nome unico (geralmente o nome da classe ou uma string identificadora) do singleton.
     * @param callable $resolver A funcao anonima (closure) que contem a logica para criar a instancia do servico.
     * Ela recebe a propria instancia do container como argumento, permitindo resolver outras dependencias internas.
     * @return void
     */
    public function singleton( string $name, callable $resolver ): void {
        $this->singletons[$name] = $resolver;
    }

    /**
     * Obtem uma instancia de um servico do container.
     *
     * Se o servico for um singleton, a mesma instancia cacheada sera retornada.
     * Se for um binding, uma nova instancia sera criada a cada chamada.
     * Este e o metodo principal para resolver dependencias.
     *
     * @param string $name O nome (binding ou singleton) do servico a ser resolvido.
     * @throws OpusException Se o nome do servico nao estiver registrado no container lancar erro.
     * @changed 2.5.0 Adicionado segundo parametro opcional array $params 
     */
    public function make( string $name, array $params = [] ): object {
        static $instances = [];

        # Singleton
        if( isset($this->singletons[$name]) ) {
            if( ! isset($instances[$name]) ) {
                $instances[$name] = $this->singletons[$name]($this, ...$params);
            }
            return $instances[$name];
        }

        # Binding normal
        if( isset($this->bindings[$name]) ) {
            return $this->bindings[$name]($this, ...$params);
        }

        throw new OpusException("
            A classe <code class=\"class-name\">{$name}</code> não esta registrado no container.
        ", "error");
    }

    /**
     * Metodo estatico auxiliar para obter instancias de servicos rapidamente.
     *
     * Este metodo e um atalho conveniente para `Container::instance()->make($name)`,
     * permitindo o acesso facilitado aos servicos registrados no container.
     *
     * @param string $name O nome do servico a ser resolvido.
     * @return mixed A instancia do servico solicitado.
     * @uses Container::instance Para obter a instancia unica do container.
     * @uses Container::make Para resolver e obter a instancia do servico.
     */
    public static function call( string $name, $params = [] ) {
        return Container::instance()->make( $name, $params );
    }


    /**
     * Forca a criacao de uma **nova instancia** de um servico, ignorando o cache de singletons.
     *
     * Este metodo e util quando voce precisa de uma nova instancia de um servico
     * que normalmente e um singleton. Se o nome for de um binding, funcionara
     * como 'make', retornando sempre uma nova instancia.
     *
     * @param string $name O nome (binding ou singleton) do servico a ser instanciado.
     * @return mixed A nova instancia do servico solicitado.
     * @throws OpusException Se o nome do servico nao estiver registrado no container.
     * @uses OpusException Para lancar erro caso o servico nao seja encontrado.
     */
    public function factory( string $name ) {
        # Se existir no singleton, cria nova instancia (ignora o cache estatico interno de 'make').
        if( isset($this->singletons[$name]) ) {
            return $this->singletons[$name]( $this );
        }

        # Se existir no binding, cria nova instancia.
        if( isset($this->bindings[$name]) ) {
            return $this->bindings[$name]( $this );
        }

        # Lanca excecao se o servico nao for encontrado.
        throw new OpusException("
            Servico <code class=\"class-name\">'{$name}'</code> nao registrado no container.
        ", "error");
    }

}