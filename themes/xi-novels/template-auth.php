<?php
/**
 * Template Name: Вход и регистрация
 *
 * Три состояния одной страницы: вход, регистрация и запрос ссылки на смену
 * пароля. Обработчики лежат в inc/auth.php и срабатывают до вывода страницы.
 *
 * @package XI_Novels
 */

$xin_view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'login';
if ( ! in_array( $xin_view, array( 'login', 'register', 'lost' ), true ) ) {
	$xin_view = 'login';
}

$xin_target = isset( $_GET['redirect_to'] ) ? wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ), '' ) : '';
$xin_name   = isset( $_GET['xin_name'] ) ? sanitize_user( wp_unslash( $_GET['xin_name'] ), true ) : '';
$xin_mail   = isset( $_GET['xin_email'] ) ? sanitize_email( wp_unslash( $_GET['xin_email'] ) ) : '';
$xin_open   = xin_registration_open();
$xin_stats  = xin_site_stats();

$xin_heads = array(
	'login'    => array( __( 'С возвращением', 'xi-novels' ), __( 'Продолжайте с той главы, на которой остановились.', 'xi-novels' ) ),
	'register' => array( __( 'Создание аккаунта', 'xi-novels' ), __( 'Закладки, история чтения и собственные проекты — в одном месте.', 'xi-novels' ) ),
	'lost'     => array( __( 'Восстановление доступа', 'xi-novels' ), __( 'Пришлём ссылку, по которой можно задать новый пароль.', 'xi-novels' ) ),
);

get_header();
?>

<div class="xin-auth">
	<div class="xin-auth__veil" aria-hidden="true"></div>

	<div class="xin-auth__stage">
		<div class="xin-auth__card">
			<a class="xin-auth__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php xin_the_icon( 'book-open' ); ?>
				<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			</a>

			<h1 class="xin-auth__title"><?php echo esc_html( $xin_heads[ $xin_view ][0] ); ?></h1>
			<p class="xin-auth__lead"><?php echo esc_html( $xin_heads[ $xin_view ][1] ); ?></p>

			<?php if ( 'lost' !== $xin_view ) : ?>
				<nav class="xin-auth__seg" aria-label="<?php esc_attr_e( 'Вход и регистрация', 'xi-novels' ); ?>">
					<a class="<?php echo 'login' === $xin_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( xin_login_url( $xin_target ) ); ?>"><?php esc_html_e( 'Вход', 'xi-novels' ); ?></a>
					<?php if ( $xin_open ) : ?>
						<a class="<?php echo 'register' === $xin_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( xin_register_url( $xin_target ) ); ?>"><?php esc_html_e( 'Регистрация', 'xi-novels' ); ?></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>

			<?php xin_auth_notice(); ?>

			<?php if ( 'register' === $xin_view && ! $xin_open ) : ?>

				<p class="xin-auth__closed"><?php esc_html_e( 'Новые аккаунты сейчас не создаются. Если учётная запись уже есть — войдите.', 'xi-novels' ); ?></p>

			<?php elseif ( 'register' === $xin_view ) : ?>

				<form class="xin-auth__form" method="post" action="<?php echo esc_url( xin_auth_url() ); ?>">
					<?php wp_nonce_field( 'xin_auth_register' ); ?>
					<input type="hidden" name="xin_auth" value="register">
					<?php if ( $xin_target ) : ?>
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $xin_target ); ?>">
					<?php endif; ?>

					<label class="xin-auth__field">
						<span><?php esc_html_e( 'Имя пользователя', 'xi-novels' ); ?></span>
						<input type="text" name="xin_user" value="<?php echo esc_attr( $xin_name ); ?>" autocomplete="username" placeholder="<?php esc_attr_e( 'например, kuroi', 'xi-novels' ); ?>" required>
					</label>

					<label class="xin-auth__field">
						<span><?php esc_html_e( 'Почта', 'xi-novels' ); ?></span>
						<input type="email" name="xin_email" value="<?php echo esc_attr( $xin_mail ); ?>" autocomplete="email" placeholder="you@example.com" required>
					</label>

					<div class="xin-auth__pair">
						<label class="xin-auth__field">
							<span><?php esc_html_e( 'Пароль', 'xi-novels' ); ?></span>
							<input type="password" name="xin_pass" autocomplete="new-password" minlength="8" placeholder="<?php esc_attr_e( 'от 8 символов', 'xi-novels' ); ?>" required>
						</label>
						<label class="xin-auth__field">
							<span><?php esc_html_e( 'Ещё раз', 'xi-novels' ); ?></span>
							<input type="password" name="xin_pass2" autocomplete="new-password" minlength="8" required>
						</label>
					</div>

					<p class="xin-auth__hp" aria-hidden="true">
						<label for="xin-site"><?php esc_html_e( 'Не заполняйте это поле', 'xi-novels' ); ?></label>
						<input id="xin-site" type="text" name="xin_site" value="" tabindex="-1" autocomplete="off">
					</p>

					<button class="xin-auth__submit" type="submit"><?php esc_html_e( 'Создать аккаунт', 'xi-novels' ); ?><?php xin_the_icon( 'arrow-right' ); ?></button>

					<p class="xin-auth__fine">
						<?php
						printf(
							/* translators: %s: link to the site rules */
							esc_html__( 'Создавая аккаунт, вы соглашаетесь с %s.', 'xi-novels' ),
							'<a href="' . esc_url( xin_page_url( 'rules' ) ) . '">' . esc_html__( 'правилами площадки', 'xi-novels' ) . '</a>'
						);
						?>
					</p>
				</form>

			<?php elseif ( 'lost' === $xin_view ) : ?>

				<form class="xin-auth__form" method="post" action="<?php echo esc_url( xin_auth_url() ); ?>">
					<?php wp_nonce_field( 'xin_auth_lost' ); ?>
					<input type="hidden" name="xin_auth" value="lost">

					<label class="xin-auth__field">
						<span><?php esc_html_e( 'Имя пользователя или почта', 'xi-novels' ); ?></span>
						<input type="text" name="user_login" autocomplete="username" required autofocus>
					</label>

					<button class="xin-auth__submit" type="submit"><?php esc_html_e( 'Прислать ссылку', 'xi-novels' ); ?><?php xin_the_icon( 'arrow-right' ); ?></button>
				</form>

			<?php else : ?>

				<form class="xin-auth__form" method="post" action="<?php echo esc_url( xin_auth_url() ); ?>">
					<?php wp_nonce_field( 'xin_auth_login' ); ?>
					<input type="hidden" name="xin_auth" value="login">
					<?php if ( $xin_target ) : ?>
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $xin_target ); ?>">
					<?php endif; ?>

					<label class="xin-auth__field">
						<span><?php esc_html_e( 'Имя пользователя или почта', 'xi-novels' ); ?></span>
						<input type="text" name="xin_user" autocomplete="username" required autofocus>
					</label>

					<label class="xin-auth__field">
						<span class="xin-auth__field-top">
							<?php esc_html_e( 'Пароль', 'xi-novels' ); ?>
							<a href="<?php echo esc_url( xin_lost_url() ); ?>"><?php esc_html_e( 'Забыли?', 'xi-novels' ); ?></a>
						</span>
						<input type="password" name="xin_pass" autocomplete="current-password" required>
					</label>

					<label class="xin-auth__remember">
						<input type="checkbox" name="xin_remember" value="1" checked>
						<span><?php esc_html_e( 'Не выходить из аккаунта', 'xi-novels' ); ?></span>
					</label>

					<button class="xin-auth__submit" type="submit"><?php esc_html_e( 'Войти', 'xi-novels' ); ?><?php xin_the_icon( 'arrow-right' ); ?></button>
				</form>

			<?php endif; ?>

			<p class="xin-auth__switch">
				<?php if ( 'login' === $xin_view && $xin_open ) : ?>
					<?php esc_html_e( 'Ещё нет аккаунта?', 'xi-novels' ); ?>
					<a href="<?php echo esc_url( xin_register_url( $xin_target ) ); ?>"><?php esc_html_e( 'Зарегистрироваться', 'xi-novels' ); ?></a>
				<?php else : ?>
					<?php esc_html_e( 'Аккаунт уже есть?', 'xi-novels' ); ?>
					<a href="<?php echo esc_url( xin_login_url( $xin_target ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a>
				<?php endif; ?>
			</p>
		</div>

		<?php if ( $xin_stats['novels'] > 0 ) : ?>
			<p class="xin-auth__meta">
				<span><?php echo esc_html( sprintf( _n( '%s тайтл', '%s тайтлов', $xin_stats['novels'], 'xi-novels' ), number_format_i18n( $xin_stats['novels'] ) ) ); ?></span>
				<i></i>
				<span><?php echo esc_html( sprintf( _n( '%s глава', '%s глав', $xin_stats['chapters'], 'xi-novels' ), number_format_i18n( $xin_stats['chapters'] ) ) ); ?></span>
				<i></i>
				<span><?php echo esc_html( sprintf( __( '%s прочтений', 'xi-novels' ), xin_num( $xin_stats['views'] ) ) ); ?></span>
			</p>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
