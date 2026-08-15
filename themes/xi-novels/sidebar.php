<?php

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<aside class="xin-sidebar">
	<?php dynamic_sidebar( 'sidebar-blog' ); ?>
</aside>
