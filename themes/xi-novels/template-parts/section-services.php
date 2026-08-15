<?php

$xin_icon_map = array(
	'trophy', 'clock', 'layers', 'compass', 'sparkles', 'newspaper',
	'bookmark', 'pen', 'crown', 'book', 'star', 'users', 'heart', 'gift', 'award', 'list',
);

$xin_menu_items = has_nav_menu( 'quick' ) ? wp_get_nav_menu_items( get_nav_menu_locations()['quick'] ) : array();

if ( $xin_menu_items ) {
	$xin_services = array();
	foreach ( $xin_menu_items as $xin_i => $xin_item ) {
		if ( (int) $xin_item->menu_item_parent ) {
			continue;
		}
		$xin_icon = '';
		$xin_gold = false;
		foreach ( (array) $xin_item->classes as $xin_class ) {
			if ( 0 === strpos( $xin_class, 'icon-' ) ) {
				$xin_icon = substr( $xin_class, 5 );
			}
			if ( 'gold' === $xin_class ) {
				$xin_gold = true;
			}
		}
		if ( ! $xin_icon || ! xin_icon_path( $xin_icon ) ) {
			$xin_icon = $xin_icon_map[ $xin_i % count( $xin_icon_map ) ];
		}
		$xin_services[] = array(
			'label' => $xin_item->title,
			'href'  => $xin_item->url,
			'icon'  => $xin_icon,
			'gold'  => $xin_gold,
		);
	}
} else {
	$xin_services = array(
	array(
		'label' => __( 'Рейтинг', 'xi-novels' ),
		'href'  => add_query_arg( 'sort', 'rating', get_post_type_archive_link( 'novel' ) ),
		'icon'  => 'trophy',
	),
	array(
		'label' => __( 'Обновления', 'xi-novels' ),
		'href'  => get_post_type_archive_link( 'chapter' ),
		'icon'  => 'clock',
	),
	array(
		'label' => __( 'Жанры', 'xi-novels' ),
		'href'  => get_post_type_archive_link( 'novel' ) . '#genres',
		'icon'  => 'layers',
	),
	array(
		'label' => __( 'Каталог', 'xi-novels' ),
		'href'  => get_post_type_archive_link( 'novel' ),
		'icon'  => 'compass',
	),
	array(
		'label' => __( 'Новинки', 'xi-novels' ),
		'href'  => get_post_type_archive_link( 'novel' ),
		'icon'  => 'sparkles',
	),
	array(
		'label' => __( 'Блог', 'xi-novels' ),
		'href'  => get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ),
		'icon'  => 'newspaper',
	),
	array(
		'label' => __( 'Библиотека', 'xi-novels' ),
		'href'  => xin_library_url(),
		'icon'  => 'bookmark',
	),
	array(
		'label' => __( 'Авторам', 'xi-novels' ),
		'href'  => xin_dashboard_url(),
		'icon'  => 'pen',
	),
	array(
			'label' => __( 'PLUS', 'xi-novels' ),
			'href'  => get_post_type_archive_link( 'novel' ),
			'icon'  => 'crown',
			'gold'  => true,
		),
	);
}
?>
<section class="xin-services">
	<?php foreach ( $xin_services as $xin_service ) : ?>
		<a
			href="<?php echo esc_url( $xin_service['href'] ); ?>"
			class="<?php echo ! empty( $xin_service['gold'] ) ? 'is-gold' : ''; ?>"
			<?php echo isset( $xin_service['attrs'] ) ? esc_attr( $xin_service['attrs'] ) : ''; ?>
		>
			<?php xin_the_icon( $xin_service['icon'] ); ?>
			<span><?php echo esc_html( $xin_service['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</section>
