<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_register_banner_type() {
	register_post_type( 'xin_banner', array(
		'labels'          => array(
			'name'               => __( 'Баннеры', 'xi-novels' ),
			'singular_name'      => __( 'Баннер', 'xi-novels' ),
			'add_new'            => __( 'Добавить', 'xi-novels' ),
			'add_new_item'       => __( 'Добавить баннер', 'xi-novels' ),
			'edit_item'          => __( 'Редактировать баннер', 'xi-novels' ),
			'all_items'          => __( 'Баннеры', 'xi-novels' ),
			'not_found'          => __( 'Баннеров пока нет', 'xi-novels' ),
			'menu_name'          => __( 'Баннеры', 'xi-novels' ),
			'featured_image'     => __( 'Картинка баннера', 'xi-novels' ),
			'set_featured_image' => __( 'Задать картинку', 'xi-novels' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-format-image',
		'menu_position'   => 7,
		'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'xin_register_banner_type' );

function xin_banner_meta_box() {
	add_meta_box( 'xin-banner', __( 'Содержимое баннера', 'xi-novels' ), 'xin_banner_box', 'xin_banner', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'xin_banner_meta_box' );

function xin_banner_box( $post ) {
	wp_nonce_field( 'xin_save_banner', 'xin_banner_nonce' );
	wp_enqueue_media();

	$subtitle = get_post_meta( $post->ID, '_xin_b_subtitle', true );
	$text     = get_post_meta( $post->ID, '_xin_b_text', true );
	$link     = get_post_meta( $post->ID, '_xin_b_link', true );
	$cta      = get_post_meta( $post->ID, '_xin_b_cta', true );
	$badge    = get_post_meta( $post->ID, '_xin_b_badge', true );
	$mobile   = (int) get_post_meta( $post->ID, '_xin_b_mobile', true );
	$align    = get_post_meta( $post->ID, '_xin_b_align', true );
	$src      = $mobile ? wp_get_attachment_image_src( $mobile, 'medium' ) : null;
	?>
	<style>
		.xin-bfields { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
		.xin-bfields label { display: block; font-weight: 600; margin-bottom: 4px; }
		.xin-bfields input[type="text"], .xin-bfields input[type="url"], .xin-bfields textarea, .xin-bfields select { width: 100%; }
		.xin-bfields .xin-full { grid-column: 1 / -1; }
	</style>
	<div class="xin-bfields">
		<p class="xin-full">
			<label for="xin_b_subtitle"><?php esc_html_e( 'Подзаголовок', 'xi-novels' ); ?></label>
			<input type="text" id="xin_b_subtitle" name="xin_b_subtitle" value="<?php echo esc_attr( $subtitle ); ?>">
		</p>
		<p class="xin-full">
			<label for="xin_b_text"><?php esc_html_e( 'Текст', 'xi-novels' ); ?></label>
			<textarea id="xin_b_text" name="xin_b_text" rows="2"><?php echo esc_textarea( $text ); ?></textarea>
		</p>
		<p>
			<label for="xin_b_link"><?php esc_html_e( 'Ссылка', 'xi-novels' ); ?></label>
			<input type="url" id="xin_b_link" name="xin_b_link" value="<?php echo esc_attr( $link ); ?>" placeholder="https://">
		</p>
		<p>
			<label for="xin_b_cta"><?php esc_html_e( 'Надпись на кнопке', 'xi-novels' ); ?></label>
			<input type="text" id="xin_b_cta" name="xin_b_cta" value="<?php echo esc_attr( $cta ); ?>" placeholder="<?php esc_attr_e( 'Открыть', 'xi-novels' ); ?>">
		</p>
		<p>
			<label for="xin_b_badge"><?php esc_html_e( 'Бейдж', 'xi-novels' ); ?></label>
			<input type="text" id="xin_b_badge" name="xin_b_badge" value="<?php echo esc_attr( $badge ); ?>" placeholder="<?php esc_attr_e( 'Новинка', 'xi-novels' ); ?>">
		</p>
		<p>
			<label for="xin_b_align"><?php esc_html_e( 'Положение текста', 'xi-novels' ); ?></label>
			<select id="xin_b_align" name="xin_b_align">
				<option value="left" <?php selected( $align, 'left' ); ?>><?php esc_html_e( 'Слева', 'xi-novels' ); ?></option>
				<option value="center" <?php selected( $align, 'center' ); ?>><?php esc_html_e( 'По центру', 'xi-novels' ); ?></option>
				<option value="right" <?php selected( $align, 'right' ); ?>><?php esc_html_e( 'Справа', 'xi-novels' ); ?></option>
			</select>
		</p>
		<div class="xin-full">
			<label><?php esc_html_e( 'Картинка для телефона', 'xi-novels' ); ?></label>
			<div id="xin-bmobile-preview" style="margin:6px 0">
				<?php if ( $src ) : ?>
					<img src="<?php echo esc_url( $src[0] ); ?>" style="max-width:220px;height:auto;border-radius:6px" alt="">
				<?php endif; ?>
			</div>
			<input type="hidden" id="xin_b_mobile" name="xin_b_mobile" value="<?php echo (int) $mobile; ?>">
			<button type="button" class="button" id="xin-bmobile-pick"><?php esc_html_e( 'Выбрать', 'xi-novels' ); ?></button>
			<button type="button" class="button-link" id="xin-bmobile-clear" style="margin-left:8px;color:#b32d2e"><?php esc_html_e( 'Убрать', 'xi-novels' ); ?></button>
			<p class="description"><?php esc_html_e( 'Необязательно. Если не задана, на телефоне используется основная картинка.', 'xi-novels' ); ?></p>
		</div>
		<p class="xin-full description">
			<?php esc_html_e( 'Основная картинка — «Изображение записи» справа. Оптимальный размер 1920×720. Порядок показа задаётся полем «Порядок» в блоке «Атрибуты страницы».', 'xi-novels' ); ?>
		</p>
	</div>
	<script>
	jQuery(function ($) {
		var frame;
		$('#xin-bmobile-pick').on('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: '<?php echo esc_js( __( 'Картинка для телефона', 'xi-novels' ) ); ?>', multiple: false });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$('#xin_b_mobile').val(att.id);
				$('#xin-bmobile-preview').html('<img src="' + (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url) + '" style="max-width:220px;height:auto;border-radius:6px">');
			});
			frame.open();
		});
		$('#xin-bmobile-clear').on('click', function (e) {
			e.preventDefault();
			$('#xin_b_mobile').val('');
			$('#xin-bmobile-preview').empty();
		});
	});
	</script>
	<?php
}

function xin_save_banner( $post_id ) {
	if ( ! isset( $_POST['xin_banner_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['xin_banner_nonce'] ) ), 'xin_save_banner' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_xin_b_subtitle' => 'xin_b_subtitle',
		'_xin_b_cta'      => 'xin_b_cta',
		'_xin_b_badge'    => 'xin_b_badge',
	);
	foreach ( $fields as $meta => $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	if ( isset( $_POST['xin_b_text'] ) ) {
		update_post_meta( $post_id, '_xin_b_text', sanitize_textarea_field( wp_unslash( $_POST['xin_b_text'] ) ) );
	}
	if ( isset( $_POST['xin_b_link'] ) ) {
		update_post_meta( $post_id, '_xin_b_link', esc_url_raw( wp_unslash( $_POST['xin_b_link'] ) ) );
	}
	if ( isset( $_POST['xin_b_align'] ) ) {
		update_post_meta( $post_id, '_xin_b_align', sanitize_key( wp_unslash( $_POST['xin_b_align'] ) ) );
	}
	if ( isset( $_POST['xin_b_mobile'] ) ) {
		update_post_meta( $post_id, '_xin_b_mobile', absint( $_POST['xin_b_mobile'] ) );
	}
}
add_action( 'save_post_xin_banner', 'xin_save_banner' );

function xin_banner_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['xin_b_image'] = __( 'Картинка', 'xi-novels' );
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['xin_b_order'] = __( 'Порядок', 'xi-novels' );
		}
	}
	return $new;
}
add_filter( 'manage_xin_banner_posts_columns', 'xin_banner_columns' );

function xin_banner_column( $column, $post_id ) {
	if ( 'xin_b_image' === $column ) {
		$src = get_the_post_thumbnail_url( $post_id, 'medium' );
		echo $src
			? '<img src="' . esc_url( $src ) . '" style="width:120px;height:45px;object-fit:cover;border-radius:4px" alt="">'
			: '—';
	}
	if ( 'xin_b_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_xin_banner_posts_custom_column', 'xin_banner_column', 10, 2 );

function xin_get_banners( $limit = 6 ) {
	$banners = get_posts( array(
		'post_type'      => 'xin_banner',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );

	$out = array();
	foreach ( $banners as $banner ) {
		$image = get_the_post_thumbnail_url( $banner->ID, 'xin-banner' );
		if ( ! $image ) {
			continue;
		}
		$mobile_id = (int) get_post_meta( $banner->ID, '_xin_b_mobile', true );
		$mobile    = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'large' ) : '';

		$out[] = array(
			'id'       => $banner->ID,
			'title'    => $banner->post_title,
			'subtitle' => get_post_meta( $banner->ID, '_xin_b_subtitle', true ),
			'text'     => get_post_meta( $banner->ID, '_xin_b_text', true ),
			'link'     => get_post_meta( $banner->ID, '_xin_b_link', true ),
			'cta'      => get_post_meta( $banner->ID, '_xin_b_cta', true ),
			'badge'    => get_post_meta( $banner->ID, '_xin_b_badge', true ),
			'align'    => get_post_meta( $banner->ID, '_xin_b_align', true ),
			'image'    => $image,
			'mobile'   => $mobile,
		);
	}

	return $out;
}

function xin_banners_from_novels( $ids ) {
	$out = array();
	foreach ( $ids as $id ) {
		$image = xin_background_url( $id );
		if ( ! $image ) {
			continue;
		}
		$status = xin_novel_status( $id );
		$terms  = get_the_terms( $id, 'genre' );

		$out[] = array(
			'id'       => $id,
			'title'    => get_the_title( $id ),
			'subtitle' => ( ! is_wp_error( $terms ) && $terms ) ? $terms[0]->name : '',
			'text'     => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 26 ),
			'link'     => get_permalink( $id ),
			'cta'      => __( 'Открыть тайтл', 'xi-novels' ),
			'badge'    => $status ? $status->name : '',
			'align'    => 'left',
			'image'    => $image,
			'mobile'   => '',
		);
	}
	return $out;
}
