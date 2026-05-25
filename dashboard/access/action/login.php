<?php
if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) ) {

    $email = Ensure::email( $_POST['mail'] ?? '' );
    $password = $_POST['pswd'] ?? '';
    
    if( empty($email) ) {
        alert( 'error', 'Insira o email' );
        return;
    }
    if( empty($password) ) {
        alert( 'error', 'Insira a senha' );
        return;
    }
    
    $sql = $conn->prepare("SELECT ID, email, pswd, token FROM admins WHERE email = ? AND status = ?");
    $sql->execute([ $email, 1 ]);

    if( $sql->rowCount() > 0 ) {
        $row = $sql->fetch( PDO::FETCH_ASSOC );
        $id    = $row["ID"];
        $mail  = $row["email"];
        $pswd  = $row["pswd"];
        $token = $row["token"];

        if( ! password_verify($password, $pswd) ) {
            alert('error', 'Senha incorreta.');
        }
        else {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_token'] = $token;

            alert_redirect('success', 
                'Redirecionando para o sistema. .  .', 
                dash_url(), 3000
            );
        }
    } 
    else {
        alert('error', 'Conta de administrador não ativa ou não registrado.');
    }

}
# session_regenerate_id()