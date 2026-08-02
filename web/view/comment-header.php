<section id="comment-area">

    <div id="comment-header">

        <?php if( $auth->is_logged() ) : ?>

            <strong id="auth-title">Conectado como <?= $auth->logged()->name ?></strong>

            <div id="auth-avatar">
                <?= $image->user_avatar() ?>
            </div>

            <?php printf(
                '<p class="comment-links">
                    <a href="%1$s">Editar perfil</a>. <a onclick="%2$s" href="%3$s">Sair</a>.
                </p>
                ',
                $auth->URL(),
                'javascript: return confirm(`Confirmar encerramento da sessão.`)',
                access_url('logout'),
            ) ?>

        <?php else : 

            printf(
                '<p class="comment-links">
                    <a href="%1$s" %2$s>Registre-se</a> ou faça <a href="%3$s">Login</a> para comentar.
                </p>
                ',
                access_url('register'),
                'target="_blank" rel="noopener"',
                access_url('login', 'redirect') . '&to=comment-area'
            );

        endif; ?>

    </div>