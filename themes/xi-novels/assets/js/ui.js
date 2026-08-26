/*
 * Поведения, ради которых тема держала bootstrap.bundle.min.js: модальное окно,
 * панель сбоку, выпадающее меню и аккордеон. Всего четыре — за 81 КБ скрипта
 * (23.7 КБ в gzip), большую часть которого занимали карусели, тултипы, тосты и
 * Popper, ни разу не встречающиеся в разметке.
 *
 * Атрибуты оставлены прежними (`data-bs-toggle`, `data-bs-target`,
 * `data-bs-dismiss`, `data-bs-parent`): ни один шаблон при переходе не менялся,
 * и любая сторонняя разметка, написанная под Bootstrap, продолжает работать.
 *
 * Слушателей ровно три — клик, нажатие клавиши и переход по якорю; всё
 * остальное делегируется от document, поэтому блоки, добавленные в страницу
 * позже (поиск, подгрузка глав), работают без переинициализации.
 */
(function () {
	'use strict';

	var LOCK = 'xin-locked';
	var backdrop = null;

	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

	function reduced() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	/** Цель кнопки: data-bs-target, а если его нет — href, как это делал Bootstrap. */
	function target(el) {
		var sel = el.getAttribute('data-bs-target') || el.getAttribute('href');
		if (!sel || '#' === sel) return null;
		try { return $(sel); } catch (e) { return null; }
	}

	function lock(on) {
		document.documentElement.classList.toggle(LOCK, on);
		document.body.classList.toggle(LOCK, on);
	}

	/** Что-нибудь ещё открыто? Тогда прокрутку страницы не возвращаем. */
	function anyOpen() {
		return !!$('.modal.show, .offcanvas.show');
	}

	/* ── Подложка ───────────────────────────────────────────────────────────
	 * Одна на всё: панель сбоку рисует её узлом, модалка — своим фоном. Клик по
	 * подложке закрывает то, что её вызвало.
	 */
	function showBackdrop(owner) {
		if (!backdrop) {
			backdrop = document.createElement('div');
			backdrop.className = 'xin-backdrop';
			backdrop.addEventListener('click', function () {
				if (backdrop.owner) hide(backdrop.owner);
			});
		}
		backdrop.owner = owner;
		document.body.appendChild(backdrop);
		requestAnimationFrame(function () { backdrop.classList.add('is-open'); });
	}

	function hideBackdrop() {
		if (!backdrop || !backdrop.parentNode) return;
		var node = backdrop;
		node.owner = null;
		node.classList.remove('is-open');
		after(node, function () {
			if (node.parentNode && !node.classList.contains('is-open')) node.parentNode.removeChild(node);
		});
	}

	/** Выполнить после перехода — или сразу, если анимации выключены. */
	function after(el, fn) {
		if (reduced()) { fn(); return; }
		var done = false;
		var finish = function () {
			if (done) return;
			done = true;
			el.removeEventListener('transitionend', finish);
			fn();
		};
		el.addEventListener('transitionend', finish);
		setTimeout(finish, 400);
	}

	/* ── Модалка и панель ───────────────────────────────────────────────────── */

	function show(el) {
		if (!el || el.classList.contains('show')) return;

		var isModal = el.classList.contains('modal');

		el.classList.add('show');
		el.removeAttribute('aria-hidden');
		if (!isModal) showBackdrop(el);
		lock(true);

		requestAnimationFrame(function () {
			el.classList.add('is-open');
			var focusable = $('input, textarea, select, button, [href]', el);
			if (focusable) { try { focusable.focus({ preventScroll: true }); } catch (e) { focusable.focus(); } }
		});

		$$('[data-bs-target="#' + el.id + '"], [href="#' + el.id + '"]').forEach(function (btn) {
			btn.setAttribute('aria-expanded', 'true');
		});
	}

	function hide(el) {
		if (!el || !el.classList.contains('show')) return;

		el.classList.remove('is-open');
		hideBackdrop();

		after(el, function () {
			el.classList.remove('show');
			el.setAttribute('aria-hidden', 'true');
			if (!anyOpen()) lock(false);
		});

		$$('[data-bs-target="#' + el.id + '"], [href="#' + el.id + '"]').forEach(function (btn) {
			btn.setAttribute('aria-expanded', 'false');
		});
	}

	/* ── Аккордеон ──────────────────────────────────────────────────────────
	 * Анимируем высоту от нуля до реальной и снимаем её обратно: фиксированная
	 * высота, оставшаяся на элементе, обрезала бы текст, который вырос после
	 * загрузки шрифта или картинки внутри.
	 */
	function collapseToggle(el, open) {
		if (!el) return;

		var isOpen = el.classList.contains('show');
		open = 'undefined' === typeof open ? !isOpen : open;
		if (open === isOpen) return;

		if (reduced()) {
			el.classList.toggle('show', open);
			return;
		}

		el.classList.add('collapsing');
		el.classList.remove('show');
		el.style.height = (open ? 0 : el.scrollHeight) + 'px';

		requestAnimationFrame(function () {
			el.style.height = (open ? el.scrollHeight : 0) + 'px';
			after(el, function () {
				el.classList.remove('collapsing');
				el.classList.toggle('show', open);
				el.style.height = '';
			});
		});
	}

	function accordion(el, btn) {
		var parentSel = btn.getAttribute('data-bs-parent');
		var opening = !el.classList.contains('show');

		if (parentSel && opening) {
			var parent = null;
			try { parent = $(parentSel); } catch (e) { parent = null; }
			// data-bs-parent Bootstrap ждёт на самой панели; тема ставит его и на
			// кнопке, поэтому ищем в обоих местах.
			if (!parent) {
				try { parent = $(el.getAttribute('data-bs-parent') || ''); } catch (e) { parent = null; }
			}
			if (parent) {
				$$('.accordion-collapse.show', parent).forEach(function (other) {
					if (other !== el) {
						collapseToggle(other, false);
						setExpanded(other, false);
					}
				});
			}
		}

		collapseToggle(el, opening);
		setExpanded(el, opening);
	}

	function setExpanded(panel, open) {
		$$('[data-bs-target="#' + panel.id + '"], [href="#' + panel.id + '"]').forEach(function (btn) {
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			btn.classList.toggle('collapsed', !open);
		});
	}

	/* ── Выпадающее меню ────────────────────────────────────────────────────── */

	function closeDropdowns(except) {
		$$('.dropdown-menu.show').forEach(function (menu) {
			if (menu === except) return;
			menu.classList.remove('show');
			var btn = $('[data-bs-toggle="dropdown"]', menu.parentNode);
			if (btn) btn.setAttribute('aria-expanded', 'false');
		});
	}

	/* ── Один обработчик на всё ─────────────────────────────────────────────── */

	document.addEventListener('click', function (event) {
		var toggle = event.target.closest ? event.target.closest('[data-bs-toggle]') : null;
		var dismiss = event.target.closest ? event.target.closest('[data-bs-dismiss]') : null;

		if (dismiss) {
			var kind = dismiss.getAttribute('data-bs-dismiss');
			var box = dismiss.closest('.' + kind) || target(dismiss);
			if (box) {
				event.preventDefault();
				hide(box);
				return;
			}
		}

		if (toggle) {
			var what = toggle.getAttribute('data-bs-toggle');

			if ('modal' === what || 'offcanvas' === what) {
				var panel = target(toggle);
				if (panel) {
					event.preventDefault();
					show(panel);
					return;
				}
			}

			if ('collapse' === what) {
				var area = target(toggle);
				if (area) {
					event.preventDefault();
					accordion(area, toggle);
					return;
				}
			}

			if ('dropdown' === what) {
				event.preventDefault();
				var menu = $('.dropdown-menu', toggle.parentNode);
				if (menu) {
					var open = !menu.classList.contains('show');
					closeDropdowns(menu);
					menu.classList.toggle('show', open);
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				}
				return;
			}
		}

		// Клик мимо: закрываем меню, а модалку — только если попали в её фон.
		if (!event.target.closest || !event.target.closest('.dropdown-menu')) closeDropdowns();

		var openModal = $('.modal.show');
		if (openModal && event.target === openModal) hide(openModal);
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' !== event.key && 'Esc' !== event.key) return;

		var layer = $('.modal.show') || $('.offcanvas.show');
		if (layer) {
			hide(layer);
			return;
		}
		closeDropdowns();
	});

	/* ── Вкладки страницы тайтла ─────────────────────────────────────────────
	 * Описание и оглавление лежат в разметке рядом, поэтому без скрипта видны
	 * оба — страница остаётся целой и для поисковика, и при выключенном JS.
	 * Здесь только показ одной и скрытие другой.
	 */
	function tabs() {
		var bar = $('[data-xin-tabs]');
		if (!bar) return;

		var buttons = $$('[data-xin-tab]', bar);
		var panels = $$('[data-xin-panel]');
		if (!buttons.length || !panels.length) return;

		function open(name) {
			buttons.forEach(function (btn) {
				var on = btn.getAttribute('data-xin-tab') === name;
				btn.classList.toggle('is-active', on);
				btn.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			panels.forEach(function (panel) {
				panel.hidden = panel.getAttribute('data-xin-panel') !== name;
			});
		}

		bar.addEventListener('click', function (event) {
			var btn = event.target.closest ? event.target.closest('[data-xin-tab]') : null;
			if (!btn) return;
			open(btn.getAttribute('data-xin-tab'));
		});

		/*
		 * Ссылка «Оглавление» из шапки и из читалки ведёт на #chapters. Если
		 * панель с главами закрыта, якорь прокручивал бы к пустому месту —
		 * поэтому по такой ссылке сразу открываем нужную вкладку.
		 */
		function fromHash() {
			var id = (location.hash || '').replace('#', '');
			if (!id) return;
			var panel = document.getElementById(id);
			if (panel && panel.hasAttribute('data-xin-panel')) {
				open(panel.getAttribute('data-xin-panel'));
				panel.scrollIntoView({ block: 'start' });
			}
		}

		open('about');
		fromHash();
		window.addEventListener('hashchange', fromHash);

		document.addEventListener('click', function (event) {
			var link = event.target.closest ? event.target.closest('a[href*="#chapters"]') : null;
			if (link) open('toc');
		});
	}

	tabs();

	/*
	 * Ссылка внутри выехавшей панели ведёт на ту же страницу — панель должна
	 * уйти сама, иначе якорь прокрутит текст под перекрытым экраном.
	 */
	document.addEventListener('click', function (event) {
		var link = event.target.closest ? event.target.closest('.offcanvas a[href^="#"]') : null;
		if (!link) return;
		var panel = link.closest('.offcanvas');
		if (panel) hide(panel);
	});
})();
