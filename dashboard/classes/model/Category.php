<?php
/**
 * Gerencia as operacoes CRUD para categorias incluindo funcionalidade hierarquica 
 * e geracao de HTML para exibicao
 * 
 * Esse Model nao eh soh para guardar dados, ele sabe como esses dados precisam viver na interface.
 * Isso eh design por uso, nao por diagrama
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev | int.opuscore.dev
 * 
 * @package System\Model
 */

class Category extends Model {

    /**
     * Seleciona categorias do banco de dados com base nos parametros da URL.
     * Pode filtrar por 'type' e opcionalmente por 'id' .
     */
    public function select(): array {
        $list = [];
        if( URL::has('id') ) {
            $cmd = $this->conn->prepare("
                SELECT c.*, m.attachment FROM categories AS c 
                LEFT JOIN medias AS m ON m.related_id = c.ID AND m.related_type = ? 
                WHERE c.type = ? AND c.ID = ?
            ");
            $cmd->execute([ 
                'category-post', # tipo da midia (imagem) relacionada
                'post',          # tipo da categoria
                URL::int('id'),  # ID da categoria
            ]);
        }
        else {
            $cmd = $this->conn->prepare(
                "SELECT c.*, m.attachment FROM categories AS c 
                LEFT JOIN medias AS m ON m.related_id = c.ID AND m.related_type = ? 
                WHERE c.type = ?
            ");
            $cmd->execute([ 'category-post', 'post' ]);
        }
        while( $row = $cmd->fetch(PDO::FETCH_ASSOC) ) {
            $bind = new Assign;

            $bind->ID      = $row['ID'];
            $bind->name    = $row['name'];
            $bind->parent  = $row['parent'];
            $bind->slug    = $row['slug'];
            $bind->segment = $row['segment'];
            $bind->content   = $row['content'];
            $bind->date    = $row['created'];
            $bind->attachment = json_decode($row['attachment'] ?? '');
            $list[] = $bind;
        }

        return $list;
    }


    public function insert( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            INSERT INTO categories(type, name, parent, slug, segment, content, created)
            VALUES(?, ?, ?, ?, ?, ?, ?)
        ");
        $cmd->execute([
            $bind->type,
            $bind->name,
            $bind->parent,
            $bind->slug,
            $bind->segment,
            $bind->content,
            $bind->date,
        ]);
        $bind->LastID = $this->conn->lastInsertId();

        return $bind->LastID > 0;
    }


    public function update( Assign $bind ): bool {
        $columns = ['name', 'parent', 'slug', 'content', 'type'];

        /*if( ! parent::hasChanged('categories', $columns, $bind) ) {
            return false;
        }*/
        $columns = implode(' = ?, ', $columns) . ' = ?';
        $cmd = $this->conn->prepare("UPDATE categories SET $columns WHERE ID = ?");

        $cmd->execute([
            $bind->name,
            $bind->parent,
            $bind->slug,
            $bind->content,
            $bind->type,
            $bind->ID
        ]);
        
        return true;
    }


    public function delete( Assign $bind ): ?bool {
        $cmd = $this->conn->prepare(
            "SELECT 1 FROM categories WHERE parent = ? LIMIT 1"
        );
        $cmd->execute([ $bind->ID ]);
        $fetch = (int) $cmd->fetchColumn() > 0;
        if( $fetch ) {
            return null;
        }

        $cmd = $this->conn->prepare(
            "DELETE FROM categories WHERE type = ? AND ID = ?"
        );
        $cmd->execute([ $bind->type, $bind->ID ]);

        return $cmd->rowCount() > 0;
    }


    /**
     * Verifica se uma categoria com o mesmo nome e pai ja existe no banco de dados
     * para prevenir a criacao de categorias da mesma hierarquia duplicadas.
     * No modo de 'update' (quando 'id' esta na URL), ele exclui a propria categoria da verificacao.
     */
    public function exists( Assign $bind ): bool {
        if( URL::has('id') ) {
            # com 'id' indica (update)
            $sql = "SELECT 1 FROM categories 
            WHERE type = ? AND parent = ? AND name = ? AND ID != ? LIMIT 1";

            $params = [ $bind->type, $bind->parent, $bind->name, $bind->ID ];
        }
        else {
            # modo (insert)
            $sql = "SELECT 1 FROM categories WHERE type = ? AND parent = ? AND name = ? LIMIT 1";
            $params = [ $bind->type, $bind->parent, $bind->name ];
        }
        $cmd = $this->conn->prepare( $sql );
        $cmd->execute( $params );

        return (bool) $cmd->fetch();
    }

    
    /**
     * Gera e retorna uma string HTML contendo elementos `<option>` para um `<select>`,
     * representando categorias de forma hierarquica e indentada.
     */
    public function select_option(): string {
        $NOT_ID = URL::has('id') ? 'AND ID != ?' : '';
        # Busca todas as categorias para o tipo especificado na URL, ordenadas por pai e nome
        $cmd = $this->conn->prepare("
            SELECT ID, name, parent FROM categories 
            WHERE type = ? {$NOT_ID} 
            ORDER BY parent, name ASC
        ");

        $kind  = kind();
        $curID = URL::int('id');

        if( URL::has('id') ) {
            $cmd->execute([ $kind, $curID ]);
        }
        else {
            $cmd->execute([ $kind ]);
        }
        $categories = $cmd->fetchAll(PDO::FETCH_ASSOC);

        # Organiza as categorias em um array hierarquico onde a chave eh o ID do pai.
        $hierarchy = [];
        foreach( $categories as $cat ) {
            $hierarchy[$cat['parent']][] = $cat;
        }

        $html = '';
        $stack = []; # Pilha usada para processar a hierarquia de forma nao recursiva (iterativa).

        # Empilha as categorias raiz (aquelas sem pai, ou seja, parent = 0).
        # `array_reverse` eh usado para garantir a ordem correta na pilha (LIFO).
        if( ! empty($hierarchy[0]) ) {
            foreach (array_reverse($hierarchy[0]) as $rootCat) {
                $stack[] = ['cat' => $rootCat, 'level' => 0];
            }
        }

        # Processa a pilha ate que todas as categorias tenham sido visitadas.
        while( ! empty($stack) ) {
            $current = array_pop($stack); # Remove o item do topo da pilha.
            $cat = $current['cat'];
            $level = $current['level'];

            # Monta o elemento <option> com indentacao baseada no nivel hierarquico.
            $html .= sprintf(
                '<option %s value="%d">%s%s</option>',
                $this->option_selected($cat['ID']), # Verifica se deve ser 'selected' .
                $cat['ID'],
                str_repeat('– ', $level), # Adiciona hifens para indentacao visual.
                $cat['name']
            );

            # Se a categoria atual tiver filhos, empilha-os para serem processados.
            # `array_reverse` garante que os filhos sejam processados na ordem correta.
            if( ! empty($hierarchy[$cat['ID']]) ) {
                foreach( array_reverse($hierarchy[$cat['ID']]) as $child ) {
                    $stack[] = ['cat' => $child, 'level' => $level + 1]; # Incrementa o nivel para os filhos.
                }
            }
        }

        return $html;
    }

    /**
     * Verifica se uma categoria especifica deve estar pre-selecionada em um elemento `<select>`.
     * A categoria sera marcada como 'selected' se o ID fornecido corresponder
     * ao ID do pai da categoria atualmente em edicao (obtido da URL 'id').
     *
     * @param int $id O ID da categoria a ser verificada para o atributo 'selected' .
     * @return string A string 'selected' se a categoria deve ser pre-selecionada, caso contrario, uma string vazia.
     */
    public function option_selected( int $id ): string {
        # Obtem o ID da categoria que esta sendo editada via URL.
        $current_category_id = URL::int('id');

        # Busca o pai da categoria atualmente em edicao.
        $cmd = $this->conn->prepare("SELECT parent FROM categories WHERE ID = ?");
        $cmd->execute([ $current_category_id ]);
        $row = $cmd->fetch(PDO::FETCH_ASSOC);

        # Se a categoria pai da categoria em edicao for igual ao ID passado, marca como selecionado.
        $selected = ( $row && (int) $id === (int) $row['parent'] ) ? 'selected' : '';

        return $selected;
    }

    /**
     * Gera e retorna uma string HTML para uma tabela completa de categorias.
     * Inclui informacoes da categoria, hierarquia visual e botoes de acao (como exclusao).
     *
     * @return string O HTML completo da tabela de categorias.
     */
    public function select_table(): string {
        $relation  = Container::call('Relations');       // Para contar itens relacionados.

        # Busca todas as categorias para o tipo especificado na URL, ordenadas por pai e nome.
        $cmd = $this->conn->prepare("
            SELECT c.*, m.attachment FROM categories AS c 
            LEFT JOIN medias AS m ON m.related_id = c.ID AND m.related_type = ? 
            WHERE type = ? ORDER BY parent, name ASC
        ");
        $cmd->execute([ 'category-post', 'post' ]);
        $categories = $cmd->fetchAll( PDO::FETCH_ASSOC );

        # Monta array hierarquico para facilitar a organizacao da tabela.
        $hierarchy = [];
        foreach( $categories as $cat ) {
            $hierarchy[$cat['parent']][] = $cat;
        }

        $html = '';
        $stack = []; # Pilha para controle da iteracao hierarquica.

        # Empilha categorias raiz (parent=0) para iniciar a construcao da tabela.
        if( ! empty($hierarchy[0]) ) {
            foreach( array_reverse($hierarchy[0]) as $rootCat ) {
                $stack[] = ['cat' => $rootCat, 'level' => 0];
            }
        }

        # Processa a pilha para montar as linhas da tabela.
        while( ! empty($stack) ) {
            $current = array_pop($stack);
            $cat   = $current['cat'];
            $level = $current['level'];

            $attachment = json_decode($cat['attachment'] ?? '', true);
            $thumbnail = $attachment['thumb']['path'] ?? null;
            $fallback = ' fallback';
            if( is_string($thumbnail) && file_exists(UPLOAD_DIR . $thumbnail) ) {
                $thumbnail = upload_url($thumbnail);
                $fallback = null;
            }

            $href = dash_url( "posts/category/?id={$cat["ID"]}" );
            ### style="margin-left: ' . $level * 10 . 'px;"
            # Monta a linha da tabela (<tr>) com os dados da categoria e botoes de acao.
            $html .= 
            '<form method="POST" action="' . URL::current() . '">
                <tr>
                    <td class="thumb w60px' . $fallback . '">
                        <div>
                            <a href="' . $href . '">
                                <img src="' . $thumbnail . '" alt="" />
                            </a>
                        </div>
                    </td>
                    <td class="show">' 
                        . str_repeat(' – &nbsp; ', $level) 
                        . '<a href="' . $href . '">' . $cat["name"] . '</a>
                    </td>

                    <td class="fw300">' . $cat["slug"] . '</td>

                    <td>' . chronos_format( $cat['created'] ) . '</td>

                    <td class="txt_center">' . $relation->num_added( $cat['slug'] ) . '</td>

                    <td>
                        <button 
                            onclick="javascript: return confirm(`Vai mesmo deletar esta categoria?\n\nNão será possível excluir caso essa categoria possua sucessores.`)" 
                            class="input_false link delete fs23 txt_center" 
                            type="submit" name="action" value="delete"
                        >
                            <span icon="close" size="26" top="3"></span>
                        </button>

                        <input type="hidden" name="target_id" value="' . $cat["ID"] . '" />
                        <input type="hidden" id="target_type" name="target_type" value="category" />
                        <input type="hidden" name="media_type" value="category-post" />
                    </td>
                <tr>
            </form>';

            # Se a categoria atual tiver filhos, empilha-os para serem processados depois.
            if( ! empty($hierarchy[$cat['ID']]) ) {
                foreach( array_reverse($hierarchy[$cat['ID']]) as $child ) {
                    $stack[] = ['cat' => $child, 'level' => $level + 1];
                }
            }
        }
        
        return $html;
    }


    public function build_segment( int $id ): string {
        $slugs = [];
        $currentID = $id;

        # Constroi segmento hierarquico ( ex: grandparent/parent/child )
        while( $currentID > 0 ) {
            $cmd = $this->conn->prepare("SELECT slug, parent FROM categories WHERE ID = ?");
            $cmd->execute([ $currentID ]);
            $row = $cmd->fetch( PDO::FETCH_ASSOC );

            if( ! $row ) {
                break;
            }

            array_unshift( $slugs, $row['slug'] ); # Empilha slugs do mais alto ao mais baixo
            $currentID = (int) $row['parent'];     # Sobe na hierarquia
        }

        if( count($slugs) > 3 ) {
            $slugs = array_slice( $slugs, -3 );
        }

        return implode( '/', $slugs );
    }


    /**
     * Atualiza a coluna segment da tabela categories na insercao de uma categoria
     * apos outros campos da tabela serem inseridos ficando dentro da condicao: 
     * `if( $category->insert($assign) )`
     * 
     * Entao esse metodo Atualiza a categoria com o segmento calculado
     **/
    public function update_segment( string $segment, int $id ): bool {
        $cmd = $this->conn->prepare("
            UPDATE categories SET segment = ? WHERE ID = ?
        ");

        return $cmd->execute([ $segment, $id ]);
    }


    public function update_rebuild_segment( int $bindID ): string {
        $queue = [$bindID];
        $rebuild = '';

        while( ! empty($queue) ) {

            $id = array_shift($queue);

            # recalcula segment deste noh
            $segment = $this->build_segment($id);
            $this->update_segment( $segment, $id );

            if( $id === $bindID ) {
                $rebuild = $segment;
            }

            # busca filhos diretos
            $sql = $this->conn->prepare("SELECT ID FROM categories WHERE parent = ?");
            $sql->execute([ $id ]);

            while( $row = $sql->fetch(PDO::FETCH_ASSOC) ) {
                $queue[] = (int) $row['ID'];
            }
        }

        return $rebuild;
    }



    # Se categoria tem um parent, retorna o slug do mesmo
    public function parent( string $parent ): string {
        $cmd = $this->conn->prepare("SELECT slug FROM categories WHERE ID = ?");
        $cmd->execute([ $parent ]);
        $row = $cmd->fetch( PDO::FETCH_ASSOC );

        return $row ? $row['slug'] : '';
    }

}