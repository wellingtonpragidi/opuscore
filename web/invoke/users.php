<?php

/**
 * 
 * 
 * 
 * Esse arquivo nao eh chamado em local algum
 * Aguardando termino da atualizacao por parte do responsavel por User* 
 *
 */



/**
 * retorna o tempo decorrido desde o registro do usuario,
 * formatado como "ha x tempo atras".
 */
function since(): void {
    echo calc_time( created() );
}

/**
 * numero de comentarios feitos pelo usuario
 */
function comment_count(): void {
    $comment = Container::call('Comment');
    echo $comment->user_count();
}


/**
 * Lista os comentarios feitos pelo usuario,
 * com links e datas formatadas.
 */
function commented_on(): void {
    $comments = Container::call('Comment');
    
    if( ! $comments->where() ) {
        return;
    }
    
    foreach( $comments->where() as $show ) {
        $date = chronos_format( $show->created, 2 );
        echo "<p>
            Comentou em <a href=\"{$show->URL}\">{$show->title}</a> no dia <span>{$date}</span>
        </p>";
    }
}

    /**
     * Exibe a ultima atualizacao do perfil, com ou sem HTML.
     * @param $html | se false so exibe a data, padrao true
     */
    static function lastupdate( bool $html = true ): void {
        $lastupdate = chronos_format( self::$instance->profile->lastupdate(), 2 );
        echo $html 
        ? '<p class="lastupdate">Ultima atualização: '. $lastupdate .'</p>'
        : $lastupdate;
    }

    /**
     * Gera os formularios HTML para atualizar imagem, nome e nome de usuario.
     * Usado na tela de edicao do perfil.
     */
    static function settings_form(): void {
        $html = self::open_form('edit_image', true);
        $html .= '<input id="upload" type="file" name="upload" />
        <label for="upload">Atualizar imagem de perfil</label>
        <input id="imgname" type="hidden" name="imgname" 
            value="' . self::$instance->profile->username() . '" />
        <input id="imgtitle" type="hidden" name="imgtitle" 
            value="' . self::$instance->profile->name() . '" />';
        $html .= self::beforeend();
        $html .= '</form>';

        $html .= self::open_form('edit_name');
        $html .= '<label for="name">Nome</label>
        <input id="name" type="text" name="name" value="'. self::$instance->profile->name() .'" autocomplete="off" />';
        $html .= self::beforeend('name');
        $html .= '</form>';

        $html .= self::open_form('edit_username');
        $html .= '<label for="username">Nome de usuário</label>
        <p>Nomes de usuários estão sujeitos a verificação.</p>
        <input id="username" type="text" name="username" value="'. self::$instance->profile->username() .'" autocomplete="off" />';
        $html .= self::beforeend('username');
        $html .= '</form>';

        echo $html;
    }

    /**
     * Gera a abertura de formulario com ID e enctype se necessario.
     * @param $id_attr - seletor id do form | string
     * @param bool $enctype | se true, adiciona suporte a upload. Parao false
     * @return string
     */
    private static function open_form( string $id_attr, bool $enctype = false ): string {
        $enctype = ( $enctype === true ) ?: 'enctype="multipart/form-data"';
        return '<form id="'. $id_attr .'" method="POST" action="'. URL::current() .'" '. $enctype .'>';
    }

    /**
     * Adiciona campos ocultos e botao de envio ao formulario.
     * @param string $response | atributo ID de resposta opcional
     * @return string
     */
    private static function beforeend( string $response = '' ): string {
        $html = '<input class="getid" type="hidden" name="getid" value="'. self::$instance->profile->id() .'" />';
        if( ! empty($response) ) {
            $html .= '<div id="response-' . $response . '"></div>
            <button class="btn sm" type="submit">Salvar</button>';
        }
        return $html;
    }

/**
 * Verifica se o usuario autenticado esta autorizado a acessar configuracoes
 * na pagina de perfil atual (baseado na URL).
 */
function user_authorized(): bool {
    $sts = Container::call('UserStatus');

    if( user_base() !== URL::param(0) ) {
        return false;
    }

    return $sts->('username') === URL::param(1);
}