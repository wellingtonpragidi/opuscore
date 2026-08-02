// A API que deveria substituir execCommand eh a Input Events / beforeinput, mas ela nao cobre tudo
( function() {

    if( ! document.getElementById("pk-body") || ! document.getElementById('editor') ) {
        return;
    }

    // trava para evitar loop infinito entre Editor Conteudo e Codigo Fonte
    let changingOrigin = false;

    let editor = {
        init() {
            punk.init();
            toolbar.init();

            document.execCommand("defaultParagraphSeparator", false, "p");

            // cola o conteudo dentro da area de texto do editor sem formatacao ( so texto )
            // comente para colar com formatacao de onde copiou
            punk.content.addEventListener('paste', (event) => {
                event.preventDefault();
                let text = (event.originalEvent || event).clipboardData.getData("text/plain");
                document.execCommand("insertHTML", false, text);
            });

            insert.init();
            extend.popup();
        },
        cleaning() {
            window.setTimeout( () => {
                let dataWrap = punk.content.querySelectorAll('[data-wrap="file"]');

                dataWrap?.forEach( dw => {
                    dw.querySelector('br')?.remove();

                    dw.removeAttribute('data-wrap');
                });

                // Como limpamos elementos no DOM, força uma sincronização manual controlada
                synchrContent();

            }, 1500 );
        }
    };

    let punk = {
        frame:    document.getElementById("punk"),
        tollbar:  document.getElementById("toolbar"),
        content:  document.getElementById("pk-body"),
        footer:   document.getElementById("foot"),
        srcode:   document.getElementById("srcode"),
        textarea: document.getElementById('editor'),

        init() {
            this.textarea.style.display  = "none";
            this.srcode.style.display    = "none";
            this.frame.style.width       = "100%";
            this.content.style.minHeight = "50vh";
            this.srcode.style.minHeight  = "50vh";
        }
    };

    // Sincroniza do Editor para as Textareas
    const synchrContent = () => {
        // Se a mudança veio do codigo-fonte, ignora para nao dar loop
        if( changingOrigin ) {
            return; 
        }
        
        punk.srcode.value   = punk.content.innerHTML;
        punk.textarea.value = punk.content.innerHTML;
    };

    // Eventos de alteracao real de conteudo
    const MutationEvents = ['keyup', 'input', 'paste', 'drop', 'change'];

    // Escuta eventos reais de alteracao
    MutationEvents.forEach( (evt) => {
        punk.content.addEventListener(evt, synchrContent);
    });


    const Sentinel = new MutationObserver(synchrContent);

    const connectObserver = () => {
        Sentinel.observe(punk.content, { 
            childList: true, 
            subtree: true, 
            characterData: true 
        });
    };

    // Inicializa o valor na tela e liga o observador pela primeira vez
    punk.content.innerHTML = punk.textarea.value;
    punk.srcode.value      = punk.textarea.value;
    
    connectObserver();

    // Quando clicar no botao de CODIGO FONTE: checked=true `toolbar.switchmode()`
    const enableSrcode = () => {
        Sentinel.disconnect(); // Desconectar o Observer
        
        // esconder a contenteditable e mostrar o textarea=srcode
        punk.content.style.display = "none";
        punk.srcode.style.display  = "block";
        punk.srcode.focus();
    };

    // Quando CLICAR PARA VOLTAR para o Editor Visual: checked=false `toolbar.switchmode()`
    const disableSrcode = () => {
        // 1. Sincroniza o HTML que você editou na unha para o painel visual
        punk.content.innerHTML = punk.srcode.value;
        punk.textarea.value    = punk.srcode.value;

        // esconder a textarea=srcode e mostrar o contenteditable
        punk.srcode.style.display  = "none";
        punk.content.style.display = "block";

        // Religa o observador para voltar a proteger o DOM
        connectObserver(); 
    };

    let toolbar = {
        btnwrap: document.querySelectorAll("#toolbar > div"),
        buttons: document.querySelectorAll("#toolbar button"),
        btnopen: document.querySelectorAll("#toolbar .open-options"),
        options: document.querySelectorAll("#toolbar .options"),
        divider: document.querySelectorAll("#toolbar .divider"),
        inputSwitch: document.querySelector("#switch-mode"),
        navigator() {
            this.buttons.forEach( element => {
                element.type = "button";
            });
            this.btnopen.forEach( element => {
                element.addEventListener('click', () => {
                    if( element.classList.contains("active") ) {
                        element.classList.remove("active");
                    } 
                    else {
                        let current = document.getElementsByClassName("active");
                        if( current.length > 0 ) { 
                            current[0].className = current[0].className.replace(" active", "");
                        }
                        element.className += ' active';
                    }
                });
            });

            punk.content.addEventListener('click', () => {
                toolbar.btnopen.forEach( element => {
                    element.classList.remove("active");
                });
            });

            this.options.forEach( element => {
                element.addEventListener('click', () => {
                    if( element.previousElementSibling.classList.contains("active") ) {
                        element.previousElementSibling.classList.remove("active");
                    }
                });
            });
        },
        switchmode() {
            if( this.checked ) {
                punk.srcode.style.display = "block";
                punk.content.style.display = "none";
                this.classList.add("modecode");

                enableSrcode();
            }
            else {
                punk.srcode.style.display = "none";
                punk.content.style.display = "block";
                this.classList.remove("modecode");

                disableSrcode();
            }
            let switchlabel = document.querySelector("#switch label");
            if( this.classList.contains("modecode") ) {
                switchlabel.setAttribute("title", "Retornar editor visual");

                toolbar.buttons.forEach( element => {
                    element.style.display = "none";
                });
                toolbar.divider.forEach( element => {
                    element.style.display = "none";
                });
            } 
            else {
                switchlabel.setAttribute("title", "Ver código fonte");

                toolbar.buttons.forEach( element => {
                    element.removeAttribute("style");
                });
                toolbar.divider.forEach( element => {
                    element.removeAttribute("style");
                });
            }
        },
        activefocus() {
            if( Selection.rangeCount > 0 ) {
                let range = Selection.getRangeAt(0);
                let container = range.commonAncestorContainer;
                while(container && container.nodeType !== 1) {
                    container = container.parentNode
                }
                if(container && container.closest("#pk-body")) {
                    return container.tagName.toLowerCase();
                }
            }
        },
        activeParentfocus() {
            if( Selection.rangeCount > 0 ) {
                let range = Selection.getRangeAt(0);
                let container = range.commonAncestorContainer;
                while(container && container.nodeType !== 1) {
                    container = container.parentNode.parentNode
                }
                if(container && container.closest("#pk-body")) {
                    return container.tagName.toLowerCase();
                }
            }
        },
        action() {
            punk.content.addEventListener('mousedown', () => {
                toolbar.buttons.forEach( element => {
                    if( 
                        element.dataset.label == toolbar.activefocus() || 
                        element.dataset.label == toolbar.activeParentfocus()
                    ) {
                        element.classList.remove('action');
                        if( element.parentElement.previousElementSibling != null ) {
                            element.parentElement.previousElementSibling.classList.remove("action");
                        }
                    }

                });
            });
            punk.content.addEventListener('mouseup', () => {
                toolbar.buttons.forEach( element => {
                    if( 
                        element.dataset.label == toolbar.activefocus() || 
                        element.dataset.label == toolbar.activeParentfocus()
                    ) {
                        element.classList.add("action");
                        if( element.parentElement.previousElementSibling != null ) {
                            element.parentElement.previousElementSibling.classList.add("action");
                        }
                    }
                });
            });
        },
        init() {
            this.navigator();
            this.inputSwitch.addEventListener('change', this.switchmode);
            this.action();
        }
    };

    const Selection = window.getSelection();

    let insert = {
        cmd( element ) {
            return "<" + element + ">" + Selection + "</" + element + ">";
        },
        inline() {
            let inlines = document.querySelectorAll("#inline .options button");
            inlines.forEach( element => {
                element.addEventListener('click', () => {
                    document.execCommand("insertHTML", false, insert.cmd(element.dataset.label));
                });
            });
        },
        block() {
            let blocks = document.querySelectorAll("#block .options button");
            blocks.forEach( element => {
                element.addEventListener('click', () => {
                    document.execCommand("insertHTML", false, insert.cmd(element.dataset.label));
                });
            });
        },
        grid(columns) {
            insertGrid = (html) => {
                if(Selection.rangeCount) {
                    let range = Selection.getRangeAt(0);
                    if( range.commonAncestorContainer.parentNode && 
                    range.commonAncestorContainer.parentNode.closest("#pk-body") ) {
                        let mktemp = document.createElement("div");
                        mktemp.innerHTML = html;
                        let frag = document.createDocumentFragment(), node, lastNode;
                        while( (node = mktemp.firstChild) ) {
                            if(range.commonAncestorContainer.tagName == 'P') {
                                range.commonAncestorContainer.remove();
                            }
                            lastNode = frag.appendChild(node);
                        }
                        range.insertNode(frag);
                    }
                }
            }
            let column = '<div><p></p></div>', gridColumns;
            switch(columns) {
                case '__x__':
                    gridColumns = '<div class="cn__x__">'+column+column+'</div>';
                break;
                case '___x_':
                    gridColumns = '<div class="cn___x_">'+column+column+'</div>';
                break;
                case '_x___':
                    gridColumns = '<div class="cn_x___">'+column+column+'</div>';
                break;
                case '_x_x_':
                    gridColumns = '<div class="cn_x_x_">'+column+column+column+'</div>';
                break;
            }

            insertGrid( gridColumns + ' ' );
        },
        textalign() {
            let align = document.querySelectorAll("#textalign .options button");
            align.forEach( element => {
                element.addEventListener('click', () => {
                    document.execCommand("styleWithCSS", false, null);
                    document.execCommand(element.dataset.cmd, false, null);
                });
            });
        },
        link() {
            let link = document.querySelector("#link button");
            link.addEventListener('click', () => {
                let linkURL = prompt("Insira a URL:", "https://");
                // document.execCommand("createLink", false, linkURL);
                document.execCommand("insertHTML", false, '<a href="'+linkURL+'" target="_blank">'+Selection+'</a>');

            });
        },
        list() {
            let orderedlist = document.querySelector("#orderedlist button");
            orderedlist.addEventListener('click', () => {
                document.execCommand("insertOrderedList", false, null);
            });
            let unorderedlist = document.querySelector("#unorderedlist button");
            unorderedlist.addEventListener('click', () => {
                document.execCommand("insertUnorderedList", false, null);
            });
        },
        quote() {
            let quotes = document.querySelectorAll("#quote .options button");
            quotes.forEach( element => {
                element.addEventListener('click', () => {
                    document.execCommand("insertHTML", false, insert.cmd(element.dataset.label));
                });
            });
        },
        color() {
            let color = document.querySelectorAll("#color .options button");
            color.forEach( element => {
                element.addEventListener('click', () => {
                    $value = '<span style="color: '+element.dataset.color+'">'+Selection+'</span>';
                    document.execCommand("insertHTML", false, $value);
                });
            });
        },
        fontsize() {
            let fontsize = document.querySelectorAll("#fontsize .options button");
            fontsize.forEach( element => {
                element.addEventListener('click', () => {
                    $value = `<span style="font-size: ${element.dataset.size}">${Selection}</span>`;
                    document.execCommand("insertHTML", false, $value);
                });
            });
        },
        files() {
            // #
            // #
            // # Abre o popup de midias
            // #
            let btnImage = document.querySelector("#image");

            // cria elemento onde posteriormente elementos de midia serao inseridos
            btnImage.addEventListener('click', () => {
                let wrapfile = '<div data-wrap="file"></div>';
                document.execCommand("insertHTML", false, wrapfile);
            });

            // remove elemento caso nao insira midia (fechou popup sem adicionar midia)
            document.querySelector(".dismiss").addEventListener('click', removeWrapFile);
            document.querySelector(".popup_over").addEventListener('click', removeWrapFile);

            function removeWrapFile() {
                let wrapfile = document.querySelector('#pk-body [data-wrap="file"]');
                window.setTimeout(() => {
                    wrapfile?.remove();
                }, 1000);
            }


            // #
            // #
            // # Insercao de arquivo/midia na sidebar do popup - para dentro do conteudo
            // #

            // Botao para inserir a midia no conteudo do editor. Esse botao fica na sidebar
            // visivel apenas quando uma miniatura da galeria for checked
            let inserfile = document.getElementById("inserfile");

            // Um arquivo recem carregado ja adiciona a miniatura na galeria com o radio checked 
            // tornando todas as informacoes e o botao para inserir visiveis na sidebar

            inserfile.addEventListener('click', () => {
                let media;
                let alttext   = document.querySelector("#alttext");
                let inputurl  = document.querySelector("#fileurl");

                if( alttext.value === '' ) {
                    alert("Insira o texto alternativo.");
                    return;
                }

                let filename = inputurl.value;

                let ext = filename.split('.').pop();

                let wrapfile = document.querySelector('#pk-body [data-wrap="file"]');
                
                if( ['gif', 'jpg', 'jpeg', 'png', 'webp', 'svg'].includes(ext) ) {
                    media = document.createElement('img');
                    media.src    = filename;
                    media.alt    = alttext.value;

                    let dimension = JSON.parse( document.getElementById('upload_dimensions').value );
                    if( dimension['width'] > 0 && dimension['height'] > 0 ) {
                        media.width  = dimension['width'];
                        media.height = dimension['height'];
                    }
                }
                else if( ext === 'mp4' ) {
                    media = document.createElement('video');
                    media.setAttribute('controls', 'true');
                    source = document.createElement('source');
                    media.appendChild(source);
                    source.src  = filename;
                    source.type = 'video/' + ext;
                }
                else if( ext === 'mp3' ) {
                    media = document.createElement('audio');
                    media.setAttribute('controls', 'true');
                    source = document.createElement('source');
                    media.appendChild(source);
                    source.src  = filename;
                    source.type = 'audio/' + ext;
                }
                else {
                    media = document.createElement('a');
                    media.setAttribute('target', '_blank');
                    media.setAttribute('rel', 'noopener');
                    media.title = alttext.value;
                    media.href  = filename;
                    media.textContent = filename;
                }

                // insere a midia dentro do elemento adiciona quando abril o popup de midias
                wrapfile.appendChild(media);

                editor.cleaning();

                // fecha o popup apos insericao da midia
                extend.popupModal.style.removeProperty('display');
                extend.popupModal.style.removeProperty('opacity');
                document.body.style.removeProperty('overflow-y');

            });


            // seletor do <input type=file> com visual de botao
            let uploadFile = document.getElementById('upload');

            // Upload do arquivo enviado pelo usuario
            // Apos concluir o upload, a miniatura retornada pelo servidor eh
            // inserida na galeria e seus dados sao carregados na sidebar
            uploadFile.addEventListener('change', (event) => {
                let gallery = document.getElementById("gallery");

                let file   = event.target.files[0];
                let upload = new FormData();
                upload.append("upload", file);

                ["target_id", "upload_type", "title"].forEach( element => {
                    let value = document.getElementById(element)?.value;
                    value !== undefined && upload.append(element, value);
                });

                let xhrUpload = new XMLHttpRequest();
                xhrUpload.open( "POST", OpusCore.media.editor.upload_url );

                // Apos inserir a nova miniatura na galeria, carrega imediatamente
                // seus dados na sidebar (#details) para exibicao inicial
                xhrUpload.addEventListener( 'load', () => {
                    if( xhrUpload.status >= 200 && xhrUpload.status < 300 ) {
                        OpusCore.debug( "UPLOAD ⌵ \n", xhrUpload.response );

                        let scroll = gallery.querySelector(".scroll");
                        scroll.insertAdjacentHTML( 'afterbegin', xhrUpload.response );

                        let checkedID = document.querySelector(".datafile:checked");

                        if( ! checkedID ) {
                            return;
                        }

                        checkedID.checked;

                        checkedID.closest('.thumb').classList.add('active');
                        

                        let xhrInsertSelected = new XMLHttpRequest();

                        xhrInsertSelected.open( 
                            'GET', OpusCore.media.editor.selected_url + checkedID.value, true 
                        );

                        xhrInsertSelected.addEventListener('load', () => {

                            if( xhrInsertSelected.status >= 200 && xhrInsertSelected.status < 300 ) {
                                OpusCore.debug( 
                                    "UPLOAD: checked › selected ⌵ \n", 
                                    xhrInsertSelected.response 
                                );
                                document.querySelector("#details").innerHTML = xhrInsertSelected.response;
                            }
                        });

                        xhrInsertSelected.send();

                        /*
                        |----------------------------------------------------------------------
                        | Miniaturas adicionadas apos upload
                        |----------------------------------------------------------------------
                        |
                        | As miniaturas carregadas ao abrir o popup já possuem seus proprios
                        | listeners de selecao (checked).
                        |
                        | Como essas miniaturas acabam de ser inseridas na galeria via AJAX,
                        | eh preciso registrar um listener nelas tambem para que participem
                        | normalmente do fluxo de selecao na sidebar.
                        |
                        | Isso permite alternar o foco entre midias antigas e recem enviadas,
                        | retornando a nova midia sempre que ela for selecionada novamente.
                        */
                        checkedID.addEventListener('change', () => {

                            if( checkedID.checked ) {
                                let activeThumb = document.querySelector('.thumb.active');
                                if( activeThumb ) {
                                    activeThumb.classList.remove('active');
                                }

                                checkedID.closest('.thumb').classList.add('active');
                            }

                            let xhrToggleSelected = new XMLHttpRequest();

                            xhrToggleSelected.open(
                                'GET', OpusCore.media.editor.selected_url + checkedID.value, true
                            );

                            xhrToggleSelected.addEventListener('load', () => {

                                if( xhrToggleSelected.status >= 200 && xhrToggleSelected.status < 300 ) {
                                    OpusCore.debug( 
                                        `Alternar foco entre midias antigas e recem enviadas: 
                                        checked › selected ⌵`, "\n",
                                        xhrToggleSelected.response 
                                    );
                                    document.querySelector("#details").innerHTML = xhrToggleSelected.response;
                                }
                            }); 

                            xhrToggleSelected.send();
                        });

                        inserfile.style.display = "block";
                    }
                });

                xhrUpload.send(upload);
            });
        },
        embed() {
            let video = document.querySelector("#video button");
            video.addEventListener('click', () => {
                let youtubeURL = prompt("Insira a URL de vídeo do YouTube:", "");
                youtubeURL = youtubeURL.indexOf("https://") ? "https://" + youtubeURL : youtubeURL;
                if( youtubeURL && youtubeURL.indexOf("youtube.com/watch?v=") > -1 || youtubeURL.indexOf("youtube.com/embed/") > -1 ) {
                    youtubeURL = youtubeURL.replace("watch?v=", "embed/");
                    if( youtubeURL.indexOf("&") > -1 ) {
                        youtubeURL = youtubeURL.substring(0, youtubeURL.indexOf('&'));
                    }
                    else {
                        youtubeURL = youtubeURL;
                    }
                }
                document.execCommand("insertHTML", false, '<div class="embed"><iframe src="'+youtubeURL+'"></iframe></div>');

            });
        },
        ruler() {
            document.querySelector("#ruler").addEventListener('click', () => {
                document.execCommand('insertHorizontalRule', false, null);
            });
            let contentRules = document.querySelectorAll("#pk-body hr");
            contentRules.forEach( element => {
                if(element.hasAttribute("_moz_dirty")) {
                    element.removeAttribute("_moz_dirty");
                }
            });
        },
        init() {
            this.inline();
            this.block();
            let grid = document.querySelectorAll("#grid .options button");
            grid.forEach( element => {
                element.addEventListener('click', () => {
                    insert.grid(element.dataset.col);
                });
            });
            this.textalign();
            this.link();
            this.list();
            this.quote();
            this.color();
            this.fontsize();
            this.files();
            this.embed();
            this.ruler();
        }
    };

    let extend = {
        popupModal: document.querySelector(".popup"),
        dismiss: document.querySelector(".dismiss"),
        popup() {
            this.dismiss.addEventListener('click', () => {
                extend.popupModal.style.removeProperty("display");
                extend.popupModal.style.removeProperty("opacity");
                document.body.style.removeProperty("overflow-y");
            });
        }
    };

    let zlorem = `<p>Zombie ipsum reversus ab viral inferno, nam Rick grimes malum cerebro.</p><p>De carne lumbering animata corpora quaeritis. Summus brains sit, morbo vel maleficia? De apocalypsi gorger omero undead survivor dictum mauris.</p><p>Hi mindless mortuis soulless creaturas, imo evil stalking monstra adventus resident evil vultus comedat cerebella viventium. Qui animated corpse, cricket bat max brucks terribilem incessu zomby.</p><p>The voodoo sacerdos flesh eater, suscitat mortuos comedere carnem virus. Zonbi tattered for solum oculi eorum defunctis go lum cerebro.</p><p>Daryl Dixon nescio brains an Undead zombies. Sicut malus putrid voodoo horror. Nigh tofth eliv ingdead.</p>`;
    document.querySelector("#insertext button").addEventListener('click', () => {
        document.execCommand("insertHTML", false, zlorem);
    });

    editor.init();

})();
