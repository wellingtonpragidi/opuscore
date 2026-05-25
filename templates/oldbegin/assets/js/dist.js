
const toggle = {

    hide(element, duration = 500) {

        element.style.height = element.offsetHeight + "px";
        element.style.transition = "height " + duration + "ms";

        element.offsetHeight;

        element.style.overflow = "hidden";
        element.style.height = 0;

        window.setTimeout( () => {

            element.style.display = "none";

            element.style.removeProperty("height");
            element.style.removeProperty("overflow");
            element.style.removeProperty("transition");

        }, duration);

    },

    show(element, duration = 500) {

        element.style.removeProperty("display");

        let display = window.getComputedStyle(element).display;

        if( display === "none" ) {
            display = "block";
        }

        element.style.display = display;

        let height = element.scrollHeight;

        element.style.height = 0;

        element.offsetHeight;

        element.style.overflow = "hidden";
        element.style.transition = "height " + duration + "ms";
        element.style.height = height + "px";

        window.setTimeout( () => {

            element.style.removeProperty("height");
            element.style.removeProperty("overflow");
            element.style.removeProperty("transition");

        }, duration);

    },

    slide(element, duration = 500) {

        return window.getComputedStyle(element).display === "none"
            ? this.show(element, duration)
            : this.hide(element, duration);

    }

};


const navbar = {
    nav: document.querySelector("#navbar"),
    list: document.querySelector("#navbar .menu"),
    toggle: document.querySelector("#navbar .toggle"),
    switch: document.querySelector("#navbar .switch"),

    navLinks: document.querySelectorAll("#navbar a"),
    hasubLinks: document.querySelectorAll("#navbar .hasub > a"), // nivel 1
    isubLinks: document.querySelectorAll("#navbar .isub a"),

    deepSubs: document.querySelectorAll("#navbar .isub .hasub"), // nivel 2

    overlay: null,

    init() {
        if (!this.nav) return;

        this.createOverlay();
        this.mobile();
        this.keyboard();
        this.current();
        this.submenus();
        this.overlayClose();
    },

    createOverlay() {
        this.nav.insertAdjacentHTML('beforeend', '<div class="overlay"></div>');
        this.overlay = this.nav.querySelector(".overlay");
        this.overlay.style.display = "none";
    },

    mobile() {
        this.toggle.addEventListener('click', () => {
            this.list.classList.toggle("visible");
            this.toggle.classList.toggle("focus");
        });
    },

    keyboard() {
        this.switch.addEventListener('click', () => {
            this.list.querySelector("a")?.focus();
        });

        const first = this.navLinks[0];
        const last = this.navLinks[this.navLinks.length - 1];

        this.list.addEventListener("keydown", (ev) => {
            if (ev.key === "Tab" && document.activeElement === last) {
                first?.focus();
                ev.preventDefault();
            }
        });
    },

    current() {
        const url = document.URL;

        this.navLinks.forEach(link => {
            if (link.href === url) {
                link.parentElement.classList.add("current");
            }
        });

        this.isubLinks.forEach(link => {
            if (link.href === url) {
                link.closest(".hasub")?.classList.add("active");
            }
        });

        // Submenu de segundo nivel ativo
        const deepActive = document.querySelector("#navbar .hasub .hasub.active");
        if (deepActive) {
            deepActive.parentElement.parentElement.classList.add("active");
        }
    },

    submenus() {
        // ==================== NIVEL 1 ====================
        this.hasubLinks.forEach(element => {
            element.insertAdjacentHTML('beforeend', ' <span icon="caret"></span>');
            element.nextElementSibling.style.display = "none";

            element.addEventListener('click', (e) => {
                e.preventDefault();

                const submenu = element.nextElementSibling;
                const parentLi = element.parentElement;

                toggle.slide(submenu);

                parentLi.classList.toggle("open");

                this.updateOverlay();
            });
        });

        // ==================== NIVEL 2 ====================
        // sho 1 nivel de submenu
        this.deepSubs.forEach( element => {
            element.style.display = "none";
        });
    },

    updateOverlay() {
        if (!this.overlay) return;
        
        const openCount = document.getElementsByClassName('open').length;
        this.overlay.style.display = (openCount > 0) ? "block" : "none";
    },

    overlayClose() {
        if (!this.overlay) return;

        this.overlay.addEventListener('click', () => {
            this.nav.querySelectorAll(".open").forEach(item => {
                item.classList.remove("open");
            });

            this.nav.querySelectorAll(".isub").forEach(sub => {
                sub.style.display = "none";
            });

            this.overlay.style.display = "none";
        });
    }
};


document.addEventListener("DOMContentLoaded", () => {
    navbar.init();
});
