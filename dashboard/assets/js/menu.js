// Ao carregar o DOM, verifica se existe um tipo de item salvo no localStorage
// Se existir, exibe a lista correspondente no menu
// Em seguida remove o item do localStorage para nao manter o estado
document.addEventListener("DOMContentLoaded", function () {
    var extended = localStorage.getItem("lastActiveType");
    if( extended) {
        var sourceItems = document.getElementById(`source-${extended}`);
        if( sourceItems) {
            sourceItems.style.display = "block";
        }
        localStorage.removeItem("lastActiveType");
    }
});

const MENU_CONTROLLER = DASH_URL + '/controller/async/menu.php';

let MenuBuilder = {
    init: function() {
        this.addCheckedItems();
        this.addItemCustom();
        this.addItemAuth();
        this.saveEditedItem();
        this.deleteItem();
        this.alertRole();
    },
    
    result: document.querySelectorAll(".response"),
    alertRole() {
        this.result.forEach( alert => {
            alert.style.display = "none";
        });
    },

    addCheckedItems() {
        let buttons = document.querySelectorAll(".add_checked_items");
        buttons.forEach(button => {
            button.addEventListener("click", function() {
                let type = this.value;

                // checkboxes do tipo (e home-page)
                let selector = `.checkboxes-list input[type="checkbox"][data-type="${type}"]:checked`;
                let checkboxes = Array.from( document.querySelectorAll(selector) );

                if( type === "page" ) {
                    let homePage = document.querySelector('.checkboxes-list input[data-type="home-page"]:checked');
                    if( homePage ) {
                        checkboxes.push( homePage );
                    }
                }
                if( ! checkboxes.length ) {
                    return alert("Selecione pelo menos um item para adicionar.");
                }
                let items = checkboxes.map( el => ({
                    related_id: el.value,
                    type:       el.dataset.type
                }));

                let data = new FormData();
                data.append("action", "add_bulk_items");
                data.append("menu_name", CURRENT_MENU);
                data.append("items", JSON.stringify(items));

                let xhr = new XMLHttpRequest();
                xhr.open( "POST", MENU_CONTROLLER );
                xhr.addEventListener("load", function() {
                    console.log(this.response)
                    let json = JSON.parse(xhr.response);
                    if( json.success ) {
                        localStorage.setItem("lastActiveType", type);
                        MenuBuilder.refreshMenuTree();
                    } 
                    else {
                        alert("Ocorreu algum erro.");
                    }
                });
                xhr.send(data);
            });
        });
    },

    addItemCustom: function() {
        let form = document.getElementById("item-form-custom");
        let action = form.querySelector("button");
        let responseCustom = form.querySelector(".response");
        let customLabel = form.querySelector("#custom_label");
        let customUrl   = form.querySelector("#custom_url");
        action.addEventListener('click', function() {
            let data = new FormData(form);
            data.append( "action", "add_item_custom" );
            data.append( "menu_name", CURRENT_MENU );
            let xhr = new XMLHttpRequest();
            xhr.open( "POST", MENU_CONTROLLER );
            xhr.addEventListener("load", function () {
                responseCustom.style.display = "block";
                responseCustom.innerHTML = xhr.response;
                MenuBuilder.refreshMenuTree();
                window.setTimeout( function() {
                    customLabel.value = '';
                    customUrl.value = '';
                    fade.out.get( responseCustom, 700 );
                }, 3000);
            });
            xhr.send(data);
        });
    },

    addItemAuth: function() {
        let form = document.getElementById("item-form-auth");
        if( ! form ) {
            return;
        }
        form.querySelector("button").addEventListener('click', function() {
            let responseAuth = form.querySelector(".response");
            let data = new FormData(form);
            data.append( "action", "add_item_auth" );
            data.append( "menu_name", CURRENT_MENU );
            let xhr = new XMLHttpRequest();
            xhr.open( "POST", MENU_CONTROLLER );
            xhr.addEventListener("load", function () {
                responseAuth.style.display = "block";
                responseAuth.textContent = xhr.response;
                MenuBuilder.refreshMenuTree();
                window.setTimeout(function () {
                    fade.out.get( responseAuth, 700 );
                }, 3000);
            });
            xhr.send(data);
        });
    },

    saveMenuOrder() {
        let structure = this.getStructure();
        let saveOrder = document.querySelector(".response.save-order");
        let xhr = new XMLHttpRequest();
        xhr.open( "POST", MENU_CONTROLLER );
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.addEventListener("load", function () {
            if( xhr.status >= 200 && xhr.status < 300) {
                try {
                    let data = JSON.parse(xhr.responseText);
                    saveOrder.style.display = "block";
                    if( data.success) {
                        saveOrder.textContent = "Ordem salva.";
                        saveOrder.classList.remove("txt_error");
                        saveOrder.classList.add("txt_success");
                        MenuBuilder.elementTimeOut(saveOrder);
                    } 
                    else {
                        saveOrder.classList.replace("txt_success", "txt_error");
                        saveOrder.textContent = "Erro ao salvar ordem.";
                        MenuBuilder.elementTimeOut(saveOrder);
                    }
                } 
                catch (err) {
                    saveOrder.style.display = "block";
                    saveOrder.classList.replace("txt_success", "txt_error");
                    saveOrder.textContent = "Erro inesperado ao processar resposta.";
                    MenuBuilder.elementTimeOut(saveOrder);
                }
            } 
            else {
                saveOrder.style.display = "block";
                saveOrder.classList.replace("txt_success", "txt_error");
                saveOrder.textContent = `Erro na requisicao:${xhr.status}${xhr.statusText}`;
                MenuBuilder.elementTimeOut(saveOrder);
            }
        });
        xhr.send( JSON.stringify({ action: "reorder_menu_items", items: structure }) );
    },

    getStructure() {
        let parseList = (ul, parentId = 0) => {
            let structure = [];
            ul.querySelectorAll(":scope > li").forEach((li, index) => {
                var id = li.dataset.id;
                structure.push({ id: id, parent: parentId, sort: index });
                let childUl = li.querySelector("ul");
                if( childUl) {
                    structure = structure.concat(parseList(childUl, id));
                }
            });
            return structure;
        };
        let root = document.querySelector("#menu-builder > ul");
        if( ! root ) {
            return [];
        }
        return parseList(root);
    },

    saveEditedItem() {
        let forms = document.querySelectorAll(".menu_items_form");
        forms.forEach( element => {
            var form = document.getElementById(element.id);
            var button = form.querySelector(".btn_edit");
            button.addEventListener('click', function() {
                let data = new FormData(form);
                data.append("action", "update_menu_item");
                var dataID = form.querySelector(".edit_id").value;
                let xhr = new XMLHttpRequest();
                xhr.open( "POST", MENU_CONTROLLER );
                xhr.addEventListener("load", function() {
                    let editItem = form.querySelector(".response");
                    editItem.style.display = "block";
                    let json = JSON.parse(xhr.responseText);
                    if( json.success) {
                        editItem.textContent = "Item atualizado";
                        let label = form.closest('.item');
                        label = label.querySelector('.drag');
                        label.textContent = json.message;
                        editItem.classList.remove("txt_error");
                        editItem.classList.add("txt_success");
                        MenuBuilder.elementTimeOut(editItem);
                    } 
                    else {
                        editItem.classList.replace("txt_success", "txt_error");
                        editItem.textContent = "Erro ao atualizar";
                        MenuBuilder.elementTimeOut(editItem);
                    }
                });
                xhr.send(data);
            });
        });
    },

    deleteItem() {
        let forms = document.querySelectorAll(".menu_items_form");
        forms.forEach( element => {
            var form = document.getElementById(element.id);
            var button = form.querySelector(".btn_delete");
            button.addEventListener('click', function() {
                if( ! confirm("Vai mesmo deletar esse item do menu?") ) {
                    return;
                }
                let data = new FormData();
                let item_id = form.querySelector(".edit_id").value;
                data.append("action", "delete_menu_item");
                data.append("delete_id", item_id);
                let xhr = new XMLHttpRequest();
                xhr.open( "POST", MENU_CONTROLLER );
                xhr.addEventListener("load", function () {
                    let json = JSON.parse(xhr.responseText);
                    if( json.success ) {
                        let listItem = document.querySelector(`li[data-id="${item_id}"]`);
                        if( listItem ) {
                            listItem.remove();
                        }
                    } 
                    else {
                        alert(`Erro ao excluir item:${json.message || "Erro desconhecido."}`);
                    }
                });
                xhr.send(data);
            });
        });
    },

    refreshMenuTree() {
        let xhr = new XMLHttpRequest();
        let select = document.getElementById("select-menu");
        let currentMenuName = select ? select.value : "";
        xhr.open("GET", `${MENU_CONTROLLER}?render_tree=1&key=${encodeURIComponent(currentMenuName)}`);
        xhr.addEventListener("load", function () {
            if( xhr.status >= 200 && xhr.status < 300 ) {
                //console.log(this.response)
                let wrapper = document.querySelector("#menu-builder");
                wrapper.innerHTML = xhr.response;

                // recarrega icones nos novos elementos
                refreshIcons(wrapper);

                // Reaplica accordion apenas nos novos elementos
                accordion.refresh(wrapper);

                MenuBuilder.reinitializeSortable();

                // reinicia esses novamente do (MenuBuilder.init) 
                MenuBuilder.saveEditedItem();
                MenuBuilder.deleteItem();
            }
        });
        xhr.send();
    },

    elementTimeOut( selector, time = 2600 ) {
        window.setTimeout(function () {
            fade.out.get(selector, 700);
        }, time);
    }
};

MenuBuilder.init();