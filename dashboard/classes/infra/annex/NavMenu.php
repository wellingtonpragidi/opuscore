<?php
declare( strict_types = 1 );
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
class NavMenu extends Router {

    /**
     * array $structure: 
     * Lista os itens principais do menu com adicao de titulo e ordena submenus (se houver) 
     * */
    private $structure = [
        # home
        '' => [
            'label' => 'Painel', 
            'submenu_order' => [
                'select'   => 'Início', 
                'upgrades'
            ]
        ],

        'pages' => [
            'label' => 'Páginas',
            'submenu_order' => [
                'select' => 'Gerenciar páginas',
                'insert', 
            ]
        ],

        'articles' => [
            'label' => 'Artigos',
            'submenu_order' => [
                'select' => 'Gerenciar artigos',
                'insert', 
                'categories'
            ]
        ],

        'contexts' => [
            'label' => 'Contextos',
            'submenu_order' => [
                'select' => 'Gerenciar contextos',
                'insert', 
            ]
        ],

        'medias' => [
            'label' => 'Mídias',
            'submenu_order' => [
                'select' => 'Galeria',
                'favicon', 
                'poster'
            ]
        ],

        'menus' => [
            'label' => 'Menus'
        ],

        'settings' => [
            'label' => 'Configurações',
            'submenu_order' => [
                'select' => 'Início',
                'media', 
                'options', 
                'reading', 
                'email', 
                'socialnet', 
                'seo', 
                'urls', 
                'templates'
            ]
        ],

        'admins' => [
            'label' => 'Administradores',
            'submenu_order' => [
                'select' => 'Gerenciar admins',
                'register'
            ]
        ],

        'comments' => [
            'label' => 'Comentários',
            'submenu_order' => [
                'select' => 'Gerenciar comentários'
            ]
        ],

        'users' => [
            'label' => 'Usuários',
            'submenu_order' => [
                'select' => 'Gerenciar usuários'
            ]
        ]
    ];

    /**
     * Links (silenciosos) ocultos 
     * Servem para manter submenus abertos e com classes do tipo .current, .active no item
     * Esses sao links de URLs que contem query string
     * Adicione os parametros de rota dentro do subarray de uma query string
     * */
    private $silents = [
        'params' => [
            'id' => [
                'admins/update',
                'comments/update', 
                'contexts/update',
                'medias',
                'pages/update',
                'articles/update',
                'articles/category',
                'users/update',
            ],
            'key' => [
                'contexts/section',
                'menus',
            ]
        ],
    ];


    public function list(): string {
        $html = [];

        $html[] = '<ul>';

        foreach ( $this->menu() as $item ) {
            $label       = $item['label']   ?? '';
            $href        = $item['href']    ?? '#';
            $attrs       = $item['attrs']   ?? '';
            $bullet      = $item['bullet']  ?? '';
            $icon        = $item['icon']    ?? '';
            $isub        = $item['submenu'] ?? [];
            $labelSelect = $item['label-select'] ?? false;
            
            $has_submenu = ! empty( $isub );
            $liClass     = $has_submenu ? ' class="hasub"' : '';

            // 1. Abertura do item principal
            $html[] = "<li{$liClass}><a href=\"{$href}\" {$attrs}>{$label} {$icon}{$bullet}</a>";

            if ( $has_submenu ) {
                $html[] = '<ul class="isub">';

                # Primeiro item do submenu
                if ( $labelSelect ) {
                    $html[] = "<li><a href=\"{$href}\">{$labelSelect}</a></li>";
                }

                foreach ( $isub as $sub ) {
                    $isSilent = isset($sub['silent']) && $sub['silent'];
                    $subLabel = $isSilent ? '&nbsp;' : ($sub['label'] ?? '');
                    $subHref  = $sub['href'] ?? '#';
                    $subClass = $isSilent ? ' class="sr"' : '';

                    // bloco em linha única para submenus simples
                    $html[] = "<li{$subClass}><a href=\"{$subHref}\">{$subLabel}</a></li>";
                }

                $html[] = '</ul>';
            }

            $html[] = '</li>';
        }

        $html[] = '</ul>';

        return implode( '', $html );
    }


    /**
     * Gera a estrutura do menu dinâmico baseado nas views existentes
     */
    public function menu(): array {
        $menu = [];

        foreach( $this->structure as $key => $config ) {
            $menu_item = [
                'label'   => $config['label'], # xxx
                'href'    => dash_url($key),
                'submenu' => $this->submenu( $key, $config['submenu_order'] ?? [] ),

                'label-select'   => ($config['submenu_order']['select'] ?? ''),
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
     * 
     * Na rota o $source eh referente a: 
     * - primeiro paramentro da URL
     * - o parent ou entidade a que o segundo parametro esta relacionado a executar
     * O $source geralmente vem seguido de um $action
     * 
     * @example : se fosse query strings nuas, seria algo como:
     * - dashboard/?source=articles&action=categories
     * - dashboard/?source=pages&action=update
     * 
     */


    /**
     * Gera subitens do menu baseado nas views existentes e ordem definida
     * parametro $source - Nome da fonte da rota
     * parametro $submenu_order - Ordem personalizada dos subitens
     */
    protected function submenu( string $source, array $submenu_order ): array {
        $submenu = [];

        # self::$views eh herdado da classe Router
        $routes = array_keys( self::$views );

        $children = array_filter( 
            $routes, fn($route) => str_starts_with($route, "{$source}/") 
        );

        $actions = [];

        foreach( $children as $child ) {
            $action = str_replace( "{$source}/", '', $child );

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
                    'label' => $this->menu_labels[$child] ?? $fallback,
                    'href'  => dash_url( $child )
                ];

                unset( $actions[$action] ); # Remove para nao duplicar depois
            }
        }

        return array_merge( $submenu, $this->silent($source) );
    }

    /**
     * returna array de itens fixos prontos para adicionar ao menu
     */
    private function fixed_items(): array {
        return [
            [
                'label' => 'Ver site',
                'href'  => site_url(),
                'attrs' => 'target="_blank"',
                'icon' => '<span icon="newtab" size="15" top="1.5"></span>'
            ],
            [
                'label' => 'Sair',
                'href'  => dash_url('logout'),
                'attrs' => 'label="Logout"',
                'icon' => '<span icon="logout" top="2"></span>'
            ]
        ];
    }

    private function silent( string $source ): array {
        $items = [];
        
        # encontra TODAS as rotas para este source
        $sourceRoutes = [];
        
        # Busca em 'params'
        foreach( $this->silents['params'] ?? [] as $param => $routes ) {
            foreach( $routes as $route ) {
                if( str_starts_with($route, $source . '/') ) {
                    $action = str_replace( $source . '/', '', $route );

                    $sourceRoutes[$action]['params'][] = $param;
                }
            }
        }
        
        # processa cada acao encontrada
        foreach( $sourceRoutes as $action => $config ) {
            $params = [];
            # parametros normais
            foreach( $config['params'] ?? [] as $param ) {
                $value = URL::Get($param);
                if( $value !== null && $value !== '' ) {
                    $params[$param] = $value;
                }
            }
            
            $query = $this->build_query_string($params);

            $items[] = [
                'label' => '',
                'href' => dash_url("{$source}/{$action}/{$query}"),
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