<?php
/**
 * Classe com metodos estaticos auxiliares para realizar contagens de registros 
 * para uso rapido e pratico em diferentes tabelas do banco de dados. 
 * 
 * @return int | numero total de registros de cada tabela que usam contagem
 * 
 * @package System/Model/Utils
 */
class Count {

    private static PDO $conn;

    private static function init(): void {
        self::$conn = Container::call('Connection');
    }


    public static function admins(): int {
        return self::totRows( 'admins' );
    }


    public static function categories(): int {
        return self::totRows( 'categories' );
    }


    public static function comments(): int {
        return self::totRows( 'comments' );
    }


    public static function menus(): int {
        return count( Menu::load() );
    }

    public static function contexts(): int {
        $vars = Context::join();
        return count($vars);
    }


    public static function pages(): int {
        self::init();
        $cmd = self::$conn->prepare("SELECT COUNT(*) FROM pages");
        $cmd->execute();

        return (int) $cmd->fetchColumn();
    }
    

    public static function posts(): int {
        self::init();
        $cmd = self::$conn->prepare("SELECT COUNT(*) FROM posts");
        $cmd->execute();

        return (int) $cmd->fetchColumn();
    }


    /**
     * total de visitas registradas na tabela 'statistics' oq representa o total de page views
     */
    public static function statistics(): int {
        return self::totRows( 'statistics' );
    }

    
    public static function users(): int {
        return self::totRows( 'users' );
    }


    /**
     * Exibe a contagem de posts/paginas com mensagens no singular, plural ou zero registros.
     * - Se houver 1 registro: exibe no singular (ex: "1 Post").
     * - Se houver > 1 registros: exibe no plural (ex: "5 Posts").
     * - Se houver 0 registros: exibe mensagem customizada com link para adicionar novo.
     */
    public static function selects(): string {
        $target = URL::param(0);
        $href   = dash_url("{$target}/insert");

        $empty = "Nenhum registro de %s. <a href=\"$href\">Publicar</a>";

        $message = [
            'posts' => [ 
                sprintf($empty, 'post'),
                "1 Post", 
                "%d Posts" 
            ],
            'pages' => [ 
                sprintf($empty, 'página'),
                "1 Página", 
                "%d Páginas" 
            ]
        ];
        
        $count = self::$target();

        $index = min( $count, 2 );

        return sprintf( $message[$target][$index], $count );
    }


    private static function totRows( string $table ): int {
        self::init(); # Garante que a conexao com o banco de dados esteja inicializada.
        $cmd = self::$conn->prepare("SELECT COUNT(*) FROM $table");
        $cmd->execute();

        return (int) $cmd->fetchColumn();
    }

}