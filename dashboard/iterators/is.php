<?php

/**
 * Verifica se o usuario logado possui a funcao de 'administrador gerente' (role 1).
 * Esta funcao e essencial para controlar o acesso a funcionalidades criticas do sistema.
 */
function is_admin_manager(): bool {
    $admin = Container::call('Admin');
    $role = Ensure::int( $admin->logged_role() );
    return $role === 1; # Retorna true se a funcao for 1.
}


function is_home(): bool {
    return URL::param(0) === '';
}


function is_posts(): bool {
    return URL::param(0) === 'posts';
}
function is_post_insert(): bool {
    return URL::param(0) === 'posts' && URL::param(1) === 'insert';
}
function is_post_update(): bool {
    if( ! URL::has('id') ) {
        return false;
    }

    return URL::param(0) === 'posts' && URL::param(1) === 'update';
}

function is_categories(): bool {
    return URL::param(1) === 'categories';
}
function is_category(): bool {
    return URL::param(1) === 'category' && URL::has('id');
}

function is_post_categories(): bool {
    return is_posts() && is_categories();
}
# /posts/category(/?id=nº)
function is_post_category(): bool {
    return is_posts() && is_category();
}


function is_pages(): bool {
    return URL::param(0) === 'pages';
}
function is_page_insert(): bool {
    return URL::param(0) === 'pages' && URL::param(1) === 'insert';
}
function is_page_update(): bool {
    if( ! URL::has('id') ) {
        return false;
    }

    return URL::param(0) === 'pages' && URL::param(1) === 'update';
}


function is_settings(): bool {
    return URL::param(0) === 'settings';
}

function is_customize(): bool {
    return URL::param(0) === 'customize';
}

function is_medias(): bool {
    return URL::param(0) === 'media';
}

function is_media(): bool {
    return URL::param(0) === 'media' && URL::has('id');
}