<?php 
if( ! URL::has('key') && ! URL::has('id') ) {
    die('<p class="title">URL sem parametros da chave de ativação e identificação</p>');
}
if( URL::has('key') && empty($_GET['key']) || URL::has('id') && empty($_GET['id']) ) {
    die(
        '<p class="title">URL com parametros da chave de ativação ou identificação vazias</p>
            <ul>
            <li><a href="'. dash_url('access/?act=login') .'">Login</a></li>
            <li><a href="'. dash_url('access/?act=lost-password') .'">Recuperar senha</a></li>
            <li><a href="'. dash_url().'">'.site_title() .'</a></li>
        </ul>'
    );
}

$sql = $conn->prepare("
    SELECT ID, name, email, pswd, token, status 
    FROM admins WHERE token = ? AND ID = ?
");
$sql->execute([ URL::Get('key'), URL::int('id') ]);

$row = $sql->fetch(PDO::FETCH_ASSOC);

if( $row["token"] != URL::Get('key') || (int) $row["ID"] != URL::int('id') ) {
    alert('error', 'Chave de ativação inválida.');
    exit;
}
elseif( $row["status"] == 1 ) {
    alert('warning', 
        'Conta de administrador já ativa!<br>
        Tente <a href="'. dash_url('access/?act=lost-password') .'">recuperar a senha</a> caso não esteja conseguindo acessar.'
    );
    exit;
}
else {
    echo '<p class="title">Olá '.$row["name"].', seu e-mail foi confirmado, agora defina sua senha.</p>';

    if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activation']) ) :

        $sql = $conn->prepare("UPDATE admins SET pswd = ?, token = ?, status = ? WHERE ID = ?");
        if( empty($_POST['pswd']) ) {
            alert('warning', 'Insira a senha!');
        }
        elseif( ! requisite_password($_POST['pswd']) ) {
            alert('error', 'Senhas devem conter no minimo 8 caracteres com letras maiúsculas, minúsculas e números!');
        } 
        else {
            $sql->execute([
                password_hash($_POST['pswd'], PASSWORD_DEFAULT),
                token_generator(26),
                1,
                URL::int('id')
            ]);
            if($sql->rowCount() > 0) {
                alert('success', 'Conta Ativada: <a href="'. dash_url('access/?act=login') .'">Acessar painel de controle.</a>.');
            }
            else {
                alert('error', 'Ocorreu algum erro.<br>Por favor reportar ao dono do site.');
            }
        }
    endif;
}
