<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XIN_Widget_Novels extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'xin_novels',
			__( 'XI: подборка новелл', 'xi-novels' ),
			array( 'description' => __( 'Топ по просмотрам, оценке, новинки или обновления.', 'xi-novels' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Популярное', 'xi-novels' );
		$type  = ! empty( $instance['type'] ) ? $instance['type'] : 'popular';
		$count = ! empty( $instance['count'] ) ? (int) $instance['count'] : 5;

		$ids = xin_get_novels( $type, $count );
		if ( ! $ids ) {
			return;
		}

		echo $args['before_widget']; 
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; 
		echo '<div class="xin-widget-novels">';
		foreach ( $ids as $id ) {
			$cover  = xin_cover_url( $id, 'xin-cover-sm' );
			$rating = xin_rating( $id );
			?>
			<a class="xin-widget-novel" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
				<span class="xin-widget-novel__cover">
					<?php if ( $cover ) : ?>
						<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy">
					<?php endif; ?>
				</span>
				<span>
					<h4><?php echo esc_html( get_the_title( $id ) ); ?></h4>
					<small>
						<span><?php echo esc_html( xin_num( xin_get_views( $id ) ) ); ?> <?php esc_html_e( 'просм.', 'xi-novels' ); ?></span>
						<?php if ( $rating['count'] ) : ?>
							<span class="xin-gold">★ <?php echo esc_html( number_format( $rating['value'], 1, ',', '' ) ); ?></span>
						<?php endif; ?>
					</small>
				</span>
			</a>
			<?php
		}
		echo '</div>';
		echo $args['after_widget']; 
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Популярное', 'xi-novels' );
		$type  = isset( $instance['type'] ) ? $instance['type'] : 'popular';
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок', 'xi-novels' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Что показывать', 'xi-novels' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
				<?php
				$types = array(
					'popular' => __( 'По просмотрам', 'xi-novels' ),
					'rating'  => __( 'По оценке', 'xi-novels' ),
					'latest'  => __( 'Новинки', 'xi-novels' ),
					'updated' => __( 'Недавно обновлены', 'xi-novels' ),
				);
				foreach ( $types as $key => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $type, $key, false ), esc_html( $label ) );
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Сколько', 'xi-novels' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="20" value="<?php echo (int) $count; ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'type'  => sanitize_key( $new_instance['type'] ),
			'count' => min( 20, max( 1, (int) $new_instance['count'] ) ),
		);
	}
}

class XIN_Widget_Chapters extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'xin_chapters',
			__( 'XI: последние главы', 'xi-novels' ),
			array( 'description' => __( 'Свежие публикации со всего сайта.', 'xi-novels' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Последние главы', 'xi-novels' );
		$count = ! empty( $instance['count'] ) ? (int) $instance['count'] : 5;

		$ids = xin_get_latest_chapters( $count );
		if ( ! $ids ) {
			return;
		}

		echo $args['before_widget']; 
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; 
		echo '<div class="xin-widget-novels">';
		foreach ( $ids as $id ) {
			$novel_id = xin_chapter_novel_id( $id );
			$cover    = $novel_id ? xin_cover_url( $novel_id, 'xin-cover-sm' ) : '';
			$label    = xin_chapter_label( $id );
			?>
			<a class="xin-widget-novel" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
				<span class="xin-widget-novel__cover">
					<?php if ( $cover ) : ?>
						<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy">
					<?php endif; ?>
				</span>
				<span>
					<h4><?php echo $label ? esc_html( sprintf( __( 'Гл. %s — ', 'xi-novels' ), $label ) ) : ''; ?><?php echo esc_html( get_the_title( $id ) ); ?></h4>
					<small><?php echo esc_html( $novel_id ? get_the_title( $novel_id ) : '' ); ?></small>
				</span>
			</a>
			<?php
		}
		echo '</div>';
		echo $args['after_widget']; 
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Последние главы', 'xi-novels' );
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок', 'xi-novels' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Сколько', 'xi-novels' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="20" value="<?php echo (int) $count; ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'count' => min( 20, max( 1, (int) $new_instance['count'] ) ),
		);
	}
}

function xin_register_widgets() {
	register_widget( 'XIN_Widget_Novels' );
	register_widget( 'XIN_Widget_Chapters' );
}
add_action( 'widgets_init', 'xin_register_widgets' );
