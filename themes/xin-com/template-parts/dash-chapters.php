<?php

$xin_novel_id = isset( $args['novel_id'] ) ? (int) $args['novel_id'] : 0;

if ( ! $xin_novel_id || ! xin_owns( $xin_novel_id ) ) {
	echo '<p class="xin-empty-inline">' . esc_html__( 'Проект не найден.', 'xin-com' ) . '</p>';
	return;
}

/*
 * Список глав в кабинете автора теперь постраничный. Прежде он поднимал все
 * главы проекта разом — вместе с текстом каждой, — и у большого перевода
 * экран студии переставал открываться.
 */
$xin_ch_page  = isset( $_GET['ch'] ) ? max( 1, absint( $_GET['ch'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$xin_ch_per   = xin_chapters_per_page();
$xin_ch_query = new WP_Query( array(
	'post_type'      => 'chapter',
	'posts_per_page' => $xin_ch_per,
	'paged'          => $xin_ch_page,
	'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	'meta_key'       => '_xin_number', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
	'meta_query'     => array( array( 'key' => '_xin_novel', 'value' => $xin_novel_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	'update_post_term_cache' => false,
) );

$xin_chapters = $xin_ch_query->posts;
$xin_ch_pages = (int) $xin_ch_query->max_num_pages;
?>

<div class="xin-panel">
	<div class="xin-panel__head">
		<h2><?php xin_the_icon( 'list' ); ?><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?></h2>
		<div class="xin-flex">
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'edit-novel', 'id' => $xin_novel_id ) ) ); ?>">
				<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Настройки проекта', 'xin-com' ); ?>
			</a>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'glossary', 'project' => $xin_novel_id ) ) ); ?>">
				<?php xin_the_icon( 'languages' ); ?><?php esc_html_e( 'Словарь проекта', 'xin-com' ); ?>
			</a>
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-chapter', 'project' => $xin_novel_id ) ) ); ?>">
				<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Добавить главу', 'xin-com' ); ?>
			</a>
		</div>
	</div>

	<?php if ( ! $xin_chapters ) : ?>
		<p class="xin-empty-inline"><?php esc_html_e( 'В проекте ещё нет глав.', 'xin-com' ); ?></p>
	<?php else : ?>
		<table class="xin-chaptable">
			<thead>
				<tr>
					<th style="width:64px"><?php esc_html_e( '№', 'xin-com' ); ?></th>
					<th><?php esc_html_e( 'Название', 'xin-com' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'Дата', 'xin-com' ); ?></th>
					<th style="width:170px"><?php esc_html_e( 'Действия', 'xin-com' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
					<?php $xin_state = xin_chapter_state( $xin_chapter ); ?>
					<tr>
						<td class="xin-muted"><?php echo esc_html( xin_chapter_label( $xin_chapter->ID ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>"><?php echo esc_html( $xin_chapter->post_title ); ?></a>
							<?php xin_the_chapter_badges( $xin_state['badges'] ); ?>
						</td>
						<td class="xin-muted xin-chaptable__when">
							<?php echo esc_html( $xin_state['date'] ); ?>
							<?php if ( ! empty( $xin_state['note'] ) ) : ?>
								<small><?php echo esc_html( $xin_state['note'] ); ?></small>
							<?php endif; ?>
						</td>
						<td>
							<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'edit-chapter', 'id' => $xin_chapter->ID ) ) ); ?>"><?php esc_html_e( 'Править', 'xin-com' ); ?></a>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( xin_dashboard_url( array( 'xin_action' => 'delete', 'id' => $xin_chapter->ID ) ), 'xin_delete' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Удалить главу?', 'xin-com' ) ); ?>')"><?php esc_html_e( 'Удалить', 'xin-com' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $xin_ch_pages > 1 ) : ?>
			<nav class="xin-nv__pager" aria-label="<?php esc_attr_e( 'Страницы списка глав', 'xin-com' ); ?>">
				<?php if ( $xin_ch_page > 1 ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( add_query_arg( 'ch', $xin_ch_page - 1, xin_dashboard_url( array( 'view' => 'chapters', 'id' => $xin_novel_id ) ) ) ); ?>"><?php xin_the_icon( 'chevron-left' ); ?><?php esc_html_e( 'Раньше', 'xin-com' ); ?></a>
				<?php endif; ?>
				<span class="xin-nv__pagenum">
					<?php
					printf(
						/* translators: 1: current page, 2: total pages. */
						esc_html__( 'Страница %1$d из %2$d', 'xin-com' ),
						(int) $xin_ch_page,
						(int) $xin_ch_pages
					);
					?>
				</span>
				<?php if ( $xin_ch_page < $xin_ch_pages ) : ?>
					<a class="btn btn-outline btn-sm" href="<?php echo esc_url( add_query_arg( 'ch', $xin_ch_page + 1, xin_dashboard_url( array( 'view' => 'chapters', 'id' => $xin_novel_id ) ) ) ); ?>"><?php esc_html_e( 'Дальше', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?></a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>
