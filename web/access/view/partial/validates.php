<?php
defined( 'ENTRY_GUARD' ) or die;

/**
 * 
 * arquivo auxiliar para validacao em ativacao e redefinicao de senha
 */

$token = URL::GET('key'); 
$id    = URL::int('id');

$field = $access->field_activate( $token, $id );

$not_query   = ! URL::has('key') || ! URL::has('id');
$empty_query = $token === '' || $id === '';

$rel = 'rel="noindex nofollow"';
$link['root']  = '<a href="' . site_url() . '">' . site_title() . '</a>';
$link['login'] = '<a href="' . access_url('login') . '" ' . $rel . '>Login</a>';
$link['lost']  = '<a href="' . access_url('lost-password') . '" ' . $rel . '>Redefinir senha</a>';
$link['regs']  = '<a href="' . access_url('register') . '" ' . $rel . '>Registrar-se</a>';

$has_error = false;
$html_list = null;

$show_form = false;

if( $not_query || $empty_query ) {
    $warning = '<p class="title">
        <b>Este link está incompleto</b>, Verifique o link enviado no seu e-mail, depois copie e cole diretamente no navegador.</p>
    ';
    
    if( $empty_query ) {
        $html_list = '
        <ul>
            <li>' . $link['login'] . '</li>
            <li>' . $link['lost'] . '</li>
            <li>' . $link['root'] . '</li>
        </ul>';
    }

    $has_error = true;
}
    
else if( ! $field || $field->token !== $token || $field->ID !== $id ) {
    $warning = '<p><strong>Este link de ativação é inválido</strong>, verifique a URL enviada no seu e-mail, copie e cole na barra de endereço do navegador. Se o erro persistir, tente ' . $link['lost'] . ' para resolver.</p>';

    $html_list = '
    <ul>
        <li>' . $link['regs'] . '</li>
        <li>' . $link['root'] . '</li>
    </ul>';

    $has_error = true;
}
else if( URL::is('action', 'activate') && $field->status === 1 ) {
    $warning = '<p><strong>Essa conta já está ativa</strong>.<br>
    Se você esqueceu a senha, você pode redefini-la em: <em>' . $link['lost'] . '</em>.</p>';

    $has_error = true;
}
else {
    echo '<p class="title">' . $route['title'] . '</p>';
    echo '<p class="title">' 
        . $field->name . '.<br>
        E-mail foi confirmado.<br>
        <strong>Agora defina sua senha de acesso.</strong>
    </p>';

    $show_form = true;
}

if( $has_error ) {
    if( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
        alert( 'warning', $warning );
    }
    echo $html_list ?? null;
    return;
}