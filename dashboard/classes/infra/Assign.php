<?php
declare( strict_types = 1 );
/**
 * classe com propriedades genericas para transferencia de dados 
 * Data Transfer Object (DTO)
 * na maioria dos casos esses objetos sao usados em colunas de tabelas do banco de dados
 *
 * utiliza os metodos magicos `__get` e `__set` com & no __get, permitindo alteracoes no valor
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

class Assign {

    ## Identificacoes

    # ID unico de um registro. Usado em todas tabelas
    private int $ID = 0;

    # Ultimo ID inserido no banco de dados `PDO::lastInsertId()`
    private int $LastID;

    # para hierarquias: parent, child, children, ID
    private int $parent = 0;
    private bool $is_child = false;

    # para relacionados:  slugs, segmentos, types, IDs etc
    private object $related;


    # transliterada para arquivos e parametros de URLs
    private string $slug = '';

    # segmento de parametros (slug) da URL apos o host e base se existir 
    private ?string $segment = null;

    # Tipo de entidade ou relacao
    private string $type = '';

    # Nome de usuario. Usado para entidades de usuarios e tabela classe Comments
    private ?string $username = null;

    # URL associada a um registro. Uo global
    private string $URL = '';

    ##


    /**
     * @var $title — Titulo de uma entidade. Uso global
     * @var $name  — Nome de uma entidade. Uso global (em categorias name eh o titulo)
     */
    private string $title = '';
    private string $name = '';


    # Conteudo principal ou corpo de um registro
    private ?string $content = null;


    # Resumos de textos, em geral Assign->content. Muito usado em feed e meta-description
    private ?string $summary = null;



    ## ... date

    # vincula com todas os nomes de propriedade abaixo e +
    # private object $date;

    # que um registro foi criado (imutavel pelo sistema)
    private string $created;

    # da ultima atualizacao. Padrao `NULL`
    private ?string $updated = null;

    # para entidades de pages Uso: Timestamp para sitemap. Padrao `NULL`
    private ?string $lastmod = null;

    # para hora : minuto : segundo
    private string $time;

    ## date ...


    /**
     * usado em entidade de midia. Valores:
     * $bind->media->ID | $bind->media->type | $bind->media->title
     */
    private object $media;

    /**
     * Nome de anexos para caminhos do arquivos e relacionados. 
     * Usado para coluna `attachment` do tipo JSON da tabela `medias`
     */
    private string|array|object|null $attachment = null;


    /**
     * usado em entidade `context`. Valores:
     * $bind->media->title | $bind->media->section | $bind->media->value | $bind->media->basename
     */
    private object $context;


    # Usado em tudo que for relacionado a templates e paginas personalizadas
    private string|array|object $template;


    /**
     * Status de um registro:
     * ('Publicado' = 1 'Rascunho' = 0) ('Confirmado' = 1 'Pendente' = 0)
     * OU: 
     * ( 'Rascunho/Pendente' = 0 ) ( 'Publicado/Confirmado' = 1 ) ( 'Lixo' = 2 )
     */
    private int $status = 0;


    ## Admin, Comment, User ..
    # Endereco de e-mail
    private string $email = '';

    # Senhas
    private string|array|null $pswd = null;

    # Codigo ou status de ativacao de conta. Geralmente URL contendo token de validacao
    private ?string $activation = null;

    # Token de seguranca 
    private string $token;

    # Token de seguranca CSRF
    private string $nonce;

    # Nome do autor em entidades de publicacoes
    private ?string $author = null;

    # Nivel de permissao ( ADMIN )
    private int $role = 3;


    # Endereco IP de um visitante
    private ?string $IP = null;


    # Status de aprovacao de comentario ou usuario
    private int|bool $approved;

    # Status de leitura (lido/nao lido)
    private int|bool $read;


    # Usado para organizar qualquer string contendo html, ex.: $bind->html->attr->class
    private object $html;



    public function __construct( array $data = [] ) {

        foreach( $data as $property => $value ) {

            $this->{$property} = $value;
        }
        

        $this->html = new stdClass;

        # $this->related = new stdClass;

        $this->media = new class {
            public int $ID = 0;
            public ?string $type = null;
            public ?string $title = null;
        };

        $this->context = new class {
            public int $ID = 0;
            public ?string $title = null;
            public ?string $section = null;
            public ?string $value = null;
            public ?string $name = null;
            public ?string $basename = null;
        };
    }


    /**
     * magic __set: Define o valor de uma propriedade
     *
     * Permite atribuir valores a propriedades privadas da classe dinamicamente
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
