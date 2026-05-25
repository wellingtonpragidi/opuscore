<?php
/**
 * Gerencia as operacoes CRUD para categorias incluindo funcionalidade hierarquica 
 * e geracao de HTML para exibicao
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System
 */
class DashboardMenu extends Router {

    /**
     * array $structure: 
     * Lista os itens principais do menu com adicao de titulo e ordena submenus (se houver) 
     * */
    private $structure = [
        '' => ['title' => 'Painel'], # home
        'posts' => ['title' => 'Posts',
            'submenu_order' => ['insert', 'categories']
        ],
        'pages' => ['title' => 'Paginas',
            'submenu_order' => ['insert']
        ],
        'settings' => ['title' => 'Configurações',
            'submenu_order' => ['media', 'reading', 'options', 'email', 'socialnet', 'seo', 'urls']
        ],
        'customize' => ['title' => 'Personalizar',
            'submenu_order' => ['menus', 'templates', 'context']
        ],
        'media' => ['title' => 'Mídias',
            'submenu_order' => ['favicons', 'poster']
        ],
        'admins' => ['title' => 'Administradores',
            'submenu_order' => ['register']
        ],
        'comments' => ['title' => 'Comentários'],
        'users' => ['title' => 'Usuários']
    ];

    /**
     * Links (silenciosos) ocultos 
     * Servem para manter submenus abertos e com classes do tipo .current, .active etc no item
     * Esses sao links de URLs que contem query string
     * Adicione os parametros de rota dentro do subarray de uma query string
     * */
    private $silents = [
        'params' => [
            'id' => [
                'admins/admin',
                'comments/comment', 
                'media/media',
                'pages/update',
                'posts/update',
                'posts/category'
            ],
            'name' => [
                'customize/menus'
            ],
            'update' => [
                'customize/context'
            ]
        ],
        'special' => [
            'insert' => [
                'customize/context' => ['insert' => '']
            ]
        ]
    ];

    /**
     * Gera a estrutura do menu dinâmico baseado nas views existentes
     */
    public function menu(): array {
        $menu = [];

        foreach( $this->structure as $key => $config ) {
            $menu_item = [
                'title' => $config['title'],
                'href'  => dash_url($key),
                'submenu' => $this->submenu( $key, $config['submenu_order'] ?? [] )
            ];
            if( empty($menu_item['submenu']) ) {
                unset( $menu_item['submenu'] );
            }
            
            $menu[] = $menu_item;
        }

        # Permite modificacoes externas via hook
        $menu = Hook::call_filter( 'dashboard_menu', $menu );
        
        return array_merge( $menu, $this->fixed_items() );
    }

    /**
     * Gera subitens do menu baseado nas views existentes e ordem definida
     * parametro $parent - Nome da rota pai
     * parametro $submenu_order - Ordem personalizada dos subitens
     */
    protected function submenu( string $parent, array $submenu_order ): array {
        $submenu = [];
        $routes = array_keys( $this->views );

        $children = array_filter( $routes, fn($route) => str_starts_with($route, "{$parent}/") );

        $actions = [];

        foreach( $children as $child ) {
            $action = str_replace( "{$parent}/", '', $child );
            if( empty($action) || is_numeric($action) ) {
                continue;
            }
            $actions[$action] = $child;
        }

        foreach( $submenu_order as $action ) {
            if( isset($actions[$action]) ) {
                $child = $actions[$action];
                $fallback = ucfirst( str_replace('-', ' ', $action) );
                $submenu[] = [
                    'title' => $this->rewrite_menu_label[$child] ?? $fallback,
                    'href'  => dash_url( $child )
                ];
                unset( $actions[$action] ); # Remove para nao duplicar depois
            }
        }

        return array_merge( $submenu, $this->silent($parent) );
    }

    /**
     * returna array de itens fixos prontos para adicionar ao menu
     */
    private function fixed_items(): array {
        return [
            [
                'title' => 'Ver site',
                'href'  => site_url(),
                'attrs' => 'target="_blank"',
                'icon' => '<span icon="newtab" size="15" top="1.5"></span>'
            ],
            [
                'title' => 'Sair',
                'href'  => dash_url('logout'),
                'attrs' => 'title="Logout"',
                'icon' => '<span icon="logout" top="2"></span>'
            ]
        ];
    }

    private function silent( string $parent ): array {
        $items = [];
        
        # encontra TODAS as rotas para este parent
        $parentRoutes = [];
        
        # Busca em 'params'
        foreach( $this->silents['params'] ?? [] as $param => $routes ) {
            foreach( $routes as $route ) {
                if( str_starts_with($route, $parent . '/') ) {
                    $action = str_replace( $parent . '/', '', $route );
                    $parentRoutes[$action]['params'][] = $param;
                }
            }
        }
        
        # busca em 'special'  
        foreach( $this->silents['special'] ?? [] as $special_key => $special_routes ) {
            foreach( $special_routes as $route => $special_config ) {
                if( str_starts_with($route, $parent . '/') ) {
                    $action = str_replace($parent . '/', '', $route);
                    $parentRoutes[$action]['special'][$special_key] = $special_config;
                }
            }
        }
        
        # processa cada acao encontrada
        foreach( $parentRoutes as $action => $config ) {
            $params = [];
            # parametros normais
            foreach( $config['params'] ?? [] as $param ) {
                $value = URL::Get($param);
                if( $value !== null && $value !== '' ) {
                    $params[$param] = $value;
                }
            }
            
            # casos especiais
            foreach( $config['special'] ?? [] as $special_key => $special_config ) {
                if( URL::has($special_key) ) {
                    $params = array_merge( $params, $special_config );
                }
            }
            
            $querystr = $this->build_query_string($params);
            $items[] = [
                'title' => '',
                'href' => dash_url("{$parent}/{$action}/{$querystr}"),
                'silent' => true
            ];
        }
        
        return $items;
    }

    private function build_query_string( array $params ): string {
        if( empty($params) ) {
            return '';
        }
        $pairs = [];
        foreach( $params as $key => $value ) {
            if( $value === '' ) {
                $pairs[] = $key; # 'insert' vira ?insert
            } 
            else {
                $pairs[] = $key . '=' . urlencode($value); # 'name=foo' vira ?name=foo
            }
        }
        return '?' . implode( '&', $pairs );
    }

}