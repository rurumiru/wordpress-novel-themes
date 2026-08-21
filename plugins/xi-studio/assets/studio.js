/**
 * Студия темы: контролы слева, живой сайт справа.
 *
 * CSS собирает сервер — тот же генератор, что рисует сайт, поэтому
 * предпросмотр не может разойтись с тем, что увидит читатель.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-xis]');
	if (!root || !window.XIS) return;

	var CFG = window.XIS;
	var L = CFG.i18n || {};
	var fields = CFG.fields || {};

	var values = JSON.parse(JSON.stringify(CFG.values || {}));
	var saved = JSON.parse(JSON.stringify(values));
	var dirty = false;

	var frame = root.querySelector('[data-xis-preview]');
	var stage = root.querySelector('[data-xis-frame]');
	var state = root.querySelector('[data-xis-state]');
	var toast = root.querySelector('[data-xis-toast]');
	var fileField = root.querySelector('[data-xis-file]');

	var scheme = 'light';
	var css = '';

	function $(sel) { return root.querySelector(sel); }
	function $$(sel) { return Array.prototype.slice.call(root.querySelectorAll(sel)); }

	/* ------------------------------------------------------------- Состояние */

	function same(a, b) {
		return JSON.stringify(a) === JSON.stringify(b);
	}

	function mark() {
		dirty = !same(values, saved);
		if (state) {
			state.textContent = dirty ? L.dirty : L.clean;
			state.classList.toggle('is-dirty', dirty);
		}
	}

	function say(message, bad) {
		if (!toast) return;
		toast.textContent = message;
		toast.classList.toggle('is-bad', !!bad);
		toast.hidden = false;
		clearTimeout(say.timer);
		say.timer = setTimeout(function () { toast.hidden = true; }, 2600);
	}

	/* ------------------------------------------------------------ Контролы */

	function unit(name) {
		var field = fields[name] || {};
		return field.unit || '';
	}

	function show(name) {
		var out = $('[data-xis-out="' + name + '"]');
		var field = fields[name] || {};

		if (out) {
			if ('choice' === field.type) {
				out.textContent = field.choices && field.choices[values[name]] ? field.choices[values[name]] : values[name];
			} else if ('color' === field.type) {
				out.textContent = values[name] || L.default;
			} else {
				out.textContent = values[name] + unit(name);
			}
		}

		var input = $('[data-xis-input="' + name + '"]');
		if (input && 'color' !== field.type) input.value = values[name];

		if ('color' === field.type) {
			var hex = $('[data-xis-hex="' + name + '"]');
			if (hex) hex.value = values[name];
			var picker = $('[data-xis-input="' + name + '"]');
			if (picker && values[name]) picker.value = values[name];
		}

		$$('[data-xis-choice="' + name + '"]').forEach(function (btn) {
			btn.classList.toggle('is-active', btn.getAttribute('data-value') === String(values[name]));
		});
	}

	function showAll() {
		Object.keys(fields).forEach(show);
	}

	function set(name, value, quiet) {
		values[name] = value;
		show(name);
		mark();
		if (!quiet) preview();
	}

	$$('[data-xis-input]').forEach(function (input) {
		var name = input.getAttribute('data-xis-input');

		input.addEventListener('input', function () {
			set(name, 'color' === (fields[name] || {}).type ? input.value : parseInt(input.value, 10));
		});
	});

	$$('[data-xis-hex]').forEach(function (input) {
		var name = input.getAttribute('data-xis-hex');

		input.addEventListener('change', function () {
			var hex = input.value.trim();
			if (hex && !/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)) {
				input.value = values[name];
				return;
			}
			set(name, hex);
		});
	});

	$$('[data-xis-clear]').forEach(function (btn) {
		btn.addEventListener('click', function () { set(btn.getAttribute('data-xis-clear'), ''); });
	});

	$$('[data-xis-choice]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			set(btn.getAttribute('data-xis-choice'), btn.getAttribute('data-value'));
		});
	});

	/* ---------------------------------------------------------------- Вкладки */

	$$('[data-xis-tab]').forEach(function (tab) {
		tab.addEventListener('click', function () {
			var key = tab.getAttribute('data-xis-tab');

			$$('[data-xis-tab]').forEach(function (item) { item.classList.toggle('is-active', item === tab); });
			$$('[data-xis-pane]').forEach(function (pane) {
				pane.classList.toggle('is-active', pane.getAttribute('data-xis-pane') === key);
			});
		});
	});

	/* ------------------------------------------------------------ Предпросмотр */

	function inject() {
		if (!frame) return;

		var doc = null;
		try { doc = frame.contentDocument; } catch (e) { doc = null; }
		if (!doc || !doc.head) return;

		var style = doc.getElementById('xis-live');
		if (!style) {
			style = doc.createElement('style');
			style.id = 'xis-live';
			doc.head.appendChild(style);
		}

		style.textContent = css;
		doc.documentElement.setAttribute('data-theme', scheme);
	}

	var pending = null;

	function preview() {
		clearTimeout(pending);
		pending = setTimeout(function () {
			fetch(CFG.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CFG.nonce },
				body: JSON.stringify({ values: values, save: false })
			})
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (!data || 'string' !== typeof data.css) return;
					css = data.css;
					inject();
				})
				.catch(function () { });
		}, 140);
	}

	if (frame) frame.addEventListener('load', inject);

	$$('[data-xis-page]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			$$('[data-xis-page]').forEach(function (item) { item.classList.toggle('is-active', item === btn); });
			if (frame) frame.src = btn.getAttribute('data-xis-page');
		});
	});

	$$('[data-xis-width]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var width = parseInt(btn.getAttribute('data-xis-width'), 10);

			$$('[data-xis-width]').forEach(function (item) { item.classList.toggle('is-active', item === btn); });

			if (stage) {
				stage.style.maxWidth = width ? width + 'px' : '';
				stage.classList.toggle('is-device', !!width);
			}
		});
	});

	$$('[data-xis-scheme]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			scheme = btn.getAttribute('data-xis-scheme');
			$$('[data-xis-scheme]').forEach(function (item) { item.classList.toggle('is-active', item === btn); });
			inject();
		});
	});

	var reloadBtn = $('[data-xis-reload]');
	if (reloadBtn && frame) {
		reloadBtn.addEventListener('click', function () {
			var url = frame.getAttribute('src');
			frame.src = url;
		});
	}

	/* -------------------------------------------------------------- Действия */

	var saveBtn = $('[data-xis-save]');
	if (saveBtn) {
		saveBtn.addEventListener('click', function () {
			saveBtn.disabled = true;

			fetch(CFG.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CFG.nonce },
				body: JSON.stringify({ values: values, save: true })
			})
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (!data || !data.saved) throw new Error('not saved');
					values = data.values;
					saved = JSON.parse(JSON.stringify(values));
					css = data.css;
					showAll();
					inject();
					mark();
					say(L.saved);
				})
				.catch(function () { say(L.failed, true); })
				.then(function () { saveBtn.disabled = false; });
		});
	}

	var resetBtn = $('[data-xis-reset]');
	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			if (!window.confirm(L.reset)) return;

			Object.keys(fields).forEach(function (name) { values[name] = fields[name].default; });
			showAll();
			mark();
			preview();
		});
	}

	$$('[data-xis-preset]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var preset = (CFG.presets || {})[btn.getAttribute('data-xis-preset')];
			if (!preset) return;

			Object.keys(fields).forEach(function (name) {
				if (name in preset.values) values[name] = preset.values[name];
			});

			$$('[data-xis-preset]').forEach(function (item) { item.classList.toggle('is-active', item === btn); });
			showAll();
			mark();
			preview();
			say(L.preset);
		});
	});

	var exportBtn = $('[data-xis-export]');
	if (exportBtn) {
		exportBtn.addEventListener('click', function () {
			var blob = new Blob([JSON.stringify({ xiStudio: 1, values: values }, null, '\t')], { type: 'application/json' });
			var url = URL.createObjectURL(blob);
			var link = document.createElement('a');

			link.href = url;
			link.download = 'xi-studio-' + window.location.hostname + '.json';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);

			setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
		});
	}

	var importBtn = $('[data-xis-import]');
	if (importBtn && fileField) {
		importBtn.addEventListener('click', function () { fileField.click(); });

		fileField.addEventListener('change', function () {
			var file = fileField.files && fileField.files[0];
			if (!file) return;

			var reader = new FileReader();

			reader.onload = function () {
				var data = null;
				try { data = JSON.parse(String(reader.result)); } catch (e) { data = null; }

				fileField.value = '';

				var incoming = data && (data.values || data);
				if (!incoming || 'object' !== typeof incoming) {
					say(L.badFile, true);
					return;
				}

				Object.keys(fields).forEach(function (name) {
					if (name in incoming) values[name] = incoming[name];
				});

				showAll();
				mark();
				preview();
				say(L.imported);
			};

			reader.readAsText(file);
		});
	}

	window.addEventListener('beforeunload', function (e) {
		if (!dirty) return;
		e.preventDefault();
		e.returnValue = L.leave;
		return L.leave;
	});

	showAll();
	mark();
	preview();
})();
