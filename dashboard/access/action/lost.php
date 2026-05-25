<?php
if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lost']) ) :

    if( ! isset($_POST['mail']) ) {
        alert('warning', 'Insira a o e-mail!');
        return;
    }
    
    $sql = $conn->prepare("SELECT ID, name, email, token FROM admins WHERE email = ?");

    $sql->execute([ Ensure::email($_POST['mail']) ]);

    $row = $sql->fetch( PDO::FETCH_ASSOC );

    if( $sql->rowCount() > 0 ) {
        $mailer = new PHPMailer;
        $mailer->IsHTML();
        $mailer->CharSet    = 'utf-8';
        $mailer->IsSMTP();
        $mailer->Host       = email_host();
        $mailer->SMTPAuth   = TRUE;
        $mailer->Username   = email_user();
        $mailer->Password   = email_pswd();
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port       = 587;
        $mailer->FromName   = site_title();
        $mailer->From       = email_address();
        $mailer->addReplyTo( email_address(), site_title() );
        $mailer->AddAddress( $row["email"], $row["name"] );
        $mailer->Subject = site_title() . ' – Redefenir senha';
        $reset_url = dash_url('access/?act=reset-password&key='.$row["token"].'&id='.$row["ID"]);
        $mailer->Body = '<div style="font-size: 1.2rem">
            <p>Clique no link abaixo para criar uma nova senha.</p>
            <a href="'.$reset_url.'" target="_blank">'.$reset_url.'</a>
            </div>';
        if( $mailer->Send() ) {
            redirect( URL::current() . '&sent=' . $row["email"], 600 );
        }
        else {
            alert('error', 'E-mail de para redefinir senha não enviado, por favor tente novamente ou entre em contato com o dono do website');
        }
    }
    else {
        alert('error', 'E-mail não cadastrado no sistema de administradores');
    }
    
endif;