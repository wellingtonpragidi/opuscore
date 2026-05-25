<?php
declare( strict_types = 1 );
/**
 * Responsavel por preparar e iterar os resultados obtidos de uma consulta ao banco de dados,
 * permitindo acessar os campos de forma organizada e controlada.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Seek
 */
class SeekPreparer {

    # Armazena todas as linhas retornadas da consulta.
    private array $fetch = [];

    /**
     * Mantem o indice atual da iteração sobre as linhas.
     * Começa em -1, indicando que nenhuma linha foi processada ainda.
     */
    private int $index = -1;

    /**
     * Armazena a linha atual que esta sendo processada.
     * Fica como null ate que ::show_rows() seja chamado com sucesso.
     * 
     * $row precisa ser public pois eh acessado nos metodos de saida na classe Seek
     */
    public ?array $row = null;


    public function __construct( $fetch ) {
        $this->fetch = $fetch;
    }


    /**
     * Verifica se  proxima linha existe para iterar
     * 
     */
    public function hasNext(): bool {
        return isset( $this->fetch[$this->index + 1] );
    }


    /**
     * Avança para a proxima linha de resultados, se ela existir.
     * 
     * Se houver mais uma linha, atualiza o indice e o campo atual ($row) para essa linha.
     * Caso nao haja mais linhas, define $row como null.
     * 
     * Esse metodo deve ser chamado dentro de loops para iterar sobre os resultados.
     */
    public function next(): ?array {
        if( $this->hasNext() ) {
            $this->index++; # Avança o indice para a proxima linha

            return $this->row = $this->fetch[$this->index]; # Armazena a linha atual
        }
        else {

            return $this->row = null; # Nao ha mais linhas
        }
    }
    /*public function next(): void {
        if( $this->hasNext() ) {
            $this->index++; # Avança o indice para a proxima linha

            $this->row = $this->fetch[$this->index]; # Armazena a linha atual
        }
        else {

            $this->row = null; # Nao ha mais linhas
        }
    }*/


    /**
     * Reinicia a iteracao.
     * 
     * Util quando for necessario percorrer novamente os mesmos resultados.
     * Zera o índice e limpa o campo atual. 
     */
    public function reset(): void {
        $this->index = -1; # Reinicia o indice
        $this->row = null; # Limpa o campo atual
    }

}