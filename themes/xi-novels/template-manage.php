<?php
/**
 * Template Name: Панель управления
 *
 * Пять вкладок: обзор, пользователи, модерация, тайтлы, настройки.
 * Обработчики форм лежат в inc/manage.php.
 *
 * @package XI_Novels
 */

$xin_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
if ( ! in_array( $xin_tab, array( 'overview', 'users', 'moderation', 'titles', 'settings' ), true ) ) {
	$xin_tab = 'overview';
}

get_header();

if ( ! xin_can_moderate() ) {
	?>
	<div class="xin-wrap">
		<div class="xin-empty" style="padding-block:80px">
			<?php xin_the_icon( 'lock' ); ?>
			<h1><?php esc_html_e( 'Панель управления', 'xi-novels' ); ?></h1>
			<p><?php esc_html_e( 'Раздел для модераторов и администрации площадки.', 'xi-novels' ); ?></p>
			<?php if ( ! is_user_logged_in() ) : ?>
				<div class="xin-mt-2"><a class="btn btn-primary" href="<?php echo esc_url( xin_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	get_footer();
	return;
}

$xin_stats = xin_manage_stats();
$xin_tabs  = array(
	'overview'   => __( 'Обзор', 'xi-novels' ),
	'users'      => __( 'Пользователи', 'xi-novels' ),
	'moderation' => __( 'Модерация', 'xi-novels' ),
	'titles'     => __( 'Тайтлы', 'xi-novels' ),
);
if ( xin_can_manage() ) {
	$xin_tabs['settings'] = __( 'Настройки', 'xi-novels' );
}
?>

<div class="xin-wrap xin-manage">

	<header class="xin-pagehead">
		<h1><?php esc_html_e( 'Панель управления', 'xi-novels' ); ?></h1>
		<p class="xin-pagehead__sub"><?php esc_html_e( 'Роли, доступ PLUS, очередь на проверку и настройки площадки — здесь, без админки WordPress.', 'xi-novels' ); ?></p>
	</header>

	<nav class="xin-manage__tabs" aria-label="<?php esc_attr_e( 'Разделы панели', 'xi-novels' ); ?>">
		<?php foreach ( $xin_tabs as $xin_key => $xin_label ) : ?>
			<a class="<?php echo $xin_key === $xin_tab ? 'is-active' : ''; ?>" href="<?php echo esc_url( xin_manage_url( array( 'tab' => $xin_key ) ) ); ?>">
				<?php echo esc_html( $xin_label ); ?>
				<?php if ( 'moderation' === $xin_key && $xin_stats['pending'] > 0 ) : ?>
					<b><?php echo esc_html( number_format_i18n( $xin_stats['pending'] ) ); ?></b>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php xin_manage_notice(); ?>

	<?php if ( 'overview' === $xin_tab ) : ?>

		<div class="xin-manage__cards">
			<div class="xin-manage__card">
				<span><?php esc_html_e( 'Пользователей', 'xi-novels' ); ?></span>
				<b><?php echo esc_html( number_format_i18n( $xin_stats['users'] ) ); ?></b>
			</div>
			<div class="xin-manage__card">
				<span><?php esc_html_e( 'С доступом PLUS', 'xi-novels' ); ?></span>
				<b><?php echo esc_html( number_format_i18n( $xin_stats['plus'] ) ); ?></b>
			</div>
			<div class="xin-manage__card">
				<span><?php esc_html_e( 'Тайтлов', 'xi-novels' ); ?></span>
				<b><?php echo esc_html( number_format_i18n( $xin_stats['novels'] ) ); ?></b>
			</div>
			<div class="xin-manage__card">
				<span><?php esc_html_e( 'Глав', 'xi-novels' ); ?></span>
				<b><?php echo esc_html( number_format_i18n( $xin_stats['chapters'] ) ); ?></b>
			</div>
			<div class="xin-manage__card<?php echo $xin_stats['pending'] > 0 ? ' is-warn' : ''; ?>">
				<span><?php esc_html_e( 'Ждут проверки', 'xi-novels' ); ?></span>
				<b><?php echo esc_html( number_format_i18n( $xin_stats['pending'] ) ); ?></b>
			</div>
		</div>

		<div class="xin-manage__links">
			<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'moderation' ) ) ); ?>"><?php xin_the_icon( 'check' ); ?><?php esc_html_e( 'Очередь на проверку', 'xi-novels' ); ?></a>
			<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'users' ) ) ); ?>"><?php xin_the_icon( 'users' ); ?><?php esc_html_e( 'Пользователи и PLUS', 'xi-novels' ); ?></a>
			<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_dashboard_url() ); ?>"><?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></a>
			<a class="btn btn-outline btn-sm" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php xin_the_icon( 'book' ); ?><?php esc_html_e( 'Каталог', 'xi-novels' ); ?></a>
		</div>

	<?php elseif ( 'users' === $xin_tab ) : ?>

		<?php
		$xin_q     = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$xin_page  = isset( $_GET['pg'] ) ? max( 1, absint( $_GET['pg'] ) ) : 1;
		$xin_per   = 20;
		$xin_query = new WP_User_Query( array(
			'number'  => $xin_per,
			'paged'   => $xin_page,
			'search'  => $xin_q ? '*' . $xin_q . '*' : '',
			'orderby' => 'registered',
			'order'   => 'DESC',
		) );
		$xin_users = $xin_query->get_results();
		$xin_total = (int) $xin_query->get_total();
		$xin_pages = (int) ceil( $xin_total / $xin_per );
		$xin_roles = xin_manage_roles();
		?>

		<form class="xin-manage__search" method="get" action="<?php echo esc_url( xin_manage_url() ); ?>">
			<?php xin_hidden_query_fields( xin_manage_url(), array( 'tab', 'q' ) ); ?>
			<input type="hidden" name="tab" value="users">
			<input type="search" name="q" value="<?php echo esc_attr( $xin_q ); ?>" placeholder="<?php esc_attr_e( 'Имя, логин или почта', 'xi-novels' ); ?>">
			<button class="btn btn-outline btn-sm" type="submit"><?php esc_html_e( 'Найти', 'xi-novels' ); ?></button>
			<?php if ( $xin_q ) : ?>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'users' ) ) ); ?>"><?php esc_html_e( 'Сбросить', 'xi-novels' ); ?></a>
			<?php endif; ?>
		</form>

		<div class="xin-manage__table">
			<?php foreach ( $xin_users as $xin_user ) : ?>
				<?php $xin_plus = xin_plus_label( $xin_user->ID ); ?>
				<div class="xin-manage__row">
					<div class="xin-manage__who">
						<?php echo get_avatar( $xin_user->ID, 40 ); ?>
						<div>
							<a href="<?php echo esc_url( get_author_posts_url( $xin_user->ID ) ); ?>"><?php echo esc_html( $xin_user->display_name ); ?></a>
							<small><?php echo esc_html( $xin_user->user_email ); ?></small>
						</div>
					</div>

					<div class="xin-manage__state">
						<span class="xin-badge"><?php echo esc_html( xin_role_label( $xin_user ) ); ?></span>
						<?php if ( $xin_plus ) : ?>
							<span class="xin-badge xin-badge--gold"><?php echo esc_html( 'PLUS ' . $xin_plus ); ?></span>
						<?php endif; ?>
					</div>

					<div class="xin-manage__act">
						<?php if ( xin_can_manage() && ! user_can( $xin_user->ID, 'manage_options' ) && get_current_user_id() !== $xin_user->ID ) : ?>
							<form method="post" action="<?php echo esc_url( xin_manage_url() ); ?>">
								<?php wp_nonce_field( 'xin_manage_role' ); ?>
								<input type="hidden" name="xin_manage" value="role">
								<input type="hidden" name="tab" value="users">
								<input type="hidden" name="user_id" value="<?php echo esc_attr( $xin_user->ID ); ?>">
								<select name="role" aria-label="<?php esc_attr_e( 'Роль', 'xi-novels' ); ?>">
									<?php foreach ( $xin_roles as $xin_slug => $xin_label ) : ?>
										<option value="<?php echo esc_attr( $xin_slug ); ?>" <?php selected( in_array( $xin_slug, (array) $xin_user->roles, true ) ); ?>><?php echo esc_html( $xin_label ); ?></option>
									<?php endforeach; ?>
								</select>
								<button class="btn btn-outline btn-sm" type="submit"><?php esc_html_e( 'Сменить', 'xi-novels' ); ?></button>
							</form>
						<?php endif; ?>

						<form method="post" action="<?php echo esc_url( xin_manage_url() ); ?>">
							<?php wp_nonce_field( 'xin_manage_plus' ); ?>
							<input type="hidden" name="xin_manage" value="plus">
							<input type="hidden" name="tab" value="users">
							<input type="hidden" name="user_id" value="<?php echo esc_attr( $xin_user->ID ); ?>">
							<select name="days" aria-label="<?php esc_attr_e( 'Доступ PLUS', 'xi-novels' ); ?>">
								<option value="30"><?php esc_html_e( '+30 дней', 'xi-novels' ); ?></option>
								<option value="90"><?php esc_html_e( '+90 дней', 'xi-novels' ); ?></option>
								<option value="365"><?php esc_html_e( '+год', 'xi-novels' ); ?></option>
								<option value="-1"><?php esc_html_e( 'Бессрочно', 'xi-novels' ); ?></option>
								<option value="0"><?php esc_html_e( 'Снять', 'xi-novels' ); ?></option>
							</select>
							<button class="btn btn-outline btn-sm" type="submit"><?php esc_html_e( 'Применить', 'xi-novels' ); ?></button>
						</form>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( ! $xin_users ) : ?>
				<p class="xin-empty-inline"><?php esc_html_e( 'Никого не нашлось.', 'xi-novels' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $xin_pages > 1 ) : ?>
			<div class="xin-manage__pager">
				<?php if ( $xin_page > 1 ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'users', 'q' => $xin_q, 'pg' => $xin_page - 1 ) ) ); ?>"><?php esc_html_e( 'Назад', 'xi-novels' ); ?></a>
				<?php endif; ?>
				<span><?php echo esc_html( sprintf( '%d / %d', $xin_page, $xin_pages ) ); ?></span>
				<?php if ( $xin_page < $xin_pages ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'users', 'q' => $xin_q, 'pg' => $xin_page + 1 ) ) ); ?>"><?php esc_html_e( 'Дальше', 'xi-novels' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php elseif ( 'moderation' === $xin_tab ) : ?>

		<?php $xin_queue = xin_manage_queue(); ?>

		<div class="xin-manage__table">
			<?php foreach ( $xin_queue as $xin_item ) : ?>
				<div class="xin-manage__row">
					<div class="xin-manage__who">
						<div>
							<a href="<?php echo esc_url( get_permalink( $xin_item ) ); ?>"><?php echo esc_html( get_the_title( $xin_item ) ); ?></a>
							<small>
								<?php
								printf(
									/* translators: 1: post type, 2: author, 3: date */
									esc_html__( '%1$s · %2$s · %3$s', 'xi-novels' ),
									esc_html( 'novel' === $xin_item->post_type ? __( 'Тайтл', 'xi-novels' ) : __( 'Глава', 'xi-novels' ) ),
									esc_html( get_the_author_meta( 'display_name', $xin_item->post_author ) ),
									esc_html( get_the_date( '', $xin_item ) )
								);
								?>
							</small>
						</div>
					</div>

					<div class="xin-manage__act">
						<form method="post" action="<?php echo esc_url( xin_manage_url() ); ?>">
							<?php wp_nonce_field( 'xin_manage_moderate' ); ?>
							<input type="hidden" name="xin_manage" value="moderate">
							<input type="hidden" name="tab" value="moderation">
							<input type="hidden" name="post_id" value="<?php echo esc_attr( $xin_item->ID ); ?>">
							<button class="btn btn-primary btn-sm" type="submit" name="what" value="publish"><?php esc_html_e( 'Опубликовать', 'xi-novels' ); ?></button>
							<button class="btn btn-outline btn-sm" type="submit" name="what" value="draft"><?php esc_html_e( 'В черновики', 'xi-novels' ); ?></button>
							<button class="btn btn-ghost btn-sm" type="submit" name="what" value="trash"><?php esc_html_e( 'В корзину', 'xi-novels' ); ?></button>
						</form>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( ! $xin_queue ) : ?>
				<p class="xin-empty-inline"><?php esc_html_e( 'Очередь пуста — проверять нечего.', 'xi-novels' ); ?></p>
			<?php endif; ?>
		</div>

	<?php elseif ( 'titles' === $xin_tab ) : ?>

		<?php
		$xin_page  = isset( $_GET['pg'] ) ? max( 1, absint( $_GET['pg'] ) ) : 1;
		$xin_novels = new WP_Query( array(
			'post_type'      => 'novel',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 20,
			'paged'          => $xin_page,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );
		?>

		<div class="xin-manage__table">
			<?php while ( $xin_novels->have_posts() ) : $xin_novels->the_post(); ?>
				<div class="xin-manage__row">
					<div class="xin-manage__who">
						<div>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<small>
								<?php
								printf(
									/* translators: 1: author, 2: chapter count, 3: status */
									esc_html__( '%1$s · %2$s · %3$s', 'xi-novels' ),
									esc_html( get_the_author() ),
									esc_html( sprintf( _n( '%d глава', '%d глав', xin_chapter_count( get_the_ID() ), 'xi-novels' ), xin_chapter_count( get_the_ID() ) ) ),
									esc_html( get_post_status() === 'publish' ? __( 'опубликован', 'xi-novels' ) : get_post_status() )
								);
								?>
							</small>
						</div>
					</div>

					<div class="xin-manage__act">
						<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'chapters', 'project' => get_the_ID() ) ) ); ?>"><?php esc_html_e( 'Главы', 'xi-novels' ); ?></a>
						<form method="post" action="<?php echo esc_url( xin_manage_url() ); ?>">
							<?php wp_nonce_field( 'xin_manage_moderate' ); ?>
							<input type="hidden" name="xin_manage" value="moderate">
							<input type="hidden" name="tab" value="titles">
							<input type="hidden" name="post_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
							<?php if ( 'publish' === get_post_status() ) : ?>
								<button class="btn btn-outline btn-sm" type="submit" name="what" value="draft"><?php esc_html_e( 'Скрыть', 'xi-novels' ); ?></button>
							<?php else : ?>
								<button class="btn btn-primary btn-sm" type="submit" name="what" value="publish"><?php esc_html_e( 'Опубликовать', 'xi-novels' ); ?></button>
							<?php endif; ?>
						</form>
					</div>
				</div>
			<?php endwhile; wp_reset_postdata(); ?>

			<?php if ( ! $xin_novels->found_posts ) : ?>
				<p class="xin-empty-inline"><?php esc_html_e( 'Тайтлов пока нет.', 'xi-novels' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $xin_novels->max_num_pages > 1 ) : ?>
			<div class="xin-manage__pager">
				<?php if ( $xin_page > 1 ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'titles', 'pg' => $xin_page - 1 ) ) ); ?>"><?php esc_html_e( 'Назад', 'xi-novels' ); ?></a>
				<?php endif; ?>
				<span><?php echo esc_html( sprintf( '%d / %d', $xin_page, $xin_novels->max_num_pages ) ); ?></span>
				<?php if ( $xin_page < $xin_novels->max_num_pages ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_manage_url( array( 'tab' => 'titles', 'pg' => $xin_page + 1 ) ) ); ?>"><?php esc_html_e( 'Дальше', 'xi-novels' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<form class="xin-manage__settings" method="post" action="<?php echo esc_url( xin_manage_url() ); ?>">
			<?php wp_nonce_field( 'xin_manage_settings' ); ?>
			<input type="hidden" name="xin_manage" value="settings">
			<input type="hidden" name="tab" value="settings">

			<label class="xin-manage__toggle">
				<input type="checkbox" name="open_registration" value="1" <?php checked( (bool) get_theme_mod( 'xin_open_registration', true ) ); ?>>
				<span>
					<b><?php esc_html_e( 'Открытая регистрация', 'xi-novels' ); ?></b>
					<small><?php esc_html_e( 'Форма на странице входа принимает новых читателей.', 'xi-novels' ); ?></small>
				</span>
			</label>

			<label class="xin-manage__toggle">
				<input type="checkbox" name="discussions" value="1" <?php checked( xin_discussions_on() ); ?>>
				<span>
					<b><?php esc_html_e( 'Обсуждения', 'xi-novels' ); ?></b>
					<small><?php esc_html_e( 'Комментарии под главами и тайтлами. По умолчанию выключены.', 'xi-novels' ); ?></small>
				</span>
			</label>

			<label class="xin-manage__field">
				<span><?php esc_html_e( 'Кем становится новый пользователь', 'xi-novels' ); ?></span>
				<select name="new_user_role">
					<option value="author" <?php selected( xin_new_user_role(), 'author' ); ?>><?php esc_html_e( 'Автор', 'xi-novels' ); ?></option>
					<option value="contributor" <?php selected( xin_new_user_role(), 'contributor' ); ?>><?php esc_html_e( 'Участник', 'xi-novels' ); ?></option>
					<option value="subscriber" <?php selected( xin_new_user_role(), 'subscriber' ); ?>><?php esc_html_e( 'Читатель', 'xi-novels' ); ?></option>
				</select>
			</label>

			<label class="xin-manage__field">
				<span><?php esc_html_e( 'Скачивание книг', 'xi-novels' ); ?></span>
				<select name="download_audience">
					<?php foreach ( xin_download_audiences() as $xin_dl_key => $xin_dl_label ) : ?>
						<option value="<?php echo esc_attr( $xin_dl_key ); ?>" <?php selected( xin_download_audience(), $xin_dl_key ); ?>><?php echo esc_html( $xin_dl_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<div class="xin-manage__field">
				<span><?php esc_html_e( 'Роли со скачиванием', 'xi-novels' ); ?></span>
				<div class="xin-manage__roles">
					<?php $xin_dl_roles = xin_download_roles(); ?>
					<?php foreach ( xin_download_role_choices() as $xin_role_key => $xin_role_name ) : ?>
						<label>
							<input type="checkbox" name="download_roles[]" value="<?php echo esc_attr( $xin_role_key ); ?>" <?php checked( in_array( $xin_role_key, $xin_dl_roles, true ) ); ?>>
							<span><?php echo esc_html( $xin_role_name ); ?></span>
							<i><?php echo esc_html( $xin_role_key ); ?></i>
						</label>
					<?php endforeach; ?>
				</div>
				<small><?php esc_html_e( 'Учитываются в двух последних режимах. Достаточно одной отмеченной роли; администратор скачивает всегда.', 'xi-novels' ); ?></small>
			</div>

			<label class="xin-manage__field">
				<span><?php esc_html_e( 'Основной язык', 'xi-novels' ); ?></span>
				<select name="default_lang">
					<?php foreach ( xin_languages() as $xin_lang_key => $xin_lang_data ) : ?>
						<option value="<?php echo esc_attr( $xin_lang_key ); ?>" <?php selected( get_theme_mod( 'xin_default_lang', 'ru' ), $xin_lang_key ); ?>><?php echo esc_html( $xin_lang_data['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="xin-manage__field">
				<span><?php esc_html_e( 'Схема по умолчанию', 'xi-novels' ); ?></span>
				<select name="default_scheme">
					<option value="light" <?php selected( get_theme_mod( 'xin_default_scheme', 'light' ), 'light' ); ?>><?php esc_html_e( 'Светлая', 'xi-novels' ); ?></option>
					<option value="dark" <?php selected( get_theme_mod( 'xin_default_scheme', 'light' ), 'dark' ); ?>><?php esc_html_e( 'Тёмная', 'xi-novels' ); ?></option>
					<option value="auto" <?php selected( get_theme_mod( 'xin_default_scheme', 'light' ), 'auto' ); ?>><?php esc_html_e( 'Как в системе', 'xi-novels' ); ?></option>
				</select>
			</label>

			<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Сохранить', 'xi-novels' ); ?></button>
		</form>

	<?php endif; ?>

</div>

<?php get_footer(); ?>
