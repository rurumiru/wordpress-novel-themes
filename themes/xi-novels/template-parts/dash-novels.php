<?php

$xin_novels = xin_user_novels();
?>

<div class="xin-panel">
	<div class="xin-panel__head">
		<h2><?php xin_the_icon( 'library' ); ?><?php esc_html_e( 'Мои проекты', 'xi-novels' ); ?></h2>
		<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-novel' ) ) ); ?>">
			<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Новый проект', 'xi-novels' ); ?>
		</a>
	</div>

	<?php if ( ! $xin_novels ) : ?>
		<p class="xin-empty-inline"><?php esc_html_e( 'Проектов пока нет. Создайте первый — это займёт минуту.', 'xi-novels' ); ?></p>
	<?php else : ?>
		<div class="xin-worklist">
			<?php foreach ( $xin_novels as $xin_novel ) : ?>
				<?php
				$xin_cover  = xin_cover_url( $xin_novel->ID, 'xin-cover-sm' );
				$xin_count  = xin_chapter_count( $xin_novel->ID );
				$xin_status = get_post_status_object( $xin_novel->post_status );
				?>
				<article class="xin-work">
					<span class="xin-work__cover">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</span>

					<div>
						<h3><a href="<?php echo esc_url( get_permalink( $xin_novel->ID ) ); ?>"><?php echo esc_html( $xin_novel->post_title ); ?></a></h3>
						<div class="xin-work__meta">
							<span><?php echo (int) $xin_count; ?> <?php echo esc_html( xin_plural( $xin_count, __( 'глава', 'xi-novels' ), __( 'главы', 'xi-novels' ), __( 'глав', 'xi-novels' ) ) ); ?></span>
							<span><?php echo esc_html( xin_num( xin_get_views( $xin_novel->ID ) ) ); ?> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?></span>
							<?php if ( 'publish' !== $xin_novel->post_status ) : ?>
								<span class="xin-badge xin-badge--gold"><?php echo esc_html( $xin_status ? $xin_status->label : $xin_novel->post_status ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="xin-work__actions">
						<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-chapter', 'project' => $xin_novel->ID ) ) ); ?>">
							<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Глава', 'xi-novels' ); ?>
						</a>
						<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'chapters', 'project' => $xin_novel->ID ) ) ); ?>">
							<?php xin_the_icon( 'list' ); ?><?php esc_html_e( 'Главы', 'xi-novels' ); ?>
						</a>
						<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'edit-novel', 'id' => $xin_novel->ID ) ) ); ?>">
							<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Правка', 'xi-novels' ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
