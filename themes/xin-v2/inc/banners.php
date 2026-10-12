<?php
/**
 * Баннеры площадки.
 *
 * Объявления о наборе переводчиков, анонсы, ссылки на канал. Это не реклама
 * со стороны — это то, что площадка говорит своим читателям, поэтому баннер
 * тут обычная запись со сроком показа, а не блок разметки в настройках.
 *
 * Мета совпадает с XIN-Com (`_xin_b_*`): баннеры переживают смену темы.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_register_banner_type() {
	register_post_type( 'xv_banner', array(
		'labels'          => array(
			'name'          => __( 'Баннеры', 'xin-v2' ),
			'singular_name' => __( 'Баннер', 'xin-v2' ),
			'add_new_item'  => __( 'Добавить баннер', 'xin-v2' ),
			'edit_item'     => __( 'Правка баннера', 'xin-v2' ),
			'all_items'     => __( 'Баннеры', 'xin-v2' ),
			'not_found'     => __( 'Баннеров нет', 'xin-v2' ),
			'menu_name'     => __( 'Баннеры', 'xin-v2' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'xin-v2',
		'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
		'capability_type' => 'page',
		'menu_icon'       => 'dashicons-megaphone',
	) );
}
add_action( 'init', 'xv_register_banner_type' );

/**
 * Поля баннера.
 */
function xv_banner_fields() {
	return array(
		'_xin_b_subtitle' => __( 'Подпись', 'xin-v2' ),
		'_xin_b_text'     => __( 'Текст', 'xin-v2' ),
		'_xin_b_cta'      => __( 'Надпись на кнопке', 'xin-v2' ),
		'_xin_b_link'     => __( 'Куда ведёт', 'xin-v2' ),
		'_xin_b_badge'    => __( 'Метка', 'xin-v2' ),
		'_xin_b_place'    => __( 'Где показывать', 'xin-v2' ),
		'_xin_b_from'     => __( 'Показывать с', 'xin-v2' ),
		'_xin_b_until'    => __( 'Показывать до', 'xin-v2' ),
	);
}

function xv_banner_places() {
	return array(
		'home'    => __( 'Главная', 'xin-v2' ),
		'catalog' => __( 'Каталог', 'xin-v2' ),
		'reader'  => __( 'Читалка', 'xin-v2' ),
	);
}

function xv_banner_box() {
	add_meta_box(
		'xv-banner',
		__( 'Баннер', 'xin-v2' ),
		'xv_banner_box_render',
		'xv_banner',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'xv_banner_box' );

function xv_banner_box_render( $post ) {
	wp_nonce_field( 'xv_banner', 'xv_banner_nonce' );

	$place = get_post_meta( $post->ID, '_xin_b_place', true );
	?>
	<p class="xv-admin-field">
		<label for="_xin_b_subtitle"><strong><?php esc_html_e( 'Подпись над заголовком', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="text" id="_xin_b_subtitle" name="_xin_b_subtitle" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_subtitle', true ) ); ?>">
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_text"><strong><?php esc_html_e( 'Текст', 'xin-v2' ); ?></strong></label>
		<textarea class="widefat" rows="3" id="_xin_b_text" name="_xin_b_text"><?php echo esc_textarea( get_post_meta( $post->ID, '_xin_b_text', true ) ); ?></textarea>
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_cta"><strong><?php esc_html_e( 'Кнопка', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="text" id="_xin_b_cta" name="_xin_b_cta" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_cta', true ) ); ?>">
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_link"><strong><?php esc_html_e( 'Ссылка', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="url" id="_xin_b_link" name="_xin_b_link" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_link', true ) ); ?>">
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_badge"><strong><?php esc_html_e( 'Метка', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="text" id="_xin_b_badge" name="_xin_b_badge" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_badge', true ) ); ?>">
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_place"><strong><?php esc_html_e( 'Где показывать', 'xin-v2' ); ?></strong></label>
		<select class="widefat" id="_xin_b_place" name="_xin_b_place">
			<?php foreach ( xv_banner_places() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $place, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_from"><strong><?php esc_html_e( 'Показывать с', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="date" id="_xin_b_from" name="_xin_b_from" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_from', true ) ); ?>">
	</p>

	<p class="xv-admin-field">
		<label for="_xin_b_until"><strong><?php esc_html_e( 'Показывать до', 'xin-v2' ); ?></strong></label>
		<input class="widefat" type="date" id="_xin_b_until" name="_xin_b_until" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_b_until', true ) ); ?>">
		<span class="description"><?php esc_html_e( 'Пусто — показывать без срока.', 'xin-v2' ); ?></span>
	</p>
	<?php
}

function xv_save_banner( $post_id ) {
	if ( ! isset( $_POST['xv_banner_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['xv_banner_nonce'] ) ), 'xv_banner' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array_keys( xv_banner_fields() ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$value = '_xin_b_link' === $key ? esc_url_raw( $value ) : sanitize_text_field( $value );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	delete_transient( 'xv_banners' );
}
add_action( 'save_post_xv_banner', 'xv_save_banner' );

/**
 * Баннеры для места на странице.
 *
 * Срок показа считаем здесь, а не запросом: баннеров на площадке единицы, и
 * сравнить две даты дешевле, чем строить мета-запрос по двум ключам.
 */
function xv_banners( $place = 'home', $limit = 2 ) {
	$all = get_transient( 'xv_banners' );

	if ( ! is_array( $all ) ) {
		$all = array();

		$posts = get_posts( array(
			'post_type'      => 'xv_banner',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );

		foreach ( $posts as $post ) {
			$all[] = array(
				'id'       => (int) $post->ID,
				'title'    => $post->post_title,
				'subtitle' => (string) get_post_meta( $post->ID, '_xin_b_subtitle', true ),
				'text'     => (string) get_post_meta( $post->ID, '_xin_b_text', true ),
				'cta'      => (string) get_post_meta( $post->ID, '_xin_b_cta', true ),
				'link'     => (string) get_post_meta( $post->ID, '_xin_b_link', true ),
				'badge'    => (string) get_post_meta( $post->ID, '_xin_b_badge', true ),
				'place'    => (string) get_post_meta( $post->ID, '_xin_b_place', true ) ?: 'home',
				'from'     => (string) get_post_meta( $post->ID, '_xin_b_from', true ),
				'until'    => (string) get_post_meta( $post->ID, '_xin_b_until', true ),
				'image'    => get_the_post_thumbnail_url( $post->ID, 'xv-banner' ),
			);
		}

		set_transient( 'xv_banners', $all, HOUR_IN_SECONDS );
	}

	$today = wp_date( 'Y-m-d' );
	$out   = array();

	foreach ( $all as $banner ) {
		if ( $banner['place'] !== $place ) {
			continue;
		}

		if ( $banner['from'] && $banner['from'] > $today ) {
			continue;
		}

		if ( $banner['until'] && $banner['until'] < $today ) {
			continue;
		}

		$out[] = $banner;

		if ( count( $out ) >= $limit ) {
			break;
		}
	}

	return $out;
}

/**
 * Один баннер.
 */
function xv_the_banner( $banner ) {
	$tag  = $banner['link'] ? 'a' : 'div';
	$link = $banner['link'] ? ' href="' . esc_url( $banner['link'] ) . '"' : '';
	?>
	<<?php echo esc_html( $tag ); ?> class="xv-promo"<?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( $banner['image'] ) : ?>
			<span class="xv-promo-image" style="background-image:url('<?php echo esc_url( $banner['image'] ); ?>')"></span>
		<?php endif; ?>

		<span class="xv-promo-body">
			<?php if ( $banner['badge'] ) : ?>
				<span class="xv-promo-badge"><?php echo esc_html( $banner['badge'] ); ?></span>
			<?php endif; ?>

			<?php if ( $banner['subtitle'] ) : ?>
				<span class="xv-promo-sub"><?php echo esc_html( $banner['subtitle'] ); ?></span>
			<?php endif; ?>

			<span class="xv-promo-title"><?php echo esc_html( $banner['title'] ); ?></span>

			<?php if ( $banner['text'] ) : ?>
				<span class="xv-promo-text"><?php echo esc_html( $banner['text'] ); ?></span>
			<?php endif; ?>

			<?php if ( $banner['cta'] ) : ?>
				<span class="xv-promo-cta"><?php echo esc_html( $banner['cta'] ); ?> <span aria-hidden="true">&rarr;</span></span>
			<?php endif; ?>
		</span>
	</<?php echo esc_html( $tag ); ?>>
	<?php
}

/**
 * Блок баннеров для места. Пусто — ничего не выводим: пустая рамка на
 * витрине выглядит поломкой.
 */
function xv_banner_block( $place = 'home', $limit = 2 ) {
	$banners = xv_banners( $place, $limit );

	if ( ! $banners ) {
		return;
	}
	?>
	<div class="xv-promos">
		<?php foreach ( $banners as $banner ) : ?>
			<?php xv_the_banner( $banner ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}
