/*
 * Кнопки блока хранилища: проверка связи и догрузка существующих страниц.
 *
 * Отдельный файл, потому что import.js целиком про одно задание с прогрессом,
 * а тут два коротких запроса без состояния.
 */
( function () {
	'use strict';

	var buttons = document.querySelectorAll( '[data-xni-s3]' );
	var result  = document.querySelector( '.xni-s3-result' );

	if ( ! buttons.length || 'undefined' === typeof XNI ) {
		return;
	}

	function say( text, ok ) {
		if ( ! result ) {
			return;
		}

		result.textContent = text;
		result.style.color = ok ? '#146c43' : '#b32d2e';
	}

	function run( action, button ) {
		var body = new FormData();
		body.append( 'action', action );
		body.append( '_wpnonce', XNI.nonce );

		buttons.forEach( function ( b ) {
			b.disabled = true;
		} );

		say( button.dataset.busy || '…', true );

		fetch( XNI.ajax, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( json ) {
				var message = json && json.data && json.data.message ? json.data.message : XNI.failed;

				/* Первые несколько причин отказа полезнее одного общего итога:
				   по ним видно, это права, регион или размер файла. */
				if ( json && json.data && json.data.errors && json.data.errors.length ) {
					message += ' — ' + json.data.errors.join( '; ' );
				}

				say( message, !! ( json && json.success ) );
			} )
			.catch( function () {
				say( XNI.failed, false );
			} )
			.finally( function () {
				buttons.forEach( function ( b ) {
					b.disabled = false;
				} );
			} );
	}

	buttons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			run( 'probe' === button.dataset.xniS3 ? 'xni_s3_probe' : 'xni_s3_sync', button );
		} );
	} );
}() );
