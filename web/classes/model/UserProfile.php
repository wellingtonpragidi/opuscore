<?php
declare( strict_types = 1 );
/**
 * Classe Responsavel por consultar e atualizar os dados publicos do perfil do usuario,
 * como nome, descricao, username, datas e metadados. Usada principalmente
 * em paginas de perfil publico (`/usuario/nome`) e em componentes de exibicao.
 *
 * Nao acessa logica de autenticacao nem tokens ? atua apenas como interface de leitura e edicao.
 *
 * @uses classe Assign (para updates)
 * @uses Helpers: Ensure::string(), summary_attr(), URL::param()
 * 
 * @link https://opuscore.dev/classe-userprofile
 *
 * Mapa de funcionalidades:
 * ────────────────────────────────────────────────────────────────
 * Leitura de dados
 * - id(), name(), username(), email()
 * - description(), registered(), updated()
 * - meta_description(), querycreated()
 * 
 * Atualizacao
 * - update_from_picture()
 * - update_name(), update_username()
 * 
 * Verificacao
 * - user_page_logged()  → Verifica se URL acessada corresponde ao usuario logado
 * - user_has_picture()  → Verifica se usuario possui imagem cadastrada
 * - username_exists()   → Checa se username ja existe no banco
 * 
 * web
 *  ├── classes
 *  │      └── UserProfile.php
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\User\Model
 */
class UserProfile extends Model {


    private static array $cache = [];

    # manter 'url' sempre como ultimo item do array
    private const array PROFILE_COLUMNS = [
        'ID', 'name', 'username', 'created', 'updated', 'content'
    ];


    # consulta colunas especifico do usuario com base na URL 
    public function field( string $column ): string|int|null {
        if( ! in_array($column, self::PROFILE_COLUMNS, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'field', 'UserProfile') 
            );
        }

        $username = URL::param(1);
        
        # retorna null se nao for uma pagina de perfil de usuario 
        if( URL::param(0) !== user_base() || $username === '' ) {
            return null;
        }

        $key = 'user_profile:' . $username;

        if( ! array_key_exists($key, self::$cache) ) {
        
            $cmd = $this->conn->prepare("
                SELECT " . implode(', ', self::PROFILE_COLUMNS) . " FROM users 
                WHERE username = ? LIMIT 1
            ");

            $cmd->execute([ $username ]);

            self::$cache[$key] = $cmd->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        return self::$cache[$key][$column] ?? null;
    }
    



    /**
     * atualizacoes do perfil de usuario
     * 1. imagem de perfil
     * 2. nome "do" usuario
     * 3. nome "de" usuario (chave unica - exibida na URL)
     * */

	/** 
     * verifica se usuario possui imagem relacionada na tabela media 
     * @return int
     * */
    public function user_has_picture( int $user_id ): int {
        $cmd = $this->conn->prepare("
            SELECT u.ID, m.ID FROM users AS u LEFT JOIN medias AS m ON m.related_id = u.ID 
            WHERE m.related_type = ? AND u.ID = ?
        ");
        $cmd->execute([ 'user', $user_id ]);
        return $cmd->rowCount();
    }

    /** 
     * atualiza campo updated ao inserir ou atualizar imagem
     * */
    public function update_from_picture( Assign $bind ): bool {
        if( ! Model::hasChanged('users', 'updated', $bind) ) {
            return false;
        }
        $cmd = $this->conn->prepare("UPDATE users SET updated = ? WHERE ID = ?");
        $cmd->execute([ $bind->update, $bind->ID ]);

        return true;
    }

    /** 
     * atualiza nome do usuario 
     * @return int
     * */
    public function update_name( Assign $bind ): bool {
        $cmd = $this->conn->prepare("UPDATE users SET name = ?, updated = ? WHERE ID = ?");
        $cmd->execute([ $bind->name, $bind->update, $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    /** 
     * verifica se username ja existe 
     * */
    public function username_exists( string $username ): bool {
        $cmd = $this->conn->prepare("SELECT username FROM users WHERE username = ?");
        $cmd->execute([ $username ]);

        return $cmd->rowCount() > 0;
    }

    /** 
     * atualiza username do usuario 
     * */
    public function update_username( Assign $bind ): bool {
        $cmd = $this->conn->prepare("UPDATE users SET username = ?, updated = ? WHERE ID = ?");
        $cmd->execute([ $bind->username, $bind->update, $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

}