<?php
/**
 * Classe responsavel por gerenciar a paginacao de listagens de dados
 * em diferentes secoes do painel de controle (comentarios, paginas, estatisticas, usuarios).
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 *
 * @package System\Core
 */
class Pagination {

    private int $total, $perpage, $current;


    public function __construct( int $totalrecords, int $perpage ) {
        $this->total   = max( 0, $totalrecords ); # garante que registro nao sejam negativos/invalidos
        $this->perpage = max( 1, $perpage ); # garante que registro nao sejam negativos/invalidos ou 0

        $page = (int) ( $_GET['pg'] ?? 1 );
        $this->current = max( 1, $page );
    }


    /**
     * Calcula o offset (deslocamento) para a consulta SQL de paginacao
     * @example
     * $this->offset = ( new Pagination( $this->count(), per_page('xxxs')) )->offset();
     */
    public function offset(): int {
        return ( $this->current - 1 ) * $this->perpage;
    }


    /**
     * gera o HTML da navegacao paginada para views
     * Exibe botoes para:
     * - primeira pagina
     * - paginas anteriores
     * - numeros de pagina
     * - paginas seguintes
     * - ultima pagina
     * redireciona para a ultima pagina valida caso 'pagina' seja maior que o total de paginas
     * @example
     * $pagination = new Pagination( Count::table(), per_page('xxxs') );
     * echo $pagination->render();
     */
    public function render(): string {
        # calcula o numero total de paginas (offsets) arredondado para cima
        $totalpages = max( 1, (int) ceil($this->total / $this->perpage) );

        # se limite por pagina for menor que a quantidade de registros, a paginacao nao eh exibida
        if( $this->total <= $this->perpage ) {
            return '';
        }

        # redireciona para a ultima pagina valida caso 'pg' seja maior que o total de paginas
        if( $this->current > $totalpages ) {
            header( 'Location: ' . $this->link($totalpages) );
            exit;
        }

        $html  = '<nav id="pagination">';

        # texto com o numero da pagina atual e o total de paginas
        $html .= '<div class="total-page">' . $this->current . ' / ' . $totalpages . '</div>';

        # botao ir/voltar para a primeira pagina (se pagina atual for a primeira, nao tem href)
        if( $this->current > 1 ) {
            $html .= '<a class="first-page" href="' . $this->link(1) . '"><span>&laquo;</span></a>';
        }
        else {
            $html .= '<a class="first-page"><span class="disabled">&laquo;</span></a>';
        }

        # botao pagina anterior (sem href se estiver na 1ª pagina)
        if( $this->current > 1 ) {
            $html .= '<a class="prev" href="' . $this->link($this->current - 1) . '"><span>&lsaquo;</span></a>';
        }
        else {
            $html .= '<a class="prev"><span class="disabled">&lsaquo;</span></a>';
        }

        # botoes numericos (exibe maximo 5 botoes)
        $html .= '<div class="numeric-pagination">';
        $start = max( 1, $this->current - 2 );
        $end   = min( $totalpages, $this->current + 2 );

        for( $i = $start; $i <= $end; $i++ ) {
            if( $i === $this->current ) {
                $html .= '<a class="current"><span>' . $i . '</span></a>';
            }
            else {
                $html .= '<a href="' . $this->link($i) . '"><span>' . $i . '</span></a>';
            }
        }
        $html .= '</div>';

        # botao proxima paginas (sem href caso esteja na ultima pagina)
        if( $this->current < $totalpages ) {
            $html .= '<a class="next" href="' . $this->link($this->current + 1) . '"><span>&rsaquo;</span></a>';
        }
        else {
            $html .= '<a class="next"><span class="disabled">&rsaquo;</span></a>';
        }

        # button vai para a ultima pagina (se ja estiver na ultima, ancora sem href)
        if( $this->current < $totalpages ) {
            $html .= '<a class="last-page" href="' . $this->link($totalpages) . '"><span>&raquo;</span></a>';
        }
        else {
            $html .= '<a class="last-page"><span class="disabled">&raquo;</span></a>';
        }

        return $html . '</nav>';
    }



    /**
     * Monta a url para uma pagina especifica da paginacao
     * @example
     * $this->link( (int) page number )
     */
    private function link( int $page ): string {
        $params = $_GET;

        if( $page > 1 ) {
            $params['pg'] = $page;
        }
        else {
            unset( $params['pg'] );
        }

        $query = http_build_query( $params );

        return $query ? dash_url(URL::param(0) . '/?' . $query) : dash_url(URL::param(0));
    }

}