<?php
/**
 * Classe Assign: Entidade generica para mapeamento de dados de diversas tabelas do sistema.
 *
 * Esta classe atua como uma entidade generica ou Data Transfer Object (DTO),
 * projetada para representar propriedades comuns encontradas em varias tabelas
 * do banco de dados (e.g., paginas, categorias, usuarios, comentarios, midias,
 * configuracoes, estatisticas).
 *
 * Ela utiliza os metodos magicos `__get` e `__set` para permitir acesso
 * e modificacao dinamica de suas propriedades, tornando-a flexivel para
 * diferentes contextos de dados sem a necessidade de getters/setters explicitos
 * para cada atributo.
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Core/Entity/Mapper/DTO
 * @see https://opuscore.dev/classes/assign
 */

// #[\AllowDynamicProperties]

class Assign {

    # ID unico de um registro. Usado em todas tabelas
    private ?int $ID;


    # transliterada para arquivos e parametros de URLs
    private ?string $slug;

    # ID ou referente numerica ao elemento pai. 
    # Usado em relacoes hierarquicas e mapeadas
    private ?int $parent;

    # transliterada para o slug de parents "parent_slug"
    private ?string $strparent;

    # Tipo de entidade ou relacao
    private string $type;

    # URL associada a um registro. Uo global
    private ?string $URL;

    /**
     * @var $title — Titulo de uma entidade. Uso global
     * @var $name  — Nome de uma entidade. Uso global (em categorias name eh o titulo)
     */
    private ?string $title, $name;

    # Segmento de parametros da URL apos o host 
    # (principalmente em categorias eh apos o parametro de slug base) por isso nao eh $pathname
    private ?string $segment;

    # Conteudo principal ou corpo de um registro. Uso geral
    private ?string $content;

    # Resumos de textos, em geral Assign->content. Muito usado em feed e meta-description
    private ?string $summary;

    # Nome de usuario. Usado para entidades de usuarios e tabela classe Comments
    private ?string $username;


    # Data e hora que um registro foi criado (imutavel pelo sistema)
    private ?string $created;

    /**
     * @deprecated (s) use $created
     */
    private $date;


    # Timestamp ou data da ultima atualizacao. Mais usado para colunas `updated`
    private ?string $updated;

    /**
     * @deprecated use $updated
     */
    private $update;


    # Data de atualizacao para entidades do tipo 'page' (paginas). Uso: Timestamp para sitemap
    private ?string $lastmod;

    # Usado em tudo que for relacionado a templates e paginas personalizadas
    private ?string $template;

    # Nome de anexos para caminhos do arquivos e relacionados. 
    # Usado para coluna `attachment` do tipo JSON da tabela `medias`
    private string|array|object|null $attachment;

    /**
     * slug, segmento as vezes ID relacionado para identificar local de exibicao ou $slug(s) secundarios
     * @var string
     */
    private string|int|null $related;


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
    private ?string $rTitle;

    /**
     * @deprecated use $rTitle
     */
    private $relatedtitle;


    # Nome do autor em entidades de publicacoes
    private ?string $author;

    /**
     * Status de um registro:
     * ('Publicado' = 1 'Rascunho' = 0) ('Confirmado' = 1 'Pendente' = 0)
     * OU: 
     * ( 'Rascunho/Pendente' = 0 ) ( 'Publicado/Confirmado' = 1 ) ( 'Lixo' = 2 )
     */
    private int|bool $status;

    # Endereco de e-mail
    private string $email;

    # Senhas
    private ?string $pswd;

    # Codigo ou status de ativacao de conta. Geralmente URL contendo token de validacao
    private ?string $activation;

    # Token de seguranca 
    private string $token;

    # Nivel de permissao ( ADMIN )
    private int $role;

    # Secao ou grupo de uma configuracao ou contexto
    private ?string $section;

    # Valor de uma configuracao ou contexto
    private ?string $value;

    # Endereco IP de um visitante
    private ?string $IP;

    # Usado para hora e/ou minuto e/ou segundo.
    private ?string $time;

    # Ultimo ID inserido no banco de dados `PDO::lastInsertId()`
    private int|bool $LastID;

    # Status de aprovacao de comentario ou usuario
    private int|bool $approved;

    # Status de leitura (lido/nao lido)
    private int|bool $read;

    # Usado para valores de entrada de formularios do tipo 'checkbox' e 'radio'
    private array $checked = [];


    /**
     * @deprecated -- nada sera usado no lugar dele nesse ambiente
     * Ainda nao excluido pois esta vinculado com o distribuidor pegando os dois ambientes
     */
    private array $dynamo = [];


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