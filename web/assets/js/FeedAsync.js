class FeedAsync {

    constructor( settings ) {
        this.limit    = settings.limit;
        this.selector = settings.selector;
        this.url      = settings.url;

        this.offset = 0;

        this.container = document.querySelector(this.selector);
        if( ! this.container ) {
            return;
        }

        this.feed   = this.container.querySelector('[data-feed="async"]');
        this.button = this.container.querySelector('.js-loadmore');

        if( ! this.feed || ! this.button ) {
            return;
        }

        this.button.addEventListener('click', () => this.#loadData());

        this.#loadData();
    }

    #postData( url, data ) {
        return new Promise( ( resolve, reject ) => {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.addEventListener('load', () => {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    let callback = JSON.parse( xhr.response );
                    if( callback.button === false ) {
                        this.button.style.display = "none";
                    }

                    resolve( callback );
                } 
                else {
                    if( xhr.status === 404 ) {
                        reject( new Error(`Nenhum arquivo encontrado na URL: '${url}'`) );
                    }
                    reject( new Error(`Erro HTTP! status: ${xhr.status}`) );
                }
            });

            xhr.addEventListener('error', () => {
                reject( new Error('Erro na requisição') );
            });

            xhr.send( data );
        });
    }

    async #loadData() {
        let params = new URLSearchParams({
            limit: this.limit,
            offset: this.offset
        }).toString();

        try {
            let callback = await this.#postData( this.url, params );
            this.offset += this.limit;
            this.feed.insertAdjacentHTML( 'beforeend', callback.content );
        } 
        catch( error ) {
            console.error( error );
        }
    }
}