<?php
/**
 * Gerencia as relacoes entre paginas e categorias no banco de dados.
 *
 * Esta classe e responsavel por inserir, atualizar e deletar relacoes
 * na tabela 'relations', baseando-se em dados enviados via requisicoes HTTP (POST/GET).
 * Ela lida com a associacao de paginas a categorias e a manutencao
 * da integridade dessas relacoes, incluindo a remocao de duplicatas
 * e de associacoes desmarcadas.
 *
 * @package System\Hierarchy\Model
 */
class Relations {

    private PDO $conn;


    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }


    /**
     * Conta o numero de paginas que possuem relacao com uma categoria especifica
     *
     * Consulta as tabelas 'relations' e 'categories' para contar quantas entradas
     * existem para um 'slug' de categoria fornecido
     */
    public function num_added( string $catslug ): int {
        $cmd = $this->conn->prepare("
            SELECT COUNT(r.category_id) 
            FROM relations AS r
            JOIN categories AS c ON c.ID = r.category_id
            WHERE c.slug = ?
        ");
        $cmd->execute([ $catslug ]);

        return (int) $cmd->fetchColumn(); 
    }


    /**
     * Sincroniza a relacao entre um tipo de pagina com as categorias selecionadas. 
     * Marcou: INSERT: Desmarcou: DELETE; 
     * E tambem Remove duplicacoes caso aja: DELETE
     *
     * o metodo processa os dados enviados via POST (especificamente 'checkcat' e 'id-').
     * Insere novas relacoes de categoria para uma pagina especifica.
     * Apos a insercao, ele executa metodos para limpar duplicatas e remover
     * relacoes que foram desmarcadas (nao enviadas no POST).
     */
    public function synchronize( Assign $bind ): bool {
        $checked = array_map('intval', $bind->checked);

        # estado atual
        $cmd = $this->conn->prepare("
            SELECT category_id 
            FROM relations 
            WHERE type_id = ? AND type = ?
        ");
        $cmd->execute([ $bind->ID, $bind->type ]);

        $current = $cmd->fetchAll( PDO::FETCH_COLUMN );

        # ordenacao de arrays para comparacao
        sort($checked);
        sort($current);

        # nada mudou
        if( $checked === $current ) {
            return false;
        }

        # deleta registros antigos duplicados
        $this->delete_repeats();

        # deleta registros desmarcados 
        $this->delete_unchecked($bind);

        # insere soh o que, e se foi marcado
        $insert = array_diff($checked, $current);

        if( $insert ) {

            $cmd = $this->conn->prepare("
                INSERT INTO relations(type, type_id, category_id) VALUES(?, ?, ?)
            ");

            foreach( $insert as $catID ) {
                $cmd->execute([ $bind->type, $bind->ID, $catID ]);
            }
        }

        return true;
    }


    /**
     * Deleta todas as relacoes de uma pagina especifica.
     *
     * Este metodo e ativado por uma requisicao POST que contenha um parametro 'target_id'
     * Ele remove todas as entradas da tabela 'relations' que estao associadas
     * ao 'type_id' fornecido no POST de forma direta e eficiente.
     */
    public function delete_type( Assign $bind ): bool { 
        $cmd = $this->conn->prepare("DELETE FROM relations WHERE type_id = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    /**
     * Deleta todas as relacoes de uma categoria especifica.
     *
     * Semelhante ao `delete_related_type`, este metodo e ativado por POST com 'target_id'
     * Ele remove todas as entradas da tabela 'relations' que estao associadas
     * ao 'category_id' fornecido no POST de forma direta e eficiente
     */
    public function delete_category( Assign $bind ): bool {
        $cmd = $this->conn->prepare("DELETE FROM relations WHERE category_id = ?");
        $cmd->execute([ $bind->ID ]);

        return $cmd->rowCount() > 0;
    }


    /**
     * Remove entradas duplicadas na tabela 'relations'.
     *
     * Este metodo SQL remove todas as linhas duplicadas da tabela 'relations',
     * mantendo apenas a primeira ocorrencia (menor ID) para cada combinacao unica
     * de 'type_id' e 'category_id'.
     */
    private function delete_repeats(): bool {
        $cmd = $this->conn->prepare("
            DELETE r1 FROM relations r1, relations r2
            WHERE r1.ID > r2.ID
            AND r1.type_id = r2.type_id
            AND r1.category_id = r2.category_id
        ");

        $cmd->execute();

        return $cmd->rowCount() > 0;
    }


    /**
     * Remove relacoes de categoria que nao foram marcadas em uma requisicao POST.
     *
     * Compara as categorias previamente associadas a uma pagina com as categorias
     * atualmente selecionadas no formulario (enviadas via $_POST['checkcat']).
     * Todas as relacoes existentes que nao estao presentes no array 'checkcat'
     * serao deletadas do banco de dados para aquela 'type_id'.
     * Este metodo so age em requisicoes POST.
     */
    private function delete_unchecked( Assign $bind ): bool {
        $checked = array_map( 'intval', $bind->checked );

        $placeholders = implode( ',', array_fill(0, count($checked), '?') );

        $values = array_merge([ $bind->ID, $bind->type ], $checked);

        $cmd = $this->conn->prepare("
            DELETE FROM relations
            WHERE type_id = ? AND type = ? 
            AND category_id NOT IN ($placeholders)
        ");

        $cmd->execute($values);

        return $cmd->rowCount() > 0;
    }

}