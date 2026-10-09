
( function() {

    document.getElementById('category').addEventListener('change', function() {

        const categoryID = this.value;

        if( categoryID === '0' ) {

            window.location.href = OpusCore.dash_url + 'articles';
            return;
        }

        window.location.href = OpusCore.dash_url + 'articles/?by=category&id=' + categoryID;

        console.log( OpusCore.dash_url )
    });

})();