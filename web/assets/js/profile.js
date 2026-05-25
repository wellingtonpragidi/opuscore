let xhr = new XMLHttpRequest();

var upload = document.querySelector("#upload");
var image = document.querySelector("#image-profile img");
upload.addEventListener('change', function() {
    var file = this.files[0];
    var data = new FormData();
    data.append("upload", file);
    data.append("getid", document.querySelector("#edit_image .getid").value);
    data.append("imgname", document.getElementById("imgname").value);
    data.append("imgtitle", document.getElementById("imgtitle").value);
    xhr.open("POST", URL + 'web/controller/user-picture.php');
    xhr.addEventListener('load', function() {
        if(xhr.status >= 200 && xhr.status < 300) {
            image.src = this.response;
        }
    });
    xhr.send(data); 
});

var editName = document.getElementById("edit_name");
var responseName = document.getElementById("response-name");
editName.addEventListener('submit', event => {
    event.preventDefault();
    var data = new FormData();
    data.append("getid", editName.querySelector(".getid").value);
    data.append("name", document.querySelector("#name").value);
    xhr.open("POST", URL + 'web/controller/user-name.php', true);
    xhr.addEventListener('load', function() {
        if(xhr.status >= 200 && xhr.status < 300) {
            var result = JSON.parse(this.response);
            if( document.querySelector(".master-title") ) {
                pageTitle = document.querySelector(".master-title");
                pageTitle.innerHTML = result.title;
            }
            responseName.textContent = result.alertext;
            window.setTimeout(function() {
                fade.out.selector('#response-name', 6000);
            }, 2100);
        }
    });
    xhr.send(data); 
});

var editUsername = document.getElementById("edit_username");
var responseUsername = document.getElementById("response-username");
editUsername.addEventListener('submit', event => {
    event.preventDefault();
    var data = new FormData();
    data.append("getid", editUsername.querySelector(".getid").value);
    data.append("username", document.querySelector("#username").value);
    xhr.open("POST", URL + 'web/controller/user-username.php', true);
    xhr.addEventListener('load', function() {
        if(xhr.status >= 200 && xhr.status < 300) {
            var result = JSON.parse(this.response);
            responseUsername.textContent = result.alertext;
            window.setTimeout( function() {
                fade.out.selector('#response-username', 3000);
                window.location = result.redirect;
            }, 5000);
        }
    });
    xhr.send(data); 
});
