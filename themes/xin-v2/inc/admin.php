<?php
/**
 * Административная часть: оформление wp-admin, сводка и настройки сайта.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_admin_assets( $hook ) {
	wp_enqueue_style( 'xv-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), xv_asset_ver( 'assets/css/admin.css' ) );

	if ( 'toplevel_page_eriiba' === $hook ) {
		wp_enqueue_style( 'xv-fonts-admin', get_theme_file_uri( 'assets/css/fonts.css' ), array(), xv_asset_ver( 'assets/css/fonts.css' ) );
	}
}
add_action( 'admin_enqueue_scripts', 'xv_admin_assets' );

/* -------------------------------------------------------------------------
 * Сводка
 * ---------------------------------------------------------------------- */

function xv_admin_menu() {
	add_menu_page(
		__( 'Платформа', 'xin-v2' ),
		__( 'Платформа', 'xin-v2' ),
		'edit_posts',
		'xin-v2',
		'xv_admin_page',
		'dashicons-book',
		3
	);

	add_submenu_page( 'xin-v2', __( 'Сводка', 'xin-v2' ), __( 'Сводка', 'xin-v2' ), 'edit_posts', 'xin-v2', 'xv_admin_page' );
	add_submenu_page( 'xin-v2', __( 'Настройки сайта', 'xin-v2' ), __( 'Настройки сайта', 'xin-v2' ), 'manage_options', 'xin-v2-settings', 'xv_settings_page' );
}
add_action( 'admin_menu', 'xv_admin_menu' );

/**
 * Верхние показатели сводки.
 */
function xv_admin_metrics() {
	$stats = xv_site_stats();

	$views = 0;
	$top   = xv_get_novels( 'popular', 8 );

	foreach ( xv_get_novels( 'popular', 200 ) as $novel ) {
		$views += xv_get_views( $novel->ID );
	}

	$week = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'date_query'     => array( array( 'after' => '7 days ago' ) ),
	) );

	$drafts = wp_count_posts( 'chapter' );

	return array(
		'novels'    => $stats['novels'],
		'chapters'  => $stats['chapters'],
		'readers'   => $stats['readers'],
		'views'     => $views,
		'this_week' => count( $week ),
		'drafts'    => (int) $drafts->draft,
		'top'       => $top,
	);
}

function xv_admin_page() {
	$m = xv_admin_metrics();
	?>
	<div class="wrap xv-admin">
		<h1 class="xv-admin-title"><?php esc_html_e( 'Платформа', 'xin-v2' ); ?></h1>
		<p class="xv-admin-lede"><?php esc_html_e( 'Что происходит с библиотекой прямо сейчас.', 'xin-v2' ); ?></p>

		<div class="xv-admin-tiles">
			<?php
			$tiles = array(
				array( __( 'Тайтлов', 'xin-v2' ), $m['novels'], __( 'опубликовано', 'xin-v2' ) ),
				array( __( 'Глав', 'xin-v2' ), $m['chapters'], sprintf( __( '%s в черновиках', 'xin-v2' ), number_format_i18n( $m['drafts'] ) ) ),
				array( __( 'Просмотров', 'xin-v2' ), $m['views'], __( 'за всё время', 'xin-v2' ) ),
				array( __( 'Читателей', 'xin-v2' ), $m['readers'], __( 'с аккаунтом', 'xin-v2' ) ),
				array( __( 'Глав за неделю', 'xin-v2' ), $m['this_week'], __( 'вышло за 7 дней', 'xin-v2' ) ),
			);

			foreach ( $tiles as $tile ) :
				?>
				<div class="xv-admin-tile">
					<span class="xv-admin-tile-label"><?php echo esc_html( $tile[0] ); ?></span>
					<span class="xv-admin-tile-value"><?php echo esc_html( number_format_i18n( $tile[1] ) ); ?></span>
					<span class="xv-admin-tile-note"><?php echo esc_html( $tile[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="xv-admin-cols">
			<section class="xv-admin-card">
				<h2><?php esc_html_e( 'Самое читаемое', 'xin-v2' ); ?></h2>
				<p class="xv-admin-card-sub"><?php esc_html_e( 'по просмотрам за всё время', 'xin-v2' ); ?></p>

				<?php if ( $m['top'] ) : ?>
					<ol class="xv-admin-top">
						<?php foreach ( $m['top'] as $i => $novel ) : ?>
							<li>
								<span class="xv-admin-top-num"><?php echo (int) ( $i + 1 ); ?></span>
								<span class="xv-admin-top-cover">
									<?php if ( xv_cover_url( $novel->ID ) ) : ?>
										<img src="<?php echo esc_url( xv_cover_url( $novel->ID ) ); ?>" alt="">
									<?php endif; ?>
								</span>
								<span class="xv-admin-top-body">
									<a href="<?php echo esc_url( (string) get_edit_post_link( $novel->ID ) ); ?>"><?php echo esc_html( $novel->post_title ); ?></a>
									<span class="xv-admin-top-meta">
										<?php
										printf(
											/* translators: 1: число глав, 2: оценка. */
											esc_html__( '%1$s гл. · ★ %2$s', 'xin-v2' ),
											esc_html( number_format_i18n( (int) get_post_meta( $novel->ID, '_xin_chapter_count', true ) ) ),
											esc_html( number_format_i18n( (float) get_post_meta( $novel->ID, '_xin_rating', true ), 1 ) )
										);
										?>
									</span>
								</span>
								<span class="xv-admin-top-views"><?php echo esc_html( xv_num( xv_get_views( $novel->ID ) ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<p class="xv-admin-empty"><?php esc_html_e( 'Тайтлов пока нет.', 'xin-v2' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="xv-admin-card">
				<h2><?php esc_html_e( 'Свежие главы', 'xin-v2' ); ?></h2>
				<p class="xv-admin-card-sub"><?php esc_html_e( 'последнее, что вышло', 'xin-v2' ); ?></p>

				<?php $recent = xv_get_latest_chapters( 8 ); ?>

				<?php if ( $recent ) : ?>
					<ul class="xv-admin-feed">
						<?php foreach ( $recent as $chapter ) : ?>
							<?php $novel = xv_chapter_novel( $chapter->ID ); ?>
							<li>
								<a href="<?php echo esc_url( (string) get_edit_post_link( $chapter->ID ) ); ?>"><?php echo esc_html( $chapter->post_title ); ?></a>
								<span class="xv-admin-feed-meta">
									<?php echo esc_html( $novel ? $novel->post_title : __( 'без тайтла', 'xin-v2' ) ); ?>
									· <?php echo esc_html( xv_ago( get_post_timestamp( $chapter->ID ) ) ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="xv-admin-empty"><?php esc_html_e( 'Глав пока нет.', 'xin-v2' ); ?></p>
				<?php endif; ?>
			</section>
		</div>

		<div class="xv-admin-actions">
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=novel' ) ); ?>"><?php esc_html_e( 'Новый тайтл', 'xin-v2' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=chapter' ) ); ?>"><?php esc_html_e( 'Новая глава', 'xin-v2' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=xin-v2-settings' ) ); ?>"><?php esc_html_e( 'Настройки сайта', 'xin-v2' ); ?></a>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Настройки
 * ---------------------------------------------------------------------- */

function xv_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	if ( isset( $_POST['xv_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xv_settings_nonce'] ) ), 'xv_settings' ) ) {
		xv_set_opt( 'tagline', isset( $_POST['tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline'] ) ) : '' );
		xv_set_opt( 'footer_note', isset( $_POST['footer_note'] ) ? sanitize_text_field( wp_unslash( $_POST['footer_note'] ) ) : '' );
		xv_set_opt( 'coin_label', isset( $_POST['coin_label'] ) ? sanitize_text_field( wp_unslash( $_POST['coin_label'] ) ) : '' );
		xv_set_opt( 'hero_limit', isset( $_POST['hero_limit'] ) ? max( 1, min( 10, (int) $_POST['hero_limit'] ) ) : 5 );
		xv_set_opt( 'site_width', isset( $_POST['site_width'] ) ? max( 0, min( 2000, (int) $_POST['site_width'] ) ) : 1200 );
		xv_set_opt( 'catalog_per_page', isset( $_POST['catalog_per_page'] ) ? max( 6, min( 96, (int) $_POST['catalog_per_page'] ) ) : 24 );
		xv_set_opt( 'discussions', ! empty( $_POST['discussions'] ) );
		xv_set_opt( 'reader_toolbar', ! empty( $_POST['reader_toolbar'] ) );

		wp_cache_flush();

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Сохранено.', 'xin-v2' ) . '</p></div>';
	}
	?>
	<div class="wrap xv-admin">
		<h1 class="xv-admin-title"><?php esc_html_e( 'Настройки сайта', 'xin-v2' ); ?></h1>

		<form method="post" class="xv-admin-form">
			<?php wp_nonce_field( 'xv_settings', 'xv_settings_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tagline"><?php esc_html_e( 'Подзаголовок', 'xin-v2' ); ?></label></th>
					<td>
						<input class="regular-text" type="text" id="tagline" name="tagline" value="<?php echo esc_attr( xv_opt( 'tagline' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Короткая строка о проекте.', 'xin-v2' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="footer_note"><?php esc_html_e( 'Строка в подвале', 'xin-v2' ); ?></label></th>
					<td>
						<input class="regular-text" type="text" id="footer_note" name="footer_note" value="<?php echo esc_attr( xv_opt( 'footer_note' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Пусто — возьмём описание сайта.', 'xin-v2' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="site_width"><?php esc_html_e( 'Ширина колонки сайта', 'xin-v2' ); ?></label></th>
					<td>
						<select id="site_width" name="site_width">
							<?php
							$widths = array(
								1040 => __( '1040 — узкая', 'xin-v2' ),
								1200 => __( '1200 — обычная', 'xin-v2' ),
								1360 => __( '1360 — широкая', 'xin-v2' ),
								1600 => __( '1600 — очень широкая', 'xin-v2' ),
								0    => __( 'Во всю ширину экрана', 'xin-v2' ),
							);

							foreach ( $widths as $value => $label ) :
								?>
								<option value="<?php echo (int) $value; ?>" <?php selected( (int) xv_opt( 'site_width', 1200 ), $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php
							endforeach;
							?>
						</select>
						<p class="description"><?php esc_html_e( 'Шапка, контент и подвал держат одну ширину. Колонка чтения настраивается отдельно читателем.', 'xin-v2' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="catalog_per_page"><?php esc_html_e( 'Тайтлов на странице каталога', 'xin-v2' ); ?></label></th>
					<td><input type="number" min="6" max="96" id="catalog_per_page" name="catalog_per_page" value="<?php echo esc_attr( xv_opt( 'catalog_per_page', 24 ) ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="hero_limit"><?php esc_html_e( 'Книг в подборке недели', 'xin-v2' ); ?></label></th>
					<td><input type="number" min="1" max="10" id="hero_limit" name="hero_limit" value="<?php echo esc_attr( xv_opt( 'hero_limit' ) ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="coin_label"><?php esc_html_e( 'Обозначение цены', 'xin-v2' ); ?></label></th>
					<td><input type="text" id="coin_label" name="coin_label" value="<?php echo esc_attr( xv_opt( 'coin_label' ) ); ?>" class="small-text"></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Обсуждения', 'xin-v2' ); ?></th>
					<td>
						<label><input type="checkbox" name="discussions" value="1" <?php checked( xv_opt( 'discussions' ) ); ?>> <?php esc_html_e( 'Комментарии к тайтлам и главам', 'xin-v2' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Читалка', 'xin-v2' ); ?></th>
					<td>
						<label><input type="checkbox" name="reader_toolbar" value="1" <?php checked( xv_opt( 'reader_toolbar' ) ); ?>> <?php esc_html_e( 'Прятать шапку сайта при чтении', 'xin-v2' ); ?></label>
						<p class="description"><?php esc_html_e( 'Выключите, если читателям привычнее обычная навигация.', 'xin-v2' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Сохранить', 'xin-v2' ) ); ?>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Виджет на главной админки
 * ---------------------------------------------------------------------- */

function xv_dashboard_widget() {
	wp_add_dashboard_widget( 'xv_overview', __( 'Библиотека', 'xin-v2' ), 'xv_dashboard_widget_render' );
}
add_action( 'wp_dashboard_setup', 'xv_dashboard_widget' );

function xv_dashboard_widget_render() {
	$stats = xv_site_stats();
	?>
	<div class="xv-admin-widget">
		<p>
			<?php
			printf(
				/* translators: 1: тайтлы, 2: главы, 3: читатели. */
				esc_html__( 'Сейчас на платформе: %1$s тайтлов, %2$s глав, %3$s читателей.', 'xin-v2' ),
				'<strong>' . esc_html( number_format_i18n( $stats['novels'] ) ) . '</strong>',
				'<strong>' . esc_html( number_format_i18n( $stats['chapters'] ) ) . '</strong>',
				'<strong>' . esc_html( number_format_i18n( $stats['readers'] ) ) . '</strong>'
			);
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=xin-v2' ) ); ?>"><?php esc_html_e( 'Открыть сводку', 'xin-v2' ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * Автор в кабинете правит свои работы на фронте — из админки его удобнее
 * возвращать туда же.
 */
function xv_row_actions( $actions, $post ) {
	if ( ! in_array( $post->post_type, array( 'novel', 'chapter' ), true ) ) {
		return $actions;
	}

	$url = 'novel' === $post->post_type
		? xv_author_url( array( 'work' => $post->ID ) )
		: xv_author_url( array( 'work' => (int) get_post_meta( $post->ID, '_xin_novel', true ), 'part' => $post->ID, 'view' => 'chapter' ) );

	if ( $url ) {
		$actions['xv_cabinet'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'В кабинете', 'xin-v2' ) . '</a>';
	}

	return $actions;
}
add_filter( 'post_row_actions', 'xv_row_actions', 10, 2 );
