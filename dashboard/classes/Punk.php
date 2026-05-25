<?php
/**
 * Classe responsavel por gerar a estrutura HTML basica e a barra de ferramentas (toolbar)
 * do editor de texto "Punk", otimizando a velocidade de renderizacao ao inserir o HTML diretamente.
 *
 * @package System\Component
 */
Class Punk {

    /**
     * Construtor da classe Punk.
     * Gera e imprime a estrutura HTML basica do editor de texto,
     * incluindo a area de edicao, o textarea para o codigo fonte e a toolbar.
     * Tambem invoca o metodo para gerar o popup de gerenciamento de arquivos.
     *
     * @return void
     */
    public function __construct() {
        echo '<div id="punk">';
            $this->toolbar();
            echo '<textarea id="srcode" spellcheck="false"></textarea>
            <div id="pk-body" contenteditable="true" spellcheck="false"></div>
            <div id="foot"></div>
        </div>';
        $this->popup_files();
    }

    /**
     * Gera e imprime a barra de ferramentas (toolbar) do editor Punk.
     * A toolbar eh composta por diversos botoes e seletores para funcionalidades de edicao de texto.
     *
     * @return void
     */
    public function toolbar(): void {
        echo '<div id="toolbar">';
            $this->block();
            $this->inline();
            $this->grid();
            $this->align();
            $this->link();
            $this->divider();
            $this->list();
            $this->divider();
            $this->quote();
            $this->color();
            $this->fontsize();
            $this->divider();
            $this->files();
            $this->embed_video();
            $this->divider();
            $this->ruler();
            $this->text();
            $this->divider();
            $this->switch_mode();
        echo '</div>';
    }

    /**
     * Gera e imprime o HTML para o popup de gerenciamento de arquivos (galeria de imagens/uploads).
     * Inclui areas para upload, exibicao de arquivos e botoes de controle.
     *
     * @return void
     */
    public function popup_files(): void {
        echo '<div id="files" class="popup" role="dialog">
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
                        <div id="details"></div>
                        <button id="inserfile" type="button" class="btn xlg">Inserir Arquivo</button>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o seletor de formatacao de bloco (headings, pre, address).
     *
     * @return void
     */
    public function block(): void {
        echo '<div id="block" class="select">
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

    /**
     * Gera e imprime o HTML para o seletor de formatacao em linha (negrito, italico, codigo, sublinhado, riscado, destaque).
     *
     * @return void
     */
    public function inline(): void {
        echo '<div id="inline" class="select">
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

    /**
     * Gera e imprime o HTML para o seletor de layout de grid (colunas).
     *
     * @return void
     */
    public function grid(): void {
        echo '<div id="grid" class="select">
            <button ico="grid" class="open-options" title="Inserir colunas de grid"></button>
            <div class="options">
                <button class="cn__x__" data-col="__x__"></button>
                <button class="cn___x_" data-col="___x_"></button>
                <button class="cn_x___" data-col="_x___"></button>
                <button class="cn_x_x_" data-col="_x_x_"></button>
            </div>
        </div>';
    }

    /**
     * Gera e imprime o HTML para o seletor de alinhamento de texto.
     *
     * @return void
     */
    public function align(): void {
        echo '<div id="textalign" class="select">
            <button ico="aligncenter" class="open-options" title="Alinhamento de texto"></button>
            <div class="options">
                <button id="left" ico="alignleft" title="Esquerda" data-cmd="justifyLeft"></button>
                <button id="center" ico="aligncenter" title="Centro" data-cmd="justifyCenter"></button>
                <button id="right" ico="alignright" title="Direita" data-cmd="JustifyRight"></button>
                <button id="justify" ico="alignjustify" title="Justificar" data-cmd="justifyFull"></button>
            </div>
        </div>';
    }

    /**
     * Gera e imprime o HTML para o botao de insercao de link.
     *
     * @return void
     */
    public function link(): void {
        echo '<div id="link">
            <button ico="link" title="Adicionar Link" data-label="a"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para os botoes de lista ordenada e nao ordenada.
     *
     * @return void
     */
    public function list(): void {
        echo '<div id="orderedlist">
            <button ico="orderedlist" title="Lista numerada" data-label="ol"></button>
        </div>
        <div id="unorderedlist">
            <button ico="unorderedlist" title="Lista de marcadores" data-label="ul"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o seletor de citacoes (blockquote, q, cite).
     *
     * @return void
     */
    public function quote(): void {
        echo '<div id="quote" class="select">
            <button ico="blockquote" class="open-options" title="Citações"></button>
            <div class="options">
                <button data-label="blockquote" ico="blockquote" title="Bloco de citação"></button>
                <button data-label="q" ico="quote" title="Linha de citação"></button>
                <button data-label="cite" ico="cite" title="Autoria de citação"> Caption</button>
            </div>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o seletor de cores de fonte.
     * Contem uma paleta predefinida de cores.
     *
     * @return void
     */
    public function color(): void {
        $colors = array(
            "#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FFA500", "#E60077", "#808080",
            "#CC0000", "#00CC00", "#0000CC", "#E6E600", "#E69500", "#CC0069", "#777777",
            "#B30000", "#00B300", "#0000B3", "#CCCC00", "#CC8500", "#B3005C", "#666666",
            "#990000", "#009900", "#000099", "#B3B300", "#C37400", "#99004F", "#555555",
            "#800000", "#008000", "#000080", "#999900", "#996300", "#800042", "#444444",
            "#660000", "#006B00", "#000066", "#808000", "#805300", "#660035", "#333333",
            "#4D0000", "#004D00", "#00004D", "#666600", "#664200", "#4D0028", "#222222",
            "#330000", "#003300", "#000033", "#4D4D00", "#4C3200", "#33001A", "#111111",
            "#1A0000", "#001A00", "#00001A", "#333300", "#332100", "#1A000A", "#000000"
        );
        $textcolor = '';
        foreach( $colors as $color ) {
            $textcolor .= '<button title="'. $color .'" style="background:'. $color .'" data-color="'. $color .'"></button>';
        }
        echo '<div id="color" class="select">
            <button ico="color" class="open-options" title="Cor da fonte"></button>
            <div class="options">'.$textcolor.'</div>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o seletor de tamanho de fonte.
     * Oferece tamanhos predefinidos em pixels e seus equivalentes em rem.
     *
     * @return void
     */
    public function fontsize(): void {
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
            $fontsize .= '<button title="Equivale a '. $rem .'" data-size="'. $rem .'">'. $px .'</button>';
        }
        echo '<div id="fontsize" class="select">
            <button ico="fontsize" class="open-options" title="Tamanho da fonte"></button>
            <div class="options">'. $fontsize .'</div>
        </div>';
    }

    /**
     * Gera e imprime o HTML para o botao de insercao de arquivos (imagens).
     * Este botao abre o popup de gerenciamento de arquivos.
     *
     * @return void
     */
    public function files(): void {
        echo '<div data-popup-modal="files" id="image" class="image files">
            <button ico="image" title="Inserir imagem"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o botao de embutir video.
     *
     * @return void
     */
    public function embed_video(): void {
        echo '<div id="video">
            <button ico="video" title="Embutir video"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o botao de insercao de linha horizontal (ruler).
     *
     * @return void
     */
    public function ruler(): void {
        echo '<div id="ruler">
            <button ico="ruler" title="Linha horizontal"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o botao de insercao de texto.
     *
     * @return void
     */
    public function text(): void {
        echo '<div id="insertext">
            <button ico="text"></button>
        </div>';
    }
    
    /**
     * Gera e imprime o HTML para o seletor de modo (editor visual vs. codigo fonte).
     *
     * @return void
     */
    public function switch_mode(): void {
        echo '<div id="switch">
            <input id="switch-mode" type="checkbox">
            <label for="switch-mode" title="Ver código fonte" ico="srcode"></label>
        </div>';
    }
    
    /**
     * Gera e imprime um divisor visual na barra de ferramentas.
     *
     * @return void
     */
    public function divider(): void {
        echo '<div class="divider"></div>';
    }
    
}