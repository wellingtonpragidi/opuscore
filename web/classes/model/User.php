<?php
declare( strict_types = 1 );
/**
 * Consulta e atualiza dados publicos do perfil do usuario.
 * 
 * colunas:
 * ID, name, username, email, pswd, created, updated, content, token, nonce, status, approved
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @link https://opuscore.dev/classes/user
 * 
 * @package Output\Model\Interaction
 */

class User extends Model {

    private array $data = [];

    private static ?self $instance = null;

    public static function instance(): self {
        self::init();
        return self::$container->make('User');
    }


    public static function id(): int {
        return (int) self::instance()->target('ID') ?? 0;
    }

    public static function name(): string {
        return self::instance()->target('name') ?? '';
    }

    public static function username(): ?string {
        return self::instance()->target('username') ?? null;
    }

    public static function description(): ?string {
        return self::instance()->target('content') ?? null;
    }

    public static function meta_description(): string {
        $description = self::instance()->target('content') ?? '';
        if( ! $description ) {
            $description = SEO('user_description');
        }

        return text_summary_attr( $description );
    }

    public static function created(): ?string {
        return self::instance()->target('created') ?? null;
    }

    public static function updated(): ?string {
        return self::instance()->target('updated') ?? null;
    }

    # consulta colunas especificas do usuario com base na URL 
    private function target( string $column ): string|int|null {
        $columns = ['ID', 'name', 'username', 'created', 'updated', 'content'];

        if( ! in_array($column, $columns, true) ) {
            throw new OpusException( 
                OpusException::allowedColumns($column, 'target', 'User') 
            );
        }

        $key = $username = URL::param(1);

        # retorna null se nao for qualquer view de perfil de usuario 
        if( URL::param(0) !== user_base() ) {
            return null;
        }

        if( ! array_key_exists($key, $this->data) ) {
        
            $cmd = $this->conn->prepare("
                SELECT " . implode(', ', $columns) . " FROM users 
                WHERE username = ? LIMIT 1
            ");

            $cmd->execute([ $username ]);

            $this->data[$key] = $cmd->fetch( PDO::FETCH_ASSOC );
        }

        if( $this->data[$key] === false ) {
            return null;
        }

        # retorna null se nao for uma view desse usuario 
        if( $username !== $this->data[$key]['username'] ) {
            return null;
        }

        return $this->data[$key][$column] ?? null;
    }
    

    /** 
     * sempre que algo muda, atualizar o timestamp da coluna `updated` de ´users`
     * como imagens eh da entidade `medias` necessita esse metodo separado para 
     * atualizar campo `updated` ao inserir ou atualizar a foto/imagem do perfil
     */
    public function update_lastupdate( Auth $auth ): bool {
        $cmd = $this->conn->prepare("UPDATE users SET updated = ? WHERE ID = ?");

        $cmd->execute([ date('Y-m-d H:i:s'), $auth->id() ]);


        return $cmd->rowCount() > 0;
    }


    public function update_name( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            UPDATE users SET name = ?, updated = ? 
            WHERE ID = ?
        ");

        $cmd->execute([ 
            $bind->name, 
            $bind->updated, 
            $bind->ID 
        ]);

        return $cmd->rowCount() > 0;
    }


    # verifica se username ja existe 
    public function username_exists( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            SELECT 1 FROM users 
            WHERE username = ? AND ID != ? LIMIT 1
        ");

        $cmd->execute([ $bind->username, $bind->ID ]);

        return (bool) $cmd->fetchColumn();
    }

    # atualiza username do usuario 
    public function update_username( Assign $bind ): bool {
        $cmd = $this->conn->prepare("
            UPDATE users SET username = ?, updated = ? WHERE ID = ?
        ");

        $cmd->execute([ $bind->username, $bind->updated, $bind->ID ]);

        return $cmd->rowCount() > 0;
    }

    public function update_token( Assign $bind ): bool {
        return parent::updater( 'users', ['token'], $bind );
    }
    public function update_nonce( Assign $bind ): bool {
        return parent::updater( 'users', ['nonce'], $bind );
    }
    public function update_tokens( Assign $bind ): bool {
        return parent::updater( 'users', ['token', 'nonce'], $bind );
    }
}