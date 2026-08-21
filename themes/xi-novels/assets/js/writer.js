/**
 * Редактор главы: contenteditable, никаких зависимостей.
 *
 * Панель, чистая вставка из Word, черновик в браузере, поиск и замена,
 * типографика, глоссарий проекта, режим фокуса и просмотр HTML.
 */
(function () {
	'use strict';

	function init(root) {
		var CFG = window.XIN_WRITER || {};
		var L = CFG.i18n || {};

		var body = root.querySelector('[data-xin-w-body]');
		var source = root.querySelector('[data-xin-w-source]');
		var input = root.querySelector('[data-xin-w-input]');
		var stats = root.querySelector('[data-xin-w-stats]');
		var note = root.querySelector('[data-xin-w-note]');
		var form = root.closest('form');

		if (!body || !input) return null;

		function t(key, value) {
			var line = L[key] || '';
			return typeof value === 'undefined' ? line : line.replace('%s', value);
		}

		function $(sel) { return root.querySelector(sel); }
		function $$(sel) { return Array.prototype.slice.call(root.querySelectorAll(sel)); }

		/* ------------------------------------------------------------- Разметка */

		var ALLOWED = {
			P: [], BR: [], STRONG: [], EM: [], S: [], U: [],
			H2: [], H3: [], BLOCKQUOTE: [], HR: [],
			UL: [], OL: [], LI: [],
			A: ['href', 'title'], IMG: ['src', 'alt', 'width', 'height'],
			FIGURE: [], FIGCAPTION: []
		};

		var SWAP = { B: 'STRONG', I: 'EM', STRIKE: 'S', DEL: 'S', DIV: 'P', SECTION: 'P', ARTICLE: 'P', H1: 'H2', H4: 'H3', H5: 'H3', H6: 'H3' };

		function rename(el, name) {
			var swap = document.createElement(name);
			while (el.firstChild) swap.appendChild(el.firstChild);
			el.parentNode.replaceChild(swap, el);
			return swap;
		}

		function unwrap(el) {
			var parent = el.parentNode;
			while (el.firstChild) parent.insertBefore(el.firstChild, el);
			parent.removeChild(el);
		}

		/**
		 * Приводит кусок HTML к разрешённому набору тегов.
		 *
		 * @param {string} html Сырая разметка.
		 * @return {string} Чистая разметка.
		 */
		function clean(html) {
			var box = document.createElement('div');
			box.innerHTML = String(html || '')
				.replace(/<!--[\s\S]*?-->/g, '')
				.replace(/<(script|style|meta|link|o:p)[\s\S]*?>[\s\S]*?<\/\1>/gi, '')
				.replace(/<\/?(script|style|meta|link|o:p)[^>]*>/gi, '');

			var nodes = Array.prototype.slice.call(box.querySelectorAll('*'));

			nodes.forEach(function (el) {
				if (!el.parentNode) return;

				var name = el.nodeName;

				if (SWAP[name]) {
					el = rename(el, SWAP[name]);
					name = el.nodeName;
				}

				if (!ALLOWED[name]) {
					unwrap(el);
					return;
				}

				Array.prototype.slice.call(el.attributes).forEach(function (attr) {
					if (ALLOWED[name].indexOf(attr.name.toLowerCase()) === -1) {
						el.removeAttribute(attr.name);
					}
				});

				if ('A' === name) {
					var href = el.getAttribute('href') || '';
					if (/^\s*javascript:/i.test(href)) el.removeAttribute('href');
				}
				if ('IMG' === name) {
					var src = el.getAttribute('src') || '';
					if (/^\s*javascript:/i.test(src)) el.parentNode.removeChild(el);
				}
			});

			Array.prototype.slice.call(box.querySelectorAll('p, li, h2, h3, blockquote')).forEach(function (el) {
				if (!el.textContent.trim() && !el.querySelector('img, br')) {
					el.parentNode.removeChild(el);
				}
			});

			return box.innerHTML;
		}

		/** Оборачивает голый текст в абзацы. */
		function textToHtml(text) {
			return String(text || '')
				.replace(/\r/g, '')
				.split(/\n{2,}/)
				.map(function (block) {
					var line = block.replace(/\n/g, '<br>').trim();
					return line ? '<p>' + line.replace(/[&<>]/g, function (ch) {
						return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[ch];
					}).replace(/&lt;br&gt;/g, '<br>') + '</p>' : '';
				})
				.filter(Boolean)
				.join('');
		}

		function html() {
			return clean(body.innerHTML);
		}

		function setHtml(value) {
			body.innerHTML = clean(value) || '<p><br></p>';
			sync();
		}

		/* ---------------------------------------------------------- Статистика */

		function sync() {
			input.value = sourceOpen() ? clean(source.value) : html();
			count();
		}

		function count() {
			var text = (sourceOpen() ? source.value.replace(/<[^>]+>/g, ' ') : body.innerText || body.textContent || '')
				.replace(/\s+/g, ' ')
				.trim();

			var words = text ? text.split(' ').length : 0;
			var chars = text.length;
			var minutes = Math.max(1, Math.round(words / 180));

			if (stats) {
				stats.textContent = t('stats')
					.replace('%1$s', words.toLocaleString())
					.replace('%2$s', chars.toLocaleString())
					.replace('%3$s', minutes);
			}
		}

		/* ------------------------------------------------------------ Черновик */

		var draftKey = root.getAttribute('data-key') || '';
		var saveTimer = null;

		function stamp() {
			var now = new Date();
			return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		}

		function saveDraft() {
			if (!draftKey) return;
			try {
				localStorage.setItem(draftKey, JSON.stringify({ html: input.value, at: Date.now() }));
				if (note) note.textContent = t('saved') + ' · ' + stamp();
			} catch (e) { }
		}

		function readDraft() {
			if (!draftKey) return null;
			try {
				var raw = JSON.parse(localStorage.getItem(draftKey) || 'null');
				if (!raw) return null;
				if (typeof raw === 'string') return { html: raw, at: 0 };
				return raw.html ? raw : null;
			} catch (e) {
				return null;
			}
		}

		function dropDraft() {
			try { localStorage.removeItem(draftKey); } catch (e) { }
		}

		function touched() {
			sync();
			clearTimeout(saveTimer);
			saveTimer = setTimeout(saveDraft, 800);
		}

		/* -------------------------------------------------------------- Панель */

		function focusBody() {
			if (!sourceOpen()) body.focus();
		}

		function command(name, value) {
			if (!document.execCommand) return false;
			try { return document.execCommand(name, false, value); } catch (e) { return false; }
		}

		function exec(name, value) {
			focusBody();
			command(name, value);
			touched();
			state();
		}

		function blockFormat(tag) {
			exec('formatBlock', '<' + tag + '>');
		}

		function state() {
			if (sourceOpen()) return;

			$$('[data-xin-w-cmd]').forEach(function (btn) {
				var name = btn.getAttribute('data-xin-w-cmd');
				var on = false;
				try { on = document.queryCommandState && document.queryCommandState(name); } catch (e) { }
				btn.classList.toggle('is-active', !!on);
			});

			var select = $('[data-xin-w-block]');
			if (select) {
				var node = document.getSelection && document.getSelection().anchorNode;
				var el = node && (1 === node.nodeType ? node : node.parentNode);
				var tag = 'p';

				while (el && el !== body) {
					var name = el.nodeName.toLowerCase();
					if (['p', 'h2', 'h3', 'blockquote'].indexOf(name) > -1) { tag = name; break; }
					el = el.parentNode;
				}

				select.value = tag;
			}
		}

		$$('[data-xin-w-cmd]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				exec(btn.getAttribute('data-xin-w-cmd'), btn.getAttribute('data-xin-w-value') || null);
			});
		});

		$$('[data-xin-w-block-btn]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				blockFormat(btn.getAttribute('data-xin-w-block-btn'));
			});
		});

		var blockSelect = $('[data-xin-w-block]');
		if (blockSelect) {
			blockSelect.addEventListener('change', function () { blockFormat(blockSelect.value); });
		}

		var breakBtn = $('[data-xin-w-break]');
		if (breakBtn) {
			breakBtn.addEventListener('click', function (e) {
				e.preventDefault();
				exec('insertHTML', '<hr><p><br></p>');
			});
		}

		/* --------------------------------------------------------------- Ссылка */

		var linkBar = $('[data-xin-w-linkbar]');
		var linkField = $('[data-xin-w-linkfield]');
		var savedRange = null;

		function keepRange() {
			var sel = document.getSelection();
			if (sel && sel.rangeCount && body.contains(sel.anchorNode)) savedRange = sel.getRangeAt(0);
		}

		function restoreRange() {
			if (!savedRange) return;
			var sel = document.getSelection();
			sel.removeAllRanges();
			sel.addRange(savedRange);
		}

		function currentLink() {
			var node = document.getSelection && document.getSelection().anchorNode;
			var el = node && (1 === node.nodeType ? node : node.parentNode);
			while (el && el !== body) {
				if ('A' === el.nodeName) return el;
				el = el.parentNode;
			}
			return null;
		}

		var linkBtn = $('[data-xin-w-link]');
		if (linkBtn && linkBar && linkField) {
			linkBtn.addEventListener('click', function (e) {
				e.preventDefault();
				keepRange();
				var link = currentLink();
				linkField.value = link ? link.getAttribute('href') || '' : '';
				linkBar.hidden = !linkBar.hidden;
				if (!linkBar.hidden) linkField.focus();
			});

			function applyLink() {
				var url = linkField.value.trim();
				linkBar.hidden = true;
				restoreRange();
				if (!url) return;
				if (!/^([a-z][a-z0-9+.-]*:|\/|#)/i.test(url)) url = 'https://' + url;
				exec('createLink', url);
			}

			var linkOk = $('[data-xin-w-linkok]');
			if (linkOk) linkOk.addEventListener('click', function (e) { e.preventDefault(); applyLink(); });

			linkField.addEventListener('keydown', function (e) {
				if ('Enter' === e.key) { e.preventDefault(); applyLink(); }
				if ('Escape' === e.key) { linkBar.hidden = true; restoreRange(); }
			});

			var unlinkBtn = $('[data-xin-w-unlink]');
			if (unlinkBtn) {
				unlinkBtn.addEventListener('click', function (e) {
					e.preventDefault();
					linkBar.hidden = true;
					restoreRange();
					exec('unlink');
				});
			}
		}

		/* -------------------------------------------------------------- Картинка */

		var mediaBtn = $('[data-xin-w-media]');
		if (mediaBtn) {
			var frame = null;

			mediaBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (!window.wp || !window.wp.media) return;
				keepRange();

				if (!frame) {
					frame = window.wp.media({ title: t('pickImage'), library: { type: 'image' }, multiple: false });
					frame.on('select', function () {
						var item = frame.state().get('selection').first().toJSON();
						var url = item.sizes && item.sizes.large ? item.sizes.large.url : item.url;
						restoreRange();
						exec('insertHTML', '<figure><img src="' + url + '" alt="' + (item.alt || '').replace(/"/g, '') + '"></figure><p><br></p>');
					});
				}

				frame.open();
			});
		}

		/* ---------------------------------------------------------------- Вставка */

		body.addEventListener('paste', function (e) {
			var data = e.clipboardData || window.clipboardData;
			if (!data) return;

			e.preventDefault();

			var pasted = data.getData('text/html');
			var value = pasted ? clean(pasted) : textToHtml(data.getData('text/plain'));

			if (!command('insertHTML', value || '') && value) {
				/* Нет execCommand — дописываем в конец, чтобы вставка не пропала. */
				body.innerHTML = html() + value;
			}

			touched();
		});

		body.addEventListener('input', touched);
		body.addEventListener('keyup', state);
		body.addEventListener('mouseup', state);

		body.addEventListener('keydown', function (e) {
			var key = e.key.toLowerCase();

			if (e.ctrlKey || e.metaKey) {
				if ('b' === key || 'и' === key) { e.preventDefault(); exec('bold'); }
				if ('i' === key || 'ш' === key) { e.preventDefault(); exec('italic'); }
				if ('k' === key || 'л' === key) { e.preventDefault(); if (linkBtn) linkBtn.click(); }
				if ('h' === key || 'р' === key) { e.preventDefault(); toggleFind(true); }
				if ('s' === key || 'ы' === key) { e.preventDefault(); sync(); saveDraft(); }
				return;
			}

			if ('Tab' === e.key) {
				e.preventDefault();
				exec('insertHTML', '&emsp;');
			}
		});

		/* ------------------------------------------------------- Поиск и замена */

		var findBar = $('[data-xin-w-find]');
		var findField = $('[data-xin-w-findfield]');
		var replaceField = $('[data-xin-w-replacefield]');
		var findNote = $('[data-xin-w-findnote]');

		function toggleFind(show) {
			if (!findBar) return;
			findBar.hidden = typeof show === 'boolean' ? !show : !findBar.hidden;
			if (!findBar.hidden && findField) findField.focus();
		}

		var findBtn = $('[data-xin-w-findbtn]');
		if (findBtn) {
			findBtn.addEventListener('click', function (e) { e.preventDefault(); toggleFind(); });
		}

		function runReplace() {
			var from = findField ? findField.value : '';
			var to = replaceField ? replaceField.value : '';
			if (!from) return;

			var ci = !!($('[data-xin-w-findci]') && $('[data-xin-w-findci]').checked);
			var whole = !!($('[data-xin-w-findwhole]') && $('[data-xin-w-findwhole]').checked);
			var result = window.xinReplace.html(html(), [{ from: from, to: to, ci: ci, whole: whole, on: true }]);

			setHtml(result.html);
			touched();

			if (findNote) findNote.textContent = t('replaced', result.count);
		}

		var replaceBtn = $('[data-xin-w-replacebtn]');
		if (replaceBtn) {
			replaceBtn.addEventListener('click', function (e) { e.preventDefault(); runReplace(); });
		}

		if (findField) {
			findField.addEventListener('keydown', function (e) {
				if ('Enter' === e.key) { e.preventDefault(); runReplace(); }
				if ('Escape' === e.key) toggleFind(false);
			});
		}

		if (replaceField) {
			replaceField.addEventListener('keydown', function (e) {
				if ('Enter' === e.key) { e.preventDefault(); runReplace(); }
				if ('Escape' === e.key) toggleFind(false);
			});
		}

		/* --------------------------------------------------- Типографика и словарь */

		function tidy(value) {
			return String(value)
				.replace(/([>\s(«"'])"([^"]*)"/g, '$1«$2»')
				.replace(/"([^"]*)"/g, '«$1»')
				.replace(/(\s)--(\s)/g, '$1—$2')
				.replace(/(\s)-(\s)/g, '$1—$2')
				.replace(/\.\.\./g, '…')
				.replace(/[ \t]{2,}/g, ' ')
				.replace(/\s+([,.!?;:])/g, '$1')
				.replace(/<p>\s*(&nbsp;)?\s*<\/p>/gi, '');
		}

		var tidyBtn = $('[data-xin-w-tidy]');
		if (tidyBtn) {
			tidyBtn.addEventListener('click', function (e) {
				e.preventDefault();
				setHtml(tidy(html()));
				touched();
				if (note) note.textContent = t('tidied');
			});
		}

		var glossaryBtn = $('[data-xin-w-glossary]');
		if (glossaryBtn) {
			glossaryBtn.addEventListener('click', function (e) {
				e.preventDefault();

				var rules = CFG.glossary || [];
				if (!rules.length) return;

				var result = window.xinReplace.html(html(), rules);
				setHtml(result.html);
				touched();
				if (note) note.textContent = t('glossaryApplied', result.count);
			});
		}

		/* ------------------------------------------------------------ HTML-режим */

		function sourceOpen() {
			return !!(source && !source.hidden);
		}

		var sourceBtn = $('[data-xin-w-sourcebtn]');
		if (sourceBtn && source) {
			sourceBtn.addEventListener('click', function (e) {
				e.preventDefault();

				if (sourceOpen()) {
					body.innerHTML = clean(source.value) || '<p><br></p>';
					source.hidden = true;
					body.hidden = false;
					sourceBtn.classList.remove('is-active');
				} else {
					source.value = html().replace(/<\/(p|h2|h3|blockquote|ul|ol|figure)>/g, '</$1>\n');
					body.hidden = true;
					source.hidden = false;
					sourceBtn.classList.add('is-active');
					source.focus();
				}

				sync();
			});

			source.addEventListener('input', touched);
		}

		/* ---------------------------------------------------------- Режим фокуса */

		var focusBtn = $('[data-xin-w-focus]');
		if (focusBtn) {
			focusBtn.addEventListener('click', function (e) {
				e.preventDefault();
				var on = root.classList.toggle('is-focus');
				document.body.classList.toggle('xin-w-focus', on);
				focusBtn.classList.toggle('is-active', on);
				if (on) focusBody();
			});

			document.addEventListener('keydown', function (e) {
				if ('Escape' === e.key && root.classList.contains('is-focus')) focusBtn.click();
			});
		}

		/* ------------------------------------------------------ Восстановление */

		var restore = $('[data-xin-w-restore]');
		var draft = readDraft();

		if (restore && draft && draft.html && draft.html.replace(/\s+/g, '') !== html().replace(/\s+/g, '')) {
			var when = draft.at ? new Date(draft.at).toLocaleString() : '';
			var label = restore.querySelector('[data-xin-w-restore-note]');

			if (label) label.textContent = t('draftFound', when);
			restore.hidden = false;

			var yes = restore.querySelector('[data-xin-w-restore-yes]');
			var no = restore.querySelector('[data-xin-w-restore-no]');

			if (yes) {
				yes.addEventListener('click', function () {
					setHtml(draft.html);
					restore.hidden = true;
				});
			}
			if (no) {
				no.addEventListener('click', function () {
					dropDraft();
					restore.hidden = true;
				});
			}
		}

		/* ------------------------------------------------------------- Отправка */

		if (form) {
			form.addEventListener('submit', function () {
				sync();
				dropDraft();
			});
		}

		document.addEventListener('selectionchange', function () {
			if (document.activeElement === body) state();
		});

		if (!body.innerHTML.trim()) body.innerHTML = '<p><br></p>';

		sync();
		state();

		return {
			root: root,
			html: html,
			set: setHtml,
			clean: clean,
			tidy: tidy
		};
	}

	var editors = Array.prototype.map.call(document.querySelectorAll('[data-xin-writer]'), init).filter(Boolean);

	window.xinWriter = editors[0] || null;
	window.xinWriters = editors;
})();