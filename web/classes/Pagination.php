<?php 
declare( strict_types = 1 );
/**
 * classe responsavel por:
 * - calcular o offset para paginação de posts
 * - gerar o html da navegacao paginada com links para paginas anteriores, proximas e numeros de pagina
 * - montar urls para cada pagina considerando parametros get como 'q' (busca) e 'pg' (pagina)
 * 
 * usa funcoes auxiliares como posts_per_page() e url::param() para trabalhar com parametros da url
 * 
 * @system     Opus Core — Sistema Gerenciador Web
 * @author     Wellington Pragidi
 * @copyright  webship
 * @license    MPL 2.0 + Commons Clause (LICENSE.txt)
 * @docs       opuscore.dev
 * 
 * @package Output\Navigation
 */

class Pagination {

    private $conn;

    public function __construct( PDO $conn ) {
        $this->conn = $conn;
    }
    
    /**
     * calcula o offset para consulta dos posts baseado no parametro 'pg' da url
     * se 'pg' nao estiver definido ou for 1, retorna 0 (primeira pagina)
     *
     * @return int offset para usar em consulta sql (limit offset, posts_per_page())
     */
    public function post_paginate() {
        $iG = isset($_GET['pg']) ? $_GET['pg'] : 1;
        return ( $iG == '' || $iG == 1 ) ? 0 : ( $iG * posts_per_page() - posts_per_page() );
    }

    /**
     * gera o html da navegacao paginada para posts
     * exibe botoes para primeira pagina, paginas anteriores, numeros de pagina, proximas e ultima pagina
     * redireciona para a ultima pagina caso 'pg' seja maior que o total de paginas
     * usa a funcao curl() para montar urls corretamente considerando parametros de busca
     *
     * @return string
     */
    public function post_paginator() {
        $post = Container::call('Post'); 
        $GETpg = URL::Get('pg');
        $number_records_rounded = ceil( $post->total_records() / posts_per_page() );
        $prev = empty( $GETpg ) ? '' : intval( $GETpg ) - 1;
        $next = intval( $GETpg ) + 1;
        $penult = $number_records_rounded - 1;
        $current = 'class="current" title="Pagina atual" onclick="return false;"';
        if( $GETpg > $number_records_rounded ) {
            header('Location:' . $this->curl($number_records_rounded) );
            exit;
        }
        if( posts_per_page() < $post->total_records() ) {
            $html = '';
            $CPN = $GETpg; # cpn = current page number
            if( ! $CPN ) {
                $CPN = 1;
            }
            $html .= '<nav id="pagination" class="clean">';
                /* < texto com o numero da pagina atual e o total de paginas > */ 
                $html .= '<div class="total-page">'. $CPN .' / '.$number_records_rounded.'</div>';

                # < button volta para a primeira pagina > 
                if( $this->post_paginate() >= 1 ) {
                    $html .= '<a class="first-page" href="'. $this->curl(false). '" title="Primeira página"><span>&laquo;</span></a>';
                }
                else {
                    $html .= '<a><span class="disabled">&laquo;</span></a>';
                }

                # < buttons paginas anteriores >
                if( $GETpg ) { 
                    if( $GETpg == 1 || $GETpg == 2 ) { 
                        $html .= '<a class="prev" href="'. $this->curl(false) .'" title="Página anterior"><span>&lsaquo;</span></a>';
                    }
                    else {
                        $html .= '<a class="prev" href="'. $this->curl($prev) .'" title="Página anterior"><span>&lsaquo;</span></a>';
                    }
                }
                else {
                    $html .= '<a><span class="disabled">&lsaquo;</span></a>';
                }

                # buttons page numbers
                $html .= '<div class="numeric-pagination">';
                for( $i = intval($prev); $i <= intval($GETpg) - 1; $i ++ ) :
                    if( $CPN == 3 && $CPN != $GETpg ) {
                        $html .= '<a href="'. $this->curl(1) .'"><span>1</span></a>';
                    }
                    if( $CPN == 4 && $CPN != $GETpg ) {
                        $html .= '<a href="'. $this->curl(2) .'"><span>2</span></a>';
                    }
                    if( $CPN == 6 && $CPN != $GETpg ) {
                        $html .= '<a href="'. $this->curl(4) .'"><span>4</span></a>';
                    }
                    $ii = intval( $i ) - 1;
                    if( $i >= 1 ) {
                        if( $ii != 0 ) {
                            if( $ii == 1 ) {
                                $html .= '<a href="'. $this->curl(false) .'"><span>'. $ii .'</span></a>';
                            }
                            else {
                                $html .= '<a href="'. $this->curl($ii) .'"><span>'. $ii .'</span></a>';
                            }
                        }
                        if( $i == 1 ) {
                            $html .= '<a href="'. $this->curl(false) .'"><span>'. $i .'</span></a>';
                        }
                        else {
                            $html .= '<a href="'. $this->curl($i) .'"><span>'. $i .'</span></a>';
                        }
                    }
                endfor;
                if( $GETpg ) {
                    $html .= "<a $current><span>$GETpg</span></a>";
                }
                for( $i = intval($GETpg) + 1; $i <= intval($next); $i ++ ) :
                    $ii = $i + 1;
                    if( $i <= $number_records_rounded ) {
                        if( $GETpg == 1 ) {
                            $html .= '<a href="'. $this->curl($i) .'"><span>'. $i .'</span></a>';
                            if( $number_records_rounded >= 3 ) {
                                $html .= '<a href="'. $this->curl(3) .'"><span>3</span></a>';
                            }
                            if( $number_records_rounded >= 4 ) {
                                $html .= '<a href="'. $this->curl(4) .'"><span>4</span></a>';
                            }
                            if( $number_records_rounded >= 5 ) {
                                $html .= '<a href="'. $this->curl(5) .'"><span>5</span></a>';
                            }
                        } 
                        else {
                            if( $GETpg == $penult ) {
                                $html .= '<a href="'. $this->curl($i) .'"><span>'. $i .'</span></a>';
                            }
                            else {
                                $active = ( $GETpg == NULL ) ? $current : '';

                                $html .= '<a '. $active .' href="'. $this->curl($i) .'"><span>'.$i.'</span></a>';
                                $html .= '<a href="'. $this->curl($ii) .'"><span>'. $ii .'</span></a>';
                                
                                $html .= ( $GETpg == NULL ) ? '<a href="'. $this->curl(3) .'"><span>3</span></a>' : '';
                            }
                        }
                    }
                endfor;
                $html .= '</div>';

                # < buttons proximas paginas > 
                if( $number_records_rounded == $GETpg ) {
                    $html .= '<a><span class="disabled">&rsaquo;</span></a>';
                }
                else {
                    if( $GETpg == NULL ) {
                        $html .= '<a class="next" href="'.$this->curl(2).'" title="Próxima página"><span>&rsaquo;</span></a>';
                    }
                    else {
                        $html .= '<a class="next" href="'.$this->curl($next).'" title="Próxima página"><span>&rsaquo;</span></a>';
                    }
                }

                # < button vai para a ultima pagina > 
                if( $number_records_rounded == $GETpg ) {
                    $html .= '<a><span class="disabled">&raquo;</span></a>';
                }
                else {
                    $html .= '<a class="last-page" href="'. $this->curl($number_records_rounded) .'" title="Ultima página"><span>&raquo;</span></a>';
                }
            $html .= '</nav>';

            return $html;
        }
    }

    /**
     * monta a url para uma pagina especifica da paginacao
     * considera parametros get 'q' (busca) e 'pg' (pagina)
     * garante a construcao correta do path mesmo com segmentos variados na url
     *
     * @param int|false $pg numero da pagina ou false para a primeira pagina
     * @return string url completa para a pagina informada
     */
    protected function curl( $pg ) {
        $quest = URL::Get('q');
        $pathname = '';
        if( URL::paramCount() == 1 ) {
            $pathname = URL::param(0);
        }
        if( URL::paramCount() == 2 ) {
            $pathname = URL::param(0) . '/' . URL::param(1);
        }
        if( URL::paramCount() == 3 ) {
            $pathname = URL::param(0) . '/' . URL::param(1) . '/' . URL::param(2);
        }
        if( URL::paramCount() == 4 ) {
            $pathname = URL::param(0) . '/' . URL::param(1) . '/' . URL::param(2) . '/' . URL::param(3);
        }

        if( $pg === false ) {
            if( $quest ) {
                return URL::root( "$pathname?q=$quest" );
            }
            else {
                return URL::root( $pathname );
            }
        } 
        else {
            if( $quest ) {
                return URL::root( "$pathname?q=$quest&pg=$pg" );
            }
            else {
                if( URL::param(0) === '' ) {
                    return URL::root( "?pg=$pg" );
                }
                else {
                    return URL::root( "$pathname/?pg=$pg" );
                }
            }
        }
    }

}