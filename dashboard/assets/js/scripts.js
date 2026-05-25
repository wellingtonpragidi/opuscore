let navLinks = document.querySelectorAll("#nav a");
let hasub = document.querySelectorAll("#nav .hasub");
let isubLi = document.querySelectorAll("#nav .isub li");
navLinks.forEach( element => {
    if( documentPathURL(element.href) === documentPathURL(document.URL) ) {
        element.closest("li").classList.add("current");
    }
});
isubLi.forEach( element => {
    if( element.classList.contains("current") ) {
        element.closest(".isub").classList.add("opened");
        element.closest(".hasub").classList.add("isubopen");
    }
    element.addEventListener("mouseover", function () {
        element.closest(".hasub").classList.add("opendown");
    });
    element.addEventListener("mouseleave", function () {
        element.closest(".hasub").classList.remove("opendown");
    });
});
hasub.forEach( element => {
    if( element.classList.contains("current") ) {
        element.classList.remove("current");
    }
    var thisHasub = element.querySelector("a");
    if( documentPathURL(thisHasub.href) === documentPathURL(document.URL) ) {
        thisHasub.parentElement.classList.add("isubopen");
        element.querySelector(".isub").classList.add("opened");
    }
});

function documentPathURL( url ) {
    // Remove query string (?), hash (#) e trailing slash (/)
    return url.split(/[?#]/)[0].replace(/\/$/, '');
}


document.querySelector('form').addEventListener('submit', () => {
    console.log('segment enviado:', document.getElementById('segment').value);
});

( function() {
    let checkboxes = document.querySelectorAll('[name="checkcat[]"]');
    let select     = document.getElementById('segment');
    let postslug   = document.getElementById('edit-slug');

    function selectSegment() {
        let baseOption = select.options[0]; // o <option> vindo do HTML/PHP
        let slug = postslug.value.trim();

        // atualiza soh o option base vindo do banco
        if( baseOption && slug ) {
            let currentSegment = baseOption.value;
            let parts = currentSegment.split('/');

            if( parts.length > 1 ) {
                let base = parts[0] || '';
                let updatedSegment = base + '/' + slug;

                baseOption.value       = updatedSegment;
                baseOption.textContent = updatedSegment;
            }
        }

        // limpa os options criados dinamicamente
        select.length = 1;

        // recria as opcoes com base nas categorias marcadas
        checkboxes.forEach( ckb => {
            if( ckb.checked ) {
                let catslug = ckb.dataset.slug;
                let postSegment = catslug + '/' + slug;

                // para nao recria um option igual ao que vem padrao do PHP/HTML
                if( baseOption.value === postSegment ) {
                    return;
                }

                let option = document.createElement('option');
                option.value = postSegment;
                option.textContent = postSegment;

                select.appendChild(option);
            }
        });
    }

    if( ! select || ! checkboxes || ! postslug ) {
        return;
    }

    select.addEventListener('change', () => {
        document.querySelector('[name="segment_changed"]').value = 1;
    });        

    checkboxes.forEach(ckb => {
        ckb.addEventListener('change', () => {
            document.querySelector('[name="categories_changed"]').value = 1;

            selectSegment();
        });
    });

    if( document.readyState !== 'loading' ) {
        selectSegment();
    } 
    else {
        document.addEventListener( 'DOMContentLoaded', selectSegment );
    }

    postslug.addEventListener( 'input', selectSegment );

})();



const progressBeforeSent = function( width = 38, message = '' ) { 
    var has_message = message ? `<span class="message">&nbsp;${message}</span>` : ``;
    var html = `<div class="progress">
        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="${width}" fill="currentColor" class="ico spinner" viewBox="0 0 17 17"><g xmlns="http://www.w3.org/2000/svg" transform="scale(0.534)"><path d="M32 16c-0.040-2.089-0.493-4.172-1.331-6.077-0.834-1.906-2.046-3.633-3.533-5.060-1.486-1.428-3.248-2.557-5.156-3.302-1.906-0.748-3.956-1.105-5.981-1.061-2.025 0.040-4.042 0.48-5.885 1.292-1.845 0.809-3.517 1.983-4.898 3.424s-2.474 3.147-3.193 4.994c-0.722 1.846-1.067 3.829-1.023 5.79 0.040 1.961 0.468 3.911 1.254 5.694 0.784 1.784 1.921 3.401 3.316 4.736 1.394 1.336 3.046 2.391 4.832 3.085 1.785 0.697 3.701 1.028 5.598 0.985 1.897-0.040 3.78-0.455 5.502-1.216 1.723-0.759 3.285-1.859 4.574-3.208 1.29-1.348 2.308-2.945 2.977-4.67 0.407-1.046 0.684-2.137 0.829-3.244 0.039 0.002 0.078 0.004 0.118 0.004 1.105 0 2-0.895 2-2 0-0.056-0.003-0.112-0.007-0.167h0.007zM28.822 21.311c-0.733 1.663-1.796 3.169-3.099 4.412s-2.844 2.225-4.508 2.868c-1.663 0.646-3.447 0.952-5.215 0.909-1.769-0.041-3.519-0.429-5.119-1.14-1.602-0.708-3.053-1.734-4.25-2.991s-2.141-2.743-2.76-4.346c-0.621-1.603-0.913-3.319-0.871-5.024 0.041-1.705 0.417-3.388 1.102-4.928 0.683-1.541 1.672-2.937 2.883-4.088s2.642-2.058 4.184-2.652c1.542-0.596 3.192-0.875 4.832-0.833 1.641 0.041 3.257 0.404 4.736 1.064 1.48 0.658 2.82 1.609 3.926 2.774s1.975 2.54 2.543 4.021c0.57 1.481 0.837 3.064 0.794 4.641h0.007c-0.005 0.055-0.007 0.11-0.007 0.167 0 1.032 0.781 1.88 1.784 1.988-0.195 1.088-0.517 2.151-0.962 3.156z"></path></g></svg>
        ${has_message}
    </div>`;
    return html;
};


let goBack = document.getElementById("go-back");
if( goBack ) {
    goBack.addEventListener("click", function () {
        history.back();
    });
}


let mediaReader = document.querySelector(".media_reader");
if( mediaReader ) {
    document.querySelector(".btn_change").style.display = "none";
    mediaReader.addEventListener("change", function () {
        fade.in.selector(".btn_change", 1000);
    });
}


let inputsText = document.querySelectorAll("input[type=text]");
if( inputsText) {
    inputsText.forEach((element) => {
        if( ! element.hasAttribute("autocomplete")) {
            element.setAttribute("autocomplete", "off");
        }
    });
}


// Boquear tecla Enter de <textarea>s
let noEnter = document.querySelectorAll(".no_enter");
if( noEnter ) {
    noEnter.forEach(textarea => {
        textarea.addEventListener("keydown", event => {
            if( event.key === "Enter" ) {
                event.preventDefault();
            }
        });
    });
}



 // upgrades 
let opUpgrade = document.getElementById("op-upgrade");
if( opUpgrade ) {
    let formUpgrade = opUpgrade.querySelector("form");
    formUpgrade.addEventListener("submit", function() {
        opUpgrade.getElementById("in-upgrade").style.display = "none";
        let progress = progressBeforeSent( 38, 'Por favor aguarde até que a atualização do sistema seja concluída' );
        opUpgrade.insertAdjacentHTML("afterbegin", progress);
    });

    window.addEventListener("DOMContentLoaded", () => {
        if( document.querySelector(".alert-upgrade")) {
            let progress = document.querySelector(".progress");
            let spinner = progress.querySelector("svg");
            if( spinner ) {
                spinner.remove();
            }
            progress.innerHTML = "A atualização do sistema foi concluída!";
        }
    });
}

let hasUpdate = document.querySelector("#has_update");
if( hasUpdate ) {
    hasUpdate.addEventListener('submit', ev => {
        ev.preventDefault();
        var callback = document.getElementById("response_update");
        let data = new FormData();
        data.append("action", "refresh_cache");
        data.append("href", CURRENT_URL);
        let xhr = new XMLHttpRequest();
        xhr.open("POST", `${DASH_URL}/controller/async/upgrade-cache-refresh.php`);
        xhr.addEventListener('load', function() {
            if( xhr.status >= 200 && xhr.status < 300 ) {
            console.log(xhr.response)
                callback.innerHTML = this.response;
                window.setTimeout(function() {
                    fade.out.selector("#response_update", 4400);
                }, 7000)
            }
        });
        xhr.send(data);
    });
}



// setting 
document.addEventListener("DOMContentLoaded", function() {

    var forms = document.querySelectorAll("#media_size form[method=POST]");

    forms.forEach( form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var callback = form.querySelector(".response");
            if( callback ) {
                callback.style.display = 'block';
                callback.style.opacity = '1';
                callback.innerHTML = progressBeforeSent();
            }

            var data = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", form.getAttribute("action"), true);

            xhr.addEventListener('load', function() {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    if( callback ) {
                        callback.innerHTML = xhr.responseText;
                        if( typeof fade !== undefined && fade.out && fade.out.get ) {
                            fade.out.get( callback, 3700 );
                            setTimeout( () => callback.style.display = 'none', 5000 );
                        } 
                        else {
                            setTimeout(() => {
                                callback.style.opacity = '0';
                                setTimeout( () => callback.style.display = 'none', 1000 );
                            }, 3000);
                        }
                    }
                } 
                else {
                    // console.log(xhr.response);
                    if( callback ) {
                        callback.innerHTML = '<div class="alert error">Erro: <code> ' + xhr.status + '</code></div>';
                    }
                }
            });

            xhr.addEventListener('error', function() {
                if( callback ) {
                    callback.innerHTML = 'Falha na requisição.';
                }
            });

            xhr.send(data);
        });
    });

});
