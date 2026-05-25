<?php
defined( 'ENTRY_GUARD' ) or die;

/**
 * projetado especificamente para entradas de "nome" disparando alertas
 * Por padrao entradas de NOME e SOBRENOME (nao username)
 * 
 * Aplica diversas validacoes com restricoes, Nao permitindo:
 * - tags HTML
 * - entidades HTML
 * - protocolos (http/s, mailto)
 * Em caso de falha nas validacoes, exibe um alerta e retorna uma string vazia.
 */
function is_valid_name( mixed $name ): bool {
    # Detecta elementos HTML
    if( $name !== strip_tags($name) ) {
        alert('warning', 'Tags HTML não são permitidas.');
        return false;
    }
    # Detecta ENTIDADES HTML
    if( preg_match('/&[a-zA-Z0-9#]+;/', $name) ) {
        alert('warning', 'Entidades HTML não são permitidas.');
        return false;
    }
    # Detecta http(s) ou mailto
    if( preg_match('/\b(?:https?:\/\/|mailto:)/i', $name) ) {
        alert('warning', 'Protocolos não são permitidos.');
        return false;
    }
    # tamanho maximo do nome
    if( mb_strlen($name) > 40 ) {
        alert('warning', 'Máximo de 40 caracteres.');
        return false;
    }
    # Verifica se tem Nome + Sobrenome 
    if( substr_count($name, ' ') < 1 ) {
        alert('warning', 'É necessário informar Nome e Sobrenome.');
        return false;
    }
    # tamanho minimo do nome + sobrenome
    if( mb_strlen($name) < 5 ) {
        alert('warning', 'Nome completo precisa ter pelo menos 5 caracteres.');
        return false;
    }

    return true;
}


# corrige imperfeicao de digitacao, removendo espacos consecutivos
function normalize_name( mixed $name ): string {
    # Remove espacos extras e tambem espacos do inicio/fim usando a flag REMOVE_EDGE_WHITESPACE
    $name = Ensure::squeeze( $name, Ensure::REMOVE_EDGE_WHITESPACE );

    return Ensure::string( $name );
}


function sanitize_name() {}