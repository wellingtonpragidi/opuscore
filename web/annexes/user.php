<?php
/**
 * Funcoes de saida para arquivo `user.php` do template
 * 
 * usar essas funcoes fora da rota de usuario (arquivo `user.php`) nao ira retornar nada 
 *  ou ate imprimi erros e warnings do php com debug ligado
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause @see LICENSE.txt
 * @docs       opuscore.dev
 * 
 * @package Output\Helpers
 */


/** user_title ⌵ ------------------------------------------------------------------
 * 
 * Diferente de outras funcoes helper `*_title()` 
 * essa nao tem a tag <h1> interna — por nao ter a necessidade de deixar oculto por regras de rotas
 * 
 * Exemplos:
 * 
 * `master_title()` 
 * — Seu uso mais comum sao nos arquivos article.php e page.php
 * Mas pode tambem ser usado em index.php, e exibido apenas quando em listagem de articles, 
 * entao ela oculta <h1> em outras rotas
 * 
 * `category_title()` 
 * — Se o template tiver um arquivo category.php seu uso seria apenas em listagem de articles por categoria
 * Mas assim como master_title, tambem pode ser usado em index.php, e exibido apenas quando em listagem de articles por categoria, 
 * entao ocultando <h1> em rotas com listagem de todos os articles e listagem de articles por busca (?q=)
 * 
 * @see https://internal/functions/user_title @doc x
 */
    function user_title( array $args = [] ): void {
        if( ! is_user() ) {
            opus_debug('Usando uma função de usuário fora do contexto da rota');
            return;
        }

        echo User::name();
    }

/* user_title … ------------------------------------------------------------------ */



/** user_since ⌵ ------------------------------------------------------------------ 
 * 
 * retorna o tempo decorrido desde o registro do usuario,
 * formatado como "ha x tempo atras".
 * 
 * @see https://opuscore.dev/output/calc_time @doc /since
 */

    function user_since(): void {
        try {
            $origin = new DateTime(User::created());

            # 'today' eh mais rapido e limpo do que `date('Y-m-d')`
            $target = new DateTime('today'); 

            # Verifica se a data do registro eh valida e nao futura
            if( $origin > $target ) {
                echo 'Data de registro inválida.';
                return;
            }

            $interval = $origin->diff($target);

            $y = (int) $interval->y;
            $m = (int) $interval->m;
            $d = (int) $interval->d;
            
            # se registrou hoje
            if( $y === 0 && $m === 0 && $d === 0 ) {
                echo 'Ingressou hoje.';
                return;
            }

            # para tratar o singular/plural
            $words = function( int $num, string $sing, string $plur ): string {
                $word = $num === 1 ? $sing : $plur;

                return "{$num} {$word}";
            };

            $parts = [];

            if( $y > 0 ) {
                $parts[] = $words($y, 'ano', 'anos');
            }
            if( $m > 0 ) {
                $parts[] = $words($m, 'mês', 'meses');
            }
            if( $d > 0 ) {
                $parts[] = $words($d, 'dia', 'dias');
            }

            # Se soh sobrou 1 elemento (ex: "5 dias" ou "1 mes")
            if( count($parts) === 1 ) {
                echo 'Membro há ' . $parts[0] . '.';
                return;
            }

            # Se tem mais de um elemento, junta os primeiros com virgula e o ultimo com "e"
            $last = array_pop($parts);

            echo 'Membro há ' . implode(', ', $parts) . ' e ' . $last . '.';
            return;
        }
        catch( Exception $e ) {

            echo 'Data de registro inválida.';
            return;
        }
    }

/* user_since … ------------------------------------------------------------------- */

     
/** user_* chronos/date ⌵ ------------------------------------------------------------------ */

    function user_registered( ?string $prefix = null ): void {
        echo $prefix . user_chronos('created');
    }

    function user_lastupdate( ?string $prefix = null ): void {
        echo $prefix . user_chronos('updated');
    }


    /**
     * @access private
     */
    function user_chronos( string $column ): string {
        $datecol = ($column === 'created') 
            ? User::created() 
            : ( ($column === 'updated') ? User::updated() : null );

        $dateset = chronos_setting();

        if( $dateset === 'inblock' ) {
            return chronos_format($datecol, 6);
        }

        $chronos = isset($datecol) ? date($dateset, strtotime($datecol)) : '';

        return chronos_translate( $chronos );
    }

/* user_* chronos/date … ------------------------------------------------------------------ */



/** user_comment_* ⌵ ------------------------------------------------------------------ */

    /**
     * numero de comentarios feitos pelo usuario
     */
    function user_comment_count(): void {
        $comment = Container::call('Comment');

        echo $comment->user_count();
    }

    /**
     * Lista os comentarios feitos pelo usuario,
     * com links e datas formatadas.
     */
    function user_commented_on( array $args = [] ): void {
        $comment   = Container::call('Comment');
        $commented = $comment->user_commented_on();
        
        if( empty($commented) ) {
            return;
        }

        $tag   = $args['tag'] ?? 'div';
        $attrs = ($args['attrs'] ?? null) ? " {$args['attrs']}" : null;

        # tipos 'date_format' : int de 1 a 6 OU string com um formato de data valido
        $dateformat = $args['date_format'] ?? chronos_setting();

        foreach( $commented as $show ) {
            echo '<', $tag, $attrs, '>';

            echo 'Comentou em ',
                 "<a href=\"{$show->URL}\">{$show->title}</a>",
                 ' no dia ',
                 '<span>', chronos_format($show->created, $dateformat), '</span>';

            echo '</', $tag, '>';
        }
    }

/** user_comment_* … ------------------------------------------------------------------ */



function user_image_fallback( $scope = 'avatar' ) {
    $size       = user_pic_sz($scope);
    $dimensions = 'width="' . $size . '" height="' . $size . '"';

    return '
        <img 
            src="' . dist_thumbnail('user.svg') . '" 
            alt="Usuário: ' . User::name() . '" ' . $dimensions . ' 
        />';
}


/** user_image_profile ⌵ ------------------------------------------------------------------ */

    function user_image_profile( array $args = [] ): void {
        $auth  = Container::call('Auth');

        $photo = Image::get_featured([ 
            'scope' => 'profile' 
        ]);

        echo '<figure id="user-profile-image">';
            
            # <img ... />
            echo isset($photo) 
                ? $photo 
                : user_image_fallback('profile');


            if( $auth->is_self() ) {
                echo '
                <form method="POST" action="' . URL::current() . '" enctype="multipart/form-data">
                    <input type="file" id="attachment" class="sr" name="attachment" />
                    <label for="attachment">' . ($args['display'] ?? 'Atualizar imagem') . '</label>

                    <div class="response"></div>
                </form>';
            }

        echo '</figure>';
    }

/** user_image_profile … ------------------------------------------------------------------ */



/** user_form_update ⌵ ------------------------------------------------------------------
 * 
 * 
 * Gera os formulario HTML para atualizar imagem, nome e nome de usuario
 * na tela de edicao do perfil
 */

    function user_form_update( array $args = [] ): void {
        $auth = Container::call('Auth');
        if( $auth->is_self() === false ) {
            return;
        }

        $action   = URL::current();
        $uid      = User::id();
        $username = User::username();
        $name     = User::name();

        $show_title = array_key_exists('show-title', $args) ? (bool) $args['show-title'] : true;

        $id        = $args['id'] ?? 'user-settings';
        $innerText = $args['innerText'] ?? 'Configurações';
        $tag       = $args['tag'] ?? 'strong';

        $title = ($show_title === true) 
            ? '<' . $tag . ' id="' . $id . '">' . $innerText . '</' . $tag . '>' 
            : null;
        
        echo <<<HTML
        {$title}
        <form id="user-form-updates" method="POST" action="{$action}" enctype="multipart/form-data">
            <div id="user-update-name" class="user-update">
                <label for="uname">Nome e Sobrenome</label>
                <input id="uname" type="text" name="uname" value="{$name}" />

                <div class="response"></div>
                <button type="submit" name="action" value="name">Salvar</button>
            </div>

            <div id="user-update-username" class="user-update">
                <label for="uusername">Nome de usuário</label>
                <input id="uusername" type="text" name="uusername" value="{$username}" />

                <div class="response"></div>
                <button type="submit" name="action" value="username">Salvar</button>
            </div>

        </form>
        HTML;
    }

/** user_form_update … ------------------------------------------------------------------ */
