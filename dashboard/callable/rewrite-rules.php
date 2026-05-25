<?php
# converte nome e/ou caminho para arquivo em titulo utilizavel em <h1> e <title>
$page_titles = [
	'home' => 'Painel de controle',
	'404'  => 'Nada encontrado',

	'admins'       => 'Administradores',
	'admins/admin' => 'Atualizar conta de administrador',

	'comments'         => 'Comentários',
	'comments/comment' => 'Atualizar comentário',

	'customize'         => 'Personalizar', # pagina inicial @todo ainda sem conteudo
	'customize/context' => 'Contextos',

	'media'          => 'Mídias',
	# 'media/favicons' => 'Favicons',
	# 'media/poster'   => 'Poster'

	'pages' => 'Paginas',
	'pages/insert' => 'Adicionar pagina',
	'pages/update' => 'Atualizar pagina',

	# posts
	'posts/insert'     => 'Adicionar post',
	'posts/update'     => 'Atualizar post',
	'posts/categories' => 'Categorias',
	'posts/category'   => 'Atualizar Categoria',

	'settings'           => 'Configurações',
	'settings/addeds'    => 'Configurações adicionados',
	'settings/email'     => 'Configurações de e-mail',
	'settings/insert'    => 'Adicionar configuração',
	'settings/media'     => 'Configurações de midía',
	'settings/options'   => 'Configurações opcionais',
	'settings/reading'   => 'Configurações de Leitura',
	'settings/socialnet' => 'Links de redes sociais',
	'settings/update'    => 'Atualizar configuração',
	'settings/seo'       => 'Configurações de SEO',
	'settings/urls'      => 'Configurar estrutura de URLs',

	'statistics' => 'Estatísticas',

    'users' => 'Usuários'
];


# converte (nome e/ou caminho) para arquivo em titulo para links de sub-itens do menu
$menu_labels = [
	'admins/register' => 'Adicionar Administrador',

	'customize/menus'     => 'Menus',
	'customize/templates' => 'Templates',
	'customize/context'   => 'Contexto',
	'customize/context/insert' => 'Adicionar Contexto',

	'media/favicon'    => 'Favicon',
	'media/poster'     => 'Poster',
	'pages/insert'     => 'Adicionar pagina',
	'posts/categories' => 'Categorias',
	'posts/insert'     => 'Adicionar post',

	'settings/email'     => 'E-mail',
	'settings/media'     => 'Midía',
	'settings/options'   => 'Opções',
	'settings/reading'   => 'Leitura',
	'settings/socialnet' => 'Redes sociais',
	'settings/seo'       => 'SEO',
	'settings/urls'      => 'Estrutura de URLs'
];

# Titulos especiais para paginas com URLs contendo query string
$query_titles = [
    # INSERIR ------------------------------------------
    [
        'key'   => 'customize/context',
        'query' => 'insert',
        'value' => 'Adicionar contexto',
    ],
    [
        'key'   => 'media/favicon',
        'query' => 'insert',
        'value' => 'Inserir favicon',
    ],

    # ATUALIZAR ----------------------------------------
    [
        'key'   => 'customize/context',
        'query' => 'update',
        'value' => 'Atualizar contexto',
    ],
    [
        'key'   => 'admins/admin',
        'query' => 'update',
        'value' => 'Atualizar administrador',
    ],

    # adiciona mais quando precisar...
];
