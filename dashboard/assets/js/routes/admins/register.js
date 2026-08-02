( function() {

    let fadeSelector = document.getElementById("early-enable");
    let status       = document.getElementById("status");

    fadeSelector.style.display = "none";

    document.getElementById("send_mode").addEventListener('change', function() {
        if( this.checked ) {
            fade.out.get( fadeSelector, 700 );
            this.value = 'true';
        }
        else {
            fade.in.get( fadeSelector, 600 );
            this.value = 'false';
        }
    });

    document.getElementById("pswd").addEventListener('input', function() {
        let pswd = this.value.trim();

        if( pswd.length === 0 ) {
            status.textContent = 'Pendente';
            status.style.color = '';
        } 
        else if( pswd.length < 8 ) {
            status.textContent = 'Mínimo 8 caracteres';
            status.style.color = '#ffaa00';
        } 
        else {
            status.textContent = 'Aguardando confirmação';
            status.style.color = '#00ffff';
        }
    });

})();

OpusCore.dist.passwordToggle();