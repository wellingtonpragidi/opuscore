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
    private ?string $segment;

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


    # Data e hora que um registro foi criado (imutavel pelo sistema)
    private ?string $created;

    /**
     * @deprecated (s) use $created
     */
    private $date, $registered;


    # Timestamp ou data da ultima atualizacao. Mais usado para colunas `updated`
    private ?string $updated;

    /**
     * @deprecated use $updated
     */
    private $update;


    # Data de atualizacao para entidades do tipo 'page' (paginas). Uso: Timestamp para sitemap
    private ?string $lastmod;
    
    # Nome do autor em entidades de publicacoes e comentarios
    private string $author;

    /**
     * Nome de anexos para caminhos do arquivos e relacionados. 
     * Usado para coluna `attachment` do tipo JSON da tabela `medias`
     */
    private object|string|null $attachment;

    # Usado em tudo que for relacionado a templates e paginas personalizadas
    private string $template;


    # segmento, ID ou slug relacionado para identificar local de exibicao ou $slug(s) secundarios
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
    private string $rTitle;

    /**
     * @deprecated use $rTitle
     */
    private $relatedtitle;


    # Secao ou grupo de uma configuracao ou contexto
    private ?string $section;

    # Valor de uma configuracao ou contexto
    private ?string $value;

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
        # $data  | array retornado do SELECT
        # $key   | propriedade de Assign (deve ser igual o nome da coluna)
        # $value | valor atribuido a propriedade
        foreach( $data as $key => $value ) {
            $this->{$key} = $value;
        }
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