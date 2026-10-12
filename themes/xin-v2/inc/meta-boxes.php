<?php
/**
 * Поля тайтла и главы в редакторе WordPress.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_add_meta_boxes() {
	add_meta_box( 'xv-novel', __( 'Данные тайтла', 'xin-v2' ), 'xv_novel_box', 'novel', 'side', 'high' );
	add_meta_box( 'xv-chapter', __( 'Данные главы', 'xin-v2' ), 'xv_chapter_box', 'chapter', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'xv_add_meta_boxes' );

function xv_novel_box( $post ) {
	wp_nonce_field( 'xv_save_meta', 'xv_meta_nonce' );

	$fields = array(
		'_xin_author_name' => array( __( 'Автор оригинала', 'xin-v2' ), 'text' ),
		'_xin_translator'  => array( __( 'Перевод', 'xin-v2' ), 'text' ),
		'_xin_year'        => array( __( 'Год выхода', 'xin-v2' ), 'text' ),
		'_xin_country'     => array( __( 'Страна', 'xin-v2' ), 'text' ),
	);
	?>
	<div class="xv-meta">
		<?php foreach ( $fields as $key => $field ) : ?>
			<p>
				<label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $field[0] ); ?></strong></label>
				<input class="widefat" type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>">
			</p>
		<?php endforeach; ?>

		<p>
			<label for="_xin_background"><strong><?php esc_html_e( 'Баннер (ID вложения)', 'xin-v2' ); ?></strong></label>
			<input class="widefat" type="number" id="_xin_background" name="_xin_background" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_background', true ) ); ?>">
			<span class="description"><?php esc_html_e( 'Пусто — фоном станет размытая обложка.', 'xin-v2' ); ?></span>
		</p>

		<p>
			<label><input type="checkbox" name="_xin_featured" value="1" <?php checked( get_post_meta( $post->ID, '_xin_featured', true ), '1' ); ?>> <?php esc_html_e( 'Книга недели', 'xin-v2' ); ?></label>
		</p>
		<p>
			<label><input type="checkbox" name="_xin_adult" value="1" <?php checked( get_post_meta( $post->ID, '_xin_adult', true ), '1' ); ?>> <?php esc_html_e( '18+', 'xin-v2' ); ?></label>
		</p>

		<hr>
		<p class="description">
			<?php
			printf(
				/* translators: 1: просмотры, 2: оценка, 3: число глав. */
				esc_html__( 'Просмотров: %1$s · оценка: %2$s · глав: %3$s', 'xin-v2' ),
				esc_html( number_format_i18n( xv_get_views( $post->ID ) ) ),
				esc_html( number_format_i18n( (float) get_post_meta( $post->ID, '_xin_rating', true ), 1 ) ),
				esc_html( number_format_i18n( (int) get_post_meta( $post->ID, '_xin_chapter_count', true ) ) )
			);
			?>
		</p>
	</div>
	<?php
}

function xv_chapter_box( $post ) {
	wp_nonce_field( 'xv_save_meta', 'xv_meta_nonce' );

	$novel  = (int) get_post_meta( $post->ID, '_xin_novel', true );
	$locked = get_post_meta( $post->ID, '_xin_locked', true );
	$unlock = (int) get_post_meta( $post->ID, '_xin_unlock_at', true );
	?>
	<div class="xv-meta">
		<p>
			<label for="_xin_novel"><strong><?php esc_html_e( 'Тайтл', 'xin-v2' ); ?></strong></label>
			<select class="widefat" id="_xin_novel" name="_xin_novel">
				<option value="0"><?php esc_html_e( '— не выбран —', 'xin-v2' ); ?></option>
				<?php
				$novels = get_posts( array(
					'post_type'      => 'novel',
					'post_status'    => array( 'publish', 'draft', 'pending' ),
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				) );

				foreach ( $novels as $item ) :
					?>
					<option value="<?php echo (int) $item->ID; ?>" <?php selected( $novel, $item->ID ); ?>><?php echo esc_html( $item->post_title ); ?></option>
					<?php
				endforeach;
				?>
			</select>
		</p>

		<p>
			<label for="_xin_number"><strong><?php esc_html_e( 'Номер главы', 'xin-v2' ); ?></strong></label>
			<input class="widefat" type="text" id="_xin_number" name="_xin_number" value="<?php echo esc_attr( get_post_meta( $post->ID, '_xin_number', true ) ); ?>">
			<span class="description"><?php esc_html_e( 'Дробный номер (12.5) ставит главу между двенадцатой и тринадцатой.', 'xin-v2' ); ?></span>
		</p>

		<p>
			<label><input type="checkbox" name="_xin_locked" value="1" <?php checked( $locked, '1' ); ?>> <?php esc_html_e( 'Ранний доступ', 'xin-v2' ); ?></label>
		</p>

		<p>
			<label for="_xin_unlock_at"><strong><?php esc_html_e( 'Открыть для всех', 'xin-v2' ); ?></strong></label>
			<input class="widefat" type="datetime-local" id="_xin_unlock_at" name="_xin_unlock_at" value="<?php echo esc_attr( $unlock ? wp_date( 'Y-m-d\TH:i', $unlock ) : '' ); ?>">
		</p>

		<p>
			<label for="_xin_price"><strong><?php esc_html_e( 'Цена', 'xin-v2' ); ?></strong></label>
			<input class="widefat" type="number" min="0" id="_xin_price" name="_xin_price" value="<?php echo esc_attr( xv_chapter_price( $post->ID ) ); ?>">
		</p>
	</div>
	<?php
}

function xv_save_meta( $post_id ) {
	if ( ! isset( $_POST['xv_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xv_meta_nonce'] ) ), 'xv_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text = array( '_xin_author_name', '_xin_translator', '_xin_year', '_xin_country', '_xin_number' );

	foreach ( $text as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	$numbers = array( '_xin_novel', '_xin_price', '_xin_background' );

	foreach ( $numbers as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, (int) $_POST[ $key ] );
		}
	}

	// Чекбоксы приходят только во включённом виде — выключенный сохраняем сами.
	$flags = 'chapter' === get_post_type( $post_id ) ? array( '_xin_locked' ) : array( '_xin_featured', '_xin_adult' );

	foreach ( $flags as $key ) {
		if ( ! empty( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, '1' );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}

	if ( isset( $_POST['_xin_unlock_at'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['_xin_unlock_at'] ) );

		if ( $raw ) {
			update_post_meta( $post_id, '_xin_unlock_at', (int) get_gmt_from_date( $raw, 'U' ) );
		} else {
			delete_post_meta( $post_id, '_xin_unlock_at' );
		}
	}
}
add_action( 'save_post', 'xv_save_meta' );

/**
 * Колонки списка глав: без тайтла и номера список глав нечитаем.
 */
function xv_chapter_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['xv_novel']  = __( 'Тайтл', 'xin-v2' );
			$new['xv_number'] = __( '№', 'xin-v2' );
			$new['xv_lock']   = __( 'Доступ', 'xin-v2' );
		}
	}

	return $new;
}
add_filter( 'manage_chapter_posts_columns', 'xv_chapter_columns' );

function xv_chapter_column( $column, $post_id ) {
	if ( 'xv_novel' === $column ) {
		$novel = xv_chapter_novel( $post_id );
		echo $novel ? '<a href="' . esc_url( (string) get_edit_post_link( $novel->ID ) ) . '">' . esc_html( $novel->post_title ) . '</a>' : '—';
	}

	if ( 'xv_number' === $column ) {
		echo esc_html( xv_chapter_number( $post_id ) ?: '—' );
	}

	if ( 'xv_lock' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_xin_locked', true ) ? __( 'Ранний доступ', 'xin-v2' ) : __( 'Открыта', 'xin-v2' ) );
	}
}
add_action( 'manage_chapter_posts_custom_column', 'xv_chapter_column', 10, 2 );

function xv_novel_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['xv_chapters'] = __( 'Главы', 'xin-v2' );
			$new['xv_views']    = __( 'Просмотры', 'xin-v2' );
			$new['xv_rating']   = __( 'Оценка', 'xin-v2' );
		}
	}

	return $new;
}
add_filter( 'manage_novel_posts_columns', 'xv_novel_columns' );

function xv_novel_column( $column, $post_id ) {
	if ( 'xv_chapters' === $column ) {
		echo esc_html( number_format_i18n( (int) get_post_meta( $post_id, '_xin_chapter_count', true ) ) );
	}

	if ( 'xv_views' === $column ) {
		echo esc_html( number_format_i18n( xv_get_views( $post_id ) ) );
	}

	if ( 'xv_rating' === $column ) {
		$rating = xv_rating( $post_id );
		echo $rating['value'] > 0 ? esc_html( number_format_i18n( $rating['value'], 1 ) ) : '—';
	}
}
add_action( 'manage_novel_posts_custom_column', 'xv_novel_column', 10, 2 );
