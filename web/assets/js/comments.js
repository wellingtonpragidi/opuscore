let comments = {
    commentform: document.getElementById("commentform"),
    area: document.getElementById("comment"),
    parent: document.getElementById("parent"),
    related: document.getElementById("related"),
    listComments: document.getElementById("list-comments"),
    userImage: document.getElementById("userimage"),
    userUrl: document.getElementById("userurl"),
    xhr: new XMLHttpRequest(),
    main: function() {
        this.commentform.addEventListener('submit', event => {
        	event.preventDefault();
        	var data = new FormData();
        	data.append('comment', this.area.value);
        	data.append('parent', this.parent.value);
        	data.append('related', this.related.value);
            data.append('userimage', this.userImage.value);
            data.append('userurl', this.userUrl.value);
            this.xhr.open("POST", URL + "web/controller/comment.php");
            this.xhr.addEventListener('load', function() {
        	    if(this.status == 200) {
                    comments.listComments.insertAdjacentHTML('afterbegin', this.response);
                    comments.area.value = "";
        	    }
            });
            this.xhr.send(data);
        });
    },
    reply: function() {}
}
comments.main();