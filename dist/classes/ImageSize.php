<?php
class ImageSize {

    /**
     * Escopos reservados (jah eh uso padrao do sistema)
     * 
     * page:: wide / larger / minor  
     * article:: wide / larger / minor / thumb  
     * categories >
     * category-article:: plain / thumb  
     * user:: profile / avatar 
     * editores punk >
     * editor-page, editor-article, editor-context:: original
     * 
     * E todos com excecao a `user` tem o escopo reservado 'system'
     */
    private const RESERVED_SCOPES = [
        'original', 'wide', 'larger', 'minor', 'thumb', 'plain', 'profile', 'avatar', 'system'
    ];


    # tipos = type para scope-s
    private static array $registered = [
        'article'     => [],
        'page'     => [],
        'category-article' => [],

        'editor-page'    => [],
        'editor-article'    => [],
        'editor-context' => [],

        'favicon'  => [],

        'user'     => [],
    ];


    /**
     * Adiciona novo escopo de imagem
     * @param $type : Nome de um tipo registrado `$registered`
     * @param $scope : Nome do escopo - identificador unico do redimensionamento e corte
     * @param $width e $height : Largura e Altura de redimensionamento e corte do novo escopo
     * 
     */
    public static function append( array $args ): void {

        if( ! isset($args['type'], $args['scope'], $args['width'], $args['height']) ) {

            return;
        }

        $_type   = $args['type'];
        $_scope  = $args['scope'];
        $_width  = $args['width'];
        $_height = $args['height'];

        if( ! is_string($_type) || ! is_string($_scope) ) {

            return;
        }

        if( $_type === '' || $_scope === '' ) {

            return;
        }

        if( filter_var($_width, FILTER_VALIDATE_INT) === false ||
            filter_var($_height, FILTER_VALIDATE_INT) === false ) {

            return;
        }
        
        # escopos reservados do nucleo
        if( in_array($_scope, self::RESERVED_SCOPES, true) ) {
            return;
        }

        # tipo invalido
        if( ! isset(self::$registered[$_type]) ) {
            return;
        }

        # tamanho invalido
        if( (int) $_width <= 0 || (int) $_height <= 0 ) {
            return;
        }

        self::$registered[$_type][$_scope] = [

            'width'  => (int) $_width,

            'height' => (int) $_height
        ];
    }



    public static function dimensions( string $type ): array {

        $sizes = self::default_dimensions($type);

        if( isset(self::$registered[$type]) ) {

            foreach( self::$registered[$type] as $scope => $data ) {

                $sizes[$scope] = $data;
            }
        }

        # remove desativados width ou height = 0 (como em todos os casos)
        foreach( $sizes as $scope => $data ) {
            # unset nao se aplica a imagens que nao sao manipuladas por ImageHandler
            if( ($data['preserve_dimensions'] ?? false) ) {
                continue;
            }

            if( $data['width'] <= 0 || $data['height'] <= 0 ) {
                unset( $sizes[$scope] );
            }
        }

        return $sizes;
    }



    private static function default_dimensions( string $type ): array {
        $system       = system_image_size();
        $user_profile = user_pic_sz('profile');
        $user_avatar  = user_pic_sz('avatar');

        return match( $type ) {
            'article' => [
                'wide'   => [ 'width' => article_w(), 'height' => article_h() ],

                'larger' => [ 'width' => article_lg_w(), 'height' => article_lg_h() ],

                'minor'  => [ 'width' => article_md_w(), 'height' => article_md_h() ],

                'thumb'  => [ 'width' => article_sm_w(), 'height' => article_sm_h() ],
                
                'system' => [ 'width' => $system, 'height' => $system ],
            ],
            'page' => [
                'wide'   => [ 'width' => page_w(), 'height' => page_h() ],

                'larger' => [ 'width' => page_lg_w(), 'height' => page_lg_h() ],

                'minor'  => [ 'width' => article_md_w(), 'height' => article_md_h() ],
                
                'system' => [ 'width' => $system, 'height' => $system ],
            ],

            'category-article' => [
                'plain'   => [ 'width' => cat_w(), 'height' => cat_h() ],
                
                'thumb'  => [ 'width' => cat_sm_w(), 'height' => cat_sm_h() ],
                
                'system' => [ 'width' => $system, 'height' => $system ],
            ],

            'user' => [
                'profile' => [ 'width' => $user_profile, 'height' => $user_profile ],
                'avatar'  => [ 'width' => $user_avatar, 'height' => $user_avatar ],
            ],

            'editor-article',
            'editor-page',
            'editor-context' 
            => [
                'original' => [ 'preserve_dimensions' => true ],

                'system' => [ 'width' => $system, 'height' => $system ],
            ],

            default => [],
        };
    }

}