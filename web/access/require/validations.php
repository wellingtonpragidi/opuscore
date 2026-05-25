<?php
defined( 'ENTRY_GUARD' ) or die;


# arquivo auxiliar para validacao em ativacao e redefinicao de senha

$token = URL::GET('key'); 
$id    = URL::int('id');

$field = $auth->fields_by_activation( $token, $id );

$not_query = ! URL::has('key') || ! URL::has('id');
$empty_query = $token === '' || $id === '';

$root  = URL::root();
$start = $root . 'access/?action=';
$lost  = $start . 'lost-password';
$regs  = $start . 'register';
$login = $start . 'login';

$rel = 'rel="noindex nofollow"';
$linkRoot  = "<a href=\"{$root}\">" . site_title() . "</a>";
$linkLogin = "<a href=\"{$login}\" {$rel}>Login</a>";
$linkLost  = "<a href=\"{$lost}\" {$rel}>Redefinir senha</a>";
$linkRegs  = "<a href=\"{$regs}\" {$rel}>Registrar-se</a>";

$v_email[0] = 'Verifique o link enviado no seu e-mail';
$v_email[1] = 'depois copie e cole diretamente no navegador';

$has_error = false;

$list = null;

if( $not_query ) {
    $warning = "<p class=\"title\"><b>Este link não contém a chave de ativação</b> {$v_email[0]}.</p>";

    $has_error = true;
}
else if( $empty_query ) {
    $warning = "<p class=\"title\"><b>Este link está incompleto</b>, {$v_email[0]}, {$v_email[1]}.</p>";
    $list = '
    <ul>
        <li>' . $linkLogin . '</li>
        <li>' . $linkLost . '</li>
        <li>' . $linkRoot . '</li>
    </ul>';

    $has_error = true;
}
else if( ! $field || $field->token !== $token || $field->ID !== $id ) {
    $warning = '<p><b>Este link de ativação é inválido</b>, verifique a URL enviada no seu e-mail, copie e cole na barra de endereço do navegador. Se o erro persistir, tente ' . $linkLost . ' para resolver.</p>';
    $list = '
    <ul>
        <li>' . $linkRegs . '</li>
        <li>' . $linkRoot . '</li>
    </ul>';

    $has_error = true;
}
else if( $field->status === 1 ) {
    $warning = '<p><b>Essa conta já está ativa</b>.<br>
    Se você esqueceu a senha, você pode redefini-la em: <em>' . $linkLost . '</em>.</p>';

    $has_error = true;
}
else {
    echo "<p class=\"title\">{$title}</p>";
    echo "<p class=\"title\">Olá {$field->name}.<br>
    Seu e-mail foi confirmado! Agora você pode definir a senha de acesso.</p>";

    $show_form = true;
}

if( $has_error ) {
    alert( 'warning', $warning );
    echo $list ?? null;
    return;
}