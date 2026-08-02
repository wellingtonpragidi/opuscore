const userProfile = {

    figure: document.getElementById('user-profile-image'),

    get imageForm() {
        return this.figure.querySelector('form');
    },
    get inputFile() {
        return this.figure.querySelector('#attachment');
    },
    get image() {
        return this.figure.querySelector('img');
    },
    get result() {
        return this.figure.querySelector('.response');
    },


    settingsForm: document.getElementById('user-form-updates'),

    get wrappers() {
        return this.settingsForm.querySelectorAll('.user-update');
    },


    init() {
        this.upload();
        this.settings();
    },

    upload() {
        if( ! this.imageForm ) {
            return;
        }
        this.inputFile.addEventListener( 'change', () => {
            let data = new FormData(this.imageForm);

            let xhr = new XMLHttpRequest();
            
            xhr.open( 'POST', BASE_URL + 'web/controller/?route=/user-picture/', true );

            xhr.addEventListener('load', () => {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    console.log(xhr.response);

                    let respond = JSON.parse( xhr.response );

                    this.result.innerHTML = respond.alert;

                    if( respond.status === true ) {
                        this.image.src = respond.input;
                    }
                }
            });

            xhr.send(data);
        });
    },

    settings() {
        if( ! this.settingsForm ) {
            return;
        }
        this.settingsForm.addEventListener( 'submit', event => {
            event.preventDefault();

            let wrapper = event.submitter.parentElement;


            let data = new FormData( this.settingsForm );

            let action = wrapper.querySelector('button');
            data.append( 'action', action.value );


            let inputText = wrapper.querySelector('input[type=text]');

            let result    = wrapper.querySelector('.response');

            let xhr = new XMLHttpRequest();

            xhr.open( 'POST', BASE_URL + 'web/controller/?route=/user-settings/', true );

            xhr.addEventListener( 'load', () => {
                if( xhr.status >= 200 && xhr.status < 300 ) {
                    console.log( xhr.response );

                    let respond = JSON.parse( xhr.response );

                    result.innerHTML = respond.alert;

                    if( respond.status === true ) {
                        inputText.value = respond.input;

                        if( action.value === 'username' ) {
                            window.setTimeout( () => {
                                window.location = BASE_URL + 'access/?action=login';
                            }, 2800 );
                        }
                    }
                }
                else {
                    console.error( 'Error code: ' + xhr.status );
                }
            });

            xhr.send( data );
        });
    }
};

userProfile.init();
