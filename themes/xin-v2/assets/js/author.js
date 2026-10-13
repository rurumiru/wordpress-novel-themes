/**
 * XIN-V2 — кабинет автора: счётчик слов и поля раннего доступа.
 */
(function () {
	'use strict';

	var doc = document;
	var workSearch = doc.querySelector('[data-xv-work-search]');
	if (workSearch) workSearch.addEventListener('input', function () {
		var term = workSearch.value.toLocaleLowerCase().trim();
		doc.querySelectorAll('.xv-work').forEach(function (work) {
			work.hidden = !work.querySelector('.xv-work-title').textContent.toLocaleLowerCase().includes(term);
		});
	});
	var editor = doc.querySelector('#xv-content');
	var preview = doc.querySelector('[data-xv-editor-preview]');
	if (editor && preview) doc.querySelectorAll('[data-xv-editor-tab]').forEach(function (button) {
		button.addEventListener('click', function () {
			var showing = button.dataset.xvEditorTab === 'preview';
			preview.replaceChildren();
			if (showing) editor.value.split(/\n\s*\n/).filter(function (p) { return p.trim(); }).forEach(function (text) {
				var p = doc.createElement('p'); p.textContent = text; preview.appendChild(p);
			});
			editor.hidden = showing; preview.hidden = !showing;
			doc.querySelectorAll('[data-xv-editor-tab]').forEach(function (tab) { tab.setAttribute('aria-pressed', String(tab === button)); });
		});
	});

	/* ── Объём главы ────────────────────────────────────────── */

	var field = doc.querySelector('[data-xv-count]');
	var out = doc.querySelector('[data-xv-count-out]');

	if (field && out) {
		var update = function () {
			var text = field.value.trim();
			var words = text ? text.split(/\s+/).length : 0;
			var minutes = Math.max(1, Math.round(words / 180));

			out.textContent = words
				? ((window.xvData && window.xvData.i18n.wordCount) || '%1$s words · ≈ %2$s min read.').replace('%1$s',words.toLocaleString(document.documentElement.lang || 'en')).replace('%2$s',minutes)
				: '';
		};

		field.addEventListener('input', update);
		update();
	}

	/* ── Ранний доступ ──────────────────────────────────────── */

	/* Дата выхода нужна только состоянию «по расписанию». */
	var status = doc.querySelector('[data-xv-status]');
	var when = doc.querySelector('[data-xv-when]');

	if (status && when) {
		status.addEventListener('change', function () {
			when.hidden = status.value !== 'future';
		});
	}

	var lock = doc.querySelector('[data-xv-lock]');
	var lockFields = doc.querySelector('[data-xv-lock-fields]');

	if (lock && lockFields) {
		lock.addEventListener('change', function () {
			lockFields.hidden = !lock.checked;
		});
	}

	/* ── Защита от потери текста ────────────────────────────── */

	var form = doc.querySelector('.xv-form');

	if (form) {
		var dirty = false;

		form.addEventListener('input', function () { dirty = true; });
		form.addEventListener('submit', function () { dirty = false; });

		window.addEventListener('beforeunload', function (event) {
			if (!dirty) { return; }

			event.preventDefault();
			event.returnValue = '';
		});
	}
})();
