let editor = ace.edit("editor");

editor.setTheme("ace/theme/monokai");

editor.session.setMode("ace/mode/html");

editor.setOptions({
    enableBasicAutocompletion: true,
    enableLiveAutocompletion: true,
    tabSize: 4,
    useSoftTabs: true,
    fontSize: "17px",
    wordWrap: true,
    wrap: true,
    useWrapMode: true,
    indentedSoftWrap: false
});

// PEGA O CONTEUDO DIRETO do textarea
let textarea = document.getElementById("output");
editor.setValue(textarea.value);

const EVENTS = ['keyup', 'input', 'paste', 'drop', 'change'];

EVENTS.forEach( event => {
    editor.session.on(event, function() { 
        // Atualiza o textarea com o conteudo do editor
        textarea.value = editor.getValue();
    });
});

// Garante sincronizacao no submit
document.querySelector('form').addEventListener('submit', function() {
    textarea.value = editor.getValue();
});