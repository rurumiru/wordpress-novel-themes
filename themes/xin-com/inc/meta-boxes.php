<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_add_meta_boxes() {
	add_meta_box( 'xin-novel', __( 'Карточка тайтла', 'xin-com' ), 'xin_novel_box', 'novel', 'normal', 'high' );
	add_meta_box( 'xin-novel-art', __( 'Фон-арт (широкий)', 'xin-com' ), 'xin_novel_art_box', 'novel', 'side', 'low' );
	add_meta_box( 'xin-chapter', __( 'Параметры главы', 'xin-com' ), 'xin_chapter_box', 'chapter', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'xin_add_meta_boxes' );

function xin_novel_box( $post ) {
	wp_nonce_field( 'xin_save_meta', 'xin_meta_nonce' );

	$author   = get_post_meta( $post->ID, '_xin_author_name', true );
	$original = get_post_meta( $post->ID, '_xin_original_title', true );
	$transl   = get_post_meta( $post->ID, '_xin_translator', true );
	$year     = get_post_meta( $post->ID, '_xin_year', true );
	$source   = get_post_meta( $post->ID, '_xin_source', true );
	$rating   = get_post_meta( $post->ID, '_xin_rating', true );
	$rcount   = (int) get_post_meta( $post->ID, '_xin_rating_count', true );
	$views    = (int) get_post_meta( $post->ID, '_xin_views', true );
	$format   = xin_novel_format( $post->ID );
	$dir      = xin_comic_direction( $post->ID );
	$adult    = (bool) get_post_meta( $post->ID, '_xin_adult', true );
	$featured = (bool) get_post_meta( $post->ID, '_xin_featured', true );
	?>
	<style>
		.xin-fields { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
		.xin-fields label { display: block; font-weight: 600; margin-bottom: 4px; }
		.xin-fields input[type="text"], .xin-fields input[type="number"], .xin-fields input[type="url"] { width: 100%; }
		.xin-fields .xin-full { grid-column: 1 / -1; }
		.xin-fields .description { margin-top: 3px; }
	</style>
	<div class="xin-fields">
		<?php if ( xin_comics_enabled() ) : ?>
		<p>
			<label for="xin_format"><?php esc_html_e( 'Формат', 'xin-com' ); ?></label>
			<select id="xin_format" name="xin_format">
				<?php foreach ( xin_formats() as $xin_key => $xin_format ) : ?>
					<option value="<?php echo esc_attr( $xin_key ); ?>" <?php selected( $format, $xin_key ); ?>><?php echo esc_html( $xin_format['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'Комикс уходит в раздел /comics/ и открывается читалкой страниц, а не текста.', 'xin-com' ); ?></span>
		</p>
		<p>
			<label for="xin_direction"><?php esc_html_e( 'Как читается', 'xin-com' ); ?></label>
			<select id="xin_direction" name="xin_direction">
				<option value="strip" <?php selected( $dir, 'strip' ); ?>><?php esc_html_e( 'Лентой вниз (вебтун)', 'xin-com' ); ?></option>
				<option value="ltr" <?php selected( $dir, 'ltr' ); ?>><?php esc_html_e( 'Постранично, слева направо', 'xin-com' ); ?></option>
				<option value="rtl" <?php selected( $dir, 'rtl' ); ?>><?php esc_html_e( 'Постранично, справа налево (манга)', 'xin-com' ); ?></option>
			</select>
			<span class="description"><?php esc_html_e( 'Учитывается только у комиксов.', 'xin-com' ); ?></span>
		</p>
		<?php endif; ?>
		<p>
			<label for="xin_author_name"><?php esc_html_e( 'Автор оригинала', 'xin-com' ); ?></label>
			<input type="text" id="xin_author_name" name="xin_author_name" value="<?php echo esc_attr( $author ); ?>" placeholder="<?php esc_attr_e( 'Имя автора', 'xin-com' ); ?>">
		</p>
		<p>
			<label for="xin_original_title"><?php esc_html_e( 'Оригинальное название', 'xin-com' ); ?></label>
			<input type="text" id="xin_original_title" name="xin_original_title" value="<?php echo esc_attr( $original ); ?>">
		</p>
		<p>
			<label for="xin_translator"><?php esc_html_e( 'Перевод', 'xin-com' ); ?></label>
			<input type="text" id="xin_translator" name="xin_translator" value="<?php echo esc_attr( $transl ); ?>">
		</p>
		<p class="xin-full">
			<label for="xin_team"><?php esc_html_e( 'Соавторы и переводчики', 'xin-com' ); ?></label>
			<input type="text" id="xin_team" name="xin_team" value="<?php echo esc_attr( xin_team_names( $post->ID ) ); ?>">
			<span class="description"><?php esc_html_e( 'Логины через запятую. Каждый сможет добавлять и править главы проекта.', 'xin-com' ); ?></span>
		</p>
		<p>
			<label for="xin_year"><?php esc_html_e( 'Год выпуска', 'xin-com' ); ?></label>
			<input type="number" id="xin_year" name="xin_year" value="<?php echo esc_attr( $year ); ?>" min="1900" max="2200">
		</p>
		<p class="xin-full">
			<label for="xin_source"><?php esc_html_e( 'Ссылка на первоисточник', 'xin-com' ); ?></label>
			<input type="url" id="xin_source" name="xin_source" value="<?php echo esc_attr( $source ); ?>" placeholder="https://">
		</p>
		<p>
			<label for="xin_rating"><?php esc_html_e( 'Оценка (0–5)', 'xin-com' ); ?></label>
			<input type="number" step="0.1" min="0" max="5" id="xin_rating" name="xin_rating" value="<?php echo esc_attr( $rating ); ?>">
			<span class="description"><?php printf( esc_html__( 'Голосов: %d', 'xin-com' ), (int) $rcount ); ?></span>
		</p>
		<p>
			<label for="xin_views"><?php esc_html_e( 'Просмотры', 'xin-com' ); ?></label>
			<input type="number" id="xin_views" name="xin_views" value="<?php echo esc_attr( $views ); ?>" min="0">
			<span class="description"><?php esc_html_e( 'Счётчик растёт сам; поле — для переноса статистики.', 'xin-com' ); ?></span>
		</p>
		<p class="xin-full">
			<label><input type="checkbox" name="xin_adult" value="1" <?php checked( $adult ); ?>> <?php esc_html_e( 'Материал 18+ (обложка размывается в каталоге)', 'xin-com' ); ?></label>
			<label><input type="checkbox" name="xin_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Выбор редакции (показывать в витрине главной)', 'xin-com' ); ?></label>
		</p>
	</div>
	<?php
}

function xin_novel_art_box( $post ) {
	$id  = (int) get_post_meta( $post->ID, '_xin_background', true );
	$src = $id ? wp_get_attachment_image_src( $id, 'medium' ) : null;
	wp_enqueue_media();
	?>
	<div class="xin-art">
		<div id="xin-art-preview" style="margin-bottom:8px">
			<?php if ( $src ) : ?>
				<img src="<?php echo esc_url( $src[0] ); ?>" style="max-width:100%;height:auto;border-radius:6px" alt="">
			<?php endif; ?>
		</div>
		<input type="hidden" id="xin_background" name="xin_background" value="<?php echo esc_attr( $id ); ?>">
		<button type="button" class="button" id="xin-art-pick"><?php esc_html_e( 'Выбрать арт', 'xin-com' ); ?></button>
		<button type="button" class="button-link" id="xin-art-clear" style="margin-left:8px;color:#b32d2e"><?php esc_html_e( 'Убрать', 'xin-com' ); ?></button>
		<p class="description"><?php esc_html_e( 'Широкая картинка для hero-блока и плиток «самые любимые».', 'xin-com' ); ?></p>
	</div>
	<script>
	jQuery(function ($) {
		var frame;
		$('#xin-art-pick').on('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: '<?php echo esc_js( __( 'Фон-арт тайтла', 'xin-com' ) ); ?>', multiple: false });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$('#xin_background').val(att.id);
				$('#xin-art-preview').html('<img src="' + (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url) + '" style="max-width:100%;height:auto;border-radius:6px">');
			});
			frame.open();
		});
		$('#xin-art-clear').on('click', function (e) {
			e.preventDefault();
			$('#xin_background').val('');
			$('#xin-art-preview').empty();
		});
	});
	</script>
	<?php
}

function xin_chapter_box( $post ) {
	wp_nonce_field( 'xin_save_meta', 'xin_meta_nonce' );

	$novel_id = (int) get_post_meta( $post->ID, '_xin_novel', true );
	$number   = get_post_meta( $post->ID, '_xin_number', true );
	$locked   = (bool) get_post_meta( $post->ID, '_xin_locked', true );

	$novels = get_posts( array(
		'post_type'      => 'novel',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_status'    => array( 'publish', 'draft', 'pending' ),
	) );
	?>
	<p>
		<label for="xin_novel"><strong><?php esc_html_e( 'Новелла', 'xin-com' ); ?></strong></label>
		<select id="xin_novel" name="xin_novel" style="width:100%">
			<option value="">— <?php esc_html_e( 'не выбрана', 'xin-com' ); ?> —</option>
			<?php foreach ( $novels as $novel ) : ?>
				<option value="<?php echo (int) $novel->ID; ?>" <?php selected( $novel_id, $novel->ID ); ?>><?php echo esc_html( $novel->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="xin_number"><strong><?php esc_html_e( 'Номер главы', 'xin-com' ); ?></strong></label>
		<input type="number" step="0.1" id="xin_number" name="xin_number" value="<?php echo esc_attr( $number ); ?>" style="width:100%">
		<span class="description"><?php esc_html_e( 'Дробный номер — экстра/интерлюдия: 12.5', 'xin-com' ); ?></span>
	</p>
	<p>
		<label><input type="checkbox" name="xin_locked" value="1" <?php checked( $locked ); ?>> <?php esc_html_e( 'Ранний доступ: PLUS или покупка', 'xin-com' ); ?></label>
	</p>
	<?php if ( xin_woo_active() ) : ?>
		<p>
			<label for="xin_product"><strong><?php esc_html_e( 'Товар WooCommerce', 'xin-com' ); ?></strong></label>
			<input type="number" id="xin_product" name="xin_product" value="<?php echo esc_attr( (int) get_post_meta( $post->ID, '_xin_product', true ) ); ?>" style="width:100%">
			<span class="description"><?php esc_html_e( 'ID товара для разовой покупки главы. Пусто — только PLUS.', 'xin-com' ); ?></span>
		</p>
	<?php endif; ?>
	<?php
}

function xin_save_meta( $post_id ) {
	if ( ! isset( $_POST['xin_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['xin_meta_nonce'] ) ), 'xin_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['xin_team'] ) ) {
		xin_set_novel_team( $post_id, sanitize_text_field( wp_unslash( $_POST['xin_team'] ) ) );
	}
	if ( isset( $_POST['xin_product'] ) ) {
		$product = absint( $_POST['xin_product'] );
		if ( $product ) {
			update_post_meta( $post_id, '_xin_product', $product );
		} else {
			delete_post_meta( $post_id, '_xin_product' );
		}
	}

	$text_fields = array(
		'xin_author_name'    => '_xin_author_name',
		'xin_original_title' => '_xin_original_title',
		'xin_translator'     => '_xin_translator',
	);
	foreach ( $text_fields as $field => $meta ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	if ( isset( $_POST['xin_source'] ) ) {
		update_post_meta( $post_id, '_xin_source', esc_url_raw( wp_unslash( $_POST['xin_source'] ) ) );
	}
	if ( isset( $_POST['xin_year'] ) ) {
		update_post_meta( $post_id, '_xin_year', absint( $_POST['xin_year'] ) );
	}
	if ( isset( $_POST['xin_views'] ) ) {
		update_post_meta( $post_id, '_xin_views', absint( $_POST['xin_views'] ) );
	}
	if ( isset( $_POST['xin_rating'] ) ) {
		$rating = min( 5, max( 0, (float) $_POST['xin_rating'] ) );
		update_post_meta( $post_id, '_xin_rating', $rating );
		
		if ( $rating > 0 && ! get_post_meta( $post_id, '_xin_rating_count', true ) ) {
			update_post_meta( $post_id, '_xin_rating_count', 1 );
		}
	}
	if ( isset( $_POST['xin_format'] ) ) {
		update_post_meta( $post_id, '_xin_format', xin_format_key( sanitize_key( wp_unslash( $_POST['xin_format'] ) ) ) );
	}
	if ( isset( $_POST['xin_direction'] ) ) {
		$direction = sanitize_key( wp_unslash( $_POST['xin_direction'] ) );
		update_post_meta( $post_id, '_xin_direction', in_array( $direction, array( 'strip', 'ltr', 'rtl' ), true ) ? $direction : 'strip' );
	}
	if ( isset( $_POST['xin_background'] ) ) {
		update_post_meta( $post_id, '_xin_background', absint( $_POST['xin_background'] ) );
	}
	if ( isset( $_POST['xin_novel'] ) ) {
		update_post_meta( $post_id, '_xin_novel', absint( $_POST['xin_novel'] ) );
	}
	if ( isset( $_POST['xin_number'] ) && '' !== $_POST['xin_number'] ) {
		update_post_meta( $post_id, '_xin_number', (float) $_POST['xin_number'] );
	}

$checkboxes = array(
		'xin_adult'    => '_xin_adult',
		'xin_featured' => '_xin_featured',
		'xin_locked'   => '_xin_locked',
	);
	$screen_types = array( 'novel' => array( 'xin_adult', 'xin_featured' ), 'chapter' => array( 'xin_locked' ) );
	$type         = get_post_type( $post_id );
	if ( isset( $screen_types[ $type ] ) ) {
		foreach ( $screen_types[ $type ] as $field ) {
			update_post_meta( $post_id, $checkboxes[ $field ], isset( $_POST[ $field ] ) ? 1 : 0 );
		}
	}

	delete_transient( 'xin_site_stats' );
}
add_action( 'save_post', 'xin_save_meta' );

function xin_novel_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['xin_cover'] = __( 'Обложка', 'xin-com' );
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['xin_chapters'] = __( 'Главы', 'xin-com' );
			$new['xin_views']    = __( 'Просмотры', 'xin-com' );
			$new['xin_rating']   = __( 'Оценка', 'xin-com' );
		}
	}
	return $new;
}
add_filter( 'manage_novel_posts_columns', 'xin_novel_columns' );

function xin_novel_column( $column, $post_id ) {
	switch ( $column ) {
		case 'xin_cover':
			$cover = xin_cover_url( $post_id, 'xin-cover-sm' );
			echo $cover
				? '<img src="' . esc_url( $cover ) . '" style="width:34px;height:51px;object-fit:cover;border-radius:4px" alt="">'
				: '—';
			break;
		case 'xin_chapters':
			echo (int) xin_chapter_count( $post_id );
			break;
		case 'xin_views':
			echo esc_html( number_format_i18n( xin_get_views( $post_id ) ) );
			break;
		case 'xin_rating':
			$r = xin_rating( $post_id );
			echo $r['count'] ? esc_html( number_format( $r['value'], 1 ) . ' (' . $r['count'] . ')' ) : '—';
			break;
	}
}
add_action( 'manage_novel_posts_custom_column', 'xin_novel_column', 10, 2 );

function xin_chapter_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['xin_novel']  = __( 'Новелла', 'xin-com' );
			$new['xin_number'] = __( '№', 'xin-com' );
		}
	}
	return $new;
}
add_filter( 'manage_chapter_posts_columns', 'xin_chapter_columns' );

function xin_chapter_column( $column, $post_id ) {
	if ( 'xin_novel' === $column ) {
		$novel_id = xin_chapter_novel_id( $post_id );
		echo $novel_id
			? '<a href="' . esc_url( get_edit_post_link( $novel_id ) ) . '">' . esc_html( get_the_title( $novel_id ) ) . '</a>'
			: '<em>' . esc_html__( 'не привязана', 'xin-com' ) . '</em>';
	}
	if ( 'xin_number' === $column ) {
		echo esc_html( xin_chapter_label( $post_id ) ?: '—' );
	}
}
add_action( 'manage_chapter_posts_custom_column', 'xin_chapter_column', 10, 2 );

function xin_chapter_filter() {
	global $typenow;
	if ( 'chapter' !== $typenow ) {
		return;
	}
	$current = isset( $_GET['xin_novel_filter'] ) ? absint( $_GET['xin_novel_filter'] ) : 0;
	$novels  = get_posts( array( 'post_type' => 'novel', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	echo '<select name="xin_novel_filter"><option value="">' . esc_html__( 'Все новеллы', 'xin-com' ) . '</option>';
	foreach ( $novels as $novel ) {
		printf( '<option value="%d" %s>%s</option>', (int) $novel->ID, selected( $current, $novel->ID, false ), esc_html( $novel->post_title ) );
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'xin_chapter_filter' );

function xin_chapter_filter_query( $query ) {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
		return;
	}
	if ( 'chapter' !== $query->get( 'post_type' ) || empty( $_GET['xin_novel_filter'] ) ) {
		return;
	}
	$query->set( 'meta_query', array(
		array( 'key' => '_xin_novel', 'value' => absint( $_GET['xin_novel_filter'] ) ),
	) );
}
add_action( 'pre_get_posts', 'xin_chapter_filter_query' );
