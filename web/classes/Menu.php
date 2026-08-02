<?php
declare( strict_types = 1 );
/**
 * Classe de saida do sistema Seek, responsavel por consultar registros de banco de dados
 *   funcionando em conjunto com `SeekPreparer` e `Selection`
 * e fornecer acesso aos campos da linha atual
 *
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Navigation\Tree
 */

class Menu {

    /**
     * Gera a saida HTML para a exibicao da lista de menu
     */
    public static function list( array $args = [] ): void {
        $defaults = [
            'name'              => '',
            'list_id'           => null,
            'list_class'        => 'menu',
            'parent_item_class' => 'hasub',
            'sublist_class'     => 'isub',
            'auth'              => [
                'list_id'    => null,
                'list_class' => 'menu-auth',
                'label_login'    => null, # 'Entrar'
                'label_register' => null, # 'Registrar'
            ],
        ];

        # Mescla os argumentos padrao com os fornecidos.
        $args = array_merge( $defaults, $args );

        # Obtem os itens do menu com base no nome.
        $items = self::get_menu_items( $args['name'] );

        # Se nao houver itens, nao renderiza nada.
        if( empty($items) ) {
            return;
        }

        # Organiza os itens em uma estrutura de arvore hierarquica.
        $tree = self::build_tree( $items );

        $label = '';
        foreach( $tree as $value ) {
            if( $value['type'] === 'auth' ) {
                $label .= $args['label_login'] ?? $value['label'] ?: 'Entrar';
                $label .= $args['label_register'] ?? $value['label'] ?: 'Registrar';
            }
        }

        $file_cached = "menus/{$args['name']}.html";

        # arquivo de cache existe, imprime direto
        if( ($html = Cache::getFile($file_cached)) !== null ) {

            # Substitui {{AUTH_ITEM}} dinamicamente
            $html = str_replace(
                '{{AUTH_ITEM}}', self::render_auth_item($args['auth']), $html
            );

            echo $html;
            return;
        }

        # gera o menu normalmente
        $html = self::render_menu( $tree, $args );

        $html = compress_HTML( $html );

        # salva o cache
        Cache::setFile( $file_cached, $html );

        $html = str_replace(
            '{{AUTH_ITEM}}', self::render_auth_item($args['auth']), $html
        );

        echo $html;
    }


    /**
     * empilha a estrutura HTML de um menu de navegacao.
     *
     * Este metodo, como todos eh usado internamente por `list()` para construir as listas
     * `<ul>` e itens `<li>` com base na arvore de itens.
     *
     * @param $items Uma arvore de itens de menu, geralmente gerada por `build_tree()`.
     */
    private static function render_menu( array $items, array $args ): string {
        $html = '';

        # Cada item na pilha representa um nivel do menu
        $stack = [[
            'items' => $items,
            'index' => 0,
            'depth' => 0
        ]];

        while( ! empty($stack) ) {
            $level = &$stack[count($stack) - 1];
            $items = &$level['items'];
            $depth = $level['depth'];

            # Se eh o comeco desse nivel, abre o <ul>
            if( ! isset($level['opened']) ) {
                $level['opened'] = true;

                $list_id = '';
                $list_class = '';

                if( $depth === 0 && ! empty($args['list_id']) ) {
                    $list_id = ' id="' . $args['list_id'] . '"';
                }

                if( $depth === 0 && ! empty($args['list_class']) ) {
                    $list_class = ' class="' . $args['list_class'] . '"';
                } 
                elseif( $depth > 0 && ! empty($args['sublist_class']) ) {
                    $list_class = ' class="' . $args['sublist_class'] . '"';
                }

                $html .= "<ul{$list_id}{$list_class}>" . PHP_EOL;
            }

            # se acabaram os itens nesse nivel, fecha a <ul> e remove da pilha
            if( $level['index'] >= count($items) ) {
                $html .= str_repeat("  ", $depth) . "</ul>" . PHP_EOL;
                array_pop( $stack );
                continue;
            }
            # Pega o item atual
            $item = $items[$level['index']];
            $level['index']++;

            $has_children = ! empty( $item['children'] );
            $li_class = $has_children && ! empty( $args['parent_item_class'] )
                ? ' class="' . $args['parent_item_class'] . '"'
                : '';

            if( $item['type'] === 'auth' ) {
                $html .= '{{AUTH_ITEM}}';
                continue;
            }

            $html .= "<li{$li_class}>" . PHP_EOL;
            $html .= "<a href=\"{$item['url']}\">{$item['label']}</a>" . PHP_EOL;

            if( $has_children ) {
                # Empilha o novo nivel (submenu)
                $stack[] = [
                    'items' => $item['children'],
                    'index' => 0,
                    'depth' => $depth + 1
                ];
            } 
            else {
                $html .= "</li>" . PHP_EOL;
            }
        }

        return $html;
    }


    /**
     * Transforma uma lista plana de itens de menu em uma estrutura de arvore hierarquica
     *
     * @param $items Uma lista plana de itens de menu, geralmente do banco de dados.
     * @param $parent O ID do pai para o qual buscar os filhos. Nulo para itens de nivel superior.
     * @return array A arvore de itens de menu.
     */
    private static function build_tree( array $items, int $root = 0 ): array {
        $tree = [];
        $lookup = [];

        # Primeiro, cria um mapa rapido de ID -> item
        foreach( $items as $item ) {
            $item['children'] = [];
            $lookup[$item['ID']] = $item;
        }

        # organiza a hierarquia
        foreach( $lookup as $id => &$item ) {
            if( (int) $item['parent'] === (int) $root ) {
                # eh um item de topo
                $tree[] = &$item;
            } 
            elseif( isset($lookup[$item['parent']]) ) {
                # Adiciona este item a lista de filhos do pai
                $lookup[$item['parent']]['children'][] = &$item;
            }
        }

        unset( $lookup ); # libera memoria

        return $tree;
    }


    /**
     * Busca os itens de um menu especifico no banco de dados
     * Os itens sao ordenados pela coluna `sort`
     *
     * @param $menu_name O nome (slug) do menu cujos itens serao buscados
     * @return Um array de arrays associativos, cada um representando um item de menu
     */
    private static function get_menu_items( string $menu_name ): array {
        $conn = Container::call('Connection');
        $cmd = $conn->prepare("SELECT * FROM menus WHERE name = ? ORDER BY sort ASC");
        $cmd->execute([ $menu_name ]);

        return $cmd->fetchAll( PDO::FETCH_ASSOC );
    }


    private static function render_auth_item( array $args = [] ): string {
        $auth = Container::call('Auth');

        $login    = $args['label_login'] ?? 'Entrar';
        $register = $args['label_register'] ?? 'Registrar';

        $list_id    = $args['list_id'] ?? null;
        $list_class = $args['list_class'] ?? 'menu-auth';

        $attrs = '';

        if( $list_id ) {
            $attrs .= ' id="' . $list_id . '"';
        }

        if( $list_class ) {
            $attrs .= ' class="' . $list_class . '"';
        }

        $access_url = URL::root('access/?action=');

        if( $auth->is_logged() ) {
            return
                '<ul' . $attrs . '>
                    <li class="menu-item-auth logged">
                        <a href="' . $auth->URL() . '">' . $auth->logged()->name . '</a>
                    </li>
                    <li class="menu-item-auth logged">
                        <a href="' . $access_url . 'logout">Sair</a>
                    </li>
                </ul>';
        }

        return
            '<ul' . $attrs . '>
                <li class="menu-item-auth">
                    <a href="' . $access_url . 'login">' . $login . '</a>
                </li>
                <li class="menu-item-auth">
                    <a href="' . $access_url . 'register">' . $register . '</a>
                </li>
            </ul>';
    }


}