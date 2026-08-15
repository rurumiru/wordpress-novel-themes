<?php

get_header();

$xin_has_sidebar = is_active_sidebar( 'sidebar-blog' );
$xin_paged       = max( 1, (int) get_query_var( 'paged' ) );
$xin_cats        = get_categories( array( 'hide_empty' => true, 'number' => 10 ) );

$xin_lead = null;
if ( 1 === $xin_paged && have_posts() ) {
	the_post();
	$xin_lead = get_post();
}
?>

<div class="xin-aurora">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<span class="xin-eyebrow"><?php esc_html_e( 'журнал', 'xi-novels' ); ?></span>
			<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Блог', 'xi-novels' ) ); ?></h1>
			<p class="xin-pagehead__sub"><?php esc_html_e( 'Новости площадки, разборы тайтлов и заметки переводчиков.', 'xi-novels' ); ?></p>
		</header>
	</div>
</div>

<div class="xin-wrap xin-mt-2">

	<?php if ( $xin_cats ) : ?>
		<div class="xin-catpills xin-mb-2">
			<a class="xin-catpill is-active" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Все', 'xi-novels' ); ?></a>
			<?php foreach ( $xin_cats as $xin_cat ) : ?>
				<a class="xin-catpill" href="<?php echo esc_url( get_category_link( $xin_cat ) ); ?>">
					<?php echo esc_html( $xin_cat->name ); ?><b><?php echo (int) $xin_cat->count; ?></b>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $xin_lead ) : ?>
		<?php $xin_lead_img = get_the_post_thumbnail_url( $xin_lead->ID, 'full' ); ?>
		<article class="xin-blog-hero xin-reveal">
			<?php if ( $xin_lead_img ) : ?>
				<div class="xin-blog-hero__bg" aria-hidden="true">
					<img src="<?php echo esc_url( $xin_lead_img ); ?>" alt="">
				</div>
			<?php endif; ?>

			<div class="xin-blog-hero__body">
				<?php
				$xin_lead_cats = get_the_category( $xin_lead->ID );
				if ( $xin_lead_cats ) :
					?>
					<span class="xin-badge xin-badge--primary"><?php echo esc_html( $xin_lead_cats[0]->name ); ?></span>
				<?php endif; ?>

				<h2><a href="<?php echo esc_url( get_permalink( $xin_lead->ID ) ); ?>"><?php echo esc_html( get_the_title( $xin_lead->ID ) ); ?></a></h2>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $xin_lead->ID ), 32 ) ); ?></p>

				<div class="xin-blog-hero__meta">
					<?php echo get_avatar( $xin_lead->post_author, 26 ); ?>
					<span><?php echo esc_html( get_the_author_meta( 'display_name', $xin_lead->post_author ) ); ?></span>
					<span>·</span>
					<span><?php echo esc_html( get_the_date( '', $xin_lead->ID ) ); ?></span>
					<span>·</span>
					<span><?php echo esc_html( xin_num( xin_get_views( $xin_lead->ID ) ) ); ?> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?></span>
				</div>

				<p class="xin-mt-2">
					<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_lead->ID ) ); ?>">
						<?php esc_html_e( 'Читать статью', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				</p>
			</div>
		</article>
	<?php endif; ?>

	<div class="xin-layout <?php echo $xin_has_sidebar ? 'xin-layout--sidebar' : ''; ?> xin-mt-3">
		<div>
			<?php if ( have_posts() ) : ?>
				<div class="xin-grid <?php echo $xin_has_sidebar ? 'xin-grid--2' : 'xin-grid--3'; ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<div class="xin-reveal"><?php xin_post_card( get_the_ID() ); ?></div>
						<?php
					endwhile;
					?>
				</div>
				<?php xin_pagination(); ?>
			<?php elseif ( ! $xin_lead ) : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $xin_has_sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
