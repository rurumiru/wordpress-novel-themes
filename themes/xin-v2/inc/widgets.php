<?php
/**
 * Виджеты и боковая колонка.
 *
 * Витрина, каталог и читалка обходятся без сайдбара — они самодостаточны.
 * Сайдбар нужен блогу и обычным страницам: там владелец площадки хочет
 * повесить свои ссылки, и отбирать у него эту возможность незачем.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_register_sidebars() {
	register_sidebar( array(
		'name'          => __( 'Колонка блога', 'xin-v2' ),
		'id'            => 'xv-blog',
		'description'   => __( 'Показывается рядом с записями блога и обычными страницами.', 'xin-v2' ),
		'before_widget' => '<section id="%1$s" class="xv-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="xv-widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Подвал', 'xin-v2' ),
		'id'            => 'xv-footer',
		'description'   => __( 'Дополнительная колонка в подвале сайта.', 'xin-v2' ),
		'before_widget' => '<section id="%1$s" class="xv-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'xv_register_sidebars' );

/**
 * Свои виджеты: то, чего в ядре нет и что на площадке для чтения нужно
 * всем — свежие главы и подборка тайтлов.
 */
class XV_Widget_Updates extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'xv_updates',
			__( 'XIN-V2: свежие главы', 'xin-v2' ),
			array( 'description' => __( 'Последние вышедшие главы.', 'xin-v2' ) )
		);
	}

	public function widget( $args, $instance ) {
		$limit    = isset( $instance['limit'] ) ? max( 1, (int) $instance['limit'] ) : 5;
		$chapters = xv_get_latest_chapters( $limit );

		if ( ! $chapters ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Свежие главы', 'xin-v2' );

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<ul class="xv-widget-list">';

		foreach ( $chapters as $chapter ) {
			$novel = xv_chapter_novel( $chapter->ID );

			printf(
				'<li><a href="%1$s"><span class="xv-widget-title-line">%2$s</span><span class="xv-widget-note">%3$s</span></a></li>',
				esc_url( get_permalink( $chapter ) ),
				esc_html( get_the_title( $chapter ) ),
				esc_html( $novel ? get_the_title( $novel ) : xv_ago( get_post_timestamp( $chapter ) ) )
			);
		}

		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Свежие главы', 'xin-v2' );
		$limit = isset( $instance['limit'] ) ? (int) $instance['limit'] : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок', 'xin-v2' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Сколько показывать', 'xin-v2' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( isset( $new['title'] ) ? $new['title'] : '' ),
			'limit' => max( 1, min( 20, (int) ( isset( $new['limit'] ) ? $new['limit'] : 5 ) ) ),
		);
	}
}

class XV_Widget_Novels extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'xv_novels',
			__( 'XIN-V2: подборка тайтлов', 'xin-v2' ),
			array( 'description' => __( 'Популярное, свежее или высоко оценённое.', 'xin-v2' ) )
		);
	}

	public function widget( $args, $instance ) {
		$sort   = isset( $instance['sort'] ) ? $instance['sort'] : 'popular';
		$limit  = isset( $instance['limit'] ) ? max( 1, (int) $instance['limit'] ) : 5;
		$novels = xv_get_novels( $sort, $limit );

		if ( ! $novels ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Читают сейчас', 'xin-v2' );

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<ul class="xv-widget-list xv-widget-covers">';

		foreach ( $novels as $novel ) {
			$cover = xv_cover_url( $novel->ID );

			printf(
				'<li><a href="%1$s">%2$s<span class="xv-widget-title-line">%3$s</span></a></li>',
				esc_url( get_permalink( $novel ) ),
				$cover ? '<img src="' . esc_url( $cover ) . '" alt="" loading="lazy" width="34" height="51">' : '',
				esc_html( get_the_title( $novel ) )
			);
		}

		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Читают сейчас', 'xin-v2' );
		$sort  = isset( $instance['sort'] ) ? $instance['sort'] : 'popular';
		$limit = isset( $instance['limit'] ) ? (int) $instance['limit'] : 5;

		$sorts = array(
			'popular' => __( 'По просмотрам', 'xin-v2' ),
			'rating'  => __( 'По оценке', 'xin-v2' ),
			'new'     => __( 'Новые', 'xin-v2' ),
			'updated' => __( 'Недавно обновлённые', 'xin-v2' ),
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок', 'xin-v2' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sort' ) ); ?>"><?php esc_html_e( 'Порядок', 'xin-v2' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sort' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sort' ) ); ?>">
				<?php foreach ( $sorts as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $sort, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Сколько показывать', 'xin-v2' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( isset( $new['title'] ) ? $new['title'] : '' ),
			'sort'  => sanitize_key( isset( $new['sort'] ) ? $new['sort'] : 'popular' ),
			'limit' => max( 1, min( 20, (int) ( isset( $new['limit'] ) ? $new['limit'] : 5 ) ) ),
		);
	}
}

function xv_register_widgets() {
	register_widget( 'XV_Widget_Updates' );
	register_widget( 'XV_Widget_Novels' );
}
add_action( 'widgets_init', 'xv_register_widgets' );
