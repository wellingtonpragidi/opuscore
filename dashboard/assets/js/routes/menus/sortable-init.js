( function Sortable_Menu() {

    Make_Recursive_Sortable = function( element ) {
        new Sortable(element, {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag',
            draggable: 'li',  // define que os itens arrastaveis são os <li>
            onEnd: function() {
                // console.info('Mudança detectada');
                Menu_Builder.saveMenuOrder(); // chama a funcao de salvar no banco
            }
        });
        element.querySelectorAll('ul').forEach(list => {
            Make_Recursive_Sortable(list); // aplica nos filhos tambem (recursivo)
        });
    }


    document.addEventListener('DOMContentLoaded', () => {
        const rootList = document.querySelector('#menu-builder > ul');
        if( rootList ) {
            Make_Recursive_Sortable(rootList);
        }
    });

    // se precisar reusar depois de atualizar via AJAX, use isso:
    Menu_Builder.reinitializeSortable = function() {
        const rootList = document.querySelector('#menu-builder > ul');
        if( rootList ) {
            Make_Recursive_Sortable(rootList);
        }
    };

})();