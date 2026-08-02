<?php
declare( strict_types = 1 );
/**
 * Classe responsavel por gerar a estrutura HTML basica e a barra de ferramentas (toolbar)
 * do editor de texto "Punk", otimizando a velocidade de renderizacao ao inserir o HTML diretamente.
 *
 * @package System\Component
 */
Class Punk {


    public static function render( string $type, ?string $content = null ): string {
        $punk = '
        <div id="punk">' 
        . 
            self::toolbar() 
        . 
            '<textarea id="srcode" spellcheck="false"></textarea>
             <div id="pk-body" contenteditable="true" spellcheck="false"></div>
             <div id="foot"></div>

        </div>

        <textarea id="editor" name="content">' . $content . '</textarea>' 

        . self::popup_files( $type );

        return compress_HTML($punk);
    }


    public static function toolbar(): string {
        return '
        <div id="toolbar">'
            . self::block() 
            . self::inline() 
            . self::grid() 
            . self::align() 
            . self::link() 
            . self::divider() 
            . self::list() 
            . self::divider() 
            . self::quote() 
            . self::color() 
            . self::fontsize() 
            . self::divider() 
            . self::files() 
            . self::embed_video() 
            . self::divider() 
            . self::ruler() 
            . self::text() 
            . self::divider() 
            . self::switch_mode() 
        . '</div>';
    }


    public static function popup_files( string $type ): string {
        return '
        <div id="files" class="popup" role="dialog">
            <div class="modal">
                <div class="modal_header">
                    <div class="upload-area">
                        <div id="upload_filename" class="upload">
                            <input id="upload" type="file" name="upload" />
                            <label for="upload" class="btn">Escolher Arquivo</label>
                        </div>
                    </div>
                    <div class="controls">
                        <button type="button" icon="close" size="40" class="dismiss" aria-label="Click para fechar"></button>
                    </div>
                </div>
                <div class="modal_main">
                    <div id="callback">
                        <div id="gallery"></div>
                    </div>
                    <div id="box-insert">
                        <div id="details">
                            <input 
                                type="hidden" id="upload_type" 
                                name="upload_type" 
                                value="editor-' . $type . '" 
                            />
                            <input 
                                type="hidden" id="upload_id" 
                                name="upload_id" 
                                value="' . URL::int('id') . '" 
                            />
                        </div>
                        <button id="inserfile" type="button" class="btn xlg">Inserir Arquivo</button>
                    </div>
                </div>
            </div>
        </div>';
    }
    

    public static function block(): string {
        return '
        <div id="block" class="select">
            <button class="block open-options" title="Tags com formatação de bloco">Bloco</button>
            <div class="options">
                <button data-label="h2" ico="h2"></button>
                <button data-label="h3" ico="h3"></button>
                <button data-label="h4" ico="h4"></button>
                <button data-label="h5" ico="h5"></button>
                <button data-label="h6" ico="h6"></button>
                <button data-label="pre" ico="pre" title="Pré-formatado"></button>
                <button data-label="address"><address>Address</address></button>
            </div>
        </div>';
    }

    
    public static function inline(): string {
        return '
        <div id="inline" class="select">
            <button class="open-options" title="Elementos de formatação em linha">Inline</button>
            <div class="options">
                <button ico="bold" title="Negrito" data-label="strong"></button>
                <button ico="italic" title="Italico" data-label="em"></button>
                <button ico="code" title="Código em linha" data-label="code"></button>
                <button ico="underline" title="Sublinhado" data-label="u"></button>
                <button ico="strike" title="Riscado" data-label="s"></button>
                <button ico="mark" title="Destaque" data-label="mark"><mark>mark</mark></button>
            </div>
        </div>';
    }


    public static function grid(): string {
        return '
        <div id="grid" class="select">
            <button ico="grid" class="open-options" title="Inserir colunas de grid"></button>
            <div class="options">
                <button class="cn__x__" data-col="__x__"></button>
                <button class="cn___x_" data-col="___x_"></button>
                <button class="cn_x___" data-col="_x___"></button>
                <button class="cn_x_x_" data-col="_x_x_"></button>
            </div>
        </div>';
    }


    public static function align(): string {
        return '
        <div id="textalign" class="select">
            <button ico="aligncenter" class="open-options" title="Alinhamento de texto"></button>
            <div class="options">

                <button id="left" ico="alignleft" title="Esquerda" data-cmd="justifyLeft"></button>

                <button id="center" ico="aligncenter" title="Centro" data-cmd="justifyCenter"></button>

                <button id="right" ico="alignright" title="Direita" data-cmd="JustifyRight"></button>

                <button id="justify" ico="alignjustify" title="Justificar" data-cmd="justifyFull"></button>
            </div>
        </div>';
    }


    public static function link(): string {
        return '
        <div id="link">
            <button ico="link" title="Adicionar Link" data-label="a"></button>
        </div>';
    }
    

    public static function list(): string {
        return '
        <div id="orderedlist">
            <button ico="orderedlist" title="Lista numerada" data-label="ol"></button>
        </div>
        <div id="unorderedlist">
            <button ico="unorderedlist" title="Lista de marcadores" data-label="ul"></button>
        </div>';
    }
    

    public static function quote(): string {
        return '
        <div id="quote" class="select">
            <button ico="blockquote" class="open-options" title="Citações"></button>
            <div class="options">
                <button data-label="blockquote" ico="blockquote" title="Bloco de citação"></button>
                <button data-label="q" ico="quote" title="Linha de citação"></button>
                <button data-label="cite" ico="cite" title="Autoria de citação"> Caption</button>
            </div>
        </div>';
    }
    

    public static function color(): string {
        $colors = [
            "#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FFA500", "#E60077", "#808080",
            "#CC0000", "#00CC00", "#0000CC", "#E6E600", "#E69500", "#CC0069", "#777777",
            "#B30000", "#00B300", "#0000B3", "#CCCC00", "#CC8500", "#B3005C", "#666666",
            "#990000", "#009900", "#000099", "#B3B300", "#C37400", "#99004F", "#555555",
            "#800000", "#008000", "#000080", "#999900", "#996300", "#800042", "#444444",
            "#660000", "#006B00", "#000066", "#808000", "#805300", "#660035", "#333333",
            "#4D0000", "#004D00", "#00004D", "#666600", "#664200", "#4D0028", "#222222",
            "#330000", "#003300", "#000033", "#4D4D00", "#4C3200", "#33001A", "#111111",
            "#1A0000", "#001A00", "#00001A", "#333300", "#332100", "#1A000A", "#000000"
        ];
        $textcolor = '';
        foreach( $colors as $color ) {
            $textcolor .= '
            <button 
                title="' . $color . '" 
                style="background:' . $color . '" 
                data-color="' . $color . '">
            </button>';
        }

        return '
        <div id="color" class="select">
            <button ico="color" class="open-options" title="Cor da fonte"></button>
            <div class="options">' . $textcolor . '</div>
        </div>';
    }
    

    public static function fontsize(): string {
        $sizes = [
            "11px" => "0.688rem",
            "12px" => "0.75rem",
            "13px" => "0.813rem",
            "14px" => "0.875rem",
            "15px" => "0.938rem",
            "16px" => "1rem",
            "17px" => "1.063rem",
            "18px" => "1.125rem",
            "19px" => "1.188rem",
            "20px" => "1.25rem",
            "21px" => "1.131rem",
            "23px" => "1.438rem",
            "25px" => "1.563rem",
            "27px" => "1.688rem",
            "30px" => "1.875rem"
        ];
        $fontsize = '';
        foreach( $sizes as $px => $rem ) {
            $fontsize .= '
            <button title="Equivale a ' . $rem . '" data-size="' . $rem . '">
                ' . $px . '
            </button>';
        }

        return '
        <div id="fontsize" class="select">
            <button ico="fontsize" class="open-options" title="Tamanho da fonte"></button>
            <div class="options">' . $fontsize . '</div>
        </div>';
    }


    public static function files(): string {
        return '
        <div data-popup-modal="files" id="image" class="image files">
            <button ico="image" title="Inserir imagem"></button>
        </div>';
    }
    

    public static function embed_video(): string {
        return '
        <div id="video">
            <button ico="video" title="Embutir video"></button>
        </div>';
    }
    

    public static function ruler(): string {
        return '
        <div id="ruler">
            <button ico="ruler" title="Linha horizontal"></button>
        </div>';
    }
    
    # Gera o HTML para o botao de insercao de texto generico
    public static function text(): string {
        return '
        <div id="insertext">
            <button ico="text"></button>
        </div>';
    }
    
    
    # Gera o HTML para o seletor de modo (editor visual vs. codigo fonte).
    public static function switch_mode(): string {
        return '
        <div id="switch">
            <input id="switch-mode" type="checkbox">
            <label for="switch-mode" title="Ver código fonte" ico="srcode"></label>
        </div>';
    }
    

    public static function divider(): string {
        return '<div class="divider"></div>';
    }

    public static function clear_entry( string $value ): string {
        $clear = str_replace( ' data-wrap="file"', '', $value );
        $clear = str_replace( '<p><br>', '<p>', $value );
        $clear = str_replace( '<div><br>', '<div>', $value );

        return $clear;
    }
    
}