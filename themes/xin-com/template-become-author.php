<?php
/**
 * Template Name: Стать автором
 */

get_header();

$xin_stats = xin_site_stats();
$xin_logged = is_user_logged_in();

$xin_steps = array(
	array( 'icon' => 'user', 'title' => __( 'Заведите аккаунт', 'xin-com' ), 'text' => __( 'Регистрация занимает минуту. Роль «Автор» открывает публикацию сразу, «Участник» отправляет первые главы на проверку редактору.', 'xin-com' ) ),
	array( 'icon' => 'book', 'title' => __( 'Создайте проект', 'xin-com' ), 'text' => __( 'Название, краткое описание, жанры, обложка. Всё в одной форме кабинета — админка WordPress не нужна.', 'xin-com' ) ),
	array( 'icon' => 'pen', 'title' => __( 'Напишите первую главу', 'xin-com' ), 'text' => __( 'Полноценный редактор с картинками и вставкой из Word. Черновик сохраняется в браузере, пока вы печатаете.', 'xin-com' ) ),
	array( 'icon' => 'trending', 'title' => __( 'Выходите по расписанию', 'xin-com' ), 'text' => __( 'Кнопка «Опубликовать и начать следующую» держит темп. Каждая глава поднимает тайтл в ленте обновлений.', 'xin-com' ) ),
);

$xin_perks = array(
	array( 'icon' => 'library', 'title' => __( 'Своя витрина', 'xin-com' ), 'text' => __( 'Страница тайтла с обложкой, оглавлением, оценкой и похожими работами — без вёрстки с вашей стороны.', 'xin-com' ) ),
	array( 'icon' => 'users', 'title' => __( 'Профиль автора', 'xin-com' ), 'text' => __( 'Публичная страница со статистикой, ссылками и лентой ваших глав. Читателю есть что открыть после первой главы.', 'xin-com' ) ),
	array( 'icon' => 'crown', 'title' => __( 'Ранний доступ', 'xin-com' ), 'text' => __( 'Отмечайте главы как PLUS — они видны в оглавлении с замком и открываются подписчикам.', 'xin-com' ) ),
	array( 'icon' => 'eye', 'title' => __( 'Честная статистика', 'xin-com' ), 'text' => __( 'Просмотры тайтла и каждой главы, оценки читателей, позиция в рейтинге — без чёрного ящика.', 'xin-com' ) ),
	array( 'icon' => 'clock', 'title' => __( 'Лента обновлений', 'xin-com' ), 'text' => __( 'Новая глава попадает в общую ленту площадки и в раздел «Недавно обновлены» на главной.', 'xin-com' ) ),
	array( 'icon' => 'gift', 'title' => __( 'Поддержка от читателей', 'xin-com' ), 'text' => __( 'Добавьте ссылку на донат в профиле — кнопка появится рядом с вашим именем.', 'xin-com' ) ),
);

$xin_faq = array(
	array( __( 'Нужно ли платить за публикацию?', 'xin-com' ), __( 'Нет. Публикация, страницы тайтлов, читалка и статистика бесплатны и остаются такими.', 'xin-com' ) ),
	array( __( 'Я перевожу, а не пишу своё. Подойдёт?', 'xin-com' ), __( 'Да. В карточке проекта есть отдельные поля для автора оригинала, оригинального названия и команды перевода.', 'xin-com' ) ),
	array( __( 'Можно перенести тайтл с другой площадки?', 'xin-com' ), __( 'Можно. Главы создаются по одной в кабинете, а для больших переносов подойдёт любой импортёр записей — модель данных простая.', 'xin-com' ) ),
	array( __( 'Кому принадлежат права на текст?', 'xin-com' ), __( 'Автору. Площадка показывает текст и не претендует на права; удалить проект можно в любой момент из кабинета.', 'xin-com' ) ),
	array( __( 'Как часто нужно выходить?', 'xin-com' ), __( 'Жёсткого требования нет, но читатели возвращаются к тем, кто держит ритм. Даже одна глава в неделю работает лучше, чем десять раз в год.', 'xin-com' ) ),
);
?>

<div class="xin-lp">
	<section class="xin-lp__hero xin-aurora">
		<div class="xin-wrap">
			<span class="xin-eyebrow"><?php esc_html_e( 'авторам и переводчикам', 'xin-com' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p class="xin-lp__lead">
				<?php esc_html_e( 'Приносите текст — остальное площадка берёт на себя: витрина, читалка, статистика и читатели, которые возвращаются за следующей главой.', 'xin-com' ); ?>
			</p>

			<div class="xin-lp__cta">
				<?php if ( $xin_logged ) : ?>
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-novel' ) ) ); ?>">
						<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Создать проект', 'xin-com' ); ?>
					</a>
					<a class="btn btn-outline btn-lg" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
						<?php esc_html_e( 'Открыть кабинет', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				<?php else : ?>
					<?php if ( xin_registration_open() ) : ?>
						<a class="btn btn-primary btn-lg" href="<?php echo esc_url( xin_register_url( xin_dashboard_url() ) ); ?>">
							<?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Начать публиковать', 'xin-com' ); ?>
						</a>
					<?php endif; ?>
					<a class="btn btn-outline btn-lg" href="<?php echo esc_url( xin_login_url( xin_dashboard_url() ) ); ?>">
						<?php esc_html_e( 'У меня есть аккаунт', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $xin_stats['novels'] > 0 ) : ?>
				<dl class="xin-lp__numbers">
					<div><dt><?php echo esc_html( number_format_i18n( $xin_stats['novels'] ) ); ?></dt><dd><?php esc_html_e( 'тайтлов на площадке', 'xin-com' ); ?></dd></div>
					<div><dt><?php echo esc_html( number_format_i18n( $xin_stats['chapters'] ) ); ?></dt><dd><?php esc_html_e( 'опубликованных глав', 'xin-com' ); ?></dd></div>
					<div><dt><?php echo esc_html( xin_num( $xin_stats['views'] ) ); ?></dt><dd><?php esc_html_e( 'прочтений', 'xin-com' ); ?></dd></div>
				</dl>
			<?php endif; ?>
		</div>
	</section>

	<section class="xin-wrap xin-section">
		<?php
		xin_section_head( array(
			'eyebrow'  => __( 'как это работает', 'xin-com' ),
			'title'    => __( 'Четыре шага до первой главы', 'xin-com' ),
			'subtitle' => __( 'От регистрации до публикации — один вечер', 'xin-com' ),
			'icon'     => 'list',
		) );
		?>
		<div class="xin-steps">
			<?php foreach ( $xin_steps as $xin_i => $xin_step ) : ?>
				<article class="xin-step xin-reveal" style="transition-delay:<?php echo (int) $xin_i * 70; ?>ms">
					<span class="xin-step__num"><?php echo (int) ( $xin_i + 1 ); ?></span>
					<span class="xin-step__icon"><?php xin_the_icon( $xin_step['icon'] ); ?></span>
					<h3><?php echo esc_html( $xin_step['title'] ); ?></h3>
					<p><?php echo esc_html( $xin_step['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="xin-wrap xin-section">
		<?php
		xin_section_head( array(
			'eyebrow' => __( 'что вы получаете', 'xin-com' ),
			'title'   => __( 'Инструменты, а не обещания', 'xin-com' ),
			'icon'    => 'sparkles',
		) );
		?>
		<div class="xin-grid xin-grid--3">
			<?php foreach ( $xin_perks as $xin_perk ) : ?>
				<article class="xin-feature xin-reveal">
					<span class="xin-feature__icon"><?php xin_the_icon( $xin_perk['icon'] ); ?></span>
					<h3><?php echo esc_html( $xin_perk['title'] ); ?></h3>
					<p><?php echo esc_html( $xin_perk['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section class="xin-wrap xin-wrap--mid xin-section">
			<div class="xin-panel xin-content"><?php the_content(); ?></div>
		</section>
	<?php endif; ?>

	<section class="xin-wrap xin-wrap--mid xin-section">
		<?php
		xin_section_head( array(
			'title' => __( 'Частые вопросы', 'xin-com' ),
			'icon'  => 'comment',
		) );
		?>
		<div class="accordion xin-faq" id="xin-faq-author">
			<?php foreach ( $xin_faq as $xin_i => $xin_item ) : ?>
				<div class="accordion-item">
					<h3 class="accordion-header">
						<button class="accordion-button <?php echo 0 === $xin_i ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#xin-faq-a<?php echo (int) $xin_i; ?>">
							<?php echo esc_html( $xin_item[0] ); ?>
						</button>
					</h3>
					<div id="xin-faq-a<?php echo (int) $xin_i; ?>" class="accordion-collapse collapse <?php echo 0 === $xin_i ? 'show' : ''; ?>" data-bs-parent="#xin-faq-author">
						<div class="accordion-body"><?php echo esc_html( $xin_item[1] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="xin-wrap xin-section">
		<div class="xin-cta xin-cta--wide">
			<span class="xin-cta__icon"><?php xin_the_icon( 'book-open' ); ?></span>
			<h2><?php esc_html_e( 'Первая глава начинается с пустой страницы', 'xin-com' ); ?></h2>
			<p><?php esc_html_e( 'У вас уже есть текст — площадке остаётся его показать. Заведите проект и посмотрите, как он выглядит в каталоге.', 'xin-com' ); ?></p>
			<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $xin_logged ? xin_dashboard_url( array( 'view' => 'new-novel' ) ) : xin_register_url( xin_dashboard_url() ) ); ?>">
				<?php echo esc_html( $xin_logged ? __( 'Создать проект', 'xin-com' ) : __( 'Начать публиковать', 'xin-com' ) ); ?>
				<?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		</div>
	</section>
</div>

<?php get_footer(); ?>
