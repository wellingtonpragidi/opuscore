window.OpusCore = {

    base_url:    OpusCore.config.base_url,
    dash_url:    OpusCore.config.base_url + 'dashboard/',
    current_url: OpusCore.config.current_url,

    get async_url() {
        return this.dash_url + 'controller/async/?route=';
    },

    media: {
        get upload_url()  {
            return `${OpusCore.async_url}/medias/upload/`;
        },

        get loading_url() {
            return `${OpusCore.async_url}/medias/loadmore/`;
        },

        limit: OpusCore.config.limit.media,

        editor: {
            get upload_url() {
                return `${OpusCore.async_url}/medias/editor/upload/&checked_id=`;
            },
            get selected_url() {
                return `${OpusCore.async_url}/medias/editor/selected/&checked_id=`;
            },
            get delete_url() {
                return `${OpusCore.async_url}/medias/editor/delete/&checked_id=`;
            },
            get loading_url() {
                return `${OpusCore.async_url}/medias/editor/loadmore/&checked_id=`;
            },
            limit: OpusCore.config.limit.editor,
        },
    },

    has_upgrade:  OpusCore.config.has_upgrade,
    display_log:  OpusCore.config.display_log,
    current_menu: OpusCore.config.current_menu ?? null,

    caller() {
        let stack = new Error().stack.split('\n');
        let caller = '';

        // Varre a pilha e pega a primeira linha que nao seja o proprio script de debug
        for( let i = 0; i < stack.length; i++ ) {
            let line = stack[i];

            // Se a linha nao tiver o metodo do debug e contiver um caminho, acha o culpado
            if( line 
                && ! line.includes('caller') 
                && ! line.includes('debug') 
                && (line.includes('http') || line.includes('.js')) ) {

                caller = line.trim();
                break;
            }
        }

        // por garantia Se o laco falhar, pega o que estiver no indice 2
        if( ! caller ) {
            caller = stack[2]?.trim() ?? '';
        }

        return caller
            // Remove o cache busting (?v=1.0.0) mantendo a linha e coluna limpas
            .replace(/\?v=.*?(?=(:\d+:\d+)?\)?$)/, '')
            
            // Remove o prefixo do Chromium "at Object.nomeFuncao (" ou apenas "at ")
            .replace(/^at\s+.*?\s+\(/, '').replace(/^at\s+/, '')
            
            // Remove os parenteses que o Chromium fecha no final da linha
            .replace(/\)$/, '')
            
            // Remove o caminho longo da URL ate o diretorio assets/
            .replace(this.dash_url + 'assets/', '')
            
            // Substitui a sintaxe apos o nome do metodo chamador no Firefox, de "/<@" para " : "
            .replace('/<@', '')
            .replace(/@/, '')
            .replace('/</<:', '')
            .replace('/</<', '')
            .replace('js/', '() : js/')
                      
    },
    debug( ...args ) {
        if( this.display_log ) {
            let caller = this.caller(); // Ex: "js/routes/menus/menu.js:163:26"
            
            // pega o padrao ":linha:coluna" no final da string
            let parts = caller.match(/:\d+:\d+$/)?.[0] ?? '';

            // Se achou as coordenadas, quebra pelo ":" para isolar os numeros
            // split(':') de ":163:26" vai gerar ['', '163', '26']
            // O indice [1] eh exatamente a Linha pura!
            let line = parts ? parts.split(':')[1] : '';

            // 1. Adiciona parenteses () no fim da funcao onde .debug() foi chamado
            // 2. Remove o ":linha:coluna" do final do caminho do arquivo
            caller = caller.replace(parts, '');

            // Monta o depurador final com a linha em destaque
            caller = `[ ${caller} Linha: ${line} ]`;

            console.log( ...args,  "\n", caller );
        }
    },

    /*----------------------------------------------------------------------
    |
    | CASO PRECISE ACESSAR `OpusCore.config` DIRETO NO CONSOLE / SCRIPTS:
    | 
    | Descomente a linha '// config: OpusCore.config,' abaixo.
    |
    | Como este arquivo REESCREVE o objeto window.OpusCore, se nao
    | repassarmos a referencia do 'config', ele eh apagado da memoria
    | pelo motor do JS, tornando `OpusCore.config undefined`
    |---------------------------------------------------------------------- */
    // config: OpusCore.config,
};