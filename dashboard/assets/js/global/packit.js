const packit = {
    init: function() {
        form.init();
        discard.close();
        popup.modal();
        accordion.init();
    },
    events: [
        'keydown', 'keyup', 'change', 'input', 'mousedown', 'mouseup', 'click', 'paste', 'drop'
    ]
};
let fade = {
    fadeIn: function(el, duration = 400) {
        if (!el) return;
        el.style.opacity = "0";
        el.style.display = "block";

        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = (timestamp - start) / duration;
            el.style.opacity = Math.min(progress, 1).toString();
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                el.style.opacity = "1";
            }
        };
        requestAnimationFrame(animate);
    },
    in: {
        get: function (element, duration = 400) {
            return fade.fadeIn(element, duration);
        },
        query: function (element, duration = 400) {
            return this.get(element, duration);
        },
        selector: function (element, duration = 400) {
            return fade.fadeIn(document.querySelector(element), duration);
        },
    },
    fadeOut: function(el, duration = 400) {
            if (!el || el.style.display === "none") return;
            let start = null;
            const beginOpacity = parseFloat(el.style.opacity) || 1;

            const animate = (timestamp) => {
                if (!start) start = timestamp;
                const progress = (timestamp - start) / duration;
                el.style.opacity = Math.max(beginOpacity * (1 - progress), 0).toString();
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    el.style.opacity = "0";
                    el.style.display = "none";
                }
            };
            requestAnimationFrame(animate);
        },
    out: {
        get: function (element, duration = 400) {
            return fade.fadeOut(element, duration);
        },
        query: function (element, duration = 400) {
            return this.get(element, duration);
        },
        selector: function (element, duration = 400) {
            return fade.fadeOut(document.querySelector(element), duration);
        },
    },
};

let toggle = {
    hide: function (element, duration = 500) {
        return new Promise(function() {
            element.style.height = element.offsetHeight + "px";
            element.style.transition = "height " + duration + "ms";
            element.offsetHeight;
            element.style.overflow = "hidden";
            element.style.height = 0;
            window.setTimeout(function() {
                element.style.display = "none";
                element.style.removeProperty("height");
                element.style.removeProperty("overflow");
                element.style.removeProperty("transition");
            }, duration);
        });
    },
    show: function (element, duration = 500) {
        return new Promise(function() {
            element.style.removeProperty("display");
            let display = window.getComputedStyle(element).display;
            if( display === "none") {
                display = "block";
            }
            element.style.display = display;
            let height = element.offsetHeight;
            element.style.height = 0;
            element.offsetHeight;
            element.style.overflow = "hidden";
            element.style.transition = "height " + duration + "ms";
            element.style.height = height + "px";
            window.setTimeout(function() {
                element.style.removeProperty("height");
                element.style.removeProperty("overflow");
                element.style.removeProperty("transition");
            }, duration);
        });
    },
    slide: function (element, duration = 500) {
        if( window.getComputedStyle(element).display == "none") {
            return this.show(element, duration);
        } 
        else {
            return this.hide(element, duration);
        }
    },
};
let discard = {
    dismiss: document.querySelectorAll(".discard"),
    close: function() {
        this.dismiss.forEach((element) => {
            if( element.hasAttribute("data-close-element")) {
                element.setAttribute("type", "button");
                let btnClose = document.getElementById(element.dataset.closeElement);
                element.addEventListener("click", (event) => {
                    if( btnClose && btnClose.hasAttribute("id") && btnClose.id == element.dataset.closeElement) {
                        event.preventDefault();
                        fade.out.query(btnClose, 800);
                    } else {
                        console.info(
                            `Nenhum elemento com atributo "id".\nOu o atributo "id" não é igual o valor do atributo "data-close-element".`
                        );
                    }
                });
            } else {
                let size;
                if( element.hasAttribute("data-icon-size")) {
                    size = element.dataset.iconSize;
                } else {
                    size = "38";
                }
                let iconClose = document.createElement("div");
                iconClose.classList.add("close");
                let sz = parseInt(size);
                let calcSize = sz - sz * 0.46;
                iconClose.style.width = calcSize + "px";
                iconClose.style.height = calcSize + "px";
                iconClose.innerHTML =
                    `<span icon="close" size="` + size + `" aria-label="Fechar" title="Fechar"></span>`;
                element.prepend(iconClose);
                if( element.closest(".discard_this")) {
                    iconClose.addEventListener("click", (event) => {
                        event.preventDefault();
                        fade.out.selector(".discard_this", 800);
                    });
                } else {
                    iconClose.addEventListener("click", (event) => {
                        event.preventDefault();
                        fade.out.query(element, 800);
                    });
                }
            }
        });
    },
};
let form = {
    autorise: function() {
        document.querySelectorAll(".autorise").forEach((element) => {
            element.style.height = element.scrollHeight + "px";
            element.addEventListener("input", function() {
                element.style.height = 0;
                element.style.height = element.scrollHeight + "px";
            });
        });
    },
    textfield: function() {
        document.execCommand("defaultParagraphSeparator", false, "p");
        let elementEditable = document.querySelectorAll(".textfield [contenteditable]");
        elementEditable.forEach( element => {
            let textarea = document.getElementById(element.dataset.content);
            textarea.style.display = "none";
            packit.events.forEach( event  => {
                element.addEventListener(event, function() {
                    textarea.value = this.innerHTML;
                });
            });
        });
    },
    pass: document.querySelectorAll("input.pswd"),
    inputpassword: function() {
        this.pass.forEach((pswd) => {
            let look = document.createElement("span");
            look.classList.add("look");
            look.innerHTML = "&#x1F441;";
            pswd.after(look);
            let inputype = pswd.getAttribute("type");
            if( inputype == "text") {
                look.setAttribute("title", "Ocultar senha");
            }
            if( inputype == "password") {
                look.setAttribute("title", "Visualizar senha");
                look.classList.add("hidden");
            }
            look.addEventListener("click", function() {
                let inputype = pswd.getAttribute("type");
                if( inputype == "text") {
                    pswd.setAttribute("type", "password");
                    this.setAttribute("title", "Visualizar senha");
                    if( this.classList.contains("hidden")) {
                        this.classList.remove("hidden");
                    } else {
                        this.classList.add("hidden");
                    }
                }
                if( inputype == "password") {
                    pswd.setAttribute("type", "text");
                    this.setAttribute("title", "Ocultar senha");
                    if( this.classList.contains("hidden")) {
                        this.classList.remove("hidden");
                    } else {
                        this.classList.add("hidden");
                    }
                }
            });
        });
    },
    upload: document.querySelectorAll(".upload"),
    inputfile: function() {
        this.upload.forEach( (element) => {
            let label = element.querySelector("label");
            label.addEventListener("click", function() {
                this.classList.add("focus");
                setTimeout(function() {
                    label.classList.remove("focus");
                }, 60000);
            });

            let input = element.querySelector("input[type=file]");
            input.addEventListener("change", function() {
                label.classList.remove("focus");
            });
            
            if( element.hasAttribute("id")) {
                let self = document.getElementById(element.id);
                let input = self.querySelector("input[type=file]");
                let filename = document.createElement("span");
                filename.classList.add("filename");
                self.append(filename);
                input.addEventListener("change", function() {
                    filename.textContent = this.value.replace(/C:\\fakepath\\/i, "");
                });

                if( self.classList.contains("readers")) {
                    self.removeChild(self.querySelector(".filename"));

                    let output = document.createElement("div");
                    output.className = "output flexbox pack";
                    self.append(output);
                    output = self.querySelector(".output");

                    input.addEventListener("change", (event) => {
                        let files = event.target.files;
                        for( let i = 0; i < files.length; i++ ) {
                            let file = files[i];
                            if( ! file.type.match("image") ) {
                                continue;
                            }
                            let reader = new FileReader();
                            reader.addEventListener('load', (event) => {
                                let fileName = file.name.replace(/\.[^/.]+$/, "");
                                if( input.hasAttribute("multiple") ) {
                                    let col = document.createElement("div");
                                    col.classList.add("cn_25");
                                    output.prepend(col);
                                    let innerCol = document.createElement("div");
                                    col.prepend(innerCol);
                                    let image = document.createElement("img");
                                    image.src = event.target.result;
                                    image.title = file.name;
                                    image.alt = fileName;
                                    innerCol.prepend(image);
                                } 
                                else {
                                    output.innerHTML =
                                    `<div class="cn_100"> 
                                        <img 
                                            src="` + event.target.result + `" 
                                            title="` + file.name + `" 
                                            alt="` + fileName + `" 
                                        /> 
                                    </div>`;
                                }
                            });

                            reader.readAsDataURL(file);
                        }
                    });
                }
            }
        });
    },
    init: function() {
        this.autorise();
        this.textfield();
        this.inputpassword();
        this.inputfile();
    },
};
let popup = {
    openDialog: document.querySelectorAll("[data-popup-modal]"),
    popupDialog: document.querySelectorAll(".popup"),
    modalDialog: document.querySelectorAll(".modal"),
    modal: function() {
        this.openDialog.forEach((element) => {
            element.addEventListener("click", function() {
                let dialog = document.getElementById(this.dataset.popupModal);
                dialog.style.display = "block";
                document.body.style.overflowY = "hidden";
            });
        });
        this.popupDialog.forEach((element) => {
            element.setAttribute("role", "dialog");
            element.setAttribute("aria-modal", "true");
            let overlay = document.createElement("div");
            overlay.classList.add("popup_over");
            element.append(overlay);
            if( element.hasAttribute("data-popup-overlay")) {
                let dialog = document.querySelector("#" + element.id + " .modal");
                overlay.addEventListener("click", function() {
                    dialog.classList.add("shake");
                    window.setTimeout(function() {
                        dialog.classList.remove("shake");
                    }, 450);
                    dialog.classList.add("show");
                });
                document.addEventListener("keydown", (event) => {
                    if( event.key == "Escape") {
                        dialog.classList.add("shake");
                        window.setTimeout(function() {
                            dialog.classList.remove("shake");
                        }, 450);
                        dialog.classList.add("show");
                    }
                });
            } else {
                overlay.addEventListener("click", function() {
                    fade.out.get(element, 400);
                    window.setTimeout(function() {
                        element.style.removeProperty("display");
                        element.style.removeProperty("opacity");
                        document.body.style.removeProperty("overflow-y");
                    }, 450);
                });
                document.addEventListener("keydown", (event) => {
                    if( event.key == "Escape") {
                        fade.out.get(element, 400);
                        window.setTimeout(function() {
                            element.style.removeProperty("display");
                            element.style.removeProperty("opacity");
                        }, 450);
                    }
                });
            }
        });
        this.modalDialog.forEach((element) => {
            let close = document.createElement("button");
            close.classList.add("popup_close");
            close.setAttribute("icon", "close");
            close.setAttribute("aria-label", "Click para fechar");
            element.prepend(close);
            let closeElement = element.parentElement;
            if( element.parentElement.classList.contains("axis_xy")) {
                closeElement = element.parentElement.parentElement;
            }
            close.addEventListener("click", function() {
                fade.out.get(closeElement, 400);
                window.setTimeout(function() {
                    closeElement.style.removeProperty("display");
                    closeElement.style.removeProperty("opacity");
                    document.body.style.removeProperty("overflow-y");
                }, 450);
                element.classList.remove("show");
            });
        });
        if( document.querySelectorAll("[data-close-element]")) {
            let btnDiscard = document.querySelectorAll("[data-close-element]");
            btnDiscard.forEach((element) => {
                element.addEventListener("click", function() {
                    let closeElement = document.getElementById(this.dataset.closeElement);
                    fade.out.get(closeElement, 400);
                    window.setTimeout(function() {
                        closeElement.style.removeProperty("display");
                        closeElement.style.removeProperty("opacity");
                        document.body.style.removeProperty("overflow-y");
                    }, 450);
                    let dialog = document.querySelector("#" + this.dataset.closeElement + " .modal");
                    dialog.classList.remove("show");
                });
            });
        }
    },
};
let accordion = {
    btnExtend: document.querySelectorAll(".accordion.expand > .acc_btn"),
    btnCollapse: document.querySelectorAll(".accordion.collapse > .acc_btn"),
    accBtn: document.querySelectorAll(".acc_btn"),
    btnsInit: function( wrapper = document ) {
        this.btnExtend = wrapper.querySelectorAll(".accordion.expand > .acc_btn");
        this.btnCollapse = wrapper.querySelectorAll(".accordion.collapse > .acc_btn");
        this.accBtn = wrapper.querySelectorAll(".acc_btn");
        this.accBtn.forEach( btn => btn.setAttribute("type", "button") );
    },
    expand: function() {
        if( ! document.querySelector(".accordion") ) {
            return;
        }
        this.btnExtend.forEach((element) => {
            let panel = element.nextElementSibling;
            element.addEventListener("click", function() {
                this.classList.toggle("expanded");
                toggle.slide(panel, 600);
            });
        });
    },
    collapse: function() {
        if( ! document.querySelector(".accordion") ) {
            return;
        }
        this.btnCollapse.forEach((element) => {
            let panel = element.nextElementSibling;
            element.addEventListener("click", function() {
                if( this.classList.contains("extended")) {
                    this.classList.remove("extended");
                    toggle.slide(panel, 600);
                } 
                else {
                    let current = document.getElementsByClassName("extended");
                    if( current.length > 0) {
                        toggle.slide(current[0].nextElementSibling, 600);
                        current[0].className = current[0].className.replace(" extended", "");
                    }
                    this.className += " extended";
                    this.nextElementSibling += toggle.slide(panel, 600);
                }
            });
        });
    },
    init: function( wrapper = document ) {
        this.btnsInit(wrapper);
        this.expand();
        this.collapse();
    },
    refresh: function( wrapper = document ) {
        // reaplica nos elementos recem-injetados
        this.init(wrapper);
    }
};

packit.init();