<?php
require dirname(__DIR__, 2) . '/config.php';

$bind = new Assign;
$image = Container::call('Image');
$user = Container::call('UserProfile');

$bind->name   = Ensure::name( $_POST["name"] );
$bind->update = date('Y-m-d H:i:s');
$bind->ID     = Ensure::int( $_POST["getid"] );

if( $user->update_name($bind) ) {

	$image->update_user_related_title( $bind );

	echo json_encode([
		'title'    => $_POST["name"],
		'alertext' => 'Nome atualizado.'
	]);
}
else {
	echo json_encode([
		'title'    => $bind->name,
		'alertext' => 'Erro.'
	]);
}