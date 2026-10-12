/**
 * XIN-V2 — подсказки глоссария.
 *
 * Подсказка появляется по наведению и по фокусу с клавиатуры, закрывается
 * по уходу, Escape и прокрутке. Одна на весь документ: плодить по узлу на
 * каждый термин незачем — их в главе бывает несколько десятков.
 */
(function () {
	'use strict';

	var doc = document;
	var note = null;

	function hide() {
		if (note) {
			note.remove();
			note = null;
		}
	}

	function show(term) {
		hide();

		var text = term.getAttribute('data-xv-term');
		if (!text) { return; }

		note = doc.createElement('div');
		note.className = 'xv-term-note';
		note.setAttribute('role', 'tooltip');
		note.textContent = text;
		doc.body.appendChild(note);

		var box = term.getBoundingClientRect();
		var size = note.getBoundingClientRect();

		// Держим подсказку в пределах окна: у термина в конце строки она
		// иначе уезжает за правый край.
		var left = Math.min(
			Math.max(8, box.left + box.width / 2 - size.width / 2),
			window.innerWidth - size.width - 8
		);

		var top = box.top - size.height - 8;
		if (top < 8) { top = box.bottom + 8; }

		note.style.left = (left + window.scrollX) + 'px';
		note.style.top = (top + window.scrollY) + 'px';
	}

	doc.addEventListener('mouseover', function (event) {
		var term = event.target.closest('.xv-term');
		if (term) { show(term); }
	});

	doc.addEventListener('mouseout', function (event) {
		if (event.target.closest('.xv-term')) { hide(); }
	});

	doc.addEventListener('focusin', function (event) {
		var term = event.target.closest('.xv-term');
		if (term) { show(term); }
	});

	doc.addEventListener('focusout', hide);
	doc.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') { hide(); }
	});

	window.addEventListener('scroll', hide, { passive: true });
})();
