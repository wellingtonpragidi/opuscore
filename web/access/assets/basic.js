( function() {

    document.addEventListener("DOMContentLoaded", () => {
        
        let goback = document.getElementById('goback');
        if( ! goback ) {
            return;
        }
        // Pega o dominio atual
        let currentDomain = window.location.hostname;

        let currentURL    = new URL(window.location.href);

        // Se houver historico e a view anterior for o dominio do site
        if( history.length > 1 && document.referrer.includes(currentDomain) ) {

            goback.href = "javascript:history.back();";
        } 
        
        // se veio de fora ou aba nova, checa se está em sub-tela (?action=value)
        else if( currentURL.search ) {
            // Limpa a query string para gerar o URL de retorno para o pathname do /access
            currentURL.search = ''; 
            goback.href = currentURL.toString();
        } 
        
        // esta no pathname /access e nao tem historico
        else {
            // esconde da lista o elemento <li> pai do link
            goback.parentElement.remove();
            // goback.parentElement.style.display = 'none'
        }
    });

})();
