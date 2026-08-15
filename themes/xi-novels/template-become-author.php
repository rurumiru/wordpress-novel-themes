<?php
/**
 * Template Name: Стать автором
 */

get_header();

$xin_stats = xin_site_stats();
$xin_logged = is_user_logged_in();

$xin_steps = array(
	array( 'icon' => 'user', 'title' => __( 'Заведите аккаунт', 'xi-novels' ), 'text' => __( 'Регистрация занимает минуту. Роль «Автор» открывает публикацию сразу, «Участник» отправляет первые главы на проверку редактору.', 'xi-novels' ) ),
	array( 'icon' => 'book', 'title' => __( 'Создайте проект', 'xi-novels' ), 'text' => __( 'Название, краткое описание, жанры, обложка. Всё в одной форме кабинета — админка WordPress не нужна.', 'xi-novels' ) ),
	array( 'icon' => 'pen', 'title' => __( 'Напишите первую главу', 'xi-novels' ), 'text' => __( 'Полноценный редактор с картинками и вставкой из Word. Черновик сохраняется в браузере, пока вы печатаете.', 'xi-novels' ) ),
	array( 'icon' => 'trending', 'title' => __( 'Выходите по расписанию', 'xi-novels' ), 'text' => __( 'Кнопка «Опубликовать и начать следующую» держит темп. Каждая глава поднимает тайтл в ленте обновлений.', 'xi-novels' ) ),
);

$xin_perks = array(
	array( 'icon' => 'library', 'title' => __( 'Своя витрина', 'xi-novels' ), 'text' => __( 'Страница тайтла с обложкой, оглавлением, оценкой и похожими работами — без вёрстки с вашей стороны.', 'xi-novels' ) ),
	array( 'icon' => 'users', 'title' => __( 'Профиль автора', 'xi-novels' ), 'text' => __( 'Публичная страница со статистикой, ссылками и лентой ваших глав. Читателю есть что открыть после первой главы.', 'xi-novels' ) ),
	array( 'icon' => 'crown', 'title' => __( 'Ранний доступ', 'xi-novels' ), 'text' => __( 'Отмечайте главы как PLUS — они видны в оглавлении с замком и открываются подписчикам.', 'xi-novels' ) ),
	array( 'icon' => 'eye', 'title' => __( 'Честная статистика', 'xi-novels' ), 'text' => __( 'Просмотры тайтла и каждой главы, оценки читателей, позиция в рейтинге — без чёрного ящика.', 'xi-novels' ) ),
	array( 'icon' => 'clock', 'title' => __( 'Лента обновлений', 'xi-novels' ), 'text' => __( 'Новая глава попадает в общую ленту площадки и в раздел «Недавно обновлены» на главной.', 'xi-novels' ) ),
	array( 'icon' => 'gift', 'title' => __( 'Поддержка от читателей', 'xi-novels' ), 'text' => __( 'Добавьте ссылку на донат в профиле — кнопка появится рядом с вашим именем.', 'xi-novels' ) ),
);

$xin_faq = array(
	array( __( 'Нужно ли платить за публикацию?', 'xi-novels' ), __( 'Нет. Публикация, страницы тайтлов, читалка и статистика бесплатны и остаются такими.', 'xi-novels' ) ),
	array( __( 'Я перевожу, а не пишу своё. Подойдёт?', 'xi-novels' ), __( 'Да. В карточке проекта есть отдельные поля для автора оригинала, оригинального названия и команды перевода.', 'xi-novels' ) ),
	array( __( 'Можно перенести тайтл с другой площадки?', 'xi-novels' ), __( 'Можно. Главы создаются по одной в кабинете, а для больших переносов подойдёт любой импортёр записей — модель данных простая.', 'xi-novels' ) ),
	array( __( 'Кому принадлежат права на текст?', 'xi-novels' ), __( 'Автору. Площадка показывает текст и не претендует на права; удалить проект можно в любой момент из кабинета.', 'xi-novels' ) ),
	array( __( 'Как часто нужно выходить?', 'xi-novels' ), __( 'Жёсткого требования нет, но читатели возвращаются к тем, кто держит ритм. Даже одна глава в неделю работает лучше, чем десять раз в год.', 'xi-novels' ) ),
);
?>

<div class="xin-lp">
	<section class="xin-lp__hero xin-aurora">
		<div class="xin-wrap">
			<span class="xin-eyebrow"><?php esc_html_e( 'авторам и переводчикам', 'xi-novels' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p class="xin-lp__lead">
				<?php esc_html_e( 'Приносите текст — остальное площадка берёт на себя: витрина, читалка, статистика и читатели, которые возвращаются за следующей главой.', 'xi-novels' ); ?>
			</p>

			<div class="xin-lp__cta">
				<?php if ( $xin_logged ) : ?>
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-novel' ) ) ); ?>">
						<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Создать проект', 'xi-novels' ); ?>
					</a>
					<a class="btn btn-outline btn-lg" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
						<?php esc_html_e( 'Открыть кабинет', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				<?php else : ?>
					<?php if ( get_option( 'users_can_register' ) ) : ?>
						<a class="btn btn-primary btn-lg" href="<?php echo esc_url( wp_registration_url() ); ?>">
							<?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Начать публиковать', 'xi-novels' ); ?>
						</a>
					<?php endif; ?>
					<a class="btn btn-outline btn-lg" href="<?php echo esc_url( wp_login_url( xin_dashboard_url() ) ); ?>">
						<?php esc_html_e( 'У меня есть аккаунт', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $xin_stats['novels'] > 0 ) : ?>
				<dl class="xin-lp__numbers">
					<div><dt><?php echo esc_html( number_format_i18n( $xin_stats['novels'] ) ); ?></dt><dd><?php esc_html_e( 'тайтлов на площадке', 'xi-novels' ); ?></dd></div>
					<div><dt><?php echo esc_html( number_format_i18n( $xin_stats['chapters'] ) ); ?></dt><dd><?php esc_html_e( 'опубликованных глав', 'xi-novels' ); ?></dd></div>
					<div><dt><?php echo esc_html( xin_num( $xin_stats['views'] ) ); ?></dt><dd><?php esc_html_e( 'прочтений', 'xi-novels' ); ?></dd></div>
				</dl>
			<?php endif; ?>
		</div>
	</section>

	<section class="xin-wrap xin-section">
		<?php
		xin_section_head( array(
			'eyebrow'  => __( 'как это работает', 'xi-novels' ),
			'title'    => __( 'Четыре шага до первой главы', 'xi-novels' ),
			'subtitle' => __( 'От регистрации до публикации — один вечер', 'xi-novels' ),
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
			'eyebrow' => __( 'что вы получаете', 'xi-novels' ),
			'title'   => __( 'Инструменты, а не обещания', 'xi-novels' ),
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
			'title' => __( 'Частые вопросы', 'xi-novels' ),
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
			<h2><?php esc_html_e( 'Первая глава начинается с пустой страницы', 'xi-novels' ); ?></h2>
			<p><?php esc_html_e( 'У вас уже есть текст — площадке остаётся его показать. Заведите проект и посмотрите, как он выглядит в каталоге.', 'xi-novels' ); ?></p>
			<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $xin_logged ? xin_dashboard_url( array( 'view' => 'new-novel' ) ) : wp_registration_url() ); ?>">
				<?php echo esc_html( $xin_logged ? __( 'Создать проект', 'xi-novels' ) : __( 'Начать публиковать', 'xi-novels' ) ); ?>
				<?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		</div>
	</section>
</div>

<?php get_footer(); ?>
