tinymce.init({
    selector: "#editor",
    document_base_url: OpusCore.base_url,
    menubar: false,
    plugins: "autolink image link lists media table wordcount code",
    toolbar:
        "blocks fontsize align forecolor | codeformat bold italic underline | numlist bullist | link unlink autolink hr image media | table | removeformat code",
    block_formats:
        "Header 2=h2; Header 3=h3; Header 4=h4; Header 5=h5; Header 6=h6; Blockquote=blockquote; Preformatted=pre; Paragraph=p; Address=address;",
    height: "690",
    spellchecker_language: "pt_BR",
    spellchecker_languages: "Portuguese=pt_BR",
    schema: "html5",
    branding: false,
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    convert_fonts_to_spans: true,
    fontsize_formats: "10px 11px 13px 16px 17px 19px 22px 24px",
    file_picker_callback: (callback, value, meta) => {
        if( meta.filetype == "image") {
            let fileupload = document.getElementById("upload");

            fileupload.addEventListener("change", (event) => {
                let file     = event.target.files[0];
                let reader   = new FileReader();
                let formdata = new FormData();

                formdata.append("upload", file);
                formdata.append("filetype", meta.filetype);
                formdata.append("target_type", document.getElementById("target_type").value);
                formdata.append("target_id", document.getElementById("target_id").value);
                formdata.append("title", document.getElementById("title").value);

                let xhr = new XMLHttpRequest();

                xhr.open("POST", `${OpusCore.media.editor.upload_url}`, true);
                xhr.addEventListener("load", function () {
                    if( this.status >= 200 && this.status <= 300) {
                        callback(this.response);
                    }
                });
                xhr.send(formdata);
            });
        }
        fileupload.click();
    },
    entity_encoding: "raw",
    image_dimensions: true,
    image_caption: true,
    imagetools_toolbar: "editimage imageoptions",
    automatic_uploads: false,
    content_style:
        'body{background-color: #1c2420 !important; color: #949683 !important; font-family: "Segoe UI","Muli",system-ui,Roboto,Oxygen,Ubuntu,Fira Sans,Droid Sans,Helvetica Neue,sans-serif !important; font-size: 1.15rem !important;letter-spacing: 1.15px !important} body p{margin: 10px 0; !important}body p:first-child{margin-top: 0}body p:last-child {margin-bottom: 0}code[class*="language-"], pre[class*="language-"] {background:rgba(255,255,255,0.15) !important;border:none;padding:14px !important;text-shadow:none !important}code, pre{background-color:rgba(255,255,255,0.10) !important;}pre{padding:10px !important;}',
});
