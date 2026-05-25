<?php
require dirname(__DIR__, 2) .'/config.php';

$bind = new Assign;
$image = Container::call('Image');
$userprofile = Container::call('UserProfile');

$bind->username = Ensure::slug( $_POST["username"] );
$bind->ID       = Ensure::int( $_POST["getid"] );
$bind->update   = date('Y-m-d H:i:s');

if( $userprofile->username_exists( $bind->username ) ) {
    echo json_encode([
		'alertext' => 'Esse nome de usuário já existe.',
		'redirect' => site_url( user_base() .'/'. $bind->username )
	]);
}
else {

	if( $userprofile->update_username($bind) ) {

		echo json_encode([
			'alertext' => 'Nome de usuário atualizado.',
			'redirect' => site_url( user_base() .'/'. $bind->username )
		]);

		if( $userprofile->user_has_picture( $bind->ID ) ) {
			# atualiza nome da imagem, arquivo fisico coluna attachment
			$image->update_rename_images_user( $bind->ID, $bind->username );
		}
	}

}