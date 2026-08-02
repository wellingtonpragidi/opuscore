<?php
/**
 * gerenciamento de instancias (Container de Inversao de Controle - IoC).
 *
 * Esta classe atua como um simples e funcional container de servicos, 
 * responsavel por gerenciar e resolver dependencias de forma flexivel e desacoplada
 * 
 * a classe Container implementa o padrao instancia unica para ele mesma durante toda a requisicao
 *  
 * A classe permite registrar:
 * - 'singleton' (que criam uma unica instancia cacheada) 
 * - 'binding'   (que sempre criam novas instancias)
 * - 'factory'   (forcando criacao de nova instancia, ignora cache de singletons)
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Core\Instance
 */

class Container {

    # instancia unica para o proprio Container
    private static ?self $instance = null;


    /**
     * Mapeamento de 'bindings'.
     * Armazena funcoes de criacao callbacks que retornam nova instancia a cada requisicao
     * @var callable[] array associativo onde a chave, nome e valor do binding e uma funcao
     */
    private array $bindings = [];

    /**
     * Mapeamento de singletons
     * Armazena funcoes que devem ter uma unica instancia em toda a requisicao
     * @var callable[] array associativo onde a chave, nome e valor do singleton e uma funcao
     */
    private array $singletons = [];



    private array $makeables = [];

    /**
     * Construtor privado
     * Impede a criacao direta de instancias do Container de fora da classe,
     * garantindo a aplicacao do padrao singleton para o proprio container
     */
    private function __construct() {}

    /**
     * Obtem a instancia unica do container.
     *
     * Implementa o padrao "singleton" para garantir que haja apenas uma instancia
     * do Container em toda a requisicao. 
     * Se a instancia ainda nao existir, ela e criada, do contrario reutiliza a instancia em cache
     */
    public static function instance(): self {
        if( self::$instance === null ) {

            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * **Forca** a criacao de uma **nova instancia** de um servico, ignorando o cache de singletons
     *
     * Este metodo e util quando voce precisa de uma nova instancia que normalmente e um singleton
     * 
     * Funcionara da mesma maneira caso a classe estiver registrada como um binding
     * Mas se solber que a classe e registrada usando binding, eh desnecessario usar factory,
     *  jah que binding nunca retorna a mesma instancia
     */
    public function factory( string $name ): mixed {
        # Se existir no singleton, cria nova instancia (ignora o cache estatico interno de 'make').
        if( isset($this->singletons[$name]) ) {

            return $this->singletons[$name]( $this );
        }

        # Se existir no binding, cria nova instancia.
        if( isset($this->bindings[$name]) ) {
            
            return $this->bindings[$name]( $this );
        }

        # Lanca excecao se o servico nao for encontrado.
        throw new OpusException(
            "Servico <code class=\"class-name\">'{$name}'</code> nao registrado no container.", 
            "error"
        );
    }



    /**
     * Para registra uma 'binding' d/no container
     *
     * Um binding e uma definicao de como resolver uma dependencia 
     * Cada vez que esta binding for solicitada, uma **nova instancia**
     * do servico sera criada e retornada 
     */
    public function bind( string $name, callable $callback ): void {

        $this->bindings[$name] = $callback;
    }



    public function exists( string $name ): bool {
        if( isset($this->singletons[$name]) ) {
            
            return true;
        }

        if( isset($this->bindings[$name]) ) {

            return true;
        }

        return false;
    }


    /**
     * Registra uma 'singleton' no container
     *  definicao de um servico que deve ter apenas uma instancia em toda a requisicao
     * 
     * A instancia e criada apenas na primeira vez que e solicitada
     * e, em chamadas subsequentes, a mesma instancia cacheada e reutilizada
     *
     * @param :
     * $name | string identificadora da classe na instancia, podendo ser o proprio nome da classe
     * $callback | funcao anonima (closure) que contem a logica para criar a instancia do servico
     */
    public function singleton( string $name, callable $callback ): void {

        $this->singletons[$name] = $callback;
    }


    /**
     * Obtem uma instancia de um servico do container.
     *
     * Se o servico for um singleton, a mesma instancia cacheada sera retornada.
     * Se for um binding, uma nova instancia sera criada a cada chamada.
     * Este e o metodo principal para resolver dependencias.
     *
     * @param $name | O nome (binding ou singleton) do servico a ser resolvido
     * 
     * @param $params | permite passar parametros adicionais a closure registrada
     * @example : 
     * $container->singleton('Template', function($c, string $slug) {
     *     return new Template($slug);
     * });
     * Essa closure espera dois parametros:
     * $c    = referencia ao container (pra resolver dependencias, se precisar)
     * $slug = valor que sera passado na chamada
     * A chamada:
     * $temp = $container->make('Template', [$slug]);
     * 
     * Quando ->make() eh chamado, ele executa a closure +- assim:
     * return $closure($this, ...$params);
     * Entao [$slug] vira $slug dentro da funcao, e $this vira $c
     * Ou seja, make() recebe um array, mas a closure recebe os elementos desse array como parametros individuais
     */
    public function make( string $name, array $params = [] ): object {
        # Singleton
        if( isset($this->singletons[$name]) ) {

            if( ! isset($this->makeables[$name]) ) {

                $this->makeables[$name] = $this->singletons[$name]($this, ...$params);
            }

            return $this->makeables[$name];
        }

        # Binding (normal)
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
     */
    public static function call( string $name, $params = [] ) {

        return self::instance()->make( $name, $params );
    }

    public static function scope(): array {
        $container = self::instance();

        $scopes = [
            'conn'     => $container->make('Connection'),
            'auth'     => $container->make('Auth'),
            'category' => $container->make('Category'),
            'comment'  => $container->make('Comment'),
            'image'    => $container->make('Image'),
            'page'     => $container->make('Page'),
            'article'     => $container->make('Article'),
            'router'   => $container->make('Router'),
            'user'     => $container->make('User'),
        ];

        if( IS_DASHBOARD ) {
            $scopes += [
                'admin'    => $container->make('Admin'),
                'context'  => $container->make('Context'),
                'media'    => $container->make('Media'),
                'relation' => $container->make('Relations'),
            ];
        }

        if( IS_WEB ) {
            $scopes += [
                'access' => $container->make('Access'),
            ];
        }

        return $scopes;
    }

    public static function commentscope(): array {
        $container = self::instance();

        return [
            'auth'     => $container->make('Auth'),
            'comment'  => $container->make('Comment'),
            'image'    => $container->make('Image'),
            'article'     => $container->make('Article'),
            'user'     => $container->make('User'),
        ];
    }

}