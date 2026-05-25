<script>
let fade = {
    fadeIn: function( selection, duration = 400 ) {
        var start, begin, startime, time, effect, currentime, timediff, show;
        if( selection.style.getPropertyValue("display") == "none" ) {
            selection.style.opacity = "0";
            selection.style.display = "none";
            start = 1;
            begin = parseFloat(selection.style.opacity) || 0;
            startime = Date.now();
            time = 1000 / 60;
            effect = setInterval(() => {
                currentime = Date.now();
                timediff = (currentime - startime) / duration;
                show = begin - (begin - start) * timediff;
                selection.style.opacity = show.toString();
                if(timediff >= 1) {
                    clearInterval(effect);
                    effect = 1;
                    selection.style.opacity = "1";
                }
                if(timediff * timediff) {
                    selection.style.display = "block";
                }
            }, time);
        }
        else {
            return;
        }
    },
    in: {
        get: function(element, duration = 400) {
            return fade.fadeIn(element, duration);
        },
        query: function(element, duration = 400) {
            return this.get(element, duration);
        },
        selector: function(element, duration = 400) {
            return fade.fadeIn(document.querySelector(element), duration);
        }
    },
    
    fadeOut: function(selection, duration = 400) {
        var start, begin, startime, time, effect, currentime, timediff, show;
        if(
            selection.style.getPropertyValue("display") == "block" ||
            selection.style.getPropertyValue("display") == false
        ) {
            start = 0;
            begin = parseFloat(selection.style.opacity) || 1;
            startime = Date.now();
            time = 1000 / 60;
            effect = setInterval(() => {
                currentime = Date.now();
                timediff = (currentime - startime) / duration;
                show = begin - (begin - start) * timediff;
                if(timediff >= 1) {
                    clearInterval(effect);
                    effect = 0;
                    selection.style.opacity = "0";
                    selection.style.display = "none";
                }
                if(timediff * timediff) {
                    selection.style.opacity = show.toString();
                }
                
            }, time);
        }
        else {
            return;
        }
    },
    out: {
        get: function(element, duration = 400) {
            return fade.fadeOut(element, duration);
        },
        query: function(element, duration = 400) {
            return this.get(element, duration);
        },
        selector: function(element, duration = 400) {
            return fade.fadeOut(document.querySelector(element), duration);
        }
    }
};
/* look passoword */
let look = {
    pass: document.querySelectorAll("input.pswd"),
    password: function() {
        this.pass.forEach(pswd => {
            var look = document.createElement("span");
            look.classList.add("look");
            look.innerHTML = "&#x1F441;";
            pswd.after(look);
            var inputype = pswd.getAttribute("type");
            if(inputype == "text") {
                look.setAttribute("title", "Ocultar senha");
            }
            if(inputype == "password") {
                look.setAttribute("title", "Visualizar senha");
                look.classList.add("hidden");
            }
            look.addEventListener('click', function() {
                var inputype = pswd.getAttribute("type");
                if(inputype == "text") {
                    pswd.setAttribute("type", "password");
                    this.setAttribute("title", "Visualizar senha");
                    if(this.classList.contains("hidden")) {
                        this.classList.remove("hidden");
                    } else {
                        this.classList.add("hidden");
                    }
                }
                if(inputype == "password") {
                    pswd.setAttribute("type", "text");
                    this.setAttribute("title", "Ocultar senha");
                    if(this.classList.contains("hidden")) {
                        this.classList.remove("hidden");
                    } else {
                        this.classList.add("hidden");
                    }
                }
            });
        });
    }
}
look.password();
</script>
</body>
</html>