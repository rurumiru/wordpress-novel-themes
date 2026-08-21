<?php
/**
 * Template Name: Кабинет автора
 *
 * Один экран на все действия: список проектов, форма проекта, список глав,
 * редактор главы. Разделы переключаются параметром `view` — так каждое
 * состояние имеет собственную ссылку и работает кнопка «назад».
 *
 * @package XI_Novels
 */

get_header();

$xin_view     = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'novels';

$xin_novel_id = isset( $_GET['project'] ) ? absint( $_GET['project'] ) : 0;
$xin_edit_id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
?>

<div class="xin-wrap">

	<?php if ( ! is_user_logged_in() ) : ?>

		<div class="xin-empty" style="padding-block:80px">
			<?php xin_the_icon( 'pen' ); ?>
			<h1><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></h1>
			<p><?php esc_html_e( 'Войдите под своей учётной записью, чтобы вести проекты и публиковать главы.', 'xi-novels' ); ?></p>
			<div class="xin-flex xin-flex-wrap xin-mt-2" style="justify-content:center">
				<a class="btn btn-primary" href="<?php echo esc_url( xin_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a>
				<?php if ( xin_registration_open() ) : ?>
					<a class="btn btn-outline" href="<?php echo esc_url( xin_register_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Создать аккаунт', 'xi-novels' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

	<?php elseif ( ! xin_can_author() ) : ?>

		<div class="xin-empty" style="padding-block:80px">
			<?php xin_the_icon( 'lock' ); ?>
			<h1><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></h1>
			<p><?php esc_html_e( 'Учётная запись есть, но прав на публикацию у неё пока нет. Их выдаёт администрация площадки — напишите, и кабинет откроется.', 'xi-novels' ); ?></p>
			<div class="xin-flex xin-flex-wrap xin-mt-2" style="justify-content:center">
				<a class="btn btn-primary" href="<?php echo esc_url( xin_page_url( 'become-author' ) ); ?>"><?php esc_html_e( 'Как стать автором', 'xi-novels' ); ?></a>
				<a class="btn btn-outline" href="<?php echo esc_url( xin_page_url( 'contacts' ) ); ?>"><?php esc_html_e( 'Связаться', 'xi-novels' ); ?></a>
			</div>
		</div>

	<?php else : ?>

		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<h1><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></h1>
			<p class="xin-pagehead__sub"><?php esc_html_e( 'Проекты, главы и черновики — всё редактируется прямо здесь.', 'xi-novels' ); ?></p>
		</header>

		<div class="xin-dash">

			<nav class="xin-dash__nav">
				<a href="<?php echo esc_url( xin_dashboard_url() ); ?>" class="<?php echo 'novels' === $xin_view ? 'is-active' : ''; ?>">
					<?php xin_the_icon( 'library' ); ?><?php esc_html_e( 'Мои проекты', 'xi-novels' ); ?>
				</a>
				<a href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-novel' ) ) ); ?>" class="<?php echo 'new-novel' === $xin_view ? 'is-active' : ''; ?>">
					<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Новый проект', 'xi-novels' ); ?>
				</a>
				<a href="<?php echo esc_url( get_author_posts_url( get_current_user_id() ) ); ?>">
					<?php xin_the_icon( 'user' ); ?><?php esc_html_e( 'Мой профиль', 'xi-novels' ); ?>
				</a>
				<a href="<?php echo esc_url( xin_library_url() ); ?>">
					<?php xin_the_icon( 'bookmark' ); ?><?php esc_html_e( 'Моя библиотека', 'xi-novels' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url() ); ?>">
					<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Админка', 'xi-novels' ); ?>
				</a>
			</nav>

			<div>
				<?php xin_dashboard_notice(); ?>

				<?php
				switch ( $xin_view ) {
case 'new-novel':
					case 'edit-novel':
						$xin_novel = $xin_edit_id ? get_post( $xin_edit_id ) : null;
						if ( $xin_novel && ! xin_owns( $xin_novel->ID ) ) {
							echo '<p class="xin-empty-inline">' . esc_html__( 'Это не ваш проект.', 'xi-novels' ) . '</p>';
							break;
						}
						get_template_part( 'template-parts/dash', 'novel-form', array( 'novel' => $xin_novel ) );
						break;

case 'chapters':
						get_template_part( 'template-parts/dash', 'chapters', array( 'novel_id' => $xin_novel_id ) );
						break;

					case 'glossary':
						get_template_part( 'template-parts/dash', 'glossary', array( 'novel_id' => $xin_novel_id ) );
						break;

case 'new-chapter':
					case 'edit-chapter':
						$xin_chapter = $xin_edit_id ? get_post( $xin_edit_id ) : null;
						if ( $xin_chapter && ! xin_owns( $xin_chapter->ID ) ) {
							echo '<p class="xin-empty-inline">' . esc_html__( 'Это не ваша глава.', 'xi-novels' ) . '</p>';
							break;
						}
						get_template_part( 'template-parts/dash', 'chapter-form', array(
							'chapter'  => $xin_chapter,
							'novel_id' => $xin_chapter ? xin_chapter_novel_id( $xin_chapter->ID ) : $xin_novel_id,
						) );
						break;

default:
						get_template_part( 'template-parts/dash', 'novels' );
				}
				?>
			</div>
		</div>

	<?php endif; ?>
</div>

<?php get_footer(); ?>
