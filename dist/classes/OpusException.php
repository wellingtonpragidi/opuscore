<?php

/**
 * Classe de excecao personalizada para o sistema.
 *
 * Esta classe estende a classe `Exception` padrao do PHP para fornecer
 * um tratamento de erro mais especifico e visualmente formatado.
 * Ela permite definir um 'tipo' de excecao (erro ou aviso) e
 * oferece metodos para exibir mensagens de erro detalhadas em HTML.
 *
 * @package Exceptions
 * @since 1.0.0
 * @uses Exception Classe base do PHP para excecoes.
 */
class OpusException extends Exception {

    public const INVALID_PARAMETER = 666;

    /**
     * Tipo da excecao (ex: e-error, e-warning).
     *
     * Este atributo armazena o tipo categorizado da excecao,
     * que e usado para determinar o estilo de exibicao.
     *
     * @var string
     */
    private string $type;

    /**
     * Construtor da excecao.
     *
     * Inicializa uma nova instancia de OpusException.
     * Normaliza o tipo de excecao fornecido para categorias predefinidas:
     * - 'error', 'err', 'fatal' -> 'e-error' (padrao se nao for aviso)
     * - 'warning', 'warn', 'info' -> 'e-warning'
     *
     * @param string $message Mensagem descritiva da excecao.
     * @param string $type Tipo da excecao. Valores comuns: 'error', 'warning', 'info', etc.
     * O padrao e 'e-error'.
     * @param int $code Codigo numerico da excecao (opcional, padrao: 0).
     * @return void
     */
    public function __construct( string $message, string $type = 'e-error', int $code = 0 ) {
        parent::__construct( $message, $code );
        $this->type = $type;

        // A logica abaixo ira sobrescrever o $type inicial com 'e-error'
        // se nao for um dos tipos de 'warning'.
        if( $type == 'warning' || $type == 'warn' || $type == 'info' ) {
            $this->type = 'e-warning';
        }
        else {
            $this->type = 'e-error';
        }
    }

    /**
     * Retorna o tipo categorizado da excecao.
     *
     * Este metodo fornece o tipo normalizado da excecao,
     * que sera 'e-error' ou 'e-warning'.
     *
     * @return string O tipo da excecao (ex: 'e-error', 'e-warning').
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Define e retorna a representacao HTML do tipo da excecao para exibicao.
     *
     * Este metodo gera um fragmento de HTML estilizado que visualmente
     * representa o tipo da excecao (erro ou aviso), incluindo icones e cores.
     *
     * @return string Um fragmento HTML com o tipo da excecao estilizado.
     */
    public function setType(): string {
        if( $this->type == 'e-error' ) {
            return ' — <span style="color:#4E2229"><small>&#10005;</small> Erro</span>';
        }
        else {
            return ' — <span style="color:#C6989E">(&#33;) Aviso</span>';
        }
    }

    /**
     * Retorna o HTML de aviso, que atualmente e uma pagina de erro completa.
     *
     * Este metodo foi projetado para ser um aviso nao fatal.
     * No entanto, devido a questoes com `set_exception_handler` e buffer,
     * ele atualmente chama o metodo `error()`, que substitui o HTML da pagina inteira.
     *
     * @todo Investigar e corrigir o comportamento para que seja um aviso nao fatal,
     * sem limpar o buffer ou substituir a pagina completa.
     * @return string O HTML completo da pagina de erro/aviso.
     */
    public function warning() {
        $html  = '<div class="one-exception '. $this->type .'">';
        $html .= $this->getMessage();
        $html .= $this->details();
        $html .= '</div>';

        return $html;
    }

    /**
     * Gera e retorna o HTML de uma pagina de erro fatal.
     *
     * Este metodo cria uma pagina HTML completa para exibir detalhes de um erro fatal.
     * Inclui metadados, estilos e as informacoes da excecao.
     *
     * @uses site_url Para gerar a URL do arquivo CSS.
     * @uses OpusException::getMessage Para obter a mensagem da excecao.
     * @uses OpusException::details Para exibir detalhes adicionais da excecao.
     * @return string O HTML completo da pagina de erro.
     */
    public function error() {
        $host = parse_url( ENGINE_URL, PHP_URL_HOST );
        $html  = '<!DOCTYPE html><html lang="pt-BR"><head>';
        $html .= '<meta charset="utf-8"><title>Erro — '. $host .'</title>';
        $html .= '<link rel="stylesheet" href="'.site_url('web/assets/css/exception.css').'">';
        $html .= '</head><body>';

        $html .= '<div class="one-exception '. $this->type .'">';
        $html .= $this->getMessage();
        $html .= $this->details();
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Exibe detalhes da excecao se o modo de debug estiver ativo.
     *
     * Se a constante `EXCEPTION_DETAILS` for avaliada como `true`, este metodo
     * gera um bloco HTML `<details>` contendo informacoes sobre o tipo, codigo,
     * arquivo e linha onde a excecao ocorreu. Caso contrario, retorna uma string vazia.
     *
     * @uses OpusException::setType Para obter o HTML formatado do tipo da excecao.
     * @uses OpusException::getCode Para obter o codigo da excecao.
     * @uses OpusException::getFile Para obter o arquivo onde a excecao ocorreu.
     * @uses OpusException::getLine Para obter a linha onde a excecao ocorreu.
     * @return string Um fragmento HTML com os detalhes da excecao ou uma string vazia.
     */
    private function details(): ?string {
        if( ! EXCEPTION_DETAILS ) {
            return null;
        }

        $html  = '<details>';

        $html .= '<summary>Exibir detalhes</summary>';

        $html .= '<ul>';

        # $html .= '<li><strong>Tipo: <em>' . static::class . '</em></strong>'. $this->setType() . '</li>';

        $html .= '<li><strong>Tipo:</strong>
            <code class="class-name"> ' . static::class . '</code>
        </li>';

        if( $this->getCode() !== 0 ) {
            $html .= "<li><strong>Codigo:</strong> {$this->getCode()}</li>";
        }

        $html .= "<li><strong>Arquivo:</strong> {$this->getFile()}</li>";

        $html .= "<li><strong>Linha:</strong> {$this->getLine()}</li>";

        $html .= '</ul>';

        $html .= '</details>';

        return $html;
    }

    /**/
    public static function allowedColumns( 
        string $column, string $method, string $class ): string {

        $msg = "Não é possível consultar a coluna <code>'{$column}'</code><br>
        Classe: <code class=\"class-name\">{$class}</code><br>
        Método: <code>{$method}</code>";

        return $msg;
    }

}