<?php
declare( strict_types = 1 );
/**
 * Gerencia a criacao, edicao, exclusao e exibicao de menus e itens de menu de navegacao
 *
 * Esta classe interage com os arrays do arquivo storage/menus.php 
 * e outras tabelas para obter dados e vincular
 * 
 * Modulo: Menus
 * Entidade: Menu
 * Variavel com um registro: $menu
 * Variavel com varios registros: $menus
 * URL: /menus/
 * Views: menus/
 * Storage: menus.php (armazena os menus)
 * Tabela: menus (armazena itens de menus)
 * 
 * @package System\Model
 * @subpackage \Builder
 */


class Menu {

    private static ?PDO $conn = null;

    private static ?array $cache = null;


    private static function init(): void {
        if( self::$conn === null ) {
            self::$conn = Container::call('Connection');
        }
    }


    public static function load(): ?array {

        if( self::$cache !== null ) {
            return self::$cache;
        }

        $file = STORAGE_DIR . 'menus.php';

        # se $file nao existir deixa o PHP lancar warning de `include` em include_file_vars()
        # nada de !is_file() ou !file_exists() return [] (array vazio)
        # nem aqui e nenhum outro lugar que chama o metodo Provider::include_file_vars em sequencia
        $vars = Provider::include_file_vars($file);

        self::$cache = array_filter($vars, 'is_array');

        return self::$cache;
    }

    public static function exists( string $key ): bool {
        return array_key_exists($key, self::load());
    }


    /**
     * Exclui um menu e todos os seus itens associados do sistema.
     * A exclusao eh realizada usando uma transacao para garantir a atomicidade
     * ( 
     *     a ideia eh, ou tudo eh excluido, ou nada, 
     *     porem existe a chance minima de rewrite nao reescrever o arquivo 
     *     isso eh aceitavel e visivel, usuario recria 
     * )
     */
    public static function delete( string $var ): bool {
        self::init();

        # primeiro carrega registros de menu no arquivo depois... 
        # verifica $var (variavel que armazena o array a ser deletado) se nao existir, retorna false
        $file = STORAGE_DIR . 'menus.php';
        $vars = Provider::include_file_vars($file);

        if( ! isset($vars[$var]) ) {
            return false;
        }

        # segundo exclui itens relacionados na tabela `menus`
        try {
            self::$conn->beginTransaction();

            $cmd = self::$conn->prepare("DELETE FROM menus WHERE name = ?");
            $cmd->execute([$var]);

            self::$conn->commit();

        } 
        catch( PDOException $e ) {

            self::$conn->rollBack();
            return false;
        }

        # agora arquivo
        unset( $vars[$var] );

        $result = ArrayExport::rewrite($vars, $file);

        if( ! $result ) {
            return false;
        }

        self::clear();

        return true;
    }
   

    public static function update( string $var, string $new_var, array $array_data ): bool {
        $file = STORAGE_DIR . 'menus.php';

        $vars = Provider::include_file_vars($file);

        if( ! isset($vars[$var]) ) {
            return false;
        }

        unset($vars[$var]);

        $update = false;

        $delete = ArrayExport::rewrite($vars, $file);
        $insert = ArrayExport::apply($new_var, $array_data, 'menus');
 
        if( $insert ) {
            $update = true;
            self::clear();
        }

        return $update;
    }


    /*
    public static function keys(): array {
        $keys = [];
        foreach( self::load() as $menu ) {
            if (isset($menu['key'])) {
                $keys[] = $menu['key'];
            }
        }

        return $keys;
    }*/


    public static function key(): ?string {
        $menus = self::load();
        $active_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null);

        return $menus[$active_menu]['key'] ?? null;
    }

    public static function name(): ?string {
        $menus = self::load();
        $active_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null);

        return $menus[$active_menu]['title'] ?? null;
    }
    /**
     * @deprecated Sera substituido por name() -- assim como a chave 'title'
     */
    public static function title(): ?string {
        $menus = self::load();
        $active_menu = $_GET['key'] ?? ($_COOKIE['last_menu'] ?? null);

        return $menus[$active_menu]['title'] ?? null;
    }



    public static function clear(): void {
        self::$cache = null;
    }




    /**
     * 
     * 
     * CRUD tabela `menu`
     **/

    /**
     * insercao recursiva de uma arvore de itens marcados por checkbox
     *
     * Esta funcao eh usada para importar uma estrutura de menu, inserindo
     * os itens e seus filhos no banco de dados.superior
     */
    public static function add_checked_items( 
        ?string $menu_name, array $items, int $parent = 0 ): int {

        self::init();

        $conn = self::$conn;

        $cmd = $conn->prepare("
            INSERT INTO menus (name, parent, type, related_id, label, url, sort) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $next_sort = self::next_sort( $menu_name, $parent );

        $inserted = 0;

        foreach( $items as $index => $item ) {
            $related = (int) ($item['related_id'] ?? 0);
            $type    = $item['type'] ?? '';
            $sort    = $next_sort + $index;
            $label   = '';
            $url     = '';

            if( $type === 'home-page' ) {
                $related = 0;
                $type    = 'home-page';
                $label   = 'Home';
                $url     = URL::root();
                $sort    = 0;
            }
            else if( $type === 'page' ) {
                $label = self::get_entity_title( $related, 'pages' );
                $url   = self::get_entity_url( $related, 'pages' );
            }
            else if( $type === 'category' ) {
                $label = self::get_entity_title( $related, 'categories' );
                $url   = self::get_entity_url( $related, 'categories' );
            }
            
            if( empty($label) || empty($url) ) {
                continue;
            }
            
            $cmd->execute([ 
                $menu_name, 
                $parent, 
                $type, 
                $related, 
                $label, 
                $url, 
                $sort 
            ]);

            if( $cmd->rowCount() > 0 ) {
                $inserted++;
            }

            $lastID = (int) $conn->lastInsertId();
            # Se o item tiver filhos, chama a funcao recursivamente para inserir os filhos.
            if( isset($item['children']) && is_array($item['children']) ) {

                $inserted += self::add_checked_items(
                    $menu_name,
                    $item['children'],
                    $lastID
                );
            }
        }

        return $inserted;
    }

    public static function add_item_custom( array $args ): bool {
        self::init();
        $cmd = self::$conn->prepare("
            INSERT INTO menus (name, type, label, url, sort) VALUES (?, ?, ?, ?, ?)
        ");
        $cmd->execute( $args );

        return $cmd->rowCount() === 1;
    }

    public static function add_item_auth( ?string $menu_name, string $label ): bool {
        self::init();
        $cmd = self::$conn->prepare("
            INSERT INTO menus (name, type, label, url, sort) VALUES (?, ?, ?, ?, ?)
        ");
        $sort = self::next_sort( $menu_name );

        $cmd->execute([ $menu_name, 'auth', $label, '#', $sort ]);

        return $cmd->rowCount() === 1;
    }

    /**
     * Atualiza a ordem e a hierarquia dos itens de menu no banco de dados.
     * Utilizado para salvar as alteracoes de arrastar e soltar na interface do gerenciador.
     *
     * @param array $items Um array de itens de menu, cada um contendo ID, parent e sort.
     */
    public static function update_order_and_hierarchy( array $items ): void {
        self::init();

        $cmd = self::$conn->prepare("UPDATE menus SET parent = ?, sort = ? WHERE ID = ?");

        foreach( $items as $item ) {
            $cmd->execute([
                $item['parent'] ?? 0,
                $item['sort'] ?? 0,
                $item['id']
            ]);
        }
    }
    
    # Atualiza o rotulo e a URL do item no banco de dados
    public static function update_menu_item( array $args ) {
        self::init();
        $cmd = self::$conn->prepare("UPDATE menus SET label = ?, url = ? WHERE ID = ?");

        return $cmd->execute( $args );
    }

    /**
     * SELECT: para busca o parent_id
     * UPDATE: para atualizar filhos de item pai removido
     * E por fim DELETE
     */
    public static function delete_menu_item( int $id ): ?bool {
        self::init();

        $cmd = self::$conn->prepare("SELECT parent FROM menus WHERE id = ?");
        $cmd->execute([ $id ]);
        $parent = $cmd->fetchColumn();

        if( $parent === false ) {
            return null;
        }

        $cmd = self::$conn->prepare("UPDATE menus SET parent = ? WHERE parent = ?");
        $cmd->execute([ $parent, $id ]);


        $cmd = self::$conn->prepare("DELETE FROM menus WHERE id = ?");
        return $cmd->execute([ $id ]);
    }


    /**
     * 
     * 
     * Metodos publicos para exibicao na view.
     */

    /**
     * Busca os itens de um menu e os renderiza em uma estrutura HTML de lista hierarquica
     * para exibicao no painel de controle (com suporte a subniveis e edicao).
     *
     * @uses Menu::get_menu_items() Para buscar os itens do menu.
     * @uses Menu::build_tree() Para organizar os itens em arvore.
     * @uses Menu::render_tree_html() Para renderizar o HTML da estrutura de edicao.
     *
     * @param string $menu_name O nome (slug) do menu a ser renderizado.
     * @return void Imprime o HTML do menu diretamente na saida.
     */
    public static function render_menu_tree( ?string $menu_name ): string {
        $items = self::get_menu_items( $menu_name );
        $tree = self::build_tree( $items );

        return self::render_tree_html( $tree );
    }

    /**
     * Verifica se existem itens associados a um determinado menu.
     */
    public static function items_exists( ?string $current_menu ): bool {
        self::init();
        $cmd = self::$conn->prepare("SELECT 1 FROM menus WHERE name = ? LIMIT 1");
        $cmd->execute([ $current_menu ]);

        return (bool) $cmd->fetch();
    }




    public static function next_sort( ?string $menu_name, int $parent = 0 ): int {
        self::init();
        $cmd = self::$conn->prepare("SELECT MAX(sort) FROM menus WHERE name = ? AND parent = ?");
        $cmd->execute([ $menu_name, $parent ]);

        return (int) $cmd->fetchColumn() + 1;
    }

    /**
     * Metodos de uso interno da classe Menu.
     */

    /**
     * Renderiza recursivamente a estrutura HTML de uma arvore de itens de menu
     * para o painel de gerenciamento.
     *
     * Este metodo gera o HTML que permite arrastar e soltar itens, exibir detalhes
     * e botoes de acao (editar, excluir).
     *
     * @param array $tree A arvore de itens de menu (geralmente gerada por `build_tree()`).
     * @param int $depth Nivel de profundidade atual para classes CSS e logica de recursao.
     */
    private static function render_tree_html( array $tree, int $depth = 0 ): string {
        if( empty($tree) ) {
            return '';
        }
        $html = '<ul class="menu-list depth-'. $depth .'" data-level="'. $depth .'">' . PHP_EOL;
        foreach( $tree as $item ) {
            $has_children = isset( $item['children'] );
            $li_class = $has_children ? 'item item-hasub' : 'item';
            $li_class = " class=\"$li_class\"";
            # Garante que a URL seja segura, mesmo que esteja vazia.
            $item_url = ! empty($item['url']) ? Ensure::URL($item['url']) : '';
            $html .= '<li'. $li_class
                . ' data-id="'. (int) $item['ID'] .'"'
                . ' data-parent="'. (int) $item['parent'] .'"'
                . ' data-label="'. Ensure::string($item['label']) .'"'
                . ' data-type="'. Ensure::string($item['type']) .'"'
                . ' data-related-id="'. (int) $item['related_id'] .'"'
                . ' data-url="'. $item_url .'"'
                . ' data-sort="'. (int) $item['sort'] .'"'
                . '>' . PHP_EOL;
            # Conteudo visual do item de menu, incluindo arrastar, expandir e formulario de edicao.
            $html .= '<div class="menu-item-wrapper accordion expand">' . PHP_EOL;
            $html .= '<span class="drag">'. $item['label'] .'</span>';
            $html .= '<button class="acc_btn" data-expand="expand_item_'. $item['ID'] .'"></button>';
            $html .= '<div class="actions acc_panel">' . PHP_EOL;
            $html .= '<div class="acc_content">' . PHP_EOL;
            $html .= '<form id="menu_items_form_'. $item['ID'] .'" class="menu_items_form" method="POST" action="'. URL::current() .'?edit-item='. $item['ID'] .'">';
            $html .= '<label for="item-'. $item['ID'] .'">Titulo</label>
                      <input id="item-'. $item['ID'] .'" type="text" name="edit_label" value="'. $item['label'] .'" />';
            $html .= '<label for="url-'. $item['ID'] .'" class="mt15">URL:</label>
                      <input id="url-'. $item['ID'] .'" type="url" class="sm" placeholder="https://" name="edit_url" value="'. $item['url'] .'" />';
            $html .= '<input class="edit_id" type="hidden" name="edit_id" value="'. $item['ID'] .'">';
            $html .= '<button class="btn sm mt25 btn_edit" name="save_item" type="button">Atualizar</button>
                <div class="response mt5 fs15 txt_success"></div>';

            $html .= '<button type="button" class="input_false link delete btn_delete" name="delete_id"><span icon="trash" size="18" top="2"></span> Excluir</button>';
            $html .= '</form>';
            $html .= '</div></div></div>' . PHP_EOL;
            # Renderiza os filhos (subitens) recursivamente, se houver.
            $html .= $has_children
                ? self::render_tree_html( $item['children'], $depth + 1 )
                : '<ul class="menu-list depth-'. ($depth + 1) .'"></ul>';

            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul>' . PHP_EOL;

        return $html;
    }

    /**
     * Transforma uma lista plana de itens de menu em uma estrutura de arvore hierarquica.
     * ha um metodo igual em web
     *
     * $items Uma lista plana de itens de menu, geralmente do banco de dados.
     * $parent O ID do pai para o qual buscar os filhos. Nulo para itens de nivel superior.
     */
    private static function build_tree( array $items, int $parent = 0 ): array {
        $branch = [];
        foreach( $items as $item ) {
            # Verifica se o item atual eh filho do pai especificado.
            if( (int) $item['parent'] === (int) $parent ) {
                # Recursivamente busca os filhos deste item.
                $children = self::build_tree( $items, (int) $item['ID'] );
                if( $children ) {
                    $item['children'] = $children; # Adiciona os filhos ao item.
                }
                $branch[] = $item; # Adiciona o item (com seus filhos, se houver) ao ramo.
            }
        }

        return $branch;
    }

    /**
     * Busca os itens de um menu especifico no banco de dados
     * Os itens sao ordenados pela coluna `sort`
     * ha um metodo igual em web
     *
     * @param $menu_name O nome (slug) do menu cujos itens serao buscados.
     * Um array de arrays associativos, cada um representando um item de menu.
     */
    private static function get_menu_items( ?string $menu_name ): array {
        self::init();
        $cmd = self::$conn->prepare("SELECT * FROM menus WHERE name = ? ORDER BY sort ASC");
        $cmd->execute([ $menu_name ]);

        return $cmd->fetchAll( PDO::FETCH_ASSOC );
    }



    /**
     * Adiciona ao menu
     * Consultas e conteudo de outras tabelas do banco de dados
     * 
     * 
     */

    /**
     * Gera e imprime o HTML de uma lista de paginas disponiveis para adicao ao menu
     */
    public static function list_page_items(): string {
        self::init();
        $html = self::HTML_list_items_header( 'Paginas', 'page' );

        $html .= '<li>
        <input id="home-page" type="checkbox" name="add_item[]" data-type="home-page" />
        <label for="home-page"><span>Página inicial</span></label>
        </li>';

        $cmd = self::$conn->prepare("
            SELECT ID, title FROM pages ORDER BY title ASC
        ");
        $cmd->execute();
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $html .= '<li>';
            $html .= '<input id="item-page-'. $row['ID'] .'" type="checkbox" name="add_item[]" value="'. $row['ID'] .'" data-type="page" />';
            $html .= '<label for="item-page-'. $row['ID'] .'"><span>'. $row['title'] .'</span></label>';
            $html .= '</li>';
        }

        $html .= self::HTML_list_items_footer( 'page' );

        return $html;
    }

    /**
     * Gera e imprime o HTML de uma lista de categorias disponiveis para adicao ao menu
     */
    public static function list_category_items(): string {
        self::init();
        $html = self::HTML_list_items_header( 'Categorias', 'category' );

        $cmd = self::$conn->prepare("
            SELECT ID, name, parent 
            FROM categories WHERE type = 'article' ORDER BY parent, name ASC
        ");/*, slug*/
        $cmd->execute();

        $rows = $cmd->fetchAll( PDO::FETCH_ASSOC );

        $hierarchy = [];
        foreach( $rows as $cat ) {
            $hierarchy[$cat['parent']][] = $cat;
        }

        $stack = array_map( function( $cat ) {
            return ['cat' => $cat, 'level' => 0];
        }, $hierarchy[0] ?? [] );

        $prevlevel = 0;
        while( ! empty($stack) ) {
            $current = array_pop($stack);
            $cat = $current['cat'];
            $level = $current['level'];

            if( $level < $prevlevel ) {
                $html .= str_repeat( '</ul></li>', $prevlevel - $level );
            }
            $html .= '<li>';
            $html .= '<input id="item-cat-'. $cat['ID'] .'" 
                type="checkbox" name="add_item[]" value="'. $cat['ID'] .'" data-type="category" 
            />';
            $html .= '<label for="item-cat-'. $cat['ID'] .'"><span>'. $cat['name'] .'</span></label>';

            if( ! empty($hierarchy[$cat['ID']]) ) {
                $html .= '<ul>';
                foreach( array_reverse($hierarchy[$cat['ID']]) as $child ) {
                    array_push( $stack, ['cat' => $child, 'level' => $level + 1] );
                }
            }
            else {
                $html .= '</li>';
            }
            $prevlevel = $level;
        }
        $html .= ( $prevlevel > 0 ) ? str_repeat( '</ul></li><!-- level -->', $prevlevel ) : '';

        $html .= self::HTML_list_items_footer( 'category' );

        return $html;
    }

    /**
     * Gera o HTML do cabecalho para as listas de itens disponiveis para o menu
     * Utilizado internamente por `list_page_items()` e `list_category_items()`
     */
    private static function HTML_list_items_header( string $title, string $item_type ): string {
        $html  = '<button class="acc_btn" data-collapse="collapse_itens_'. $item_type .'">'. $title .'</button>';
        $html .= '<div id="source-'. $item_type .'" class="menu-source acc_panel">';
        $html .= '<div class="acc_content">';
        $html .= '<ul class="checkboxes-list scrollbar ckb">';

        return $html;
    }
 
    /**
     * Gera o HTML do rodape para as listas de itens disponiveis para o menu.
     * Inclui um botao para adicionar os itens checados ao menu.
     **/
    private static function HTML_list_items_footer( string $item_type ): string {
        $html = '<button class="btn sm right mt25 mr10 add_checked_items" name="add_itens_'. $item_type .'" type="button" value="'. $item_type .'">Adicionar ao menu</button>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }



    # Retorna titulo/nome de entidades com base no nome da tabela do DB e coluna ID
    private static function get_entity_title( int $id, string $table ): string {

        self::init();

        if( $table !== 'pages' && $table !== 'categories' ) {
            return '';
        }

        $column = ($table === 'pages') ? 'title' : 'name';

        $cmd = self::$conn->prepare("SELECT $column FROM $table WHERE ID = ?");
        $cmd->execute([ $id ]);

        $value = $cmd->fetchColumn();
        return ($value === false) ? '' : $value;
    }


    /**
     * Retorna a URL completa de entidades com base no nome da tabela do DB e coluna ID
     * 
     * @todo Necessita condicao hierquica para URLs de articles category/slug e pages parent/page
     */
    private static function get_entity_url( int $id, string $table ): string {
        self::init();

        if( $table !== 'pages' && $table !== 'categories' ) {
            return '';
        }

        $cmd = self::$conn->prepare("SELECT segment FROM $table WHERE ID = ?");
        $cmd->execute([ $id ]);

        $value = $cmd->fetchColumn();
        $field = ($value === false) ? '' : $value;

        $pathname = ($table === 'pages') ? $field : category_base() . '/' . $field;

        return URL::root($pathname);
    }

}