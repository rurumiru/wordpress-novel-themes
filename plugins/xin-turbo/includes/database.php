<?php
/**
 * Уборка базы.
 *
 * База сайта, который живёт третий год, наполовину состоит из того, чего
 * никто не откроет: истёкшие переходные значения, ревизии удалённых записей,
 * мета от давно снесённых плагинов. Всё это лежит в тех же таблицах, по
 * которым идут запросы каталога.
 *
 * Уборка идёт ночью и порциями: разово удалить полмиллиона строк на
 * обычном хостинге означает положить сайт на минуту.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Что можно убрать и сколько этого.
 */
function xt_db_report() {
	global $wpdb;

	$keep = max( 1, (int) xt_opt( 'keep_trash', 14 ) );

	return array(
		'transients' => (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			  WHERE option_name LIKE '\_transient\_timeout\_%'
			    AND option_value < UNIX_TIMESTAMP()"
		),
		'revisions'  => (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		),
		'trash'      => (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				  WHERE post_status = 'trash' AND post_modified_gmt < %s",
				gmdate( 'Y-m-d H:i:s', time() - $keep * DAY_IN_SECONDS )
			)
		),
		'spam'       => (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		),
		'orphans'    => (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			  LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			  WHERE p.ID IS NULL"
		),
	);
}

/**
 * Одна ночная уборка. Порции намеренно небольшие: успеет — уберёт остаток
 * следующей ночью, не успеет — никто этого не заметит.
 */
function xt_db_clean( $batch = 2000 ) {
	global $wpdb;

	if ( ! xt_opt( 'clean_db' ) ) {
		return array();
	}

	$done = array();
	$keep = max( 1, (int) xt_opt( 'keep_trash', 14 ) );

	// Истёкшие переходные значения. Их WordPress убирает сам, но только те,
	// на которые наткнулся: остальные лежат годами.
	$names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options}
			  WHERE option_name LIKE '\_transient\_timeout\_%'
			    AND option_value < UNIX_TIMESTAMP()
			  LIMIT %d",
			$batch
		)
	);

	foreach ( (array) $names as $name ) {
		delete_option( $name );
		delete_option( str_replace( '_transient_timeout_', '_transient_', $name ) );
	}

	$done['transients'] = count( (array) $names );

	// Ревизии сверх положенного. Через штатное удаление, а не прямым
	// запросом: у ревизии есть своя мета и свои вложения.
	$old = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' ORDER BY ID ASC LIMIT %d",
			$batch
		)
	);

	$kept = max( 1, (int) xt_opt( 'revisions', 10 ) );
	$seen = array();
	$gone = 0;

	foreach ( (array) $old as $id ) {
		$parent = (int) wp_get_post_parent_id( $id );

		if ( ! $parent ) {
			wp_delete_post_revision( $id );
			$gone++;
			continue;
		}

		$seen[ $parent ] = isset( $seen[ $parent ] ) ? $seen[ $parent ] + 1 : 1;

		if ( $seen[ $parent ] > $kept ) {
			wp_delete_post_revision( $id );
			$gone++;
		}
	}

	$done['revisions'] = $gone;

	// Давно выброшенное.
	$trash = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			  WHERE post_status = 'trash' AND post_modified_gmt < %s
			  LIMIT %d",
			gmdate( 'Y-m-d H:i:s', time() - $keep * DAY_IN_SECONDS ),
			200
		)
	);

	foreach ( (array) $trash as $id ) {
		wp_delete_post( $id, true );
	}

	$done['trash'] = count( (array) $trash );

	// Спам.
	$spam = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam' LIMIT %d",
			$batch
		)
	);

	foreach ( (array) $spam as $id ) {
		wp_delete_comment( $id, true );
	}

	$done['spam'] = count( (array) $spam );

	// Мета без записи.
	$done['orphans'] = (int) $wpdb->query(
		$wpdb->prepare(
			"DELETE pm FROM {$wpdb->postmeta} pm
			  LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			  WHERE p.ID IS NULL
			  LIMIT %d",
			$batch
		)
	);

	update_option( 'xt_last_clean', array( 'at' => time(), 'done' => $done ), false );

	return $done;
}
add_action( 'xt_nightly', 'xt_db_clean' );
