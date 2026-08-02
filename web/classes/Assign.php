<?php
/**
 * Objeto de transporte de dados do sistema.
 * 
 * Utilizado muito entre Model e Controller, 
 * evitando passagem de multiplos parametros e mantendo fluxo padronizado de dados.
 * 
 * Esta classe nao atua bem como um DTO, eh mais como objeto de ligacao
 * 
 * Usando essa classe, cada retorno precisa de uma nova instancia —
 * para garantir um estado isolado
 * O que significa que ela nao pode usar o Container ou nenhum tipo de cache por requisicao
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Output\DTO\BindingObject
 * @see https://internal/classes/assign
 */
/**
 * Objeto de transporte de dados do sistema.
 *
 * Utilizado principalmente entre Model e Controller,
 * evitando passagem de multiplos parametros e mantendo um fluxo padronizado de dados.
 *
 * Esta classe nao segue o conceito tradicional de DTO.
 * Atua como um objeto de ligacao (binding object), permitindo transportar
 * e manipular dados durante o fluxo da aplicacao.
 *
 * Cada retorno deve utilizar uma nova instancia para garantir um estado isolado.
 * Por esse motivo, nao deve utilizar Container ou cache de instancia por requisicao.
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 *
 * @package Output\DTO\Object\Bind
 * @see https://internal/classes/assign
 */

class Assign {

    # ID unico de um registro. Usado em todas tabelas
    private ?int $ID;

    # Ultimo ID inserido no banco de dados `PDO::lastInsertId()`
    private int|bool $LastID;

    # ID ou referente ao elemento pai. Usado em relacoes hierarquicas e mapeadas
    private int $parent;

    # string transliterada para arquivos e URLs
    private string $slug;

    # Segmento de parametros (slugs) — apos o host e base de URL
    private ?string $segment = null;

    # Tipo de entidade ou relacao
    private string $type;

    private string $URL;

    /**
     * @var $title — Titulo de uma entidade
     * @var $name  — Nome de uma entidade: titulo de uma categoria, nome de um usuario ...
     */
    private string $title, $name;

    # Conteudo textual geralmente contendo HTML
    private string $content;

    # Resumos para uso em feed e meta-description
    private ?string $summary;


    # ... date

    ## vincula com todas os nomes de propriedade abaixo e +
    private object $date;

    ## Data e hora que um registro foi criado (imutavel pelo sistema)
    private string $created;

    ## Timestamp ou data da ultima atualizacao. Mais usado para colunas `updated`
    private ?string $updated;

    ## Data de atualizacao para entidades do tipo 'page' (paginas). Uso: Timestamp para sitemap
    private ?string $lastmod;

    ## Usado para hora e/ou minuto e/ou segundo.
    private string $time;

    # date ...


    
    # Nome do autor em entidades de publicacoes e comentarios
    private string $author;


    /**
     * Nome de anexos para caminhos do arquivos e relacionados. 
     * Usado para coluna `attachment` do tipo JSON da tabela `medias`
     */
    private object|string|null $attachment;



    # Usado em tudo que for relacionado a templates e paginas personalizadas
    private string $template;



    private object $related;


    # ID de relaciomentos
    private ?int $rID;

    /**
     * @deprecated use $rID
     */
    private $relatedID;


    # Tipo do item relacionado
    private ?string $rType;

    /**
     * @deprecated use $rType
     */
    private $relatedtype;


    # Titulo do item relacionado. Usado para midias quando ha relacao com outra tabela
    private string $rTitle;

    /**
     * @deprecated use $rTitle
     */
    private $relatedtitle;


    /**
     * usado em entidade `context`
     */
    private object $context;



    /**
     * Status de um registro:
     * ( 'Rascunho/Pendente' = 0 ) ( 'Publicado/Confirmado' = 1 ) ( 'Lixo' = 2 )
     */
    private int|bool $status;

    # Status de aprovacao de comentario ou usuario
    private int|bool $approved;

    # Status de leitura (lido/nao lido)
    private int|bool $read;


    # Nome de usuario (slug)
    private string $username;

    private string $email;

    # Senha (password)
    private string $pswd;

    # Token de seguranca para validacoes
    private ?string $token;

    /**
     * # Token de seguranca para CSRF 
     * ( Cross-Site Request Forgery / Falsificacao de solicitacao entre sites )
     */
    private ?string $nonce;



    private array $dynamo = [];



    public function __construct( array $data = [] ) {

        foreach( $data as $property => $value ) {

            $this->{$property} = $value;
        }
        

        $this->related = new stdClass;

        $this->context = new class {
            public ?string $title = null;
            public ?string $section = null;
            public ?string $value = null;
            public ?string $name = null;
            public ?string $basename = null;
        };

        $this->date = new class {
            # created, registered, immutable podem ser date ou datetime

            public  string $created; 
            public ?string $updated = null;
            public ?string $lastmod = null;

            # || date('H:i')
            public string $period; 
            public string $registered;
            public string $immutable;

            # so para saida de datas formatadas por chronos_format e outros
            public string $format; 
        };
    }


    /**
     * Metodo magico __set: Define o valor de uma propriedade.
     *
     * Permite atribuir valores a propriedades privadas da classe dinamicamente.
     *
     * @param $name O nome da propriedade a ser definida.
     * @param $value O valor a ser atribuido a propriedade.
     * @return O valor atribuido (o proprio $value).
     */
    public function __set( string $name, mixed $value ): void {

        $this->$name = $value;
    }


    /**
     * Metodo magico __get: Obtem o valor de uma propriedade por referencia.
     *
     * Permite acessar o valor de propriedades privadas da classe dinamicamente.
     * Retorna a propriedade por referencia (`&`), o que significa que
     * alteracoes no valor retornado afetarao diretamente a propriedade original.
     *
     * @param $name O nome da propriedade a ser acessada.
     * @return A referencia da propriedade solicitada.
    */ 
    public function &__get( string $name ): mixed {

        return $this->$name;
    }


    public function __isset( string $name ): bool {
        
        return property_exists($this, $name) && isset($this->$name); 
    }


    public function __debugInfo(): array {
        $vars = get_object_vars($this);
        $cbk  = fn($value) => $value !== null;

        return array_filter( $vars, $cbk );
    }

}