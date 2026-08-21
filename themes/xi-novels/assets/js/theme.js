(function () {
	'use strict';

	var LS_THEME = 'xin-theme';
	var LS_FAVS = 'xin-favorites';
	var LS_HISTORY = 'xin-history';
	var i18n = (window.XIN && window.XIN.i18n) || {};

	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

	function read(key, fallback) {
		try {
			var raw = localStorage.getItem(key);
			return raw ? JSON.parse(raw) : fallback;
		} catch (e) {
			return fallback;
		}
	}

	function write(key, value) {
		try {
			localStorage.setItem(key, JSON.stringify(value));
		} catch (e) { }
	}

		function initTheme() {
		var btn = $('[data-xin-theme]');
		if (!btn) return;

		btn.addEventListener('click', function () {
			var now = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
			document.documentElement.setAttribute('data-theme', now);
			try { localStorage.setItem(LS_THEME, JSON.stringify(now)); } catch (e) {}

try { localStorage.setItem(LS_THEME, now); } catch (e) {}
		});
	}

		function initScroll() {
		var header = $('#xin-header');
		var top = $('[data-xin-totop]');
		if (!header && !top) return;

		var stuck = null;
		var visible = null;
		var ticking = false;

		function apply() {
			ticking = false;
			var y = window.scrollY;

			if (header) {
				var nowStuck = y > 8;
				if (nowStuck !== stuck) {
					stuck = nowStuck;
					header.classList.toggle('is-stuck', nowStuck);
				}
			}

			if (top) {
				var nowVisible = y > 600;
				if (nowVisible !== visible) {
					visible = nowVisible;
					top.classList.toggle('is-visible', nowVisible);
				}
			}
		}

		function onScroll() {
			if (ticking) return;
			ticking = true;
			requestAnimationFrame(apply);
		}

		apply();
		window.addEventListener('scroll', onScroll, { passive: true });

		if (top) {
			top.addEventListener('click', function () {
				window.scrollTo({ top: 0, behavior: 'smooth' });
			});
		}
	}

		function initOverlays() {
		var overlay = $('[data-xin-search-overlay]');
		var drawer = $('[data-xin-drawer]');
		var scrim = $('[data-xin-scrim]');

		function closeAll() {
			if (overlay) { overlay.classList.remove('is-open'); overlay.hidden = true; }
			if (drawer) drawer.classList.remove('is-open');
			if (scrim) scrim.classList.remove('is-open');
			document.body.classList.remove('xin-no-scroll');
		}

		$$('[data-xin-search]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!overlay) return;
				overlay.hidden = false;
				
				void overlay.offsetWidth;
				overlay.classList.add('is-open');
				document.body.classList.add('xin-no-scroll');
				var field = $('input[type="search"]', overlay);
				if (field) setTimeout(function () { field.focus(); }, 60);
			});
		});

		$$('[data-xin-menu]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!drawer) return;
				drawer.classList.add('is-open');
				if (scrim) scrim.classList.add('is-open');
				document.body.classList.add('xin-no-scroll');
				btn.setAttribute('aria-expanded', 'true');
			});
		});

		$$('[data-xin-menu-close]').forEach(function (btn) { btn.addEventListener('click', closeAll); });
		if (scrim) scrim.addEventListener('click', closeAll);
		if (overlay) {
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) closeAll();
			});
		}
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeAll();
		});
	}

		function initBanner() {
		var root = $('[data-xin-banner]');
		if (!root) return;
		var track = $('[data-xin-banner-track]', root);
		var slides = $$('.xin-banner__slide', root);
		var dots = $$('[data-xin-banner-dots] button', root);
		if (slides.length < 2) return;

		var index = 0;
		var timer = null;

		function go(i) {
			index = (i + slides.length) % slides.length;
			track.style.transform = 'translateX(-' + index * 100 + '%)';
			dots.forEach(function (dot, di) { dot.classList.toggle('is-active', di === index); });
		}

		function play() {
			stop();
			if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
			timer = setInterval(function () { go(index + 1); }, 7000);
		}
		function stop() { if (timer) clearInterval(timer); timer = null; }

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () { go(parseInt(dot.dataset.index, 10)); play(); });
		});
		var prev = $('[data-xin-banner-prev]', root);
		var next = $('[data-xin-banner-next]', root);
		if (prev) prev.addEventListener('click', function () { go(index - 1); play(); });
		if (next) next.addEventListener('click', function () { go(index + 1); play(); });

root.addEventListener('pointerenter', stop);
		root.addEventListener('pointerleave', play);
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) stop(); else play();
		});

var startX = null;
		root.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
		root.addEventListener('touchend', function (e) {
			if (startX === null) return;
			var dx = e.changedTouches[0].clientX - startX;
			if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
			startX = null;
		});

		go(0);
		play();
	}

		function initHero() {
		var root = $('[data-xin-hero]');
		if (!root) return;
		var slides = $$('[data-xin-hero-slide]', root);
		var cards = $$('[data-xin-hero-card]', root);
		if (slides.length < 2) return;

		var index = 0;
		var timer = null;

		function go(i) {
			index = (i + slides.length) % slides.length;
			slides.forEach(function (slide, si) {
				slide.hidden = si !== index;
				if (si === index) {
					slide.style.animation = 'none';
					void slide.offsetWidth;
					slide.style.animation = '';
				}
			});
			cards.forEach(function (card, ci) {
				
				var offset = ci - index;
				if (offset < -Math.floor(cards.length / 2)) offset += cards.length;
				if (offset > Math.floor(cards.length / 2)) offset -= cards.length;
				card.dataset.pos = Math.abs(offset) <= 1 ? String(offset) : 'hidden';
			});
		}

		function play() {
			stop();
			if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
			timer = setInterval(function () { go(index + 1); }, 7000);
		}
		function stop() { if (timer) clearInterval(timer); timer = null; }

		cards.forEach(function (card, ci) {
			card.addEventListener('click', function () { go(ci); play(); });
		});
		root.addEventListener('pointerenter', stop);
		root.addEventListener('pointerleave', play);

		go(0);
		play();
	}

		function initTabs() {
		$$('[data-xin-tabs]').forEach(function (root) {
			var buttons = $$('[data-xin-tab]', root);
			var panels = $$('[data-xin-tabpanel]', root);
			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					buttons.forEach(function (b) {
						b.classList.toggle('active', b === btn);
						b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
					});
					panels.forEach(function (panel) {
						panel.hidden = panel.dataset.xinTabpanel !== btn.dataset.xinTab;
					});
				});
			});
		});
	}

		function initReveal() {
		var items = $$('.xin-reveal');
		if (!items.length) return;
		if (!('IntersectionObserver' in window)) {
			items.forEach(function (el) { el.classList.add('is-in'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					io.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
		items.forEach(function (el) { io.observe(el); });
	}

		function initCounters() {
		var items = $$('[data-xin-count]');
		if (!items.length || !('IntersectionObserver' in window)) {
			items.forEach(function (el) { el.textContent = format(parseInt(el.dataset.xinCount, 10), el.dataset.xinCompact); });
			return;
		}

		function format(value, compact) {
			if (compact && value >= 1000000) return (value / 1000000).toFixed(1).replace('.0', '').replace('.', ',') + 'M';
			if (compact && value >= 1000) return (value / 1000).toFixed(1).replace('.0', '').replace('.', ',') + 'K';
			return value.toLocaleString('ru-RU');
		}

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				var el = entry.target;
				io.unobserve(el);
				var target = parseInt(el.dataset.xinCount, 10) || 0;
				var compact = el.dataset.xinCompact;
				if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
					el.textContent = format(target, compact);
					return;
				}
				var start = performance.now();
				var dur = 1100;
				(function tick(now) {
					var p = Math.min(1, (now - start) / dur);
					
					var eased = 1 - Math.pow(1 - p, 4);
					el.textContent = format(Math.round(target * eased), compact);
					if (p < 1) requestAnimationFrame(tick);
				})(start);
			});
		}, { threshold: 0.4 });

		items.forEach(function (el) { io.observe(el); });
	}

		function favorites() { return read(LS_FAVS, []); }

	function isFav(id) {
		return favorites().some(function (item) { return item.id === id; });
	}

	function toggleFav(data) {
		var list = favorites();
		var at = list.findIndex(function (item) { return item.id === data.id; });
		if (at > -1) {
			list.splice(at, 1);
		} else {
			list.unshift(data);
		}
		write(LS_FAVS, list);
		return at === -1;
	}

	function initFavorites() {
		$$('[data-xin-fav]').forEach(function (btn) {
			var data;
			try { data = JSON.parse(btn.dataset.xinFav); } catch (e) { return; }

			var sync = function () {
				var active = isFav(data.id);
				btn.classList.toggle('is-active', active);
				btn.title = active ? (i18n.added || 'В библиотеке') : (i18n.add || 'В библиотеку');
			};
			sync();

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				toggleFav(data);
				sync();
			});
		});
	}

		function initLibrary() {
		var openers = $$('[data-xin-library-open]');
		if (!openers.length) return;

		var modal = document.createElement('div');
		modal.className = 'xin-search-overlay';
		modal.hidden = true;
		modal.innerHTML = '<div class="xin-search-overlay__inner xin-panel" style="max-height:76vh;overflow:auto">'
			+ '<div class="xin-panel__head"><h2>Моя библиотека</h2>'
			+ '<button type="button" class="xin-iconbtn" data-close aria-label="Закрыть">✕</button></div>'
			+ '<div data-list></div></div>';
		document.body.appendChild(modal);

		function render() {
			var list = favorites();
			var box = $('[data-list]', modal);
			if (!list.length) {
				box.innerHTML = '<p class="xin-library__empty">' + (i18n.empty || 'Здесь пока пусто')
					+ '<br><small>Нажмите на закладку у любой обложки — тайтл появится здесь.</small></p>';
				return;
			}
			box.innerHTML = list.map(function (item) {
				return '<a class="xin-post-row" href="' + item.url + '">'
					+ '<span class="xin-post-row__media" style="aspect-ratio:2/3;width:56px">'
					+ (item.cover ? '<img src="' + item.cover + '" alt="" onerror="this.remove()">' : '')
					+ '</span><span><h3>' + item.title + '</h3></span></a>';
			}).join('');
		}

		function open(e) {
			if (e) e.preventDefault();
			render();
			modal.hidden = false;
			void modal.offsetWidth;
			modal.classList.add('is-open');
			document.body.classList.add('xin-no-scroll');
		}

		function close() {
			modal.classList.remove('is-open');
			modal.hidden = true;
			document.body.classList.remove('xin-no-scroll');
		}

		openers.forEach(function (btn) { btn.addEventListener('click', open); });
		modal.addEventListener('click', function (e) {
			if (e.target === modal || e.target.hasAttribute('data-close')) close();
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
	}

		function initContinue() {
		var section = $('[data-xin-continue]');
		if (!section) return;

		var history = read(LS_HISTORY, []);
		if (!history.length) return;

		var box = $('[data-xin-continue-list]', section);
		box.innerHTML = history.slice(0, 8).map(function (item) {
			var pct = Math.round((item.progress || 0) * 100);
			return '<a class="xin-continue__item" href="' + item.url + '">'
				+ (item.cover ? '<img src="' + item.cover + '" alt="" onerror="this.remove()">' : '<span></span>')
				+ '<span><h4>' + item.novel + '</h4><small>' + item.title + '</small></span>'
				+ '<span class="xin-continue__bar"><i style="width:' + pct + '%"></i></span></a>';
		}).join('');
		section.hidden = false;
	}

		function initLibraryPage() {
		var list = $('[data-xin-lib-list]');
		if (!list) return;

		var favs = favorites();
		var empty = $('[data-xin-lib-empty]');

		if (!favs.length) {
			if (empty) empty.hidden = false;
		} else {
			list.innerHTML = favs.map(function (item) {
				return '<article class="xin-novel">'
					+ '<a class="xin-novel__cover" href="' + item.url + '">'
					+ (item.cover ? '<img src="' + item.cover + '" alt="" onerror="this.remove()">' : '')
					+ '</a>'
					+ '<div class="xin-novel__body"><h3 class="xin-novel__title">'
					+ '<a href="' + item.url + '">' + item.title + '</a></h3></div></article>';
			}).join('');
		}

var cont = $('[data-xin-lib-continue]');
		var box = $('[data-xin-continue-list]', cont || document);
		var history = read(LS_HISTORY, []);
		if (cont && box && history.length) {
			box.innerHTML = history.slice(0, 12).map(function (item) {
				var p = Math.round((item.progress || 0) * 100);
				return '<a class="xin-continue__item" href="' + item.url + '">'
					+ (item.cover ? '<img src="' + item.cover + '" alt="" onerror="this.remove()">' : '<span></span>')
					+ '<span><h4>' + item.novel + '</h4><small>' + item.title + '</small></span>'
					+ '<span class="xin-continue__bar"><i style="width:' + p + '%"></i></span></a>';
			}).join('');
			cont.hidden = false;
		}
	}

		function initEditor() {
		var form = $('[data-xin-chapter-editor]');
		if (!form) return;

		var area = form.querySelector('#xin-ch-content');
		var counter = $('[data-xin-wordcount]', form);
		var note = $('[data-xin-autosave-note]', form);
		if (!area) return;

		var key = 'xin-draft-' + form.dataset.xinChapterEditor + '-' + (form.querySelector('[name="chapter_id"]').value || 'new');

				function getValue() {
			var mce = window.tinymce && window.tinymce.get('xin-ch-content');
			return mce && !mce.isHidden() ? mce.getContent({ format: 'text' }) : area.value;
		}

		function getHtml() {
			var mce = window.tinymce && window.tinymce.get('xin-ch-content');
			return mce && !mce.isHidden() ? mce.getContent() : area.value;
		}

		function count() {
			var text = getValue().replace(/<[^>]+>/g, ' ').trim();
			var words = text ? text.split(/\s+/).length : 0;
			if (counter) counter.textContent = words.toLocaleString();
		}

		var timer = null;
		function touched() {
			count();
			clearTimeout(timer);
			timer = setTimeout(function () {
				try {
					localStorage.setItem(key, getHtml());
					if (note) {
						note.textContent = (i18n.saved || 'draft saved') + ' · '
							+ new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
					}
				} catch (e) {}
			}, 900);
		}

		area.addEventListener('input', touched);

function hookMce() {
			var mce = window.tinymce && window.tinymce.get('xin-ch-content');
			if (!mce) return false;
			mce.on('input keyup change SetContent', touched);

try {
				var draft = localStorage.getItem(key);
				if (draft && !mce.getContent({ format: 'text' }).trim()) mce.setContent(draft);
			} catch (e) {}
			count();
			return true;
		}

		if (!hookMce()) {
			var tries = 0;
			var poll = setInterval(function () {
				if (hookMce() || ++tries > 40) clearInterval(poll);
			}, 150);
		}

		try {
			var draft0 = localStorage.getItem(key);
			if (draft0 && !area.value.trim()) area.value = draft0;
		} catch (e) {}

		form.addEventListener('submit', function () {

if (window.tinymce) window.tinymce.triggerSave();
			try { localStorage.removeItem(key); } catch (e) {}
		});

		count();
	}

	
		function initChapterFilter() {
		var input = $('[data-xin-chapter-search]');
		if (!input) return;
		var items = $$('[data-xin-chapter-item]');
		var empty = $('[data-xin-chapter-empty]');

		input.addEventListener('input', function () {
			var q = input.value.trim().toLowerCase();
			var shown = 0;
			items.forEach(function (item) {
				var hit = !q || item.textContent.toLowerCase().indexOf(q) > -1;
				item.hidden = !hit;
				if (hit) shown++;
			});
			if (empty) empty.hidden = shown > 0;
		});

		var sortBtn = $('[data-xin-chapter-sort]');
		if (sortBtn) {
			sortBtn.addEventListener('click', function () {
				var list = $('[data-xin-chapter-list]');
				var rows = $$('[data-xin-chapter-item]', list);
				rows.reverse().forEach(function (row) { list.appendChild(row); });
				sortBtn.classList.toggle('is-active');
			});
		}
	}

		function initSynopsis() {
		var toggle = $('[data-xin-synopsis-toggle]');
		var body = $('[data-xin-synopsis]');
		if (!toggle || !body) return;

if (body.scrollHeight <= 240) {
			body.classList.remove('is-collapsed');
			toggle.hidden = true;
			return;
		}
		toggle.addEventListener('click', function () {
			var collapsed = body.classList.toggle('is-collapsed');
			toggle.textContent = collapsed ? toggle.dataset.more : toggle.dataset.less;
		});
	}

		function initRate() {
		var root = $('[data-xin-rate]');
		if (!root) return;
		var id = parseInt(root.dataset.xinRate, 10);
		var voted = read('xin-rated-' + id, false);

		$$('[data-value]', root).forEach(function (star) {
			star.addEventListener('click', function () {
				if (voted) return;
				var value = parseInt(star.dataset.value, 10);
				fetch(window.XIN.restUrl + 'rate', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.XIN.nonce },
					body: JSON.stringify({ id: id, value: value })
				})
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (!data || !data.rating) return;
						write('xin-rated-' + id, true);
						voted = true;
						var out = $('[data-xin-rate-value]');
						if (out) out.textContent = String(data.rating).replace('.', ',');
						var count = $('[data-xin-rate-count]');
						if (count) count.textContent = data.count;
						root.classList.add('is-voted');
					})
					.catch(function () {});
			});
		});
	}


	function initDownload() {
		var btn = document.querySelector('[data-xin-dl]');
		var menu = document.querySelector('[data-xin-dl-menu]');
		if (!btn || !menu) return;

		function close() {
			menu.hidden = true;
			menu.style.transform = '';
			btn.setAttribute('aria-expanded', 'false');
		}

		// The button rides in a wrapping row, so it can sit anywhere across the
		// width. Nudge the open menu back inside the viewport instead of letting
		// it run off an edge.
		function place() {
			menu.style.transform = '';
			var pad = 8;
			var box = menu.getBoundingClientRect();
			var vw = document.documentElement.clientWidth;
			var shift = 0;
			if (box.right > vw - pad) shift = vw - pad - box.right;
			if (box.left + shift < pad) shift = pad - box.left;
			if (shift) menu.style.transform = 'translateX(' + Math.round(shift) + 'px)';
		}

		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			menu.hidden = !menu.hidden;
			btn.setAttribute('aria-expanded', menu.hidden ? 'false' : 'true');
			if (!menu.hidden) place();
		});
		window.addEventListener('resize', function () {
			if (!menu.hidden) place();
		});
		document.addEventListener('click', function (e) {
			if (!menu.hidden && !menu.contains(e.target)) close();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') close();
		});
	}

	function initTalk() {
		var talk = document.getElementById('xin-talk');
		if (!talk) return;

		talk.addEventListener('click', function (e) {
			var spoiler = e.target.closest('[data-xin-spoiler]');
			if (spoiler) {
				spoiler.classList.toggle('is-open');
				return;
			}

			var like = e.target.closest('[data-xin-like]');
			if (like) {
				var id = parseInt(like.dataset.xinLike, 10);
				like.disabled = true;
				fetch(window.XIN.restUrl + 'like', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.XIN.nonce },
					body: JSON.stringify({ id: id })
				})
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (!data || typeof data.likes === 'undefined') return;
						var out = like.querySelector('span');
						if (out) out.textContent = data.likes;
						like.classList.toggle('is-on', !!data.mine);
					})
					.catch(function () {})
					.then(function () { like.disabled = false; });
				return;
			}

			var reply = e.target.closest('[data-xin-reply]');
			if (reply) {
				var field = talk.querySelector('[data-xin-reply-field]');
				var note = talk.querySelector('[data-xin-reply-note]');
				var area = talk.querySelector('.xin-talk__form textarea');
				if (!field) return;
				field.value = reply.dataset.xinReply;
				if (note) {
					note.hidden = false;
					note.querySelector('span').textContent = window.XIN.i18n.replyingTo.replace('%s', reply.dataset.name || '');
				}
				if (area) area.focus();
				return;
			}

			if (e.target.closest('[data-xin-reply-cancel]')) {
				var f = talk.querySelector('[data-xin-reply-field]');
				var n = talk.querySelector('[data-xin-reply-note]');
				if (f) f.value = '0';
				if (n) n.hidden = true;
			}
		});

		talk.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && e.target.matches('[data-xin-spoiler]')) {
				e.target.classList.toggle('is-open');
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		initTheme();
		initScroll();
		initDownload();
		initTalk();
		initOverlays();
		initBanner();
		initHero();
		initTabs();
		initReveal();
		initCounters();
		initFavorites();
		initLibrary();
		initContinue();
		initLibraryPage();
		initEditor();
		initChapterFilter();
		initSynopsis();
		initRate();
	});

window.xinHistory = {
		push: function (entry) {
			var list = read(LS_HISTORY, []).filter(function (item) { return item.novelId !== entry.novelId; });
			list.unshift(entry);
			write(LS_HISTORY, list.slice(0, 20));
		},
		read: function () { return read(LS_HISTORY, []); }
	};
})();
