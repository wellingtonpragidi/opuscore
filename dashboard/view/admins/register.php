<?php 
    if( $admin->logged_role() === 1 ) :

    if( INPUT::formSubmitted() ) {
        require get_dashboard_path('controller/admin.php');
    }
?>
	<form method="POST" action="<?php URL::current() ?>" enctype="multipart/form-data">
		<div class="flexbox mb10">
			<div class="cn_40 mr5">
				<input type="text" name="name" placeholder="Nome" />
			</div>
			<div class="cn_40 ml5">
				<input type="text" name="mail" placeholder="E-mail" />
			</div>
		</div>
		<label>Função</label><br>
		<select name="role" class="w33">
			<!-- <option value="2">Administrador</option>
			<option value="3">Editor</option>
			<option value="4">Autor</option> -->
            <option value="1">Administrador</option>
            <option value="2">Editor</option>
            <option value="3">Autor</option>
		</select><br>
		<button type="submit" class="btn mt15" name="action" value="register">Registrar</button>
	</form>
<?php 
else :
	echo '<p>Sem autorização para adicionar administrador.</p>
	<p>Somente um administrador com função 1 pode adicionar outro administrador.</p>';
endif;