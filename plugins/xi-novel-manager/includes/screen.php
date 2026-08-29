<?php
/**
 * The admin screen: filter bar, table, bulk action bar.
 *
 * @package XI_Novel_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xnm_render_screen() {
	if ( ! current_user_can( xnm_capability() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-manager' ) );
	}

	if ( xnm_theme_missing() ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Массовое управление', 'xi-novel-manager' ) . '</h1>';
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Тип записи «Новелла» не зарегистрирован. Включите тему XIN-Com — управлять нечем.', 'xi-novel-manager' ) . '</p></div></div>';
		return;
	}

	$filters = xnm_filters();
	$query   = new WP_Query( xnm_query_args( $filters ) );
	$total   = (int) $query->found_posts;
	$pages   = (int) $query->max_num_pages;
	$chapters = xnm_chapter_counts( wp_list_pluck( $query->posts, 'ID' ) );
	?>
	<div class="wrap xnm">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Массовое управление тайтлами', 'xi-novel-manager' ); ?></h1>
		<a class="page-title-action" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=novel' ) ); ?>"><?php esc_html_e( 'Добавить', 'xi-novel-manager' ); ?></a>
		<hr class="wp-header-end">

		<?php xnm_notice(); ?>

		<form class="xnm-filters" method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
			<input type="hidden" name="post_type" value="novel">
			<input type="hidden" name="page" value="xi-novel-manager">

			<p class="search-box">
				<label class="screen-reader-text" for="xnm-s"><?php esc_html_e( 'Поиск по названию', 'xi-novel-manager' ); ?></label>
				<input type="search" id="xnm-s" name="s" value="<?php echo esc_attr( $filters['s'] ); ?>" placeholder="<?php esc_attr_e( 'Название или часть описания', 'xi-novel-manager' ); ?>">
			</p>

			<?php
			xnm_select( 'xnm_author', __( 'Владелец', 'xi-novel-manager' ), xnm_author_options(), $filters['author'] ? (string) $filters['author'] : '' );
			xnm_select( 'xnm_genre', __( 'Жанр', 'xi-novel-manager' ), xnm_term_options( 'genre' ), $filters['genre'] );
			xnm_select( 'xnm_status', __( 'Статус', 'xi-novel-manager' ), xnm_term_options( 'novel_status' ), $filters['status'] );
			xnm_select( 'xnm_state', __( 'Публикация', 'xi-novel-manager' ), array(
				'any'     => __( 'Любая', 'xi-novel-manager' ),
				'publish' => __( 'Опубликованные', 'xi-novel-manager' ),
				'draft'   => __( 'Черновики', 'xi-novel-manager' ),
				'pending' => __( 'На проверке', 'xi-novel-manager' ),
				'private' => __( 'Личные', 'xi-novel-manager' ),
				'trash'   => __( 'В корзине', 'xi-novel-manager' ),
			), $filters['state'], false );
			xnm_select( 'xnm_cover', __( 'Обложка', 'xi-novel-manager' ), array(
				'yes' => __( 'Есть', 'xi-novel-manager' ),
				'no'  => __( 'Нет', 'xi-novel-manager' ),
			), $filters['cover'] );
			xnm_select( 'xnm_adult', __( '18+', 'xi-novel-manager' ), array(
				'yes' => __( 'Только 18+', 'xi-novel-manager' ),
				'no'  => __( 'Без 18+', 'xi-novel-manager' ),
			), $filters['adult'] );
			xnm_select( 'xnm_orderby', __( 'Сортировка', 'xi-novel-manager' ), array(
				'date'     => __( 'По дате', 'xi-novel-manager' ),
				'title'    => __( 'По алфавиту', 'xi-novel-manager' ),
				'modified' => __( 'По изменению', 'xi-novel-manager' ),
				'views'    => __( 'По просмотрам', 'xi-novel-manager' ),
				'rating'   => __( 'По оценке', 'xi-novel-manager' ),
			), $filters['orderby'], false );
			xnm_select( 'xnm_order', __( 'Порядок', 'xi-novel-manager' ), array(
				'desc' => __( 'По убыванию', 'xi-novel-manager' ),
				'asc'  => __( 'По возрастанию', 'xi-novel-manager' ),
			), strtolower( $filters['order'] ), false );
			?>

			<button type="submit" class="button"><?php esc_html_e( 'Показать', 'xi-novel-manager' ); ?></button>
			<a class="button-link xnm-reset" href="<?php echo esc_url( admin_url( 'edit.php?post_type=novel&page=xi-novel-manager' ) ); ?>"><?php esc_html_e( 'Сбросить', 'xi-novel-manager' ); ?></a>
		</form>

		<form method="post" class="xnm-form" id="xnm-form">
			<?php wp_nonce_field( 'xnm_bulk' ); ?>

			<div class="xnm-bar">
				<label class="screen-reader-text" for="xnm-action"><?php esc_html_e( 'Действие', 'xi-novel-manager' ); ?></label>
				<select name="xnm_action" id="xnm-action">
					<option value=""><?php esc_html_e( 'Действие над отмеченными…', 'xi-novel-manager' ); ?></option>
					<?php foreach ( xnm_actions() as $group => $items ) : ?>
						<optgroup label="<?php echo esc_attr( $group ); ?>">
							<?php foreach ( $items as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>

				<span class="xnm-field" data-xnm-for="novel_status" hidden>
					<select name="xnm_novel_status">
						<?php foreach ( xnm_term_options( 'novel_status' ) as $slug => $name ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>

				<span class="xnm-field" data-xnm-for="terms" hidden>
					<input type="text" name="xnm_terms" size="34" placeholder="<?php esc_attr_e( 'Через запятую: Фэнтези, Драма', 'xi-novel-manager' ); ?>">
				</span>

				<span class="xnm-field" data-xnm-for="owner" hidden>
					<select name="xnm_owner">
						<?php foreach ( xnm_author_options() as $id => $name ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>

				<span class="xnm-field" data-xnm-for="translator" hidden>
					<input type="text" name="xnm_translator" size="26" placeholder="<?php esc_attr_e( 'Название команды (пусто — очистить)', 'xi-novel-manager' ); ?>">
				</span>

				<span class="xnm-field" data-xnm-for="cover" hidden>
					<input type="hidden" name="xnm_cover_id" value="" id="xnm-cover-id">
					<button type="button" class="button" id="xnm-cover-pick"><?php esc_html_e( 'Выбрать файл…', 'xi-novel-manager' ); ?></button>
					<span id="xnm-cover-name" class="xnm-muted"></span>
				</span>

				<button type="submit" class="button button-primary"><?php esc_html_e( 'Применить', 'xi-novel-manager' ); ?></button>

				<span class="xnm-count">
					<?php
					printf(
						/* translators: %s: number of novels found. */
						esc_html__( 'Найдено: %s', 'xi-novel-manager' ),
						'<b>' . esc_html( number_format_i18n( $total ) ) . '</b>'
					);
					?>
					<span id="xnm-picked"></span>
				</span>
			</div>

			<?php if ( $total > count( $query->posts ) ) : ?>
				<p class="xnm-all">
					<label>
						<input type="checkbox" name="xnm_all_matching" value="1" id="xnm-all-matching">
						<?php
						printf(
							/* translators: %s: number of novels the filter matches. */
							esc_html__( 'Применить ко всем %s найденным, а не только к отмеченным на этой странице', 'xi-novel-manager' ),
							esc_html( number_format_i18n( $total ) )
						);
						?>
					</label>
				</p>
			<?php endif; ?>

			<table class="wp-list-table widefat fixed striped xnm-table">
				<thead>
					<tr>
						<td class="check-column"><input type="checkbox" id="xnm-check-all" aria-label="<?php esc_attr_e( 'Отметить все', 'xi-novel-manager' ); ?>"></td>
						<th class="xnm-col-cover"><?php esc_html_e( 'Обложка', 'xi-novel-manager' ); ?></th>
						<th><?php esc_html_e( 'Название', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-mid"><?php esc_html_e( 'Владелец', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-mid"><?php esc_html_e( 'Жанры', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-small"><?php esc_html_e( 'Статус', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-tiny"><?php esc_html_e( 'Глав', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-tiny"><?php esc_html_e( 'Просм.', 'xi-novel-manager' ); ?></th>
						<th class="xnm-col-small"><?php esc_html_e( 'Изменён', 'xi-novel-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $query->have_posts() ) : ?>
						<tr><td colspan="9"><?php esc_html_e( 'Ничего не найдено. Попробуйте ослабить фильтры.', 'xi-novel-manager' ); ?></td></tr>
					<?php endif; ?>

					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$xnm_id = get_the_ID();
						?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="xnm_ids[]" value="<?php echo esc_attr( $xnm_id ); ?>"
									<?php disabled( ! current_user_can( 'edit_post', $xnm_id ) ); ?>>
							</th>
							<td class="xnm-col-cover">
								<?php if ( has_post_thumbnail( $xnm_id ) ) : ?>
									<?php echo get_the_post_thumbnail( $xnm_id, array( 40, 60 ), array( 'class' => 'xnm-thumb' ) ); ?>
								<?php else : ?>
									<span class="xnm-thumb xnm-thumb--empty" aria-hidden="true"></span>
								<?php endif; ?>
							</td>
							<td>
								<strong><a href="<?php echo esc_url( get_edit_post_link( $xnm_id ) ); ?>"><?php the_title(); ?></a></strong>
								<?php if ( get_post_meta( $xnm_id, '_xin_adult', true ) ) : ?>
									<span class="xnm-flag xnm-flag--adult">18+</span>
								<?php endif; ?>
								<?php if ( 'publish' !== get_post_status( $xnm_id ) ) : ?>
									<span class="xnm-flag"><?php echo esc_html( get_post_status( $xnm_id ) ); ?></span>
								<?php endif; ?>
								<div class="row-actions">
									<span><a href="<?php echo esc_url( get_edit_post_link( $xnm_id ) ); ?>"><?php esc_html_e( 'Изменить', 'xi-novel-manager' ); ?></a> | </span>
									<span><a href="<?php echo esc_url( get_permalink( $xnm_id ) ); ?>"><?php esc_html_e( 'Смотреть', 'xi-novel-manager' ); ?></a></span>
								</div>
							</td>
							<td class="xnm-col-mid"><?php echo esc_html( get_the_author() ); ?></td>
							<td class="xnm-col-mid"><?php echo esc_html( xnm_term_list( $xnm_id, 'genre' ) ); ?></td>
							<td class="xnm-col-small"><?php echo esc_html( xnm_term_list( $xnm_id, 'novel_status' ) ); ?></td>
							<td class="xnm-col-tiny"><?php echo esc_html( number_format_i18n( isset( $chapters[ $xnm_id ] ) ? $chapters[ $xnm_id ] : 0 ) ); ?></td>
							<td class="xnm-col-tiny"><?php echo esc_html( number_format_i18n( (int) get_post_meta( $xnm_id, '_xin_views', true ) ) ); ?></td>
							<td class="xnm-col-small"><?php echo esc_html( get_the_modified_date( 'd.m.Y' ) ); ?></td>
						</tr>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</tbody>
			</table>
		</form>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				echo wp_kses_post( paginate_links( array(
					'base'      => xnm_url( array( 'paged' => '' ) ) . '&paged=%#%',
					'format'    => '',
					'current'   => $filters['paged'],
					'total'     => $pages,
					'prev_text' => '‹',
					'next_text' => '›',
				) ) );
				?>
			</div></div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * The message shown after an action ran.
 */
function xnm_notice() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only.
	$msg = isset( $_GET['xnm_msg'] ) ? sanitize_key( wp_unslash( $_GET['xnm_msg'] ) ) : '';
	if ( ! $msg ) {
		return;
	}

	if ( 'none' === $msg ) {
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Ни один тайтл не был отмечен — ничего не изменилось.', 'xi-novel-manager' ) . '</p></div>';
		return;
	}

	$count = isset( $_GET['xnm_n'] ) ? absint( $_GET['xnm_n'] ) : 0;
	$did   = isset( $_GET['xnm_did'] ) ? sanitize_key( wp_unslash( $_GET['xnm_did'] ) ) : '';
	// phpcs:enable

	$label = '';
	foreach ( xnm_actions() as $items ) {
		if ( isset( $items[ $did ] ) ) {
			$label = $items[ $did ];
			break;
		}
	}

	if ( ! $count ) {
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Ничего не изменилось — возможно, не заполнено поле действия.', 'xi-novel-manager' ) . '</p></div>';
		return;
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html( sprintf(
			/* translators: 1: name of the action, 2: number of novels it touched. */
			__( 'Готово: %1$s — тайтлов затронуто: %2$s.', 'xi-novel-manager' ),
			rtrim( $label, '…' ),
			number_format_i18n( $count )
		) )
	);
}

/**
 * One labelled dropdown in the filter bar.
 *
 * @param string $name    Field name.
 * @param string $label   Visible label.
 * @param array  $options value => label.
 * @param string $current Selected value.
 * @param bool   $any     Offer an empty "any" option first.
 */
function xnm_select( $name, $label, $options, $current, $any = true ) {
	?>
	<label class="xnm-filter">
		<span><?php echo esc_html( $label ); ?></span>
		<select name="<?php echo esc_attr( $name ); ?>">
			<?php if ( $any ) : ?>
				<option value=""><?php esc_html_e( '— любой —', 'xi-novel-manager' ); ?></option>
			<?php endif; ?>
			<?php foreach ( $options as $value => $text ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (string) $current, (string) $value ); ?>><?php echo esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<?php
}

/**
 * Term slug => name for a taxonomy.
 */
function xnm_term_options( $taxonomy ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$out = array();
	foreach ( $terms as $term ) {
		$out[ $term->slug ] = $term->name;
	}
	return $out;
}

/**
 * Users who own at least one novel, plus the current user so a lone editor can
 * always hand a title to themselves.
 */
function xnm_author_options() {
	$users = get_users( array(
		'has_published_posts' => array( 'novel' ),
		'fields'              => array( 'ID', 'display_name' ),
		'orderby'             => 'display_name',
	) );

	$out = array();
	foreach ( $users as $user ) {
		$out[ (int) $user->ID ] = $user->display_name;
	}

	$me = wp_get_current_user();
	if ( $me && $me->ID && ! isset( $out[ (int) $me->ID ] ) ) {
		$out[ (int) $me->ID ] = $me->display_name;
	}

	return $out;
}
