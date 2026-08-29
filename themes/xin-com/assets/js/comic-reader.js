/*
 * Читалка комикса.
 *
 * Отдельный скрипт, а не режим внутри reader.js: там всё считается от текста —
 * кегль, высота строки, процент прокрутки. Здесь единица измерения — кадр, и
 * прогресс должен переживать смену устройства так же, как переживает его
 * текстовая читалка, поэтому позиция пишется по тем же ключам истории.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-xin-comic]' );

	if ( ! root ) {
		return;
	}

	var pagesBox = root.querySelector( '[data-xin-cr-pages]' );
	var pages    = pagesBox ? Array.prototype.slice.call( pagesBox.querySelectorAll( '.xin-cr__page' ) ) : [];
	var counter  = root.querySelector( '[data-xin-cr-counter]' );
	var panel    = root.querySelector( '[data-xin-cr-panel]' );
	var bar      = root.querySelector( '[data-xin-cr-toggle="settings"]' );
	var progress = document.querySelector( '[data-xin-progress]' );
	var total    = pages.length;
	var current  = 1;

	var SETTINGS_KEY = 'xin-comic';
	var POSITION_KEY = 'xin-comic-at-' + root.getAttribute( 'data-chapter-id' );
	var SOURCE_KEY   = 'xin-comic-source';

	var sourcesNode = root.querySelector( '[data-xin-cr-sources]' );
	var sources     = {};

	try {
		sources = sourcesNode ? JSON.parse( sourcesNode.textContent ) : {};
	} catch ( e ) {
		sources = {};
	}

	var sourceIds = Object.keys( sources );
	var source    = sourceIds.length ? sourceIds[ 0 ] : '';

	var settings = {
		width: 'normal',
		gap: 'none',
		mode: root.getAttribute( 'data-dir' ) || 'strip'
	};

	/* ------------------------------------------------------------------ */

	function read( key, fallback ) {
		try {
			var raw = window.localStorage.getItem( key );
			return null === raw ? fallback : JSON.parse( raw );
		} catch ( e ) {
			return fallback;
		}
	}

	function write( key, value ) {
		try {
			window.localStorage.setItem( key, JSON.stringify( value ) );
		} catch ( e ) {
			/* Приватное окно или запрет на хранилище — читать это не мешает. */
		}
	}

	/*
	 * Смена сервера переставляет адреса на месте. Ленивость с уже показанных
	 * кадров снимается: браузер не станет догружать невидимую картинку, а после
	 * переключения читатель смотрит именно на неё.
	 */
	function applySource( id ) {
		var urls = sources[ id ];

		if ( ! urls ) {
			return;
		}

		source = id;
		write( SOURCE_KEY, id );

		for ( var i = 0; i < pages.length; i++ ) {
			var img = pages[ i ].querySelector( 'img' );

			if ( ! img || ! urls[ i ] || img.getAttribute( 'src' ) === urls[ i ] ) {
				continue;
			}

			img.setAttribute( 'src', urls[ i ] );
		}

		mark( '[data-xin-cr-source]', 'xinCrSource', id );
	}

	function apply() {
		root.setAttribute( 'data-width', settings.width );
		root.setAttribute( 'data-gap', settings.gap );
		root.setAttribute( 'data-mode', settings.mode );

		mark( '[data-xin-cr-width]', 'xinCrWidth', settings.width );
		mark( '[data-xin-cr-gap]', 'xinCrGap', settings.gap );
		mark( '[data-xin-cr-mode]', 'xinCrMode', settings.mode );
		mark( '[data-xin-cr-source]', 'xinCrSource', source );

		if ( paged() ) {
			show( current );
		}
	}

	function mark( selector, dataset, value ) {
		var nodes = root.querySelectorAll( selector );

		for ( var i = 0; i < nodes.length; i++ ) {
			nodes[ i ].classList.toggle( 'is-active', nodes[ i ].dataset[ dataset ] === value );
		}
	}

	function paged() {
		return 'ltr' === settings.mode || 'rtl' === settings.mode;
	}

	/* ------------------------------------------------------------------ */

	function setCurrent( n ) {
		if ( ! total ) {
			return;
		}

		current = Math.min( Math.max( 1, n ), total );

		if ( counter ) {
			counter.textContent = current + ' / ' + total;
		}

		if ( progress ) {
			progress.style.width = ( current / total * 100 ) + '%';
		}

		write( POSITION_KEY, current );
	}

	function show( n ) {
		setCurrent( n );

		for ( var i = 0; i < pages.length; i++ ) {
			pages[ i ].classList.toggle( 'is-current', i === current - 1 );
		}

		/*
		 * Соседние кадры перестают быть ленивыми: в постраничном режиме следующий
		 * нужен через одно нажатие, и ждать загрузку в этот момент уже поздно.
		 */
		[ current, current + 1 ].forEach( function ( index ) {
			var page = pages[ index - 1 ];
			var img  = page ? page.querySelector( 'img' ) : null;

			if ( img ) {
				img.loading = 'eager';
			}
		} );
	}

	function step( delta ) {
		if ( ! paged() ) {
			return;
		}

		show( current + delta );
	}

	/* Лентой прогресс считается по тому кадру, который сейчас в середине экрана. */
	function trackStrip() {
		if ( paged() || ! total ) {
			return;
		}

		var middle = window.innerHeight / 2;
		var seen   = 1;

		for ( var i = 0; i < pages.length; i++ ) {
			if ( pages[ i ].getBoundingClientRect().top <= middle ) {
				seen = i + 1;
			}
		}

		setCurrent( seen );
	}

	/* ------------------------------------------------------------------ */

	function bind() {
		root.addEventListener( 'click', function ( event ) {
			var choice = event.target.closest( '[data-xin-cr-width], [data-xin-cr-gap], [data-xin-cr-mode]' );

			if ( choice ) {
				if ( choice.dataset.xinCrWidth ) {
					settings.width = choice.dataset.xinCrWidth;
				}
				if ( choice.dataset.xinCrGap ) {
					settings.gap = choice.dataset.xinCrGap;
				}
				if ( choice.dataset.xinCrMode ) {
					settings.mode = choice.dataset.xinCrMode;
				}

				write( SETTINGS_KEY, settings );
				apply();
				return;
			}

			var server = event.target.closest( '[data-xin-cr-source]' );

			if ( server ) {
				applySource( server.dataset.xinCrSource );
				return;
			}

			if ( event.target.closest( '[data-xin-cr-toggle="settings"]' ) ) {
				if ( panel ) {
					panel.hidden = ! panel.hidden;
				}
				return;
			}

			/* Клик по краю кадра листает — но только там, где есть что листать. */
			if ( paged() ) {
				var page = event.target.closest( '.xin-cr__page' );

				if ( page ) {
					var box  = page.getBoundingClientRect();
					var left = ( event.clientX - box.left ) < box.width / 2;

					step( left === ( 'rtl' === settings.mode ) ? 1 : -1 );
				}
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.target.closest( 'input, textarea, select, [contenteditable]' ) ) {
				return;
			}

			if ( 'ArrowRight' === event.key ) {
				step( 'rtl' === settings.mode ? -1 : 1 );
			} else if ( 'ArrowLeft' === event.key ) {
				step( 'rtl' === settings.mode ? 1 : -1 );
			} else if ( 'Escape' === event.key && panel && ! panel.hidden ) {
				panel.hidden = true;
			}
		} );

		var ticking = false;

		window.addEventListener( 'scroll', function () {
			if ( ticking ) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame( function () {
				trackStrip();
				ticking = false;
			} );
		}, { passive: true } );
	}

	/* ------------------------------------------------------------------ */

	function start() {
		var saved = read( SETTINGS_KEY, null );

		if ( saved && 'object' === typeof saved ) {
			settings.width = saved.width || settings.width;
			settings.gap = saved.gap || settings.gap;
			/* Направление задаёт тайтл: у манги оно не вкусовое. Читатель может
			   его переопределить, но только для себя и только осознанно. */
			settings.mode = saved.mode || settings.mode;
		}

		/*
		 * Выбранный сервер запоминается на весь сайт, а не на главу: читатель
		 * переключается потому, что один из них медленный у него в сети, и
		 * заново выбирать на каждой главе бессмысленно. Сохранённого сервера
		 * может не быть у этой главы — тогда остаётся первый.
		 */
		var saved_source = read( SOURCE_KEY, '' );

		if ( saved_source && sources[ saved_source ] ) {
			source = saved_source;
		}

		apply();
		applySource( source );

		var at = read( POSITION_KEY, 1 );

		if ( paged() ) {
			show( 'number' === typeof at ? at : 1 );
		} else {
			setCurrent( 1 );
			trackStrip();
		}

		bind();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
