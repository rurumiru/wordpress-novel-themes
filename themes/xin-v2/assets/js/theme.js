/**
 * XIN-V2 — интерфейс сайта: тема, меню, поиск, слайдер, избранное.
 */
(function () {
	'use strict';

	/* Подписи приходят из темы переведёнными: зашитая в скрипт строка
	   остаётся русской на любом языке сайта. */
	function say(key) {
		var dict = (window.xvData && window.xvData.i18n) || {};
		return dict[key] || '';
	}


	var doc = document;

	/* ── Оформление ─────────────────────────────────────────── */

	function setTheme(mode) {
		doc.documentElement.setAttribute('data-theme', mode);
		try { localStorage.setItem('xv-theme', mode); } catch (e) {}
		syncThemeButtons();
	}

	function syncThemeButtons() {
		var current = doc.documentElement.getAttribute('data-theme');
		doc.querySelectorAll('[data-xv-theme]').forEach(function (btn) {
			btn.classList.toggle('on', btn.getAttribute('data-xv-theme') === current);
		});
		doc.querySelectorAll('[data-xv-theme-toggle]').forEach(function (btn) {
			var dark = current === 'dark';
			btn.classList.toggle('is-on', dark);
			btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
		});
	}

	doc.addEventListener('click', function (event) {
		var pick = event.target.closest('[data-xv-theme]');
		if (pick) {
			setTheme(pick.getAttribute('data-xv-theme'));
			return;
		}

		var toggle = event.target.closest('[data-xv-theme-toggle]');
		if (toggle) {
			setTheme(doc.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
		}
	});

	syncThemeButtons();

	/* ── Выпадающие меню ────────────────────────────────────── */

	function closeDrops(except) {
		doc.querySelectorAll('[data-xv-drop]').forEach(function (drop) {
			if (drop === except) { return; }
			drop.classList.remove('is-open');
			var btn = drop.querySelector('[data-xv-drop-btn]');
			if (btn) { btn.setAttribute('aria-expanded', 'false'); }
		});
	}

	doc.addEventListener('click', function (event) {
		var btn = event.target.closest('[data-xv-drop-btn]');

		if (!btn) {
			closeDrops(null);
			return;
		}

		var drop = btn.closest('[data-xv-drop]');
		var open = !drop.classList.contains('is-open');

		closeDrops(drop);
		drop.classList.toggle('is-open', open);
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	});

	doc.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') { closeDrops(null); }
	});

	/* ── Боковое меню на телефоне ───────────────────────────── */

	function drawer(open) {
		var panel = doc.querySelector('[data-xv-drawer]');
		var veil = doc.querySelector('[data-xv-drawer-overlay]');
		if (!panel) { return; }

		panel.hidden = !open;
		if (veil) { veil.hidden = !open; }
		doc.body.style.overflow = open ? 'hidden' : '';
		var trigger = doc.querySelector('[data-xv-drawer-open]');
		if (trigger) { trigger.setAttribute('aria-expanded', open ? 'true' : 'false'); }
		if (open) {
			var close = panel.querySelector('[data-xv-drawer-close]');
			if (close) { close.focus(); }
		} else if (trigger) { trigger.focus(); }
	}
	doc.addEventListener('keydown', function (event) {
		var panel = doc.querySelector('[data-xv-drawer]');
		if (!panel || panel.hidden) { return; }
		if (event.key === 'Escape') { drawer(false); return; }
		if (event.key !== 'Tab') { return; }
		var items = Array.from(panel.querySelectorAll('a[href],button,input')).filter(function(el){ return el.getClientRects().length; });
		if (!items.length) { return; }
		if (event.shiftKey && doc.activeElement === items[0]) { event.preventDefault(); items[items.length-1].focus(); }
		else if (!event.shiftKey && doc.activeElement === items[items.length-1]) { event.preventDefault(); items[0].focus(); }
	});

	doc.addEventListener('click', function (event) {
		if (event.target.closest('[data-xv-drawer-open]')) { drawer(true); }
		if (event.target.closest('[data-xv-drawer-close]') || event.target.closest('[data-xv-drawer-overlay]')) { drawer(false); }
	});

	/* ── Поиск ──────────────────────────────────────────────── */

	function searchOverlay(open) {
		var overlay = doc.querySelector('[data-xv-search-overlay]');
		if (!overlay) { return; }

		overlay.hidden = !open;

		if (open) {
			var field = overlay.querySelector('input[type="search"]');
			if (field) { field.focus(); }
		}
	}

	doc.addEventListener('click', function (event) {
		if (event.target.closest('[data-xv-search-open]')) { searchOverlay(true); }
		if (event.target.closest('[data-xv-search-close]')) { searchOverlay(false); }
	});

	// «/» ставит курсор в поиск — привычка по книжным каталогам.
	doc.addEventListener('keydown', function (event) {
		if (event.key !== '/' || event.metaKey || event.ctrlKey) { return; }

		var tag = (event.target.tagName || '').toLowerCase();
		if (tag === 'input' || tag === 'textarea' || event.target.isContentEditable) { return; }

		var field = doc.querySelector('[data-xv-search-input]');
		if (field) {
			event.preventDefault();
			field.focus();
			field.select();
		}
	});

	/* ── Книга недели ───────────────────────────────────────── */

	var hero = doc.querySelector('[data-xv-hero]');

	if (hero) {
		var slides = hero.querySelectorAll('[data-xv-hero-slide]');
		var dots = hero.querySelectorAll('[data-xv-hero-dot]');
		var at = 0;
		var timer = null;

		function show(index) {
			at = (index + slides.length) % slides.length;
			slides.forEach(function (slide, i) { slide.classList.toggle('is-on', i === at); });
			dots.forEach(function (dot, i) { dot.classList.toggle('is-on', i === at); });
		}

		function play() {
			if (slides.length < 2) { return; }
			stop();
			timer = setInterval(function () { show(at + 1); }, 7000);
		}

		function stop() {
			if (timer) { clearInterval(timer); timer = null; }
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				show(parseInt(dot.getAttribute('data-xv-hero-dot'), 10));
				play();
			});
		});

		hero.addEventListener('mouseenter', stop);
		hero.addEventListener('mouseleave', play);
		play();
	}

	/* ── Описание тайтла ────────────────────────────────────── */

	doc.addEventListener('click', function (event) {
		var toggle = event.target.closest('[data-xv-synopsis-toggle]');
		if (!toggle) { return; }

		var box = toggle.closest('[data-xv-synopsis]');
		var open = box.classList.toggle('is-open');
		toggle.textContent = open
			? toggle.getAttribute('data-close') || say('less')
			: say('more');
	});

	/* ── Поиск по оглавлению ────────────────────────────────── */

	var tocSearch = doc.querySelector('[data-xv-toc-search]');

	if (tocSearch) {
		tocSearch.addEventListener('input', function () {
			var needle = tocSearch.value.trim().toLowerCase();

			doc.querySelectorAll('[data-xv-toc] .xv-toc-row').forEach(function (row) {
				var title = row.getAttribute('data-title') || '';
				row.hidden = needle !== '' && title.indexOf(needle) === -1;
			});
		});
	}

	/* ── Сортировка каталога ────────────────────────────────── */

	var sort = doc.querySelector('[data-xv-sort]');

	if (sort) {
		sort.addEventListener('change', function () {
			window.location.href = sort.value;
		});
	}

	/* ── Избранное ──────────────────────────────────────────── */

	doc.addEventListener('click', function (event) {
		var btn = event.target.closest('[data-xv-fav]');
		if (!btn || !window.xvData) { return; }

		if (!xvData.loggedIn) {
			window.location.href = xvData.home + 'wp-login.php';
			return;
		}

		btn.disabled = true;

		fetch(xvData.root + 'favourite', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': xvData.nonce },
			body: JSON.stringify({ novel: parseInt(btn.getAttribute('data-xv-fav'), 10) })
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				btn.classList.toggle('is-on', !!data.on);
				btn.setAttribute('aria-pressed', data.on ? 'true' : 'false');

				var label = btn.querySelector('[data-xv-fav-label]');
				if (label) { label.textContent = data.on ? say('faved') : say('unfaved'); }
			})
			.catch(function () {})
			.finally(function () { btn.disabled = false; });
	});

	/* ── Копирование ссылки ─────────────────────────────────── */

	doc.addEventListener('click', function (event) {
		var btn = event.target.closest('[data-xv-copy]');
		if (!btn || !navigator.clipboard) { return; }

		navigator.clipboard.writeText(btn.getAttribute('data-xv-copy')).then(function () {
			btn.classList.add('is-done');
			setTimeout(function () { btn.classList.remove('is-done'); }, 1400);
		});
	});
})();
