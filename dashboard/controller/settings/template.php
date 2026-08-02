<?php
INPUT::method_request();

$active = trim( $_POST['action'] ?? '' );

if( ArrayExport::apply('template', ['slug' => $active], 'settings') ) {

	$message = ($active === '') 
		? "Uso padrão de template desativado. Agora é possível usar templates diretamente na raiz."
		: "Template <code>{$active}</code> ativado.";

	alert_redirect( 'success', $message, URL::current() );
}
else {
    
    alert( 'warning', 'Ocorreu algum erro.' );
}