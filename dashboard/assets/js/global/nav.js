{

    function Navigation() {

        const Nav         = document.getElementById('nav');
        const links       = Nav.querySelectorAll('a');
        const parentItems = Nav.querySelectorAll('.hasub');
        const subItems    = Nav.querySelectorAll('.isub li');

        links.forEach( link => {
            if( urlOf(link.href) === urlOf(document.URL) ) {
                link.closest('li').classList.add('current');
            }
        });

        subItems.forEach( subitem => {
            if( subitem.classList.contains('current') ) {
                subitem.closest('.isub').classList.add('opened');
                subitem.closest('.hasub').classList.add('isubopen');
            }

            subitem.addEventListener('mouseover', function () {
                subitem.closest('.hasub').classList.add('opendown');
            });

            subitem.addEventListener('mouseleave', function () {
                subitem.closest('.hasub').classList.remove('opendown');
            });
        });

        parentItems.forEach( item => {
            if( item.classList.contains('current') ) {

                item.classList.remove('current');
            }

            // parent<li> -> ( parentlink<a> ) -> sublist<ul> -> subitem<li>
            const parentLink = item.querySelector('a');

            if( urlOf(parentLink.href) === urlOf(document.URL) ) {
                parentLink.parentElement.classList.add('isubopen');

                item.querySelector('.isub').classList.add('opened');
            }
        });

        // Remove query string (?), hash (#) e trailing slash (/) final
        function urlOf( url ) {
            return url.split(/[?#]/)[0].replace(/\/$/, '');
        }
    }

    Navigation();

}