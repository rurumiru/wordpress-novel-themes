<?php
/**
 * Template Name: PLUS
 */

get_header();

$xin_locked = new WP_Query( array(
	'post_type'      => 'chapter',
	'posts_per_page' => 6,
	'meta_query'     => array( array( 'key' => '_xin_locked', 'value' => '1' ) ),
) );

$xin_perks = array(
	array( 'icon' => 'lock', 'title' => __( 'Ранний доступ к главам', 'xin-com' ), 'text' => __( 'Главы с отметкой PLUS открываются подписчикам раньше остальных читателей.', 'xin-com' ) ),
	array( 'icon' => 'heart', 'title' => __( 'Поддержка переводчиков', 'xin-com' ), 'text' => __( 'Подписка — прямая помощь тем, кто ведёт ваши тайтлы и держит расписание выхода.', 'xin-com' ) ),
	array( 'icon' => 'bookmark', 'title' => __( 'Библиотека без ограничений', 'xin-com' ), 'text' => __( 'Закладки и история чтения синхронизируются с аккаунтом, а не только с браузером.', 'xin-com' ) ),
	array( 'icon' => 'sparkles', 'title' => __( 'Чистое чтение', 'xin-com' ), 'text' => __( 'Никаких промо-блоков в читалке — только текст, полоса прогресса и ваши настройки.', 'xin-com' ) ),
);

$xin_plans = array(
	array(
		'name'     => __( 'Читатель', 'xin-com' ),
		'price'    => __( 'Бесплатно', 'xin-com' ),
		'note'     => __( 'навсегда', 'xin-com' ),
		'features' => array(
			__( 'Весь открытый каталог', 'xin-com' ),
			__( 'Полноэкранная читалка и её настройки', 'xin-com' ),
			__( 'Закладки и история в браузере', 'xin-com' ),
			__( 'Оценки и рейтинги', 'xin-com' ),
		),
		'cta'      => __( 'Читать каталог', 'xin-com' ),
		'href'     => get_post_type_archive_link( 'novel' ),
		'featured' => false,
	),
	array(
		'name'     => 'PLUS',
		'price'    => __( 'по подписке', 'xin-com' ),
		'note'     => __( 'условия задаёт площадка', 'xin-com' ),
		'features' => array(
			__( 'Всё из бесплатного плана', 'xin-com' ),
			__( 'Ранний доступ к главам с отметкой PLUS', 'xin-com' ),
			__( 'Библиотека привязана к аккаунту', 'xin-com' ),
			__( 'Поддержка авторов и переводчиков', 'xin-com' ),
		),
		'cta'      => __( 'Подключить PLUS', 'xin-com' ),
		'href'     => is_user_logged_in() ? '#xin-plus-how' : xin_register_url(),
		'featured' => true,
	),
);
?>

<div class="xin-lp">
	<section class="xin-lp__hero xin-lp__hero--gold xin-aurora">
		<div class="xin-wrap">
			<span class="xin-eyebrow xin-eyebrow--gold"><?php esc_html_e( 'членство', 'xin-com' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p class="xin-lp__lead">
				<?php esc_html_e( 'Подписка для тех, кто читает много и хочет, чтобы любимые переводы выходили дальше. Ранний доступ к главам и прямая поддержка авторов.', 'xin-com' ); ?>
			</p>

			<div class="xin-lp__cta">
				<a class="btn btn-gold btn-lg" href="#xin-plus-plans">
					<?php xin_the_icon( 'crown' ); ?><?php esc_html_e( 'Что входит', 'xin-com' ); ?>
				</a>
				<a class="btn btn-outline btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
					<?php esc_html_e( 'В каталог', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
				</a>
			</div>
		</div>
	</section>

	<section class="xin-wrap xin-section">
		<div class="xin-grid xin-grid--4">
			<?php foreach ( $xin_perks as $xin_perk ) : ?>
				<article class="xin-feature xin-feature--gold xin-reveal">
					<span class="xin-feature__icon"><?php xin_the_icon( $xin_perk['icon'] ); ?></span>
					<h3><?php echo esc_html( $xin_perk['title'] ); ?></h3>
					<p><?php echo esc_html( $xin_perk['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="xin-wrap xin-section" id="xin-plus-plans">
		<?php
		xin_section_head( array(
			'eyebrow' => __( 'сравнение', 'xin-com' ),
			'title'   => __( 'Что даёт членство', 'xin-com' ),
			'icon'    => 'crown',
		) );
		?>
		<div class="xin-plans">
			<?php foreach ( $xin_plans as $xin_plan ) : ?>
				<article class="xin-plan<?php echo $xin_plan['featured'] ? ' is-featured' : ''; ?>">
					<?php if ( $xin_plan['featured'] ) : ?>
						<span class="xin-plan__flag"><?php esc_html_e( 'рекомендуем', 'xin-com' ); ?></span>
					<?php endif; ?>
					<h3><?php echo esc_html( $xin_plan['name'] ); ?></h3>
					<p class="xin-plan__price"><?php echo esc_html( $xin_plan['price'] ); ?><small><?php echo esc_html( $xin_plan['note'] ); ?></small></p>
					<ul class="xin-plan__list">
						<?php foreach ( $xin_plan['features'] as $xin_feature ) : ?>
							<li><?php xin_the_icon( 'check' ); ?><?php echo esc_html( $xin_feature ); ?></li>
						<?php endforeach; ?>
					</ul>
					<a class="btn <?php echo $xin_plan['featured'] ? 'btn-gold' : 'btn-outline'; ?> btn-block" href="<?php echo esc_url( $xin_plan['href'] ); ?>">
						<?php echo esc_html( $xin_plan['cta'] ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<?php if ( $xin_locked->have_posts() ) : ?>
		<section class="xin-wrap xin-section">
			<?php
			xin_section_head( array(
				'eyebrow'   => __( 'уже в раннем доступе', 'xin-com' ),
				'title'     => __( 'Главы для подписчиков', 'xin-com' ),
				'icon'      => 'lock',
				'more_href' => get_post_type_archive_link( 'chapter' ),
			) );
			?>
			<div class="xin-grid xin-grid--3">
				<?php
				while ( $xin_locked->have_posts() ) {
					$xin_locked->the_post();
					xin_chapter_card( get_the_ID() );
				}
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>

	<section class="xin-wrap xin-wrap--mid xin-section" id="xin-plus-how">
		<div class="xin-panel xin-content">
			<?php if ( get_the_content() ) : ?>
				<?php the_content(); ?>
			<?php else : ?>
				<h2><?php esc_html_e( 'Как подключить', 'xin-com' ); ?></h2>
				<p><?php esc_html_e( 'Отредактируйте эту страницу в админке и опишите здесь свои условия: стоимость, способы оплаты и как выдаётся доступ. Тема показывает главы с отметкой PLUS только вошедшим пользователям — остальное определяете вы.', 'xin-com' ); ?></p>
				<p><?php esc_html_e( 'Если планируете принимать оплату, подойдёт любой плагин членства: он решает, кто считается подписчиком, а тема уже умеет прятать главы от гостей.', 'xin-com' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php get_footer(); ?>
