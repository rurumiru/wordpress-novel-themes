(function () {
	'use strict';

	var root = document.querySelector('[data-xin-reader]');
	if (!root) return;

	var panel = root.querySelector('[data-xin-gl-panel]');
	if (!panel) return;

	var CFG = window.XIN_GL || {};
	var L = CFG.i18n || {};
	var ICONS = CFG.icons || {};

	var LS_KEY = 'xin-glossary';
	var MAX_RULES = 500;
	var novelId = String(parseInt(root.dataset.novelId, 10) || 0);

	/* Словарь переводчика: приходит с сервера, читателю только включить или выключить. */
	var project = (CFG.project || []).map(function (rule) {
		return {
			id: 'p' + (rule.from || ''),
			from: String(rule.from || ''),
			to: 'string' === typeof rule.to ? rule.to : '',
			ci: false !== rule.ci,
			whole: !!rule.whole,
			on: true,
			project: true
		};
	}).filter(function (rule) { return rule.from; });

	function t(key, value) {
		var line = L[key] || '';
		return typeof value === 'undefined' ? line : line.replace('%s', value);
	}

	/* --------------------------------------------------------------- Хранилище */

	var store = load();

	function load() {
		var raw = null;
		try { raw = JSON.parse(localStorage.getItem(LS_KEY) || 'null'); } catch (e) { raw = null; }
		if (!raw || typeof raw !== 'object') raw = {};

		var data = {
			v: 1,
			on: raw.on !== false,
			mark: !!raw.mark,
			project: raw.project !== false,
			global: cleanList(raw.global),
			novels: {}
		};

		if (raw.novels && typeof raw.novels === 'object') {
			Object.keys(raw.novels).forEach(function (key) {
				var list = cleanList(raw.novels[key]);
				if (list.length) data.novels[key] = list;
			});
		}

		return data;
	}

	function save() {
		try { localStorage.setItem(LS_KEY, JSON.stringify(store)); } catch (e) { }
	}

	function uid() {
		return 'g' + Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-3);
	}

	function cleanList(list) {
		if (!list) return [];

		if (!Array.isArray(list) && typeof list === 'object') {
			list = Object.keys(list).map(function (key) { return { from: key, to: String(list[key]) }; });
		}
		if (!Array.isArray(list)) return [];

		var seen = {};
		var out = [];

		list.forEach(function (rule) {
			if (Array.isArray(rule)) rule = { from: rule[0], to: rule[1] };
			if (!rule || typeof rule !== 'object') return;

			var from = typeof rule.from === 'string' ? rule.from.trim() : '';
			if (!from || out.length >= MAX_RULES) return;

			var key = from.toLowerCase();
			if (seen[key]) return;
			seen[key] = true;

			out.push({
				id: typeof rule.id === 'string' && rule.id ? rule.id : uid(),
				from: from,
				to: typeof rule.to === 'string' ? rule.to : '',
				ci: rule.ci !== false,
				whole: !!rule.whole,
				on: rule.on !== false
			});
		});

		return out;
	}

	function ownList() {
		if (!store.novels[novelId]) store.novels[novelId] = [];
		return store.novels[novelId];
	}

	function listFor(scope) {
		return 'all' === scope ? store.global : ownList();
	}

	/**
	 * Порядок решает споры: своё правило старше общего, общее — старше словаря
	 * переводчика. Одинаковой длины правила остаются в этом порядке, длинные
	 * всё равно выигрывают у коротких.
	 */
	function allRules() {
		var mine = ownList().concat(store.global);

		if (!store.project || !project.length) return mine;

		var taken = {};
		mine.forEach(function (rule) { taken[rule.from.toLowerCase()] = true; });

		return mine.concat(project.filter(function (rule) {
			return !taken[rule.from.toLowerCase()];
		}));
	}

	/* ------------------------------------------------------------------ Замена */

	function matcher() {
		if (!store.on || !window.xinReplace) return null;
		return window.xinReplace.matcher(allRules());
	}

	function scan(text, mx) {
		return window.xinReplace.scan(text, mx);
	}

	/* --------------------------------------------------------------------- DOM */

	var SKIP = 'script,style,code,pre,textarea,kbd,samp';
	var slots = [];

	function collect() {
		slots = [];

		Array.prototype.forEach.call(root.querySelectorAll('[data-xin-gl-scope]'), function (scope) {
			var walker = document.createTreeWalker(scope, NodeFilter.SHOW_TEXT, null, false);
			var node;

			while ((node = walker.nextNode())) {
				if (!node.nodeValue || !node.nodeValue.trim()) continue;

				var parent = node.parentNode;
				if (!parent || 1 !== parent.nodeType) continue;
				if (parent.closest && parent.closest(SKIP)) continue;

				slots.push({ node: node, text: node.nodeValue, extra: [] });
			}
		});
	}

	function apply() {
		var mx = matcher();
		var hits = 0;

		slots.forEach(function (slot) {
			slot.extra.forEach(function (node) {
				if (node.parentNode) node.parentNode.removeChild(node);
			});
			slot.extra = [];

			var result = mx ? scan(slot.text, mx) : null;

			if (!result) {
				if (slot.node.nodeValue !== slot.text) slot.node.nodeValue = slot.text;
				return;
			}

			hits += result.count;

			if (!store.mark) {
				slot.node.nodeValue = result.parts.map(function (part) { return part.text; }).join('');
				return;
			}

			var frag = document.createDocumentFragment();

			result.parts.forEach(function (part) {
				if (!part.hit) {
					frag.appendChild(document.createTextNode(part.text));
					return;
				}

				var mark = document.createElement('mark');
				mark.className = part.text ? 'xin-gl-hit' : 'xin-gl-hit is-cut';
				mark.setAttribute('title', t('was', part.from));
				mark.textContent = part.text;
				frag.appendChild(mark);
			});

			var inserted = Array.prototype.slice.call(frag.childNodes);
			slot.node.nodeValue = '';
			slot.node.parentNode.insertBefore(frag, slot.node.nextSibling);
			slot.extra = inserted;
		});

		return hits;
	}

	/* ------------------------------------------------------------------ Панель */

	var openBtn = root.querySelector('[data-xin-gl-open]');
	var list = panel.querySelector('[data-xin-gl-list]');
	var note = panel.querySelector('[data-xin-gl-note]');
	var form = panel.querySelector('[data-xin-gl-form]');
	var fromField = panel.querySelector('[data-xin-gl-from]');
	var toField = panel.querySelector('[data-xin-gl-to]');
	var ciField = panel.querySelector('[data-xin-gl-ci]');
	var wholeField = panel.querySelector('[data-xin-gl-whole]');
	var allField = panel.querySelector('[data-xin-gl-all]');
	var submitBtn = panel.querySelector('[data-xin-gl-submit]');
	var cancelBtn = panel.querySelector('[data-xin-gl-cancel]');
	var onField = panel.querySelector('[data-xin-gl-toggle]');
	var markField = panel.querySelector('[data-xin-gl-mark]');
	var fileField = panel.querySelector('[data-xin-gl-file]');
	var projectField = panel.querySelector('[data-xin-gl-project]');
	var exportBtn = panel.querySelector('[data-xin-gl-export]');
	var importBtn = panel.querySelector('[data-xin-gl-import]');
	var pop = root.querySelector('[data-xin-gl-pop]');
	var textEl = root.querySelector('[data-xin-rd-text]');

	var editing = null;

	function icon(name) {
		return ICONS[name] || '';
	}

	function refresh() {
		var hits = apply();
		var total = allRules().length;

		if (onField) onField.checked = store.on;
		if (markField) markField.checked = store.mark;
		if (projectField) projectField.checked = store.project;
		if (openBtn) openBtn.classList.toggle('is-active', store.on && total > 0);

		if (note) {
			note.textContent = total
				? t('stat').replace('%1$s', total).replace('%2$s', hits)
				: t('hint');
		}

		save();
	}

	function ruleRow(rule, scope) {
		var row = document.createElement('div');
		row.className = rule.on ? 'xin-gl__rule' : 'xin-gl__rule is-off';
		row.setAttribute('data-id', rule.id);
		row.setAttribute('data-scope', scope);

		var power = document.createElement('button');
		power.type = 'button';
		power.className = 'xin-gl__power';
		power.setAttribute('data-act', 'toggle');
		power.setAttribute('aria-pressed', rule.on ? 'true' : 'false');
		power.setAttribute('title', rule.on ? t('ruleOff') : t('ruleOn'));
		power.innerHTML = icon('check');
		row.appendChild(power);

		var body = document.createElement('button');
		body.type = 'button';
		body.className = 'xin-gl__body';
		body.setAttribute('data-act', 'edit');
		body.setAttribute('title', t('ruleEdit'));

		var from = document.createElement('span');
		from.className = 'xin-gl__from';
		from.textContent = rule.from;
		body.appendChild(from);

		var to = document.createElement('span');
		to.className = rule.to ? 'xin-gl__to' : 'xin-gl__to is-cut';
		to.textContent = rule.to || t('ruleCut');
		body.appendChild(to);

		var flags = [];
		if (!rule.ci) flags.push(t('flagCase'));
		if (rule.whole) flags.push(t('flagWhole'));

		if (flags.length) {
			var marks = document.createElement('span');
			marks.className = 'xin-gl__flags';
			marks.textContent = flags.join(' · ');
			body.appendChild(marks);
		}

		row.appendChild(body);

		var drop = document.createElement('button');
		drop.type = 'button';
		drop.className = 'xin-gl__drop';
		drop.setAttribute('data-act', 'delete');
		drop.setAttribute('title', t('ruleDelete'));
		drop.innerHTML = icon('trash');
		row.appendChild(drop);

		return row;
	}

	function projectRow(rule) {
		var row = document.createElement('div');
		row.className = 'xin-gl__rule is-project';

		var mark = document.createElement('span');
		mark.className = 'xin-gl__power is-static';
		mark.setAttribute('title', t('fromTranslator'));
		mark.innerHTML = icon('check');
		row.appendChild(mark);

		var text = document.createElement('span');
		text.className = 'xin-gl__body is-static';

		var from = document.createElement('span');
		from.className = 'xin-gl__from';
		from.textContent = rule.from;
		text.appendChild(from);

		var to = document.createElement('span');
		to.className = rule.to ? 'xin-gl__to' : 'xin-gl__to is-cut';
		to.textContent = rule.to || t('ruleCut');
		text.appendChild(to);

		row.appendChild(text);

		return row;
	}

	function group(title, rules, scope) {
		var box = document.createDocumentFragment();

		var head = document.createElement('h4');
		head.className = 'xin-gl__head';
		head.textContent = title + ' · ' + rules.length;
		box.appendChild(head);

		rules.forEach(function (rule) { box.appendChild(ruleRow(rule, scope)); });

		return box;
	}

	function render() {
		if (!list) return;

		list.textContent = '';

		var own = ownList();
		var shared = store.global;

		if (!own.length && !shared.length && !(project.length && store.project)) {
			var empty = document.createElement('p');
			empty.className = 'xin-gl__empty';
			empty.textContent = t('empty');
			list.appendChild(empty);
			return;
		}

		if (own.length) list.appendChild(group(t('scopeNovel'), own, 'novel'));
		if (shared.length) list.appendChild(group(t('scopeAll'), shared, 'all'));

		if (project.length && store.project) {
			var head = document.createElement('h4');
			head.className = 'xin-gl__head';
			head.textContent = t('scopeProject') + ' · ' + project.length;
			list.appendChild(head);
			project.forEach(function (rule) { list.appendChild(projectRow(rule)); });
		}
	}

	function findRule(id) {
		var scopes = ['novel', 'all'];
		var found = null;

		scopes.forEach(function (scope) {
			if (found) return;

			var rules = listFor(scope);
			for (var i = 0; i < rules.length; i++) {
				if (rules[i].id === id) {
					found = { rule: rules[i], scope: scope, index: i };
					return;
				}
			}
		});

		return found;
	}

	function resetForm() {
		editing = null;
		if (form) form.reset();
		if (ciField) ciField.checked = true;
		if (submitBtn) submitBtn.textContent = t('add');
		if (cancelBtn) cancelBtn.hidden = true;
	}

	function editRule(id) {
		var found = findRule(id);
		if (!found) return;

		editing = id;
		if (fromField) fromField.value = found.rule.from;
		if (toField) toField.value = found.rule.to;
		if (ciField) ciField.checked = found.rule.ci;
		if (wholeField) wholeField.checked = found.rule.whole;
		if (allField) allField.checked = 'all' === found.scope;
		if (submitBtn) submitBtn.textContent = t('save');
		if (cancelBtn) cancelBtn.hidden = false;
		if (fromField) fromField.focus();
	}

	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			var from = fromField ? fromField.value.trim() : '';
			if (!from) return;

			var scope = allField && allField.checked ? 'all' : 'novel';
			var data = {
				from: from,
				to: toField ? toField.value.trim() : '',
				ci: !ciField || ciField.checked,
				whole: !!(wholeField && wholeField.checked),
				on: true
			};

			var found = editing ? findRule(editing) : null;

			if (found && found.scope !== scope) {
				listFor(found.scope).splice(found.index, 1);
				found = null;
			}

			if (found) {
				found.rule.from = data.from;
				found.rule.to = data.to;
				found.rule.ci = data.ci;
				found.rule.whole = data.whole;
			} else {
				var rules = listFor(scope);
				var twin = null;

				for (var i = 0; i < rules.length; i++) {
					if (rules[i].from.toLowerCase() === data.from.toLowerCase()) { twin = rules[i]; break; }
				}

				if (twin) {
					twin.from = data.from;
					twin.to = data.to;
					twin.ci = data.ci;
					twin.whole = data.whole;
					twin.on = true;
				} else if (rules.length < MAX_RULES) {
					data.id = uid();
					rules.unshift(data);
				}
			}

			resetForm();
			render();
			refresh();
			if (fromField) fromField.focus();
		});
	}

	if (cancelBtn) cancelBtn.addEventListener('click', resetForm);

	if (list) {
		list.addEventListener('click', function (e) {
			var button = e.target.closest('[data-act]');
			if (!button) return;

			var row = button.closest('.xin-gl__rule');
			if (!row) return;

			var found = findRule(row.getAttribute('data-id'));
			if (!found) return;

			var act = button.getAttribute('data-act');

			if ('edit' === act) {
				editRule(found.rule.id);
				return;
			}

			if ('toggle' === act) {
				found.rule.on = !found.rule.on;
			} else if ('delete' === act) {
				listFor(found.scope).splice(found.index, 1);
				if (editing === found.rule.id) resetForm();
			}

			render();
			refresh();
		});
	}

	if (onField) {
		onField.addEventListener('change', function () {
			store.on = onField.checked;
			refresh();
		});
	}

	if (markField) {
		markField.addEventListener('change', function () {
			store.mark = markField.checked;
			refresh();
		});
	}

	if (projectField) {
		projectField.addEventListener('change', function () {
			store.project = projectField.checked;
			render();
			refresh();
		});
	}

	/* ------------------------------------------------------------ Файл словаря */

	function mergeInto(target, incoming) {
		var added = 0;

		cleanList(incoming).forEach(function (rule) {
			var twin = null;

			for (var i = 0; i < target.length; i++) {
				if (target[i].from.toLowerCase() === rule.from.toLowerCase()) { twin = target[i]; break; }
			}

			if (twin) {
				twin.to = rule.to;
				twin.ci = rule.ci;
				twin.whole = rule.whole;
				twin.on = rule.on;
			} else if (target.length < MAX_RULES) {
				target.push(rule);
				added++;
			}
		});

		return added;
	}

	function merge(data) {
		var added = 0;

		if (Array.isArray(data) || (!data.global && !data.novels)) {
			return mergeInto(ownList(), data.rules || data);
		}

		if (data.global) added += mergeInto(store.global, data.global);

		if (data.novels && typeof data.novels === 'object') {
			Object.keys(data.novels).forEach(function (key) {
				if (!store.novels[key]) store.novels[key] = [];
				added += mergeInto(store.novels[key], data.novels[key]);
			});
		}

		return added;
	}

	if (exportBtn) {
		exportBtn.addEventListener('click', function () {
			var data = JSON.stringify({ v: 1, global: store.global, novels: store.novels }, null, '\t');
			var blob = new Blob([data], { type: 'application/json' });
			var url = URL.createObjectURL(blob);
			var link = document.createElement('a');

			link.href = url;
			link.download = 'glossary-' + window.location.hostname + '.json';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);

			setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
		});
	}

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

				if (!data || typeof data !== 'object') {
					if (note) note.textContent = t('badFile');
					return;
				}

				var added = merge(data);

				render();
				refresh();
				if (note) note.textContent = t('imported', added);
			};

			reader.readAsText(file);
		});
	}

	/* ------------------------------------------------------- Открытие и выделение */

	function openPanel() {
		if (window.xinReader && window.xinReader.open) {
			window.xinReader.open(panel);
			return;
		}
		panel.classList.add('is-open');
	}

	if (openBtn) openBtn.addEventListener('click', openPanel);

	document.addEventListener('keydown', function (e) {
		if (e.target && /input|textarea|select/i.test(e.target.tagName)) return;
		if (e.metaKey || e.ctrlKey || e.altKey) return;
		if ('g' === e.key || 'п' === e.key) openPanel();
	});

	function hidePop() {
		if (pop) pop.hidden = true;
	}

	function placePop(rect) {
		var top = rect.top + window.scrollY - pop.offsetHeight - 10;
		var left = rect.left + window.scrollX + rect.width / 2 - pop.offsetWidth / 2;
		var limit = document.documentElement.clientWidth - pop.offsetWidth - 8;

		pop.style.top = Math.max(window.scrollY + 8, top) + 'px';
		pop.style.left = Math.max(8, Math.min(left, limit)) + 'px';
	}

	function onSelect() {
		if (!pop || !textEl) return;

		var sel = window.getSelection();
		if (!sel || sel.isCollapsed || !sel.rangeCount) { hidePop(); return; }

		var range = sel.getRangeAt(0);
		if (!textEl.contains(range.commonAncestorContainer)) { hidePop(); return; }

		var value = sel.toString().replace(/\s+/g, ' ').trim();
		if (!value || value.length > 80) { hidePop(); return; }

		var rect = range.getBoundingClientRect();
		if (!rect.width && !rect.height) { hidePop(); return; }

		pop.setAttribute('data-value', value);
		pop.hidden = false;
		placePop(rect);
	}

	if (pop && textEl) {
		['mouseup', 'touchend'].forEach(function (name) {
			document.addEventListener(name, function () { setTimeout(onSelect, 10); });
		});

		document.addEventListener('mousedown', function (e) {
			if (!pop.contains(e.target)) hidePop();
		});

		window.addEventListener('scroll', hidePop, { passive: true });

		pop.addEventListener('click', function () {
			var value = pop.getAttribute('data-value') || '';

			hidePop();
			resetForm();
			openPanel();

			if (fromField) fromField.value = value;
			if (toField) toField.focus();
		});
	}

	/* -------------------------------------------------------------------- Старт */

	collect();
	render();
	resetForm();
	refresh();

	window.xinGlossary = {
		rules: allRules,
		refresh: function () { render(); refresh(); },
		add: function (from, to, options) {
			var opts = options || {};
			var rules = listFor(opts.all ? 'all' : 'novel');

			rules.unshift({
				id: uid(),
				from: String(from || '').trim(),
				to: String(to || ''),
				ci: false !== opts.ci,
				whole: !!opts.whole,
				on: true
			});

			render();
			refresh();
		}
	};
})();
