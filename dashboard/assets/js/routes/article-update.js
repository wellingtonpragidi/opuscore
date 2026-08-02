( function Article_Segment() {

    let checkboxes  = document.querySelectorAll('[name="checkcat[]"]');
    let select      = document.getElementById('segment');
    let articleslug = document.getElementById('edit-slug');

    function selectSegment() {
        let baseOption = select.options[0]; // o <option> vindo do HTML/PHP
        let slug = articleslug.value.trim();

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
                let articleSegment = catslug + '/' + slug;

                // para nao recria um option igual ao que vem padrao do PHP/HTML
                if( baseOption.value === articleSegment ) {
                    return;
                }

                let option = document.createElement('option');
                option.value = articleSegment;
                option.textContent = articleSegment;

                select.appendChild(option);
            }
        });
    }

    if( ! select || ! checkboxes || ! articleslug ) {
        return;
    }

    select.addEventListener('change', () => {
        document.querySelector('[name="segment_changed"]').value = 1;
    });        

    checkboxes.forEach( ckb => {
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

    articleslug.addEventListener( 'input', selectSegment );

})();