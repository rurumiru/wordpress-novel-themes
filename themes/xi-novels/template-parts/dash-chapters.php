<?php

$xin_novel_id = isset( $args['novel_id'] ) ? (int) $args['novel_id'] : 0;

if ( ! $xin_novel_id || ! xin_owns( $xin_novel_id ) ) {
	echo '<p class="xin-empty-inline">' . esc_html__( 'Проект не найден.', 'xi-novels' ) . '</p>';
	return;
}

$xin_chapters = get_posts( array(
	'post_type'      => 'chapter',
	'posts_per_page' => -1,
	'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	'meta_key'       => '_xin_number',
	'orderby'        => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
	'meta_query'     => array( array( 'key' => '_xin_novel', 'value' => $xin_novel_id ) ),
) );
?>

<div class="xin-panel">
	<div class="xin-panel__head">
		<h2><?php xin_the_icon( 'list' ); ?><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?></h2>
		<div class="xin-flex">
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'edit-novel', 'id' => $xin_novel_id ) ) ); ?>">
				<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Настройки проекта', 'xi-novels' ); ?>
			</a>
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-chapter', 'project' => $xin_novel_id ) ) ); ?>">
				<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Добавить главу', 'xi-novels' ); ?>
			</a>
		</div>
	</div>

	<?php if ( ! $xin_chapters ) : ?>
		<p class="xin-empty-inline"><?php esc_html_e( 'В проекте ещё нет глав.', 'xi-novels' ); ?></p>
	<?php else : ?>
		<table class="xin-chaptable">
			<thead>
				<tr>
					<th style="width:64px"><?php esc_html_e( '№', 'xi-novels' ); ?></th>
					<th><?php esc_html_e( 'Название', 'xi-novels' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'Дата', 'xi-novels' ); ?></th>
					<th style="width:170px"><?php esc_html_e( 'Действия', 'xi-novels' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
					<tr>
						<td class="xin-muted"><?php echo esc_html( xin_chapter_label( $xin_chapter->ID ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>"><?php echo esc_html( $xin_chapter->post_title ); ?></a>
							<?php if ( get_post_meta( $xin_chapter->ID, '_xin_locked', true ) ) : ?>
								<span class="xin-badge xin-badge--gold"><?php xin_the_icon( 'lock' ); ?>PLUS</span>
							<?php endif; ?>
							<?php if ( 'publish' !== $xin_chapter->post_status ) : ?>
								<span class="xin-badge"><?php echo esc_html( get_post_status_object( $xin_chapter->post_status )->label ); ?></span>
							<?php endif; ?>
						</td>
						<td class="xin-muted"><?php echo esc_html( get_the_date( 'j M Y', $xin_chapter->ID ) ); ?></td>
						<td>
							<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'edit-chapter', 'id' => $xin_chapter->ID ) ) ); ?>"><?php esc_html_e( 'Править', 'xi-novels' ); ?></a>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xin_delete&id=' . $xin_chapter->ID ), 'xin_delete' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Удалить главу?', 'xi-novels' ) ); ?>')"><?php esc_html_e( 'Удалить', 'xi-novels' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
