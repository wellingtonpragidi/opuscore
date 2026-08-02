/**
 * popup encarregado por gerenciar de insercao de midias no editor rich-text Punk
 *  
 * Ele lista quase todas os registros da tabela `medias` carregadas pelo sistema 
 * exibindo as miniaturas das mesmas quando disponivel do contrario icone de referencia
 *  
 * Essa "galeria" no popup exibe uma quantidade de miniaturas limitada por carregamento 
 * e clicando em um botao vai carrega mais registros com as miniaturas de forma assincrona
 * 
 * Isso tambem exibe informacoes de cada midia na "sidebar" quando clicadas nas mesmas,
 * e mais input para o texto alternativo e botao que insere essa no conteudo do editor 
 */
( function() {
    
    let offset = 0;

    // Cria os elementos
    let scroll = document.createElement("div");
    scroll.className = "scroll";

    let button = document.createElement("button");
    button.type = "button";
    button.className = "loadmore btn xlg";
    button.textContent = "Carregar mais";

    let selector = document.getElementById('gallery');

    selector.appendChild(scroll);
    selector.appendChild(button);

    /**
     * Adiciona os listeners das miniaturas carregadas na galeria 
     * capturando o click no botao 'delete_media' 
     * e tambem exibindo dados da midia selecionada (por input=radio '.datafile'.each)
     * 
     * Esse metodo eh chamado em `loadData()` que por sua vez eh chamado 
     * carregando dados iniciais 
     * e depois novamente a cada click do botao "Carregar mais" :
     * Isso para que as novas miniaturas que ainda nao existiam no DOM 
     * quando os listeners anteriores foram registrados passem tambem a ser ouvidas 
     */
    function checkedSelected() {
        // seletor do <input type=radio> dentro das <label> de cada miniatura
        // Usado no Listener 'change' quando *clicado* em uma miniatura
        let datafile = document.querySelectorAll(".datafile");

        // elemento interno da sidebar (esse elemento sempre existe) 
        // esperando informacoes da miniatura clicada
        let details = document.getElementById("details");

        // Botao para inserir a midia no conteudo do editor. 
        // Ele fica na sidebar, logo apos o elemento #details - nao dentro
        let inserfile = document.getElementById("inserfile");
        inserfile.style.display = "none";

        // Cada input radio checked chama o codigo do servidor 
        // para carregar os detalhes na sidebar
        // 
        // Equivalente a datafile.id.onclick em uma miniatura referente ao registro
        datafile.forEach( thumb => {
            thumb.addEventListener('change', () => {

                if( thumb.checked ) {
                    let activeThumb = document.querySelector('.thumb.active');
                    if( activeThumb ) {
                        activeThumb.classList.remove('active');
                    }

                    thumb.closest('.thumb').classList.add('active');

                    let xhr = new XMLHttpRequest();
                    xhr.open(
                        'GET', 
                        OpusCore.media.editor.selected_url + thumb.value
                    );
                    xhr.addEventListener('load', () => {
                        if( xhr.status >= 200 && xhr.status < 300 ) {
                            OpusCore.debug( xhr.response, OpusCore.media.editor.selected_url + thumb.value );

                            details.innerHTML = xhr.response;

                            refreshIcons(details);
                        }
                    });

                    xhr.send();
                    inserfile.style.display = "block";
                }
            });
        });

        // O conteudo em #details eh recriado via innerHTML 
        //  sempre que uma miniatura eh selecionada. 
        // Por isso o botão #delete_media nem sempre existe no DOM.
        //
        // O listener fica no elemento pai (#details) 
        // e usa propagacao de eventos para capturar cliques no botao de exclusao.
        details.addEventListener('click', event => {

            // para capturar os cliques no botao de exclusao
            if( event.target && event.target.id === 'delete_media' ) {

                if( ! confirm('Vai mesmo deletar essa mídia?') ) {
                    return;
                }
                let data = new FormData();
                data.append( "upload_id", document.getElementById('upload_id').value );
                data.append( "upload_type", document.getElementById('upload_type').value );

                let xhr = new XMLHttpRequest();

                xhr.open("POST", OpusCore.media.editor.delete_url );

                xhr.addEventListener('load', () => {
                    if( xhr.status >= 200 && xhr.status < 300 ) {
                        OpusCore.debug( xhr.response );

                        let result = JSON.parse( xhr.response );

                        if( result.deleted === true ) {
                            document.getElementById("info").style.display = "none";

                            inserfile.style.display = "none";

                            document.querySelector(".datafile:checked")
                                .closest("label").style.display = "none";
                        }

                        document.getElementById("return").insertAdjacentHTML( 
                            'afterbegin', result.content 
                        );
                    }
                });

                xhr.send(data);
            }
        });

    };

    // aqui o codigo eh sobre o carregamento assincrono dos registros com as miniaturas
    function postData( data ) {
        return new Promise( (resolve, reject) => {
            let xhr = new XMLHttpRequest();

            xhr.open("POST", OpusCore.media.editor.loading_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.addEventListener('load', () => {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    try {
                        let loaded = JSON.parse( xhr.response );

                        if( loaded.button === false ) {
                            button.style.display = "none";
                        }

                        resolve( loaded );

                    } 
                    catch( error ) {
                        // Se o PHP cuspir qualquer erro e quebrar o JSON, cai no debug()
                        OpusCore.debug( "Erro no JSON do servidor: ", xhr.response );
                        
                        // Rejeita a Promise passando o erro para o tratamento global
                        reject( new Error(`Resposta do servidor não é um JSON válido.`) );
                    }
                } 
                else {
                    reject( new Error( `Erro HTTP! status: ${xhr.status}` ) );
                }
            });

            xhr.addEventListener('error', function() {
                reject( new Error( "Erro na requisição" ) );
            });
            
            xhr.send( data );
        });
    }
    
    async function loadData() {
        let params = 'limit=' + encodeURIComponent( OpusCore.media.editor.limit ) +
                       '&offset=' + encodeURIComponent(offset);
        try {
            let result = await postData( params );

            offset += OpusCore.media.editor.limit;

            scroll.insertAdjacentHTML( 'beforeend', result.content );


            checkedSelected();
        } 
        catch( error ) {
            console.error( "Erro ao carregar mais dados: ", error );
        }
    }

    // Carrega dados iniciais
    loadData();

    // ++
    button.addEventListener( 'click', loadData );

})();