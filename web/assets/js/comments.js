const comments = {
    form:     document.getElementById('comment-form'),
    ol:       document.getElementById('list-comments'),
    textarea: document.getElementById('comment'),

    init() {
        this.comment();
        // this.reply();
    },
    
    comment() {
        this.form.addEventListener( 'submit', event => {
        	event.preventDefault();

        	var data = new FormData(this.form);

            let xhr = new XMLHttpRequest();

            let segment = window.location.href.replace(BASE_URL, '');

            xhr.open( 
                'POST', 
                BASE_URL + 'web/controller/?route=/comment-area/&segment=' + segment, 
                true 
            );

            xhr.addEventListener( 'load', () => {
        	    if( xhr.status >= 200 && xhr.status < 300 ) {
                    console.log(xhr.response);

                    this.ol.insertAdjacentHTML( 'afterbegin', xhr.response );

                    this.textarea.value = '';
        	    }
            });

            xhr.send(data);
        });
    },

    reply() {}
}

comments.init();