<?php
/**
 * Массовая замена текста в существующих главах.
 *
 * Отличается от импорта ровно одним: ничего, кроме содержимого, не меняется.
 * Дата публикации, статус, ярлык, порядок, цена и замок остаются как были —
 * иначе исправление опечатки во всём проекте выбросило бы все главы наверх
 * ленты обновлений и сбросило платный доступ.
 *
 * Файл ищет свою главу по номеру в имени, а если номера нет — по названию.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ищет главу проекта под этот файл.
 *
 * @param int    $novel_id Проект.
 * @param string $name     Имя файла из архива.
 * @param float  $number   Номер, если разобрался.
 * @param string $title    Название, если разобралось.
 * @return int Идентификатор главы или 0.
 */
function xni_match_chapter( $novel_id, $name, $number, $title ) {
	if ( null !== $number ) {
		$byNumber = xni_find_chapter( $novel_id, (float) $number );
		if ( $byNumber ) {
			return $byNumber;
		}
	}

	$needle = $title ? $title : pathinfo( $name, PATHINFO_FILENAME );
	$needle = trim( wp_strip_all_tags( $needle ) );

	if ( '' === $needle ) {
		return 0;
	}

	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) absint( $novel_id ) ),
		),
	) );

	// Точное совпадение названия важнее похожего, поэтому два прохода.
	foreach ( $ids as $id ) {
		if ( 0 === strcasecmp( trim( get_the_title( $id ) ), $needle ) ) {
			return (int) $id;
		}
	}

	$folded = xni_fold( $needle );
	foreach ( $ids as $id ) {
		if ( xni_fold( get_the_title( $id ) ) === $folded ) {
			return (int) $id;
		}
	}

	return 0;
}

/**
 * Приводит название к виду, в котором «Глава 12 — Тишина» и
 * «Глава 12. Тишина» — одно и то же.
 */
function xni_fold( $text ) {
	$text = mb_strtolower( wp_strip_all_tags( (string) $text ), 'UTF-8' );
	$text = preg_replace( '/[\s\p{P}\p{S}]+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Одна глава на замене текста.
 *
 * @param array $job  Задание.
 * @param array $file Файл из архива.
 */
function xni_step_fix( $job, $file ) {
	$args     = $job['args'];
	$novel_id = (int) $args['novel_id'];
	$parsed   = xni_parse_file( $file['path'], $file['name'], $args['encoding'] );

	if ( is_wp_error( $parsed ) ) {
		$job['failed']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'failed', 'note' => $parsed->get_error_message() );
		return $job;
	}

	$chapter_id = xni_match_chapter( $novel_id, $file['name'], $parsed['number'], $parsed['title'] );

	if ( ! $chapter_id ) {
		$job['skipped']++;
		$job['log'][] = array(
			'name'  => $file['name'],
			'state' => 'skipped',
			'note'  => __( 'Не нашлось главы с таким номером или названием.', 'xi-novel-import' ),
		);
		return $job;
	}

	$content = wp_kses_post( $parsed['content'] );

	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		$job['skipped']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'skipped', 'note' => __( 'Файл пустой — не затираю главу.', 'xi-novel-import' ) );
		return $job;
	}

	if ( $content === get_post_field( 'post_content', $chapter_id ) ) {
		$job['skipped']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'skipped', 'note' => __( 'Текст не изменился.', 'xi-novel-import' ) );
		return $job;
	}

	/*
	 * Только содержимое. Дату не передаём — иначе WordPress подставит текущую и
	 * весь проект всплывёт в ленте обновлений; статус, ярлык и порядок тоже
	 * остаются нетронутыми, потому что их просто нет в этом массиве.
	 */
	$saved = wp_update_post( array(
		'ID'           => $chapter_id,
		'post_content' => $content,
	), true );

	if ( is_wp_error( $saved ) ) {
		$job['failed']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'failed', 'note' => $saved->get_error_message() );
		return $job;
	}

	$job['updated']++;
	$job['log'][] = array(
		'name'  => $file['name'],
		'state' => 'updated',
		'note'  => sprintf( __( 'Заменён текст: %s', 'xi-novel-import' ), get_the_title( $chapter_id ) ),
	);

	return $job;
}
