const Admin_Update = {
    wrappers: null,
    // essa propriedade guarda o wrapper que esta sendo editado no momento
    currentWrapper: null,

    // getters funcionam baseados na propriedade do momento
    get editEntry() {
        return this.currentWrapper 
            ? this.currentWrapper.querySelector('.edit-entry') 
            : null;
    },
    get btnTrigger() {
        return this.currentWrapper 
            ? this.currentWrapper.querySelector('.trigger') 
            : null;
    },
    get confirmSubmit() {
        return this.currentWrapper 
            ? this.currentWrapper.querySelector('.confirm-submit') 
            : null;
    },

    form:  document.getElementById('admin-update'),
    admId: document.getElementById('target_id'),
    xhr:   new XMLHttpRequest(),

    init() {   
        this.wrappers = document.querySelectorAll('.edit-wrapper');

        this.wrappers.forEach( (wrapper) => {
            let btn = wrapper.querySelector('.trigger');
            if( btn ) {
                // passa o evento usando arrow fn para NAO perder o 'this' do objeto
                btn.addEventListener( 'click', (event) => Admin_Update.toggleState(event) );
            }
        });

        this.async();
    },

    // Alterna entre o modo visualizacao e o modo edicao
    toggleState( event ) {
        event.preventDefault();

        // pega o wrapper do botao clicado e joga na propriedade do objeto
        this.currentWrapper = event.currentTarget.closest('.edit-wrapper');

        if( ! this.currentWrapper ) {
            return;
        }

        // Modo: ATIVAR EDICAO
        if( this.btnTrigger.textContent.trim() === 'Alterar' ) {
            this.btnTrigger.textContent = 'Cancelar';

            this.enableField();

            if( this.confirmSubmit ) {
                this.confirmSubmit.style.display = 'block';
                let action = this.currentWrapper.querySelector('[name="action"]').value;

                let input = document.getElementById(action);

                // sem .select() e .focus() para os inputs radio de status entao paramos aqui 
                if( action === 'status' ) {
                    return;
                }

                if( input.tagName === 'INPUT' ) {
                    input.select();
                }
                else {
                    input.focus();
                }
            }
        } 
        // Modo: CANCELAR EDICAO
        else {
            this.btnTrigger.textContent = 'Alterar';

            this.disableField();
            
            if( this.confirmSubmit ) {
                this.confirmSubmit.style.display = 'none';
            }
        }
    },

    enableField() {
        if( this.editEntry ) {
            this.editEntry.removeAttribute('readonly');
        }
        // <select>
        this.currentWrapper.querySelector('.edit-role')?.removeAttribute('disabled');
    },

    disableField() {
        if( this.editEntry ) {
            this.editEntry.value = this.editEntry.dataset.original || this.editEntry.value;

            this.editEntry.setAttribute('readonly', 'readonly');
        }
        // <select>
        this.currentWrapper.querySelector('.edit-role')?.setAttribute('disabled', 'disabled');
    },

    async() {
        this.form.addEventListener( 'submit', (event) => {
            event.preventDefault();

            let data = new FormData(this.form);

            let action = event.submitter.value;

            let result;

            if( action === 'pswd' ) {
                result = event.submitter.parentElement.querySelector('.response');
            }
            else {
                result = this.currentWrapper.querySelector('.response');
            }
                result.innerHTML = '<div class="opus-loader mt5"></div>';

            data.set( 'action', action );

            let url = OpusCore.async_url + '/admins/' + action + '/&id=' + this.admId.value;

            this.xhr.open( 'POST', url, true );

            this.xhr.addEventListener( 'load', () => {
                if( this.xhr.status >= 200 && this.xhr.status < 300 ) {
                    OpusCore.debug( this.xhr.response );

                    result.innerHTML = this.xhr.response;
                }
            });

            this.xhr.send(data);
        });
    }
};

document.addEventListener( 'DOMContentLoaded', () => Admin_Update.init() );



if( document.getElementById('btn-pswd-generator') ) {
    OpusCore.dist.passwordGenerator({
        limit:  12,
        input:  'pswd'
    });
}


document.addEventListener( 'DOMContentLoaded', () => {
    if( document.getElementById('admin-delete') ) {

        let confirmDelete = document.getElementById('confirm-delete');
        confirmDelete.style.display = 'none';

        document.getElementById('delete').addEventListener( 'click', function() {
            this.classList.add('active');
            toggle.slide( confirmDelete );
        });
    }
});