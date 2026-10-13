<?php
/**
 * Template Name: Кабинет автора
 *
 * Три состояния на одной странице: список работ, главы выбранного тайтла и
 * форма правки. Что показать, решают параметры адреса.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! is_user_logged_in() ) {
	?>
	<div class="xv-section">
		<div class="xv-empty">
			<h1 class="xv-empty-title"><?php esc_html_e( 'Кабинет автора', 'xin-v2' ); ?></h1>
			<p class="xv-empty-text"><?php esc_html_e( 'Войдите, чтобы вести свои работы.', 'xin-v2' ); ?></p>
			<a class="xv-btn primary lg" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
		</div>
	</div>
	<?php
	get_footer();
	return;
}

// Every signed-in member may edit their own profile without author privileges.
if ( ! xv_can_author() && isset($_GET['view']) && sanitize_key(wp_unslash($_GET['view'])) === 'profile' ) {
	?><div class="xv-member-page xv-member-settings"><?php xv_author_notice(); get_template_part('template-parts/author','profile'); ?></div><?php
	get_footer(); return;
}
if ( ! xv_can_author() ) {
	?>
	<div class="xv-section">
		<div class="xv-empty">
			<h1 class="xv-empty-title"><?php esc_html_e( 'Пока только читатель', 'xin-v2' ); ?></h1>
			<p class="xv-empty-text"><?php esc_html_e( 'Кабинет открывается авторам. Напишите администратору, чтобы получить доступ.', 'xin-v2' ); ?></p>
		</div>
	</div>
	<?php
	get_footer();
	return;
}

$xv_view    = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
$xv_novel   = isset( $_GET['work'] ) ? (int) $_GET['work'] : 0;
$xv_chapter = isset( $_GET['part'] ) ? (int) $_GET['part'] : 0;

if ( $xv_novel && ! current_user_can( 'edit_post', $xv_novel ) ) {
	$xv_novel = 0;
}

$xv_stats = xv_author_stats();
?>

<div class="xv-studio-shell">
	<aside class="xv-studio-sidebar"><h2><?php esc_html_e( 'Студия', 'xin-v2' ); ?></h2><p><?php esc_html_e( 'ПРОСТРАНСТВО АВТОРА И ПЕРЕВОДЧИКА', 'xin-v2' ); ?></p><nav aria-label="<?php esc_attr_e( 'Навигация студии', 'xin-v2' ); ?>"><a class="<?php echo ! $xv_view && ! $xv_novel ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url() ); ?>"><?php xv_the_icon( 'layers' ); ?><?php esc_html_e( 'Мои произведения', 'xin-v2' ); ?></a><a class="<?php echo 'novel' === $xv_view ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url( array( 'view' => 'novel' ) ) ); ?>"><?php xv_the_icon( 'pen' ); ?><?php esc_html_e( 'Новое произведение', 'xin-v2' ); ?></a><?php if ( $xv_novel ) : ?><a class="<?php echo ! $xv_view ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel ) ) ); ?>"><?php xv_the_icon( 'book-open' ); ?><?php esc_html_e( 'Главы', 'xin-v2' ); ?></a><a class="<?php echo 'glossary' === $xv_view ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel, 'view' => 'glossary' ) ) ); ?>"><?php xv_the_icon( 'tag' ); ?><?php esc_html_e( 'Глоссарий перевода', 'xin-v2' ); ?></a><?php endif; ?><a class="<?php echo 'profile' === $xv_view ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url( array( 'view' => 'profile' ) ) ); ?>"><?php xv_the_icon( 'user' ); ?><?php esc_html_e( 'Мой профиль', 'xin-v2' ); ?></a></nav><p class="xv-studio-help"><?php esc_html_e( 'Создавайте главы, сохраняйте черновики и планируйте публикации прямо здесь.', 'xin-v2' ); ?></p></aside>
<div class="xv-section xv-section-wide xv-author-area">

	<header class="xv-author-head">
		<div>
			<span class="xv-intro-label"><?php esc_html_e( 'СОЗДАВАЙТЕ · ПЕРЕВОДИТЕ · ПУБЛИКУЙТЕ', 'xin-v2' ); ?></span>
			<h1 class="xv-page-title"><?php esc_html_e( 'Творческая студия', 'xin-v2' ); ?></h1>
			<p class="xv-page-lede"><?php esc_html_e( 'Ваши произведения и переводы. Всё для следующей главы.', 'xin-v2' ); ?></p>
		</div>
		<div class="xv-author-head-actions">
			<?php if ( $xv_novel ) : ?>
				<a class="xv-btn" href="<?php echo esc_url( xv_author_url() ); ?>"><?php esc_html_e( 'Все работы', 'xin-v2' ); ?></a>
				<a class="xv-btn" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel, 'view' => 'novel' ) ) ); ?>"><?php xv_the_icon( 'pen' ); ?><?php esc_html_e( 'Настройки тайтла', 'xin-v2' ); ?></a>
				<a class="xv-btn" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel, 'view' => 'glossary' ) ) ); ?>"><?php xv_the_icon( 'tag' ); ?><?php esc_html_e( 'Словарь', 'xin-v2' ); ?></a>
				<a class="xv-btn primary" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel, 'view' => 'chapter' ) ) ); ?>"><?php esc_html_e( 'Новая глава', 'xin-v2' ); ?></a>
			<?php else : ?>
				<a class="xv-btn <?php echo 'profile' === $xv_view ? 'active' : ''; ?>" href="<?php echo esc_url( xv_author_url( array( 'view' => 'profile' ) ) ); ?>"><?php xv_the_icon( 'user' ); ?><?php esc_html_e( 'Профиль', 'xin-v2' ); ?></a>
				<a class="xv-btn primary" href="<?php echo esc_url( xv_author_url( array( 'view' => 'novel' ) ) ); ?>"><?php esc_html_e( 'Новый тайтл', 'xin-v2' ); ?></a>
			<?php endif; ?>
		</div>
	</header>

	<?php xv_author_notice(); ?>

	<?php if ( ! $xv_novel && ! $xv_view ) : ?>

		<div class="xv-stat-strip">
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['novels'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_titles( $xv_stats['novels'] ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['chapters'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_chapters( $xv_stats['chapters'] ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['views'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_views( $xv_stats['views'] ) ); ?></span>
			</div>
		</div>

		<?php $xv_list = xv_author_novels(); ?>

		<?php if ( $xv_list ) : ?>
			<div class="xv-studio-work-head"><h2><?php esc_html_e( 'Ваши произведения', 'xin-v2' ); ?></h2><input class="xv-input" type="search" data-xv-work-search placeholder="<?php esc_attr_e( 'Найти произведение…', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Поиск по своим произведениям', 'xin-v2' ); ?>"></div>
			<div class="xv-works">
				<?php foreach ( $xv_list as $xv_item ) : ?>
					<?php $xv_item_rating = xv_rating( $xv_item->ID ); ?>
					<article class="xv-work">
						<a class="xv-work-cover" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_item->ID ) ) ); ?>">
							<?php if ( xv_cover_url( $xv_item->ID ) ) : ?>
								<img src="<?php echo esc_url( xv_cover_url( $xv_item->ID ) ); ?>" alt="" loading="lazy">
							<?php endif; ?>
						</a>
						<div class="xv-work-body">
							<span class="xv-work-status is-<?php echo esc_attr( $xv_item->post_status ); ?>">
								<?php
								$xv_status_obj = get_post_status_object( $xv_item->post_status );
								echo esc_html( $xv_status_obj ? $xv_status_obj->label : $xv_item->post_status );
								?>
							</span>
							<h2 class="xv-work-title">
								<a href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_item->ID ) ) ); ?>"><?php echo esc_html( $xv_item->post_title ); ?></a>
							</h2>
							<p class="xv-work-meta">
								<span><?php xv_the_icon( 'layers' ); ?><?php echo esc_html( number_format_i18n( (int) get_post_meta( $xv_item->ID, '_xin_chapter_count', true ) ) ); ?></span>
								<span><?php xv_the_icon( 'eye' ); ?><?php echo esc_html( number_format_i18n( xv_get_views( $xv_item->ID ) ) ); ?></span>
								<?php if ( $xv_item_rating['value'] > 0 ) : ?>
									<span><?php xv_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format_i18n( $xv_item_rating['value'], 1 ) ); ?></span>
								<?php endif; ?>
							</p>
						</div>
						<div class="xv-work-actions">
							<a class="xv-btn sm" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_item->ID, 'view' => 'novel' ) ) ); ?>"><?php xv_the_icon( 'pen' ); ?><?php esc_html_e( 'Правка', 'xin-v2' ); ?></a>
							<a class="xv-btn sm" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_item->ID ) ) ); ?>"><?php esc_html_e( 'Главы', 'xin-v2' ); ?></a>
							<a class="xv-btn primary sm" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_item->ID, 'view' => 'chapter' ) ) ); ?>">+ <?php esc_html_e( 'Глава', 'xin-v2' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="xv-empty">
				<p class="xv-empty-title"><?php esc_html_e( 'Работ пока нет', 'xin-v2' ); ?></p>
				<p class="xv-empty-text"><?php esc_html_e( 'Заведите первый тайтл — главы добавите следом.', 'xin-v2' ); ?></p>
				<a class="xv-btn primary lg" href="<?php echo esc_url( xv_author_url( array( 'view' => 'novel' ) ) ); ?>"><?php esc_html_e( 'Создать тайтл', 'xin-v2' ); ?></a>
			</div>
		<?php endif; ?>

	<?php elseif ( 'novel' === $xv_view ) : ?>

		<?php get_template_part( 'template-parts/author', 'novel-form', array( 'novel' => $xv_novel ) ); ?>

	<?php elseif ( 'glossary' === $xv_view ) : ?>

		<?php get_template_part( 'template-parts/author', 'glossary', array( 'novel' => $xv_novel ) ); ?>

	<?php elseif ( 'profile' === $xv_view ) : ?>

		<?php get_template_part( 'template-parts/author', 'profile' ); ?>

	<?php elseif ( 'chapter' === $xv_view ) : ?>

		<?php get_template_part( 'template-parts/author', 'chapter-form', array( 'novel' => $xv_novel, 'chapter' => $xv_chapter ) ); ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/author', 'chapters', array( 'novel' => $xv_novel ) ); ?>

	<?php endif; ?>
</div>
</div>

<?php get_footer(); ?>
