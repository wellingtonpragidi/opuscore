let Settings_Image_Sizes = function() {

    let forms = document.querySelectorAll("#image_sizes form[method=POST]");

    forms.forEach( form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            let alert = form.querySelector(".response");
            alert.style.display = 'block';
            alert.style.opacity = '1';
            alert.innerHTML = '.. .  .';

            let data = new FormData(form);
            let xhr = new XMLHttpRequest();
            xhr.open('POST', form.getAttribute('action'), true);

            xhr.addEventListener('load', () => {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    OpusCore.debug( xhr.response );

                    alert.innerHTML = xhr.response;
                    
                    if( typeof fade !== undefined && fade.out && fade.out.get ) {
                        fade.out.get( alert, 3700 );
                        setTimeout( () => alert.style.display = 'none', 5000 );
                    } 
                    else {
                        setTimeout( () => {
                            alert.style.opacity = '0';
                            setTimeout( () => alert.style.display = 'none', 1000 );
                        }, 3000);
                    }
                }
                else {
                    alert.innerHTML = `
                        <div class="alert error">Erro: <code>${xhr.status}</code></div>`;
                }
            });

            xhr.addEventListener('error', () => {
                alert.innerHTML = 'Falha na requisição.';
            });

            xhr.send(data);
        });
    });

}

document.addEventListener( 'DOMContentLoaded', Settings_Image_Sizes );