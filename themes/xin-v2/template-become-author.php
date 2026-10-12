<?php
/**
 * Template Name: Стать автором
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_me    = get_current_user_id();
$xv_can   = $xv_me && user_can( $xv_me, 'edit_posts' );
$xv_asked = xv_has_author_request( $xv_me );
?>

<div class="xv-section xv-section-narrow">
	<header class="xv-page-head">
		<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
		<p class="xv-page-lede"><?php esc_html_e( 'Кабинет автора, свои тайтлы и главы — без единого захода в админку.', 'xin-v2' ); ?></p>
	</header>

	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="xv-prose"><?php the_content(); ?></div>
	<?php else : ?>
		<div class="xv-prose">
			<p><?php esc_html_e( 'Автор ведёт свои работы прямо на сайте: заводит тайтл, добавляет главы, ставит расписание выхода и приглашает соавторов. Ничего устанавливать не нужно.', 'xin-v2' ); ?></p>
			<p><?php esc_html_e( 'Расскажите коротко, что вы собираетесь публиковать — перевод, своё, что-то ещё. Этого хватит.', 'xin-v2' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! $xv_me ) : ?>

		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Сначала — учётная запись', 'xin-v2' ); ?></p>
			<p class="xv-empty-actions">
				<a class="xv-btn primary" href="<?php echo esc_url( xv_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
				<?php if ( xv_registration_open() ) : ?>
					<a class="xv-btn" href="<?php echo esc_url( xv_register_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Зарегистрироваться', 'xin-v2' ); ?></a>
				<?php endif; ?>
			</p>
		</div>

	<?php elseif ( $xv_can ) : ?>

		<div class="xv-notice is-ok"><?php esc_html_e( 'Вы уже можете публиковать.', 'xin-v2' ); ?></div>

		<p class="xv-empty-actions">
			<a class="xv-btn primary lg" href="<?php echo esc_url( xv_author_url() ); ?>"><?php esc_html_e( 'В кабинет автора', 'xin-v2' ); ?></a>
		</p>

	<?php elseif ( $xv_asked || ( isset( $_GET['xv_done'] ) && 'asked' === $_GET['xv_done'] ) ) : ?>

		<div class="xv-notice is-ok"><?php esc_html_e( 'Заявка отправлена. Ответим, как только прочитаем.', 'xin-v2' ); ?></div>

	<?php else : ?>

		<form class="xv-auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="xv_ask">
			<?php wp_nonce_field( 'xv_ask' ); ?>

			<div class="xv-field">
				<label class="xv-label" for="xv-about"><?php esc_html_e( 'О чём будете писать', 'xin-v2' ); ?></label>
				<textarea class="xv-textarea" id="xv-about" name="about" rows="5" required></textarea>
			</div>

			<button type="submit" class="xv-btn primary block lg"><?php esc_html_e( 'Отправить заявку', 'xin-v2' ); ?></button>
		</form>

	<?php endif; ?>
</div>

<?php get_footer(); ?>
