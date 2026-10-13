<?php
/**
 * Template Name: Вход и регистрация
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_do = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! in_array( $xv_do, array( 'login', 'register', 'lost' ), true ) ) {
	$xv_do = 'login';
}

$xv_to   = xv_auth_target();
$xv_tabs = array( 'login' => __( 'Вход', 'xin-v2' ) );

if ( xv_registration_open() ) {
	$xv_tabs['register'] = __( 'Регистрация', 'xin-v2' );
}

$xv_tabs['lost'] = __( 'Забыли пароль', 'xin-v2' );
?>

<div class="xv-section xv-section-narrow">
	<div class="xv-auth">
		<header class="xv-auth-head">
			<h1 class="xv-auth-title"><?php the_title(); ?></h1>
			<p class="xv-auth-lede"><?php esc_html_e( 'Учётная запись нужна для закладок, прогресса чтения и обсуждений.', 'xin-v2' ); ?></p>
		</header>

		<div class="xv-tabs xv-auth-tabs">
			<?php foreach ( $xv_tabs as $xv_key => $xv_label ) : ?>
				<a class="xv-tab <?php echo $xv_do === $xv_key ? 'on' : ''; ?>"
					href="<?php echo esc_url( add_query_arg( array( 'do' => $xv_key, 'to' => rawurlencode( $xv_to ) ), get_permalink() ) ); ?>">
					<?php echo esc_html( $xv_label ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php xv_auth_notice(); ?>

		<?php if ( 'login' === $xv_do ) : ?>
			<?php $xv_dev_login = xv_local_dev_available() && isset($_GET['dev']); ?>
			<?php if($xv_dev_login) : ?><div class="xv-dev-login-note"><strong><?php esc_html_e('Локальный dev-вход','xin-v2'); ?></strong><p><?php esc_html_e('Тестовый редактор: dev-preview. Пароль находится в локальном файле novelforge/tools/dev-login.json.','xin-v2'); ?></p></div><?php endif; ?>

			<form class="xv-auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="xv_login">
				<input type="hidden" name="to" value="<?php echo esc_attr( $xv_to ); ?>">
				<?php wp_nonce_field( 'xv_login' ); ?>

				<div class="xv-field">
					<label class="xv-label" for="xv-log"><?php esc_html_e( 'Логин или почта', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-log" name="log" autocomplete="username" value="<?php echo $xv_dev_login ? 'dev-preview' : ''; ?>" required>
				</div>

				<div class="xv-field">
					<label class="xv-label" for="xv-pwd"><?php esc_html_e( 'Пароль', 'xin-v2' ); ?></label>
					<input class="xv-input" type="password" id="xv-pwd" name="pwd" autocomplete="current-password" required>
				</div>

				<label class="xv-filter-item">
					<input class="xv-checkbox" type="checkbox" name="remember" value="1" checked>
					<span><?php esc_html_e( 'Не выходить на этом устройстве', 'xin-v2' ); ?></span>
				</label>

				<button type="submit" class="xv-btn primary block lg"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></button>

				<p class="xv-auth-foot">
					<?php if ( xv_registration_open() ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array( 'do' => 'register', 'to' => rawurlencode( $xv_to ) ), get_permalink() ) ); ?>"><?php esc_html_e( 'Завести учётную запись', 'xin-v2' ); ?></a>
						<span class="xv-dot">·</span>
					<?php endif; ?>
					<a href="<?php echo esc_url( add_query_arg( 'do', 'lost', get_permalink() ) ); ?>"><?php esc_html_e( 'Забыли пароль?', 'xin-v2' ); ?></a>
				</p>
			</form>

		<?php elseif ( 'register' === $xv_do ) : ?>

			<form class="xv-auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="xv_register">
				<input type="hidden" name="to" value="<?php echo esc_attr( $xv_to ); ?>">
				<?php wp_nonce_field( 'xv_register' ); ?>

				<div class="xv-field">
					<label class="xv-label" for="xv-new-login"><?php esc_html_e( 'Логин', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-new-login" name="user_login" autocomplete="username" required>
					<p class="xv-help"><?php esc_html_e( 'Под этим именем вас увидят в обсуждениях.', 'xin-v2' ); ?></p>
				</div>

				<div class="xv-field">
					<label class="xv-label" for="xv-new-email"><?php esc_html_e( 'Почта', 'xin-v2' ); ?></label>
					<input class="xv-input" type="email" id="xv-new-email" name="user_email" autocomplete="email" required>
					<p class="xv-help"><?php esc_html_e( 'На неё придёт пароль — и только он.', 'xin-v2' ); ?></p>
				</div>

				<button type="submit" class="xv-btn primary block lg"><?php esc_html_e( 'Зарегистрироваться', 'xin-v2' ); ?></button>

				<p class="xv-auth-foot">
					<a href="<?php echo esc_url( add_query_arg( array( 'do' => 'login', 'to' => rawurlencode( $xv_to ) ), get_permalink() ) ); ?>"><?php esc_html_e( 'У меня уже есть учётная запись', 'xin-v2' ); ?></a>
				</p>
			</form>

		<?php else : ?>

			<form class="xv-auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="xv_lost">
				<?php wp_nonce_field( 'xv_lost' ); ?>

				<div class="xv-field">
					<label class="xv-label" for="xv-lost"><?php esc_html_e( 'Логин или почта', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-lost" name="user_login" autocomplete="username" required>
				</div>

				<button type="submit" class="xv-btn primary block lg"><?php esc_html_e( 'Прислать ссылку', 'xin-v2' ); ?></button>

				<p class="xv-auth-foot">
					<a href="<?php echo esc_url( add_query_arg( 'do', 'login', get_permalink() ) ); ?>"><?php esc_html_e( 'Вернуться ко входу', 'xin-v2' ); ?></a>
				</p>
			</form>

		<?php endif; ?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="xv-prose xv-auth-note"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
