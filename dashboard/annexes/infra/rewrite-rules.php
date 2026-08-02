<?php
# converte caminho do arquivo em titulo para links de sub-itens do menu
$menu_labels = [
    'admins/register'  => 'Adicionar admin',

    'contexts/insert'  => 'Adicionar contexto',

    'pages/insert'     => 'Adicionar página',

    'articles/categories' => 'Categorias',
    'articles/insert'     => 'Adicionar artigo',

    'settings/email'     => 'E-mail',
    'settings/media'     => 'Midía',
    'settings/options'   => 'Opções',
    'settings/reading'   => 'Leitura',
    'settings/socialnet' => 'Redes sociais',
    'settings/seo'       => 'SEO',
    'settings/urls'      => 'Estrutura de URLs'
];


# converte nome ou caminho de arquivo em titulo utilizavel em <h1> e <title>
$router_titles = [
	'home' => 'Painel de controle',
	'404'  => 'Nada encontrado',

	'admins'        => 'Administradores',
	'admins/update' => 'Atualizar conta de administrador',

	'comments'        => 'Comentários',
	'comments/update' => 'Atualizar comentário',

    'contexts'         => 'Contextos',
    'contexts/insert'  => 'Adicionar contexto',
	'contexts/section' => 'Contextos da seção ' . Context::section_title(),
    'contexts/update'  => 'Atualizar contexto',

	'medias'          => 'Mídias',

    'pages'        => 'Páginas',
	'pages/insert' => 'Adicionar página',
	'pages/update' => 'Atualizar página',

	'articles/insert'     => 'Adicionar artigo',
	'articles/update'     => 'Atualizar artigo',
	'articles/categories' => 'Categorias',
	'articles/category'   => 'Atualizar Categoria',

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


