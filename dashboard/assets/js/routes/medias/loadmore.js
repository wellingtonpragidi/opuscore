( function() {
    let offset = 0;

    let scroll = document.createElement("div");
    scroll.className = "scroll";

    let btnLoadmore = document.createElement("button");
    btnLoadmore.type = "button";
    btnLoadmore.className = "loadmore btn xlg";
    btnLoadmore.textContent = "Carregar mais";

    let selector = document.getElementById('gallery');
    selector.appendChild(scroll);
    selector.appendChild(btnLoadmore);

    function loadData() {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", OpusCore.media.loading_url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.addEventListener('load', () => {
            if( xhr.status >= 200 && xhr.status < 400 ) {
                OpusCore.debug( xhr.response );

                let medias = JSON.parse( xhr.response );

                if( medias.button === false ) {
                    btnLoadmore.style.display = "none";
                }

                scroll.insertAdjacentHTML("beforeend", medias.content);

                offset += OpusCore.media.limit;
            }
        });

        let params = 
            "limit=" + encodeURIComponent(OpusCore.media.limit) + 
            "&offset=" + encodeURIComponent(offset);

        xhr.send(params);
    }

    loadData();
    btnLoadmore.addEventListener("click", loadData);

})();