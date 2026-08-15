<?php
/**
 * Plugin Name: XI Novels — демо-контент
 * Plugin URI: https://github.com/rurumiru/wordpress-novel-themes
 * Description: Наполняет сайт демонстрационным каталогом: 12 тайтлов, 48 глав, жанры, теги, записи блога и баннеры. Обложки рисуются на месте. Удаляется одной кнопкой.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: XI Community
 * License: GPL-2.0-or-later
 * Text Domain: xi-demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/importer.php';

function xin_demo_menu() {
	add_management_page(
		__( 'Демо-контент', 'xi-demo' ),
		__( 'Демо-контент', 'xi-demo' ),
		'manage_options',
		'xin-demo',
		'xin_demo_screen'
	);
}
add_action( 'admin_menu', 'xin_demo_menu' );

function xin_demo_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$installed = count( xin_demo_ids() );
	$ready     = xin_demo_ready();
	$gd        = function_exists( 'imagecreatetruecolor' );
	$notice    = isset( $_GET['xin-demo'] ) ? sanitize_key( wp_unslash( $_GET['xin-demo'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Демо-контент XI Novels', 'xi-demo' ); ?></h1>

		<?php if ( 'installed' === $notice ) : ?>
			<div class="notice notice-success"><p>
				<?php
				printf(
					esc_html__( 'Готово: тайтлов %1$d, глав %2$d, записей %3$d, баннеров %4$d, картинок %5$d.', 'xi-demo' ),
					(int) ( $_GET['novels'] ?? 0 ),
					(int) ( $_GET['chapters'] ?? 0 ),
					(int) ( $_GET['posts'] ?? 0 ),
					(int) ( $_GET['banners'] ?? 0 ),
					(int) ( $_GET['images'] ?? 0 )
				);
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'Открыть сайт', 'xi-demo' ); ?></a>
			</p></div>
		<?php elseif ( 'removed' === $notice ) : ?>
			<div class="notice notice-success"><p>
				<?php printf( esc_html__( 'Демо-контент удалён: %d записей.', 'xi-demo' ), (int) ( $_GET['count'] ?? 0 ) ); ?>
			</p></div>
		<?php elseif ( 'error' === $notice ) : ?>
			<div class="notice notice-error"><p>
				<?php echo esc_html( isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : __( 'Не получилось.', 'xi-demo' ) ); ?>
			</p></div>
		<?php endif; ?>

		<?php if ( ! $ready ) : ?>
			<div class="notice notice-warning"><p>
				<?php esc_html_e( 'Сначала активируйте тему XI Novels: демо создаёт новеллы и главы, а этих типов записей сейчас нет.', 'xi-demo' ); ?>
			</p></div>
		<?php endif; ?>

		<div class="card" style="max-width:760px">
			<h2><?php esc_html_e( 'Что появится на сайте', 'xi-demo' ); ?></h2>
			<p><?php esc_html_e( '12 тайтлов с описаниями, жанрами, статусами, оценками и просмотрами; 48 глав (по 4 на тайтл, последняя в каждом — PLUS); 12 жанров и 12 тегов; 5 записей блога; 3 баннера для главной.', 'xi-demo' ); ?></p>
			<p><?php esc_html_e( 'Обложки и арты рисуются на месте: градиент с дугами, 800×1200 и 1920×720. Чужих картинок и чужих текстов в пакете нет.', 'xi-demo' ); ?></p>

			<?php if ( ! $gd ) : ?>
				<p><strong><?php esc_html_e( 'Библиотека GD недоступна — импорт пройдёт без картинок.', 'xi-demo' ); ?></strong></p>
			<?php endif; ?>

			<?php if ( $installed ) : ?>
				<p><strong><?php printf( esc_html__( 'Сейчас на сайте: %d демо-записей.', 'xi-demo' ), (int) $installed ); ?></strong>
				<?php esc_html_e( 'Повторная установка обновит их, а не создаст копии.', 'xi-demo' ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px">
				<?php wp_nonce_field( 'xin_demo_install' ); ?>
				<input type="hidden" name="action" value="xin_demo_install">

				<p>
					<label><input type="checkbox" name="covers" value="1" checked <?php disabled( ! $gd ); ?>> <?php esc_html_e( 'Нарисовать обложки и арты', 'xi-demo' ); ?></label><br>
					<label><input type="checkbox" name="draft" value="1"> <?php esc_html_e( 'Создать черновиками, а не опубликовать', 'xi-demo' ); ?></label>
				</p>

				<p>
					<button type="submit" class="button button-primary button-hero" <?php disabled( ! $ready ); ?>>
						<?php esc_html_e( 'Установить демо-контент', 'xi-demo' ); ?>
					</button>
				</p>
			</form>
		</div>

		<div class="card" style="max-width:760px;margin-top:20px">
			<h2><?php esc_html_e( 'Удалить демо', 'xi-demo' ); ?></h2>
			<p><?php esc_html_e( 'Удаляются только записи, созданные этим плагином: они помечены служебным полем. Ваши собственные тайтлы, главы и статьи не трогаются.', 'xi-demo' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				onsubmit="return confirm('<?php echo esc_js( __( 'Удалить весь демо-контент?', 'xi-demo' ) ); ?>')">
				<?php wp_nonce_field( 'xin_demo_remove' ); ?>
				<input type="hidden" name="action" value="xin_demo_remove">

				<p><label><input type="checkbox" name="trash" value="1"> <?php esc_html_e( 'В корзину вместо полного удаления', 'xi-demo' ); ?></label></p>

				<p>
					<button type="submit" class="button" <?php disabled( ! $installed ); ?>>
						<?php esc_html_e( 'Удалить демо-контент', 'xi-demo' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>
	<?php
}

function xin_demo_back( $args ) {
	wp_safe_redirect( add_query_arg( $args, admin_url( 'tools.php?page=xin-demo' ) ) );
	exit;
}

function xin_demo_handle_install() {
	check_admin_referer( 'xin_demo_install' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-demo' ) );
	}

	$result = xin_demo_install( array(
		'status' => isset( $_POST['draft'] ) ? 'draft' : 'publish',
		'covers' => isset( $_POST['covers'] ),
	) );

	if ( is_wp_error( $result ) ) {
		xin_demo_back( array( 'xin-demo' => 'error', 'message' => rawurlencode( $result->get_error_message() ) ) );
	}

	xin_demo_back( array_merge( array( 'xin-demo' => 'installed' ), $result ) );
}
add_action( 'admin_post_xin_demo_install', 'xin_demo_handle_install' );

function xin_demo_handle_remove() {
	check_admin_referer( 'xin_demo_remove' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-demo' ) );
	}

	$done = xin_demo_remove( isset( $_POST['trash'] ) );

	xin_demo_back( array( 'xin-demo' => 'removed', 'count' => $done ) );
}
add_action( 'admin_post_xin_demo_remove', 'xin_demo_handle_remove' );

function xin_demo_action_link( $links ) {
	array_unshift( $links, sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'tools.php?page=xin-demo' ) ),
		esc_html__( 'Открыть', 'xi-demo' )
	) );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'xin_demo_action_link' );
