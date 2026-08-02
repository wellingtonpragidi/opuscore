window.OpusCore = window.OpusCore || {};
window.OpusCore.dist = {
    passwordGenerator( opt = {} ) {

        let limit  = opt.limit ?? 12;
        let input  = document.getElementById(opt.input ?? 'pswd');
        let button = document.getElementById(input.dataset.generator);

        if( ! button || ! input ) {
            return;
        }

        if( limit < 8 || limit > 24 ) {
            OpusCore.debug('limit deve ser o mínimo 8 e máximo 24');
            return;
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();

            const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lower = 'abcdefghijklmnopqrstuvwxyz';
            const nums  = '1234567890';
            const specs = '_-.~@!#$%&*()_+=-[]{}^><.:;?';

            // calcula proporcao com base no limite
            const proportion = limit > 21 ? 5 : (
                limit > 13 ? 3 : (
                    limit > 9 ? 2 : 1
                )
            );

            let numQty  = proportion;
            let specQty = proportion;
            
            // O que sobrar vai para as letras
            let remainder = limit - ( numQty + specQty );
            let upperQty  = Math.floor( remainder / 2 );
            // Pega o restante caso limit seja impar
            let lowerQty  = remainder - upperQty; 

            // criar o montante de caracteres da vez
            let pool = [];
            let cryptoRandom = new Uint32Array( limit );
            window.crypto.getRandomValues( cryptoRandom );
            let rIdx = 0; // Ponteiro para usar os números aleatórios gerados

            // funcao para pegar N caracteres de um grupo
            const grab = (str, qty) => {
                for( let i = 0; i < qty; i++ ) {
                    pool.push( str[ cryptoRandom[rIdx++] % str.length ] );
                }
            };

            // preenche o montante obedecendo a proporcao exata
            grab( upper, upperQty );
            grab( lower, lowerQty );
            grab( nums, numQty );
            grab( specs, specQty );

            // embaralha o montante usando Crypto para a senha nao vir previsivel
            let shuffleRandom = new Uint32Array( limit );
            window.crypto.getRandomValues( shuffleRandom );
            
            for( let i = pool.length - 1; i > 0; i-- ) {
                let j = shuffleRandom[i] % (i + 1);
                let temp = pool[i];
                pool[i] = pool[j];
                pool[j] = temp;
            }

            // transforma o array na string final
            input.value = pool.join('');

            input.dispatchEvent(
                new Event( 'input', { 
                    bubbles: true 
                })
            );
        });
    },
        
    password: document.querySelectorAll('.pswd'),

    passwordToggle() {
        if( ! this.password ) {
            return;
        }
        this.password.forEach( pswd => {
            let look = document.createElement('span');
            look.classList.add('look');
            look.innerHTML = '&#x1F441;';
            pswd.after(look);

            let inputype = pswd.getAttribute('type');
            if( inputype === 'text' ) {
                look.setAttribute('title', 'Ocultar senha');
            }
            if( inputype === 'password' ) {
                look.setAttribute('title', 'Visualizar senha');
                look.classList.add('hidden');
            }

            look.addEventListener( 'click', () => {
                let inputype = pswd.getAttribute('type');
                if( inputype === 'text' ) {
                    pswd.setAttribute('type', 'password');
                    look.setAttribute('title', 'Visualizar senha');
                    if( look.classList.contains('hidden') ) {
                        look.classList.remove('hidden');
                    } 
                    else {
                        look.classList.add('hidden');
                    }
                }
                if( inputype === 'password' ) {
                    pswd.setAttribute('type', 'text');
                    look.setAttribute('title', 'Ocultar senha');
                    if( look.classList.contains('hidden') ) {
                        look.classList.remove('hidden');
                    } 
                    else {
                        look.classList.add('hidden');
                    }
                }
            });
        });
    }

};