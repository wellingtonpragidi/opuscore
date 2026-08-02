<?php
if( ! $_GET['key'] && ! $_GET['id'] ) {
    echo '<p class="title">Sem parametros da chave de ativação e identificação</p>
    <ul>
        <li><a href="' . dash_url('access/?act=login') . '">Login</a></li>
        <li><a href="' . dash_url('access/?act=lost-password') . '">Recuperar senha</a></li>
        <li><a href="' . dash_url() . '">' . site_title() . '</a></li>
    </ul>';
    die();
}
$sql = $conn->prepare("
    SELECT ID, name, email, pswd, token 
    FROM admins WHERE token = ? AND ID = ?
");
$sql->execute([ URL::Get('key'), URL::Get('id') ]);

$row = $sql->fetch(PDO::FETCH_ASSOC);

if( $row["token"] != URL::Get('key') || $row["ID"] != URL::Get('id') ) {
    alert('error', 'Chave de ativação inválida . ');
    exit;
}
else {
    
    echo '<p class="title">Olá ' . $row["name"] . ', seu e-mail foi confirmado, agora redefina sua senha.</p>';

    if( $_SERVER['REQUEST_METHOD'] === 'POST' ) :

        if( isset($_POST['reset'])) :

            $sql = $conn->prepare("
                UPDATE admins SET pswd = ?, token = ?, status = ? WHERE ID = ?
            ");
            
            if( empty($_POST['pswd']) ) {
                alert('warning', 'Insira a senha!');
            } 
            elseif( empty($_POST['confirm-pswd']) ) {
                alert('warning', 'Insira a confirmação da senha!');
            } 
            elseif( $_POST['pswd'] != $_POST['confirm-pswd'] ) {
                alert('warning', 'As senhas não coincidem!');
            } 
            elseif( ! requisite_password($_POST['pswd']) ) {
                alert('error', 'Senhas devem conter no minimo 8 caracteres com letras maiúsculas, minúsculas e números!');
            } 
            else {
                $sql->execute([
                    password_hash( $_POST['pswd'], PASSWORD_DEFAULT ),
                    token_generator(42),
                    1,
                    URL::GET('id')
                ]);
                if( $sql->rowCount() > 0 ) {
                    alert('success', 'Senha atualizada: <a href="' . dash_url('access/?act=login') . '">Acessar painel de controle.</a> . ');
                }
                else {
                    alert('error', 'Ocorreu algum erro.<br>Por favor reportar ao dono do site . ');
                }
            }

        endif;

    endif;
}
