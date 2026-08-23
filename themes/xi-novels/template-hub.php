<?php
/**
 * Template Name: Уголок читателя
 *
 * Витрина живой площадки в терминальной подаче: тёмное полотно с сеткой и
 * развёрткой, моноширинные подписи, срезанные углы, сегментные шкалы.
 * Данные берутся из inc/hub.php, здесь только вид.
 *
 * @package XI_Novels
 */

get_header();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- переключатели вкладок.
$xin_board_by = isset( $_GET['board'] ) && 'reactions' === $_GET['board'] ? 'reactions' : 'comments';
$xin_talk_by  = isset( $_GET['talk'] ) && 'replied' === $_GET['talk'] ? 'replied' : 'reacted';
// phpcs:enable

$xin_board    = xin_hub_leaderboard( $xin_board_by, 7 );
$xin_trending = xin_hub_trending( 10 );
$xin_talk     = xin_hub_top_comments( $xin_talk_by, 6 );
$xin_stats    = xin_hub_stats();
$xin_high     = xin_hub_highlights();
$xin_activity = xin_hub_activity( 8 );
$xin_me       = xin_hub_me();
$xin_base     = get_permalink();

// Прогресс до следующего уровня: уровень растёт как корень из очков, значит
// нижняя граница текущего — квадрат предыдущего номера, верхняя — своего.
$xin_lv_pct  = 0;
$xin_lv_left = 0;
if ( $xin_me ) {
	$xin_points  = $xin_me['read'] + $xin_me['comments'] * 2;
	$xin_floor   = ( $xin_me['level'] - 1 ) * ( $xin_me['level'] - 1 );
	$xin_ceiling = $xin_me['level'] * $xin_me['level'];
	$xin_lv_left = max( 0, $xin_ceiling - $xin_points );
	$xin_lv_pct  = (int) max( 3, min( 100, round( ( $xin_points - $xin_floor ) / max( 1, $xin_ceiling - $xin_floor ) * 100 ) ) );
}
?>

<div class="xin-rh">
	<div class="xin-rh__deck" aria-hidden="true">
		<span class="xin-rh__grid"></span>
		<span class="xin-rh__glow"></span>
		<span class="xin-rh__beam"></span>
		<span class="xin-rh__lines"></span>
	</div>

	<header class="xin-rh__top">
		<div class="xin-wrap">
			<?php xin_breadcrumbs(); ?>

			<p class="xin-rh__kicker">
				<i class="xin-rh__dot" aria-hidden="true"></i>
				<?php esc_html_e( 'поток активен', 'xi-novels' ); ?>
				<span>
					<?php
					/* translators: %s: current time. */
					printf( esc_html__( 'синхронизация %s', 'xi-novels' ), esc_html( wp_date( 'H:i' ) ) );
					?>
				</span>
			</p>

			<h1 class="xin-rh__title" data-text="<?php the_title_attribute(); ?>"><?php the_title(); ?></h1>
			<p class="xin-rh__lede"><?php esc_html_e( 'Кто читает рядом, о чём спорят и что происходит на площадке прямо сейчас.', 'xi-novels' ); ?></p>

			<dl class="xin-rh__ticker">
				<?php
				$xin_cells = array(
					array( __( 'Читателей', 'xi-novels' ), xin_num( $xin_stats['readers'] ) ),
					array( __( 'Проектов', 'xi-novels' ), number_format_i18n( $xin_stats['novels'] ) ),
					array( __( 'Глав', 'xi-novels' ), xin_num( $xin_stats['chapters'] ) ),
					array( __( 'Комментариев', 'xi-novels' ), xin_num( $xin_stats['comments'] ) ),
					array( __( 'Отметок', 'xi-novels' ), xin_num( $xin_stats['reactions'] ) ),
					array( __( 'Новых за месяц', 'xi-novels' ), number_format_i18n( $xin_stats['new_series'] ) ),
				);
				foreach ( $xin_cells as $xin_n => $xin_cell ) :
					?>
					<div>
						<dt><?php echo esc_html( sprintf( '%02d', $xin_n + 1 ) ); ?> <?php echo esc_html( $xin_cell[0] ); ?></dt>
						<dd><?php echo esc_html( $xin_cell[1] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</header>

	<?php if ( $xin_high['series'] || $xin_high['author'] || $xin_high['contributor'] ) : ?>
		<div class="xin-wrap">
			<ul class="xin-rh__facts">
				<?php
				$xin_facts = array(
					array( __( 'Читают чаще всего', 'xi-novels' ), $xin_high['series'] ),
					array( __( 'Пишет больше всех', 'xi-novels' ), $xin_high['author'] ),
					array( __( 'Говорит больше всех', 'xi-novels' ), $xin_high['contributor'] ),
				);
				foreach ( $xin_facts as $xin_fact ) :
					if ( ! $xin_fact[1] ) {
						continue;
					}
					?>
					<li>
						<span><?php echo esc_html( $xin_fact[0] ); ?></span>
						<a href="<?php echo esc_url( $xin_fact[1]['url'] ); ?>"><?php echo esc_html( $xin_fact[1]['title'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<section class="xin-rh__band">
		<div class="xin-wrap xin-rh__split">
			<div class="xin-rh__aside">
				<p class="xin-rh__idx">01</p>
				<h2 class="xin-rh__h"><?php esc_html_e( 'Кто говорит', 'xi-novels' ); ?></h2>
				<p class="xin-rh__note"><?php esc_html_e( 'Семь человек, вокруг которых больше всего разговора. Шкала показывает долю от первого места.', 'xi-novels' ); ?></p>
				<nav class="xin-rh__switch">
					<a class="<?php echo 'comments' === $xin_board_by ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'board', 'comments', $xin_base ) ); ?>"><?php esc_html_e( 'по комментариям', 'xi-novels' ); ?></a>
					<a class="<?php echo 'reactions' === $xin_board_by ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'board', 'reactions', $xin_base ) ); ?>"><?php esc_html_e( 'по отметкам', 'xi-novels' ); ?></a>
				</nav>
			</div>

			<div class="xin-rh__body">
				<?php if ( ! $xin_board ) : ?>
					<p class="xin-rh__empty"><?php esc_html_e( 'Обсуждений пока нет — самое время начать.', 'xi-novels' ); ?></p>
				<?php else : ?>
					<?php $xin_top_value = (int) $xin_board[0]['value']; ?>
					<ol class="xin-rh__board">
						<?php foreach ( $xin_board as $xin_i => $xin_row ) : ?>
							<li class="<?php echo 0 === $xin_i ? 'is-first' : ''; ?>">
								<span class="xin-rh__place"><?php echo esc_html( sprintf( '%02d', $xin_i + 1 ) ); ?></span>

								<a class="xin-rh__person" href="<?php echo esc_url( $xin_row['url'] ); ?>">
									<span class="xin-rh__ava"><?php echo get_avatar( $xin_row['user_id'], 0 === $xin_i ? 56 : 40 ); ?></span>
									<span class="xin-rh__who">
										<b><?php echo esc_html( $xin_row['name'] ); ?></b>
										<small>
											<em class="xin-rh__lv">LV <?php echo esc_html( number_format_i18n( $xin_row['level'] ) ); ?></em>
											<?php
											/* translators: %s: number of chapters read. */
											printf( esc_html__( 'прочитано %s', 'xi-novels' ), esc_html( xin_num( $xin_row['read'] ) ) );
											?>
										</small>
									</span>
								</a>

								<span class="xin-rh__meter" aria-hidden="true"><i style="--p:<?php echo (int) xin_hub_share( $xin_row['value'], $xin_top_value ); ?>%"></i></span>

								<span class="xin-rh__pair <?php echo 'comments' === $xin_board_by ? 'is-key' : ''; ?>">
									<b><?php echo esc_html( number_format_i18n( $xin_row['comments'] ) ); ?></b>
									<small><?php esc_html_e( 'реплик', 'xi-novels' ); ?></small>
								</span>
								<span class="xin-rh__pair <?php echo 'reactions' === $xin_board_by ? 'is-key' : ''; ?>">
									<b><?php echo esc_html( number_format_i18n( $xin_row['reactions'] ) ); ?></b>
									<small><?php esc_html_e( 'отметок', 'xi-novels' ); ?></small>
								</span>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="xin-rh__band">
		<div class="xin-wrap xin-rh__split">
			<div class="xin-rh__aside">
				<p class="xin-rh__idx">02</p>
				<h2 class="xin-rh__h"><?php esc_html_e( 'О чём спорят', 'xi-novels' ); ?></h2>
				<p class="xin-rh__note"><?php esc_html_e( 'Главы, набравшие больше всего разговора за две недели.', 'xi-novels' ); ?></p>
			</div>

			<div class="xin-rh__body">
				<?php if ( ! $xin_trending ) : ?>
					<p class="xin-rh__empty"><?php esc_html_e( 'За две недели никто ничего не обсуждал.', 'xi-novels' ); ?></p>
				<?php else : ?>
					<?php $xin_top_talk = (int) $xin_trending[0]['comments']; ?>
					<ol class="xin-rh__chapters">
						<?php foreach ( $xin_trending as $xin_i => $xin_t ) : ?>
							<li>
								<span class="xin-rh__place"><?php echo esc_html( sprintf( '%02d', $xin_i + 1 ) ); ?></span>
								<a href="<?php echo esc_url( $xin_t['url'] ); ?>">
									<span class="xin-rh__thumb">
										<?php if ( $xin_t['cover'] ) : ?>
											<img src="<?php echo esc_url( $xin_t['cover'] ); ?>" alt="" loading="lazy">
										<?php endif; ?>
									</span>
									<span class="xin-rh__what">
										<b><?php echo esc_html( $xin_t['novel'] ? $xin_t['novel'] : $xin_t['title'] ); ?></b>
										<small>
											<?php
											/* translators: 1: chapter number, 2: chapter title. */
											printf( esc_html__( 'глава %1$s · %2$s', 'xi-novels' ), esc_html( $xin_t['label'] ), esc_html( $xin_t['title'] ) );
											?>
										</small>
										<span class="xin-rh__meter" aria-hidden="true"><i style="--p:<?php echo (int) xin_hub_share( $xin_t['comments'], $xin_top_talk ); ?>%"></i></span>
									</span>
									<span class="xin-rh__pair is-key">
										<b><?php echo esc_html( number_format_i18n( $xin_t['comments'] ) ); ?></b>
										<small><?php esc_html_e( 'реплик', 'xi-novels' ); ?></small>
									</span>
									<span class="xin-rh__pair">
										<b><?php echo esc_html( number_format_i18n( $xin_t['reactions'] ) ); ?></b>
										<small><?php esc_html_e( 'отметок', 'xi-novels' ); ?></small>
									</span>
									<span class="xin-rh__pair is-dim">
										<b><?php echo esc_html( xin_num( $xin_t['views'] ) ); ?></b>
										<small><?php esc_html_e( 'просмотров', 'xi-novels' ); ?></small>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="xin-rh__band">
		<div class="xin-wrap xin-rh__split">
			<div class="xin-rh__aside">
				<p class="xin-rh__idx">03</p>
				<h2 class="xin-rh__h"><?php esc_html_e( 'Сказано', 'xi-novels' ); ?></h2>
				<p class="xin-rh__note"><?php esc_html_e( 'Реплики, которые собрали отклик. Длинные обрезаны.', 'xi-novels' ); ?></p>
				<nav class="xin-rh__switch">
					<a class="<?php echo 'reacted' === $xin_talk_by ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'talk', 'reacted', $xin_base ) ); ?>"><?php esc_html_e( 'с отметками', 'xi-novels' ); ?></a>
					<a class="<?php echo 'replied' === $xin_talk_by ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'talk', 'replied', $xin_base ) ); ?>"><?php esc_html_e( 'с ответами', 'xi-novels' ); ?></a>
				</nav>
			</div>

			<div class="xin-rh__body">
				<?php if ( ! $xin_talk ) : ?>
					<p class="xin-rh__empty"><?php esc_html_e( 'Ни одного отмеченного комментария пока нет.', 'xi-novels' ); ?></p>
				<?php else : ?>
					<div class="xin-rh__quotes">
						<?php foreach ( $xin_talk as $xin_c ) : ?>
							<figure>
								<figcaption>
									<span class="xin-rh__prompt" aria-hidden="true">&gt;</span>
									<b><?php echo esc_html( $xin_c['author'] ); ?></b>
									<a href="<?php echo esc_url( $xin_c['url'] ); ?>"><?php echo esc_html( $xin_c['where'] ); ?></a>
									<time><?php echo esc_html( wp_date( 'j M Y', $xin_c['date'] ) ); ?></time>
								</figcaption>
								<blockquote><?php echo esc_html( $xin_c['text'] ); ?></blockquote>
								<p class="xin-rh__sig">
									<span>
										<?php echo esc_html( number_format_i18n( $xin_c['value'] ) ); ?>
										<?php echo 'replied' === $xin_talk_by ? esc_html__( 'ответов', 'xi-novels' ) : esc_html__( 'отметок', 'xi-novels' ); ?>
									</span>
								</p>
							</figure>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="xin-rh__band xin-rh__band--last">
		<div class="xin-wrap xin-rh__split">
			<div class="xin-rh__aside">
				<p class="xin-rh__idx">04</p>
				<h2 class="xin-rh__h"><?php esc_html_e( 'Прямо сейчас', 'xi-novels' ); ?></h2>

				<?php if ( $xin_me ) : ?>
					<div class="xin-rh__me">
						<p class="xin-rh__meh"><?php esc_html_e( 'ваш профиль', 'xi-novels' ); ?></p>
						<p class="xin-rh__melv">LV <b><?php echo esc_html( number_format_i18n( $xin_me['level'] ) ); ?></b></p>
						<span class="xin-rh__meter xin-rh__meter--wide" aria-hidden="true"><i style="--p:<?php echo (int) $xin_lv_pct; ?>%"></i></span>
						<dl>
							<div>
								<dt>
									<?php
									/* translators: %s: next level number. */
									printf( esc_html__( 'очков до уровня %s', 'xi-novels' ), esc_html( number_format_i18n( $xin_me['level'] + 1 ) ) );
									?>
								</dt>
								<dd><?php echo esc_html( number_format_i18n( $xin_lv_left ) ); ?></dd>
							</div>
							<div><dt><?php esc_html_e( 'прочитано', 'xi-novels' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $xin_me['read'] ) ); ?></dd></div>
							<div><dt><?php esc_html_e( 'дней подряд', 'xi-novels' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $xin_me['streak'] ) ); ?></dd></div>
							<div><dt><?php esc_html_e( 'реплик', 'xi-novels' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $xin_me['comments'] ) ); ?></dd></div>
							<div><dt><?php esc_html_e( 'отметок', 'xi-novels' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $xin_me['reactions'] ) ); ?></dd></div>
						</dl>
					</div>
				<?php else : ?>
					<p class="xin-rh__note">
						<?php
						printf(
							/* translators: %s: link to the sign-in page. */
							esc_html__( 'Свои цифры видны после того, как %s.', 'xi-novels' ),
							'<a href="' . esc_url( xin_login_url( xin_current_url() ) ) . '">' . esc_html__( 'войдёте', 'xi-novels' ) . '</a>'
						);
						?>
					</p>
				<?php endif; ?>
			</div>

			<div class="xin-rh__body">
				<?php if ( ! $xin_activity ) : ?>
					<p class="xin-rh__empty"><?php esc_html_e( 'Пока тихо. Как только кто-нибудь откроет главу, это появится здесь.', 'xi-novels' ); ?></p>
				<?php else : ?>
					<ul class="xin-rh__stream">
						<?php foreach ( $xin_activity as $xin_row ) : ?>
							<li>
								<time datetime="<?php echo esc_attr( wp_date( 'c', $xin_row['time'] ) ); ?>">[<?php echo esc_html( $xin_row['clock'] ); ?>]</time>
								<em class="xin-rh__lv">LV <?php echo esc_html( number_format_i18n( $xin_row['level'] ) ); ?></em>
								<span>
									<b><?php echo esc_html( $xin_row['name'] ); ?></b>
									<?php
									printf(
										/* translators: 1: novel title, 2: chapter label. */
										esc_html__( 'читает «%1$s», глава %2$s', 'xi-novels' ),
										'<a href="' . esc_url( $xin_row['url'] ) . '">' . esc_html( $xin_row['novel'] ) . '</a>',
										esc_html( $xin_row['label'] )
									);
									?>
								</span>
								<u><?php echo esc_html( xin_hub_ago( $xin_row['time'] ) ); ?></u>
							</li>
						<?php endforeach; ?>
						<li class="xin-rh__caret" aria-hidden="true"><time>[--:--:--]</time><span><i></i></span></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<p class="xin-wrap xin-rh__foot"><?php esc_html_e( 'Цифры обновляются раз в пять минут.', 'xi-novels' ); ?></p>
</div>

<?php
get_footer();
