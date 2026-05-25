<?php 
INPUT::method_request();

$conn   ??= Container::call('Connection');
$assign = new Assign;
$action = $_POST['action'] ?? null;


if( $action === 'register' ) {

    $name  = Ensure::string( $_POST['name'], Ensure::STRING_STRICT | Ensure::STRING_REMOVE_HOSTS );
    $email = Ensure::email( $_POST['mail'] );
    $role  = Ensure::int( $_POST['role'] );

    $sql = $conn->prepare("SELECT email FROM admins WHERE email = ?");
    $sql->execute([ $email ]);
    if( $sql->rowCount() > 0 ) {
        alert_time('error', 'Já existe um registro com esse e-mail.', 4800);
    } 
    else {
        $sql = $conn->prepare("
            INSERT admins SET 
            name = ?, email = ?, created = ?, role = ?, token = ?, nonce = ?, status = ?
        ");

        $sql->execute([
            $name,
            $email,
            date('Y-m-d'),
            $role,
            token_generator(42),
            token_generator(10),
            0
        ]);

        if( $sql->rowCount() > 0 ) {

            $sql = $conn->prepare("
                SELECT ID, name, email, token FROM admins WHERE email = ?
            ");

            $sql->execute([ $email ]);

            $row = $sql->fetch( PDO::FETCH_ASSOC );

            if( ! $row ) {
                alert_time('error', 'Ocorreu algum erro, tente novamente em alguns instantes.', 5500);
                return;
            } 
            else {
                alert_time('success', 'Administrador registrado!', 5200);
            }


            $site = site_title();

            $link = dash_url("access/?act=activation&key={$row['token']}&id={$row['ID']}");

            $body = "<h2 style=\"font-size: 1.25rem; font-weight: 500\">Verificação para a administrar o sistema do site {$site}</h2>
                <p>Clique no link abaixo para ativar sua conta e definir sua senha.</p>
                <p><a href=\"{$link}\">{$link}</a></p>";

            $mailer = Provider::send_email([
                'email'   => $row['email'],
                'name'    => $row['name'],
                'subject' => $site . ' – Verificação',
                'body'    => $body
            ]);

            if( $mailer ) {
                alert( 'success', 
                    "E-mail de confirmação enviado para {$row['name']} no endereço {$row['email']}"
                );
            }
            else {
                $link = '<a href="' . URL::root('access/?act=lost-password') . '">definir uma nova senha</a>';
                alert( 'error', 
                    "Erro ao enviar e-mail de confirmação para o novo administrador.<br>
                    <b>Verifique se digitou corretamente o e-mail:</b>: {$row['email']}<br>
                    Faça logout e defina uma nova senha, ou exclua e registre novamente."
                );
            }
        } 
        else {
            alert_redirect( 'warning', 
                'Ocorreu algum erro e não foi inserido nenhum registro.<br>
                Aguarde alguns instantes e tente novamente.', 
                URL::current(), 6000 
            );
        }
    }
}


if( $action === 'update' ) {
    $assign->role = $role;
    $assign->name = $name;
    $assign->ID   = URL::int('id');

    if( $admin->update( $assign ) > 0 ) {
        alert_time('success', 'Conta de administrador atualizado . ');
    }
    else {
        alert('warning', 'Erro ao atualizar conta de administrador . ');
    }
}


if( $action === 'delete' ) {
    $admin_id = (int) $_POST['target_id'];
    if( $admin->delete( $admin_id ) ) {
        preloader(1200);
        alert_redirect('success', 'Administrador excluido . ', dash_url('admins/'), 2200);
    } 
    else {
        alert('warning', 'O Administrador nao foi excluido . ');
    }
}