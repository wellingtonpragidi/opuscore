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
    
    $sql = $conn->prepare("
        SELECT ID, email, pswd, token, status 
        FROM admins 
        WHERE email = ? AND status = ?
    ");

    $sql->execute([ $email, 1 ]);
    $row = $sql->fetch( PDO::FETCH_ASSOC );


    if( isset($row['ID'], $row['email'], $row['token']) === false ) {
        alert('error', 'Conta de administrador não registrado.');
        return;
    }

    if( (int) ($row['status'] === 0) || empty($row['pswd']) ) {
        alert('error', 'Conta de administrador não ativa.');
        return;
    }


    if( ! password_verify($password, $row['pswd']) ) {
        alert('error', 'Senha incorreta.');
        return;
    }

    session_regenerate_id( true );

    $_SESSION['admin_id']    = $row['ID'];
    $_SESSION['admin_token'] = $row['token'];
    
    # pega a URL de redirecionamento segura da classe Auth
    $redirect_url = $auth->session_redirect() ?? dash_url();

    # limpa a sessao de redirecionamento — equivale a unset($_SESSION['admin_redirect'])
    $auth->set_session_redirect(null);

    # faz o redirecionamento limpo e seguro
    alert_redirect('success', 
        'Redirecionando para o painel. .  .', 
        $redirect_url, 
        3000
    );

}
# session_regenerate_id()
