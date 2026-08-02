<?php
# essas sao as duas unicas chaves usadas pelo sistema para query string
function has_identifier_query(): bool {
    return isset($_GET['id']) || isset($_GET['key']);
}



/* --- -----------------------------------------------
 
    Funcoes condicionais para rotas a partir da URL

 ------------------------------------------------- --- */

# categories
# --- ---------------------------------------
function is_categories(): bool {
    return URL::param(1) === 'categories';
}

function is_category(): bool {
    return URL::param(1) === 'category' && URL::has('id');
}

function is_article_categories(): bool {
    return URL::param(0) === 'articles' && is_categories();
}
# /articles/category(/?id=nº)
function is_article_category(): bool {
    return URL::param(0) === 'articles' && is_category();
}
# --------------------------------------- ---



# contexts
# --- ---------------------------------------
function is_contexts(): bool {
    return URL::pathname() === 'contexts';
}

function is_contexts_section(): bool {
    return URL::pathname() === 'contexts/section' && URL::has('key');
}

function is_context_insert(): bool {
    return URL::pathname() === 'contexts/insert';
}

function is_context(): bool {
    return URL::pathname() === 'contexts/update' && URL::has('id');
}
# --------------------------------------- ---



# medias
# --- ---------------------------------------
function is_medias(): bool {
    return URL::param(0) === 'medias';
}

function is_media(): bool {
    return URL::param(0) === 'medias' && URL::has('id');
}
# --------------------------------------- ---



# menus
# --- ---------------------------------------
function is_menus(): bool {
    return URL::param(0) === 'menus';
}
function has_active_menu(): bool {
    $active_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? false);
    if( $active_menu === false ) {
        return false;
    }
    
    return URL::param(0) === 'menus';
}
# --------------------------------------- ---



# pages
# --- ---------------------------------------
function is_pages(): bool {
    return URL::param(0) === 'pages';
}

function is_page_insert(): bool {
    return URL::pathname() === 'pages/insert';
}

function is_page(): bool {
    return URL::pathname() === 'pages/update' && URL::has('id');
}
# --------------------------------------- ---



# articles
# --- ---------------------------------------
function is_articles(): bool {
    return URL::param(0) === 'articles';
}

function is_article_insert(): bool {
    return URL::pathname() === 'articles/insert';
}

function is_article(): bool {
    return URL::pathname() === 'articles/update' && URL::has('id');
}
# --------------------------------------- ---


# settings
# --- ---------------------------------------
 # is_settings() representa todas as rotas /settings/xxx/ 
 # por esse motivo nao verifica se param [1] eh vazio
function is_settings(): bool {
    return URL::param(0) === 'settings';
}
# is_settings core()
# is_settings email()

# hoje esse eh apenas dimensoes de imagem ( `*_image_size_*` — nomeado como "size" ) 
function is_setting_media_sizes(): bool {
    return URL::pathname() === 'settings/media';
}
# 
# --------------------------------------- ---









/**
 * Busca por rota especificas
 * @example :
 * * /SELECTABLES/''
 * * /xxx/insert
 * * /xxx/update/?id=x
 */
define( 
    'SELECTABLES', 
    ['admins', 'comments', 'contexts', 'pages', 'articles', 'users'] 
);
function is_selects(): bool {
    if( ! in_array(URL::param(0), SELECTABLES) ) {
        return false;
    }

    return URL::param(1) === '';
}

function is_insert(): bool {
    return URL::param(1) === 'insert';
}

function is_update(): bool {
    return URL::param(1) === 'update' && URL::has('id');
}




define( 
    'DOCUMENTS_CONTENTS', 
    ['contexts', 'pages', 'articles'] 
);

define( 
    'IS_DOCUMENTS_CONTENTS', 
    in_array( URL::param(0), DOCUMENTS_CONTENTS )
);


function is_document_select(): bool {
    if( ! IS_DOCUMENTS_CONTENTS ) {
        return false;
    }

    return URL::param(1) === '';
}


function is_document_insert(): bool {
    if( ! IS_DOCUMENTS_CONTENTS ) {
        return false;
    }

    return URL::param(1) === 'insert';
}


function is_document_update(): bool {
    if( ! IS_DOCUMENTS_CONTENTS ) {
        return false;
    }

    return URL::param(1) === 'update' && URL::has('id');
}