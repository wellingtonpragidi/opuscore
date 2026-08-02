<?php
/**
 * Gerencia um sistema para ganchos de acoes e filtros
 *
 * Permite que componentes do sistema se "engatem" em pontos especificos
 * da execucao para realizar acoes ou modificar dados, promovendo a extensibilidade
 * e o baixo acoplamento. 
 * Inclui logica para hooks de execucao unica.
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Core
 */

class Hook {

    /**
     * Armazena as funcoes (callbacks) registradas para cada hook de acao
     * A chave e o nome do hook, e o valor e um array de callables
     */
    private static array $actions = [];

    /**
     * Armazena as funcoes (callbacks) registradas para cada filtro
     * A chave e o nome do filtro, e o valor e um array de callables
     */
    private static array $filters = [];

    /**
     * Marca hooks que foram registrados para serem unicos
     * Tenta impedir multiplos registros para um mesmo nome de gancho
     */
    private static array $unique_tags = [];

    /**
     * Marca hooks que ja foram executados usando `call_action_once()`
     * Tenta impedir novas chamadas ou registros apos a execucao unica
     */
    private static array $executed_tags = [];



    /**
     * Registra uma funcao (callback) para ser executada quando um hook de acao for chamado
     */
    public static function append_action( string $name, callable $callback ): void {
        # Bloqueia o registro se o hook ja foi executado como "once".
        if( isset(self::$executed_tags[$name]) ) {
            throw new OpusException("
                O gancho '{$name}' já foi executado com 'unique' e não pode registrar novas ações."
            );
        }
        
        self::$actions[$name][] = $callback;
    }


    /**
     * Chama todas as funcoes registradas para um gancho de acao.
     * Pode ser chamado multiplas vezes por requisicao.
     */
    public static function call_action( string $name, mixed ...$args ): void {
        if( isset(self::$actions[$name]) ) {

            foreach( self::$actions[$name] as $call ) {

                call_user_func_array( $call, $args );
            }
        }
    }



    /**
     * Registra uma funcao (callback) para um filtro
     * Filtros pode modificar valores e devem retornar o valor (original ou modificado)
     */
    public static function append_filter( string $name, callable $callback ): void {
        if( ! isset(self::$filters[$name]) ) {
            self::$filters[$name] = [];
        }

        self::$filters[$name][] = $callback;
    }


    /**
     * Aplica todos os filtros registrados a um valor
     *
     * O valor e passado sequencialmente por cada funcao de filtro,
     * e o resultado de um filtro e a entrada para o proximo.
     */
    public static function call_filter( string $name, mixed $value, ...$args ): mixed {
        if( isset(self::$filters[$name]) ) {

            foreach( self::$filters[$name] as $call ) {

                # Passa o valor atual e quaisquer argumentos adicionais para o filtro.
                $value = call_user_func( $call, $value, ...$args );
            }
        }
        
        return $value;
    }


    /**
     * Verifica se existe pelo menos uma acao registrada para um dado hook
     */
    public static function has_action( string $name ): bool {

        # return ! empty( self::$actions[$name] );
        return isset( self::$filters[$name] );
    }

    /**
     * Verifica se existe pelo menos um filtro registrado para um dado nome de filtro
     */
    public static function has_filter( string $name ): bool {

        # return ! empty( self::$filters[$name] );
        return isset( self::$filters[$name] );
    }



    # Remove todas as acoes registradas em um gancho especifico
    public static function destroy_actions( string $name ): bool {
        if( isset(self::$actions[$name]) === false ) {
            return false;
        }

        unset( self::$actions[$name] );

        return true;
    }

    # Remove todos os filtros registrados em um gancho especifico
    public static function destroy_filters( string $name ): bool {
        if( isset(self::$filters[$name]) === false ) {
            return false;
        }

        unset( self::$filters[$name] );

        return true;
    }




    /**
     * Registra uma unica funcao (callback) para um hook de acao.
     *
     * Se um hook ja foi registrado como unico, novas tentativas lancarao uma excecao.
     * Sobrescreve quaisquer acoes previamente registradas para este nome de hook.
     */
    public static function append_unique_action( string $name, callable $callback ): void {
        if( isset(self::$unique_tags[$name]) ) {
            throw new OpusException("O gancho '{$name}' já foi registrado como único e não pode ser registrado novamente.");
        }

        # Tambem nao permite registrar se ja foi executado como "once"
        if( isset(self::$executed_tags[$name]) ) {
             throw new OpusException("O gancho '{$name}' já foi executado e não pode mais registrar novas ações.");
        }

        self::$unique_tags[$name] = true;

        # Substitui todas as acoes existentes por esta unica.
        self::$actions[$name] = [$callback]; 
    }


    /**
     * Chama as funcoes registradas para um gancho de acao apenas uma vez por requisicao.
     * Apos a execucao bloqueia novas chamadas para este gancho e novos registros de acoes.
     */
    public static function call_action_once( string $name, ...$args ): void {
        if( isset(self::$executed_tags[$name]) ) {
            throw new OpusException("O gancho '{$name}' já foi executado e não pode ser chamado novamente.");
        }

        # Marca que este gancho foi executado uma vez.
        self::$executed_tags[$name] = true;

        # Executa as acoes registradas.
        if( isset(self::$actions[$name]) ) {

            foreach (self::$actions[$name] as $call ) {
                call_user_func_array( $call, $args );
            }
        }

        # Substitui a lista de acoes por uma funcao que lancara uma excecao em chamadas futuras.
        self::$actions[$name] = [ function() use ($name) { 

            throw new OpusException("
                O gancho '{$name}' já foi executado e não pode ser usado novamente."
            );
        }];
    }

}