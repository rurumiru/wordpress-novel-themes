(function () {
	'use strict';

	var LS_PREFS = 'xin-reader';
	var root = document.querySelector('[data-xin-reader]');
	if (!root) return;

	var defaults = { size: 19, height: 1.9, width: 720, font: 'serif', paper: 'default' };
	var prefs = defaults;

	try {
		var saved = JSON.parse(localStorage.getItem(LS_PREFS) || 'null');
		if (saved) prefs = Object.assign({}, defaults, saved);
	} catch (e) { }

	function $(sel) { return root.querySelector(sel); }
	function $$(sel) { return Array.prototype.slice.call(root.querySelectorAll(sel)); }

		function apply() {
		root.style.setProperty('--read-size', prefs.size + 'px');
		root.style.setProperty('--read-height', String(prefs.height));
		root.style.setProperty('--read-width', prefs.width + 'px');
		root.style.setProperty('--read-font', prefs.font === 'sans' ? 'var(--font)' : 'var(--font-read)');
		root.dataset.paper = prefs.paper;

		var sizeOut = $('[data-xin-size-value]');
		if (sizeOut) sizeOut.textContent = prefs.size;
		var leadOut = $('[data-xin-lead-value]');
		if (leadOut) leadOut.textContent = prefs.height.toFixed(1);

		$$('[data-xin-width]').forEach(function (b) { b.classList.toggle('is-active', +b.dataset.xinWidth === prefs.width); });
		$$('[data-xin-font]').forEach(function (b) { b.classList.toggle('is-active', b.dataset.xinFont === prefs.font); });
		$$('[data-xin-paper]').forEach(function (b) { b.classList.toggle('is-active', b.dataset.xinPaper === prefs.paper); });

		try { localStorage.setItem(LS_PREFS, JSON.stringify(prefs)); } catch (e) {}
	}

	$$('[data-xin-size]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			prefs.size = Math.min(30, Math.max(14, prefs.size + (+btn.dataset.xinSize)));
			apply();
		});
	});
	$$('[data-xin-lead]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			prefs.height = Math.min(2.4, Math.max(1.4, +(prefs.height + (+btn.dataset.xinLead) * 0.1).toFixed(2)));
			apply();
		});
	});
	$$('[data-xin-width]').forEach(function (btn) {
		btn.addEventListener('click', function () { prefs.width = +btn.dataset.xinWidth; apply(); });
	});
	$$('[data-xin-font]').forEach(function (btn) {
		btn.addEventListener('click', function () { prefs.font = btn.dataset.xinFont; apply(); });
	});
	$$('[data-xin-paper]').forEach(function (btn) {
		btn.addEventListener('click', function () { prefs.paper = btn.dataset.xinPaper; apply(); });
	});

	apply();

		var panel = $('[data-xin-rd-panel]');
	var toc = $('[data-xin-rd-toc-panel]');
	var scrim = $('[data-xin-rd-scrim]');

	function closePanels() {
		if (panel) panel.classList.remove('is-open');
		if (toc) toc.classList.remove('is-open');
		if (scrim) scrim.classList.remove('is-open');
		document.body.classList.remove('xin-no-scroll');
	}

	function openPanel(el) {
		closePanels();
		if (!el) return;
		el.classList.add('is-open');
		if (scrim) scrim.classList.add('is-open');
	}

	var settingsBtn = $('[data-xin-rd-settings]');
	if (settingsBtn) settingsBtn.addEventListener('click', function () { openPanel(panel); });
	var tocBtn = $('[data-xin-rd-toc]');
	if (tocBtn) {
		tocBtn.addEventListener('click', function () {
			openPanel(toc);
			
			var cur = toc && toc.querySelector('.is-current');
			if (cur) cur.scrollIntoView({ block: 'center' });
		});
	}
	$$('[data-xin-rd-close]').forEach(function (b) { b.addEventListener('click', closePanels); });
	if (scrim) scrim.addEventListener('click', closePanels);
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanels(); });

		var fullBtn = $('[data-xin-rd-full]');
	if (fullBtn) {
		fullBtn.addEventListener('click', function () {
			if (document.fullscreenElement) {
				document.exitFullscreen();
			} else if (document.documentElement.requestFullscreen) {
				document.documentElement.requestFullscreen().catch(function () {});
			}
		});
	}

		var bar = $('[data-xin-rd-bar]');
	var dock = $('[data-xin-rd-dock]');
	var progress = document.querySelector('[data-xin-progress]');
	var fill = $('[data-xin-rd-fill]');
	var pct = $('[data-xin-rd-pct]');
	var text = $('[data-xin-rd-text]');
	var hotzone = $('[data-xin-rd-hotzone]');

	var lastY = window.scrollY;
	var lastPct = -1;
	var lastHidden = null;
	var lastSaved = 0;
	var meta = root.dataset;

	function chrome(hidden) {
		if (bar) bar.classList.toggle('is-hidden', hidden);
		if (dock) dock.classList.toggle('is-hidden', hidden);
	}

	function ratio() {
		if (!text) return 0;
		var box = text.getBoundingClientRect();
		var total = box.height - window.innerHeight;
		if (total <= 0) return box.bottom < window.innerHeight ? 1 : 0;
		return Math.min(1, Math.max(0, -box.top / total));
	}

	function onScroll() {
		var y = window.scrollY;
		var p = ratio();
		var whole = Math.round(p * 100);

		if (whole !== lastPct) {
			lastPct = whole;
			if (progress) progress.style.width = whole + '%';
			if (fill) fill.style.width = whole + '%';
			if (pct) pct.textContent = whole + '%';
		}

		if (Math.abs(y - lastY) > 6) {
			var hide = y > lastY && y > 140;
			if (hide !== lastHidden) {
				lastHidden = hide;
				chrome(hide);
			}
			lastY = y;
		}

		var now = Date.now();
		if (now - lastSaved > 2000 && window.xinHistory && meta.novelId) {
			lastSaved = now;
			window.xinHistory.push({
				novelId: parseInt(meta.novelId, 10),
				novel: meta.novelTitle || '',
				title: meta.chapterTitle || '',
				url: window.location.href,
				cover: meta.cover || '',
				progress: p,
				at: now
			});
		}
	}

	var rafPending = false;
	function onScrollThrottled() {
		if (rafPending) return;
		rafPending = true;
		requestAnimationFrame(function () {
			rafPending = false;
			onScroll();
		});
	}

	window.addEventListener('scroll', onScrollThrottled, { passive: true });
	onScroll();

	if (hotzone) {
		hotzone.addEventListener('pointerenter', function () { chrome(false); });
	}
	
	if (text) {
		text.addEventListener('click', function () {
			if (bar && bar.classList.contains('is-hidden')) chrome(false);
		});
	}

		document.addEventListener('keydown', function (e) {
		if (e.target && /input|textarea|select/i.test(e.target.tagName)) return;
		if (e.metaKey || e.ctrlKey || e.altKey) return;

		if (e.key === 'ArrowLeft') {
			var prev = $('[data-xin-prev]');
			if (prev) window.location.href = prev.href;
		}
		if (e.key === 'ArrowRight') {
			var next = $('[data-xin-next]');
			if (next) window.location.href = next.href;
		}
		if (e.key === 't' || e.key === 'е') {
			openPanel(toc);
		}
		if (e.key === 's' || e.key === 'ы') {
			openPanel(panel);
		}
	});

		var readKey = 'xin-read-' + (meta.novelId || '0');
	try {
		var list = JSON.parse(localStorage.getItem(readKey) || '[]');
		var id = parseInt(meta.chapterId, 10);
		if (id && list.indexOf(id) === -1) {
			list.push(id);
			localStorage.setItem(readKey, JSON.stringify(list));
		}
	} catch (e) {}
})();
