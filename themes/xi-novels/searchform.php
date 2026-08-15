<?php

$xin_uid = wp_unique_id( 'xin-search-' );
?>
<form role="search" method="get" class="xin-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $xin_uid ); ?>"><?php esc_html_e( 'Поиск', 'xi-novels' ); ?></label>
	<input
		class="form-control form-control-pill"
		id="<?php echo esc_attr( $xin_uid ); ?>"
		type="search"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Название тайтла, автор, глава…', 'xi-novels' ); ?>"
		autocomplete="off"
	>
	<button type="submit" class="btn btn-primary">
		<?php xin_the_icon( 'search' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Найти', 'xi-novels' ); ?></span>
	</button>
</form>
