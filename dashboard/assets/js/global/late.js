( function() {

    const INPUTS = {
        text: document.querySelectorAll('input[type=text]'),
    };

    INPUTS.text?.forEach( input => {
        if( ! input.hasAttribute('autocomplete') ) {
            input.setAttribute('autocomplete', 'off');
        }
    });


    document.getElementById('goback')?.addEventListener( 'click', () => {
        // Pega o dominio atual
        const currentDomain = window.location.hostname;

        // Se houver historico eh a view anterior e contiver o dominio do site, clica e retorna a ela
        if( history.length > 1 && document.referrer.includes(currentDomain) ) {
            history.back();
        }

        // do contrario volta os parametros da URL
        else {
            const currentURL = new URL(window.location.href);

            let segments = currentURL.pathname.split('/');
            
            // Localiza onde esta o 'dashboard' na estrutura atual
            const home = segments.indexOf('dashboard');

            // Se o 'dashboard' existir E houver segmentos depois dele (esta em subrota)
            if( home !== -1 && segments.length > (home + 1) ) {

                segments.pop(); // Remove o ultimo param de slug 
               
                currentURL.pathname = '/' + segments.join('/'); // Remonta o caminho relativo
                
                window.location.href = currentURL.toString(); // Redireciona
            } 

            else {
                OpusCore.debug('Limite atingido para (goback): Você está na home do dashboard.');
            }
        }
    });

})();