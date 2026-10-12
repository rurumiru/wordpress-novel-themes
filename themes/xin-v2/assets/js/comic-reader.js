/**
 * XIN-V2 — читалка комикса.
 *
 * Настроек ровно две: направление чтения и ширина полосы. Обе живут в
 * localStorage — читатель выбирает раз и дальше просто листает.
 */
(function () {
	'use strict';

	/* Подписи приходят из темы переведёнными: зашитая в скрипт строка
	   остаётся русской на любом языке сайта. */
	function say(key) {
		var dict = (window.xvData && window.xvData.i18n) || {};
		return dict[key] || '';
	}


	var shell = document.querySelector('[data-xv-comic]');
	if (!shell) { return; }

	var DIRS = ['vertical', 'rtl', 'ltr'];
	var FITS = ['', 'wide', 'full'];

	var LABELS = {
		vertical: say('dirDown'),
		rtl: say('dirRtl'),
		ltr: say('dirLtr')
	};

	function read(key, fallback) {
		try {
			var value = localStorage.getItem(key);
			return value === null ? fallback : value;
		} catch (e) {
			return fallback;
		}
	}

	function write(key, value) {
		try {
			if (value === '') { localStorage.removeItem(key); } else { localStorage.setItem(key, value); }
		} catch (e) {}
	}

	// Выбор читателя важнее того, что задал автор: мангу читают и сверху вниз.
	var saved = read('xv-comic-dir', '');
	if (DIRS.indexOf(saved) !== -1) { shell.setAttribute('data-xv-dir', saved); }

	var fit = read('xv-comic-fit', '');
	if (fit) { shell.setAttribute('data-xv-fit', fit); }

	function label() {
		var out = shell.querySelector('[data-xv-comic-dir-label]');
		if (out) { out.textContent = LABELS[shell.getAttribute('data-xv-dir')] || ''; }
	}

	label();

	shell.addEventListener('click', function (event) {
		if (event.target.closest('[data-xv-comic-dir]')) {
			var now = shell.getAttribute('data-xv-dir');
			var next = DIRS[(DIRS.indexOf(now) + 1) % DIRS.length];

			shell.setAttribute('data-xv-dir', next);
			write('xv-comic-dir', next);
			label();
			return;
		}

		if (event.target.closest('[data-xv-comic-fit]')) {
			var cur = shell.getAttribute('data-xv-fit') || '';
			var step = FITS[(FITS.indexOf(cur) + 1) % FITS.length];

			if (step) { shell.setAttribute('data-xv-fit', step); } else { shell.removeAttribute('data-xv-fit'); }

			write('xv-comic-fit', step);
		}
	});

	// Стрелки листают главы — то же, что и в текстовой читалке.
	document.addEventListener('keydown', function (event) {
		var tag = (event.target.tagName || '').toLowerCase();
		if (tag === 'input' || tag === 'textarea' || event.target.isContentEditable) { return; }
		if (event.metaKey || event.ctrlKey || event.altKey) { return; }

		var rtl = shell.getAttribute('data-xv-dir') === 'rtl';

		if (event.key === 'ArrowLeft') {
			var left = document.querySelector(rtl ? '[data-xv-next]' : '[data-xv-prev]');
			if (left) { left.click(); }
		}

		if (event.key === 'ArrowRight') {
			var right = document.querySelector(rtl ? '[data-xv-prev]' : '[data-xv-next]');
			if (right) { right.click(); }
		}
	});
})();
