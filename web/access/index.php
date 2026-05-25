<?php
$show_form = true;

switch( URL::GET('action') ) {
    case 'login':
        $filename = 'login.php';
        $title = 'Login';
    break;
    case 'register':
        $filename = 'register.php';
        $title = 'Criar conta';
    break;
    case 'activation':
        $filename = 'activation.php';
        $title = 'Ativar conta';
    break;
    case 'lost-password':
        $filename = 'lost-password.php';
        $title = 'Recuperar senha';
    break;
    case 'reset-password':
        $filename = 'reset-password.php';
        $title = 'Redefinir senha';
    break;
    case 'logout':
        $filename = 'logout.php';
        $title = 'Logout';
    break;
    default:
        header( "Location: " . URL::root('404'), 404 );
        exit;
    break;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php echo "<title>" . $title . ' – ' . site_title() . "</title>" . PHP_EOL; ?>
<?php echo "<style>" . PHP_EOL, require get_web_path('access/require/style.css'), "</style>" . PHP_EOL; ?>
</head>
<body>
    <div id="logotipo">
        <a href="<?= URL::root() ?>" target="_blank" rel="noopener noreferrer nofollow">
            <img src="<?= URL::root('dist/assets/img/opuscore-access-logo.svg') ?>" alt="opuscore" />
        </a>
    </div>
    <div id="wrapper">

        <?php require WEB_DIR . 'access/' . $filename ?>

    </div>

<script>
let password = {
    pass: document.querySelectorAll(".pswd"),
    inputpassword: function() {
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
}.inputpassword();
</script>
</body>
</html>