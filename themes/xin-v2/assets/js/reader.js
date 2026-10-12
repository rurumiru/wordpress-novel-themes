/**
 * XIN-V2 — читалка.
 *
 * Настройки чтения (размер, ширина, бумага, иммерсив) живут в localStorage и
 * применяются к <html>: так они переживают переход к следующей главе и не
 * мигают при загрузке — значения выставляет встроенный скрипт в <head>.
 *
 * Остальное — бесконечная прокрутка, полоса прогресса, закладки по абзацам,
 * цитаты и озвучка.
 */
(function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;
	var data = window.xvReader || null;

	var SIZES = [15, 16, 17, 18, 19, 21, 23, 26];
	var WIDTHS = ['narrow', '', 'wide', 'full'];
	var PAPERS = ['', 'sepia'];
	var memory = Object.create(null);

	function read(key, fallback) {
		try {
			var value = localStorage.getItem(key);
			return value === null ? fallback : value;
		} catch (e) {
			return Object.prototype.hasOwnProperty.call(memory, key) ? memory[key] : fallback;
		}
	}

	function write(key, value) {
		memory[key] = value;
		try {
			if (value === '' || value === null) {
				localStorage.removeItem(key);
			} else {
				localStorage.setItem(key, value);
			}
		} catch (e) {}
	}

	function say(key) {
		return (data && data.i18n && data.i18n[key]) || '';
	}

	function post(route, body) {
		return fetch(window.xvData.root + route, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.xvData.nonce },
			body: JSON.stringify(body)
		}).then(function (response) {
			return response.ok ? response.json() : Promise.reject(new Error(String(response.status)));
		});
	}

	/* ── Настройки чтения ───────────────────────────────────── */

	function fontSize(step) {
		var current = parseInt(read('xv-font-size', '18'), 10);
		var index = SIZES.indexOf(current);

		if (index === -1) { index = SIZES.indexOf(18); }

		index = Math.max(0, Math.min(SIZES.length - 1, index + step));

		root.style.setProperty('--reader-size', SIZES[index] + 'px');
		write('xv-font-size', String(SIZES[index]));
		syncPreferenceButtons();
	}

	function cycle(list, key, attribute) {
		var current = read(key, '');
		var next = list[(list.indexOf(current) + 1) % list.length];

		if (next === '') {
			root.removeAttribute(attribute);
		} else {
			root.setAttribute(attribute, next);
		}

		write(key, next);
		syncPreferenceButtons();
	}

	function syncPreferenceButtons() {
		var smaller = doc.querySelector('[data-xv-font="-"]');
		var larger = doc.querySelector('[data-xv-font="+"]');
		var size = parseInt(read('xv-font-size', '18'), 10);
		var index = SIZES.indexOf(size);
		if (index < 0) { index = SIZES.indexOf(18); }
		if (smaller) { smaller.disabled = index <= 0; }
		if (larger) { larger.disabled = index >= SIZES.length - 1; }

		var paper = doc.querySelector('[data-xv-paper]');
		if (paper) {
			var sepia = read('xv-paper', '') === 'sepia';
			paper.setAttribute('aria-pressed', sepia ? 'true' : 'false');
			paper.classList.toggle('is-on', sepia);
		}

		var width = doc.querySelector('[data-xv-width]');
		if (width) {
			var wider = !!read('xv-reader-width', '');
			width.setAttribute('aria-pressed', wider ? 'true' : 'false');
			width.classList.toggle('is-on', wider);
		}

		var immersive = doc.querySelector('[data-xv-immersive]');
		var shell = doc.querySelector('[data-xv-reader]');
		if (immersive && shell) {
			var on = shell.classList.contains('no-side');
			immersive.setAttribute('aria-pressed', on ? 'true' : 'false');
			immersive.classList.toggle('is-on', on);
		}
	}

	if (read('xv-immersive', '') === '1') {
		var shell = doc.querySelector('[data-xv-reader]');
		if (shell) { shell.classList.add('no-side'); }
	}

	syncPreferenceButtons();

	/* ── Полоса прогресса ───────────────────────────────────── */

	var bar = doc.querySelector('[data-xv-progress] span');
	var body = doc.querySelector('[data-xv-body]');

	function paintProgress() {
		if (!bar || !body) { return; }

		var box = body.getBoundingClientRect();
		var height = box.height - window.innerHeight;
		var done = height > 0 ? (-box.top) / height : 1;

		bar.style.transform = 'scaleX(' + Math.max(0, Math.min(1, done)) + ')';
	}

	/* ── Прогресс чтения на сервере ─────────────────────────── */

	var progressSent = {};
	var viewSent = {};

	function recordView(chapterId) {
		if (viewSent[chapterId]) { return; }

		viewSent[chapterId] = true;
		post('view', { chapter: chapterId }).catch(function () { delete viewSent[chapterId]; });
	}

	function keepProgress(chapterId) {
		if (!window.xvData || !window.xvData.loggedIn || progressSent[chapterId]) { return; }

		progressSent[chapterId] = true;
		post('progress', { chapter: chapterId }).catch(function () {});
	}

	/* ── Бесконечная прокрутка ──────────────────────────────── */

	var loading = false;
	var retryNeeded = false;
	var nextId = data ? data.next : 0;
	var tail = doc.querySelector('[data-xv-tail]');
	var pendingNavigation = '';

	/**
	 * Следующая глава дописывается в ту же колонку, а адрес в строке
	 * браузера меняется на её: читатель закрывает вкладку там, где читал, и
	 * возвращается туда же.
	 */
	function loadNext() {
		if (loading || retryNeeded || !nextId || !data || !data.endless || !body) { return; }

		loading = true;
		retryNeeded = false;

		if (tail) { tail.textContent = say('loading'); }

		fetch(window.xvData.root + 'chapter?id=' + nextId, { headers: { 'X-WP-Nonce': window.xvData.nonce } })
			.then(function (response) {
				return response.ok ? response.json() : Promise.reject(new Error(String(response.status)));
			})
			.then(function (chapter) {
				if (chapter.locked) {
					if (tail) { tail.textContent = say('locked'); }
					nextId = 0;
					if (pendingNavigation) {
						var lockedUrl = pendingNavigation;
						pendingNavigation = '';
						location.href = lockedUrl;
					}
					return;
				}

				var block = doc.createElement('section');
				block.className = 'xv-chapter-more';
				block.setAttribute('data-xv-chapter', String(chapter.id));
				block.setAttribute('data-xv-number', chapter.number === undefined ? '' : String(chapter.number));
				block.setAttribute('data-xv-url', chapter.url);
				block.setAttribute('data-xv-prev-url', chapter.prevUrl || '');
				block.setAttribute('data-xv-prev-title-value', chapter.prevTitle || '');
				block.setAttribute('data-xv-next-url', chapter.nextUrl || '');
				block.setAttribute('data-xv-next-title-value', chapter.nextTitle || '');

				var head = doc.createElement('header');
				head.className = 'xv-chapter-more-head';

				if (chapter.number !== '') {
					var kicker = doc.createElement('p');
					kicker.className = 'xv-chapter-kicker';
					kicker.textContent = (window.xvData.i18n.chapter || 'Глава %s').replace('%s', chapter.number);
					head.appendChild(kicker);
				}

				var title = doc.createElement('h2');
				title.className = 'xv-chapter-title';
				title.textContent = chapter.title;
				head.appendChild(title);

				block.appendChild(head);

				var text = doc.createElement('div');
				text.className = 'xv-chapter-body';
				text.innerHTML = chapter.html;
				block.appendChild(text);

				body.appendChild(block);
				if (Array.isArray(chapter.marks)) {
					chapter.marks.forEach(function (paragraph) { marked[chapter.id + ':' + paragraph] = true; });
					paintMarks();
				}

				nextId = chapter.next;

				if (tail) { tail.textContent = nextId ? '' : say('last'); }

				if (pendingNavigation) {
					var requested = pendingNavigation;
					var destination = chapterBlockForUrl(requested);
					if (destination) {
						pendingNavigation = '';
						history.pushState({ xvReader: true }, '', requested);
						lastUrl = location.href;
						window.requestAnimationFrame(function () { destination.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
					}
				}
			})
			.catch(function () {
				retryNeeded = true;

				if (tail) {
					var retry = doc.createElement('button');
					retry.type = 'button';
					retry.className = 'xv-reader-retry';
					retry.textContent = say('failed');
					retry.addEventListener('click', function () {
						retryNeeded = false;
						tail.textContent = '';
						loadNext();
					});
					tail.replaceChildren(retry);
				}
			})
			.finally(function () {
				loading = false;
			});
	}

	/**
	 * Какая глава сейчас перед глазами. От неё зависят и адрес в строке
	 * браузера, и отметка «продолжить».
	 */
	function currentChapter() {
		var blocks = doc.querySelectorAll('[data-xv-chapter]');
		var seen = null;

		for (var i = 0; i < blocks.length; i++) {
			if (blocks[i].getBoundingClientRect().top < window.innerHeight * 0.4) {
				seen = blocks[i];
			}
		}

		return seen;
	}

	var lastUrl = location.href;

	function chapterBlockForUrl(url) {
		if (!url) { return null; }

		var target;
		try { target = new URL(url, location.href).href; } catch (e) { target = url; }

		var blocks = doc.querySelectorAll('[data-xv-chapter]');
		for (var i = 0; i < blocks.length; i++) {
			var blockUrl = blocks[i].getAttribute('data-xv-url');
			try { blockUrl = new URL(blockUrl, location.href).href; } catch (e) {}
			if (blockUrl === target) { return blocks[i]; }
		}

		return null;
	}

	function syncReaderNavigation(block) {
		if (!block) { return; }

		var number = block.getAttribute('data-xv-number');
		var heading = doc.querySelector('.xv-pia-reader-number');
		if (heading && number !== null) {
			heading.textContent = number === '' ? '' : ((window.xvData && window.xvData.i18n.chapter) || 'Глава %s').replace('%s', number);
		}

		['prev', 'next'].forEach(function (direction) {
			var url = block.getAttribute('data-xv-' + direction + '-url') || '';
			var title = block.getAttribute('data-xv-' + direction + '-title-value') || '';
			var anchors = doc.querySelectorAll('[data-xv-' + direction + ']');
			var empty = doc.querySelectorAll('[data-xv-' + direction + '-empty]');
			var titles = doc.querySelectorAll('[data-xv-' + direction + '-title]');

			anchors.forEach(function (anchor) {
				anchor.hidden = !url;
				if (url) {
					anchor.href = url;
					anchor.removeAttribute('aria-hidden');
				} else {
					anchor.setAttribute('aria-hidden', 'true');
				}
			});
			empty.forEach(function (placeholder) { placeholder.hidden = !!url; });
			titles.forEach(function (label) { label.textContent = title; });
		});
	}

	function scrollToChapter(block, pushHistory) {
		if (!block) { return; }

		var url = block.getAttribute('data-xv-url');
		if (pushHistory && url && url !== location.href) {
			history.pushState({ xvReader: true }, '', url);
		}
		lastUrl = location.href;
		block.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}

	doc.addEventListener('click', function (event) {
		var link = event.target.closest('[data-xv-prev], [data-xv-next]');
		if (!link || !data || !data.endless || !body || link.hidden) { return; }

		if (link.hasAttribute('data-xv-prev')) {
			var previous = chapterBlockForUrl(link.href);
			if (previous) {
				event.preventDefault();
				scrollToChapter(previous, true);
			}
			return;
		}

		var existing = chapterBlockForUrl(link.href);
		if (existing) {
			event.preventDefault();
			scrollToChapter(existing, true);
			return;
		}

		if (!nextId) { return; }
		event.preventDefault();
		pendingNavigation = link.href;
		loadNext();
	});

	window.addEventListener('popstate', function () {
		var destination = chapterBlockForUrl(location.href);
		if (destination) { scrollToChapter(destination, false); }
	});

	function syncUrl() {
		var block = currentChapter();
		if (!block) { return; }

		var url = block.getAttribute('data-xv-url');
		var id = parseInt(block.getAttribute('data-xv-chapter'), 10);
		syncReaderNavigation(block);

		if (url && url !== lastUrl) {
			lastUrl = url;
			history.replaceState(null, '', url);
		}

		if (id) {
			recordView(id);
			keepProgress(id);
		}
	}

	/* ── Один обработчик прокрутки на всё ───────────────────── */

	var ticking = false;

	function onScroll() {
		if (ticking) { return; }

		ticking = true;

		window.requestAnimationFrame(function () {
			ticking = false;

			paintProgress();
			syncUrl();

			// Грузим заранее, за два экрана до конца: иначе читатель успевает
			// упереться в пустоту.
			if (data && data.endless && body && body.getBoundingClientRect().bottom < window.innerHeight * 3) {
				loadNext();
			}
		});
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll, { passive: true });
	onScroll();

	/* ── Закладки и цитаты по абзацам ───────────────────────── */

	var marked = {};

	if (data && data.marks) {
		data.marks.forEach(function (p) { marked[data.chapter + ':' + p] = true; });
	}

	function paintMarks() {
		if (!body) { return; }

		Object.keys(marked).forEach(function (key) {
			if (!marked[key]) { return; }

			var parts = key.split(':');
			var block = body.querySelector('[data-xv-chapter="' + parts[0] + '"]') || body;
			var p = block.querySelector('[data-xv-p="' + parts[1] + '"]');

			if (p) { p.classList.add('is-marked'); }
		});
	}

	paintMarks();

	var tools = null;

	function closeTools() {
		if (tools) {
			tools.remove();
			tools = null;
		}
	}

	/**
	 * Кнопки появляются у абзаца по клику, а не висят в разметке постоянно:
	 * иначе текст главы обрастает интерфейсом и перестаёт читаться.
	 */
	function openTools(paragraph, chapterId) {
		closeTools();

		var key = chapterId + ':' + paragraph.getAttribute('data-xv-p');
		var on = !!marked[key];

		tools = doc.createElement('div');
		tools.className = 'xv-p-tools';

		var mark = doc.createElement('button');
		mark.type = 'button';
		mark.textContent = on ? say('unmark') : say('mark');

		mark.addEventListener('click', function () {
			if (!window.xvData.loggedIn) {
				location.href = window.xvData.login || window.xvData.home;
				return;
			}

			post('mark', {
				chapter: chapterId,
				paragraph: parseInt(paragraph.getAttribute('data-xv-p'), 10)
			}).then(function (result) {
				marked[key] = result.on;
				paragraph.classList.toggle('is-marked', result.on);
				closeTools();
			}).catch(function () { closeTools(); });
		});

		var copy = doc.createElement('button');
		copy.type = 'button';
		copy.textContent = say('copy');

		copy.addEventListener('click', function () {
			var quote = paragraph.textContent.trim();
			var where = doc.querySelector('[data-xv-chapter="' + chapterId + '"]');
			var url = (where && where.getAttribute('data-xv-url')) || location.href;
			var line = '«' + quote + '»\n' + url + '#' + paragraph.id;

			if (navigator.clipboard) {
				navigator.clipboard.writeText(line).then(function () {
					copy.textContent = say('copied');
				}).catch(function () { fallbackCopy(line, copy); });
			} else {
				fallbackCopy(line, copy);
			}
		});

		tools.appendChild(mark);
		tools.appendChild(copy);
		paragraph.appendChild(tools);
	}

	function fallbackCopy(value, button) {
		var field = doc.createElement('textarea');
		field.value = value;
		field.setAttribute('readonly', '');
		field.style.position = 'fixed';
		field.style.opacity = '0';
		doc.body.appendChild(field);
		field.select();

		try {
			if (doc.execCommand('copy')) { button.textContent = say('copied'); }
		} catch (e) {}

		field.remove();
	}

	if (body) {
		body.addEventListener('click', function (event) {
			if (event.target.closest('.xv-p-tools') || event.target.closest('a')) { return; }

			var paragraph = event.target.closest('[data-xv-p]');

			if (!paragraph) {
				closeTools();
				return;
			}

			var block = paragraph.closest('[data-xv-chapter]');
			openTools(paragraph, parseInt((block || body).getAttribute('data-xv-chapter'), 10));
		});
	}

	/* ── Озвучка ────────────────────────────────────────────── */

	var speaking = false;

	/**
	 * Читаем по абзацу, а не главу целиком: длинную реплику браузеры
	 * обрывают на середине, и остановиться в нужном месте становится нельзя.
	 */
	function speak(button) {
		if (!('speechSynthesis' in window)) {
			button.title = say('noVoice');
			button.setAttribute('aria-label', say('noVoice'));
			button.disabled = true;
			return;
		}

		if (speaking) {
			window.speechSynthesis.cancel();
			speaking = false;
			button.classList.remove('is-on');
			button.setAttribute('aria-pressed', 'false');
			button.title = say('speakOn');
			button.setAttribute('aria-label', say('speakOn'));
			doc.querySelectorAll('.is-speaking').forEach(function (p) { p.classList.remove('is-speaking'); });
			return;
		}

		var block = currentChapter() || body;
		var list = Array.prototype.slice.call((block || doc).querySelectorAll('[data-xv-p]'));

		// Начинаем с того абзаца, который читатель видит, а не с начала главы.
		var from = list.findIndex(function (p) {
			return p.getBoundingClientRect().bottom > 0;
		});

		list = list.slice(Math.max(0, from));

		if (!list.length) { return; }

		speaking = true;
		button.classList.add('is-on');
		button.setAttribute('aria-pressed', 'true');
		button.title = say('speakOff');
		button.setAttribute('aria-label', say('speakOff'));

		var lang = root.getAttribute('lang') || 'ru-RU';

		list.forEach(function (paragraph) {
			var line = new SpeechSynthesisUtterance(paragraph.textContent.trim());
			line.lang = lang;

			line.onstart = function () {
				paragraph.classList.add('is-speaking');
				paragraph.scrollIntoView({ block: 'center', behavior: 'smooth' });
			};

			line.onend = function () {
				paragraph.classList.remove('is-speaking');
			};

			window.speechSynthesis.speak(line);
		});

		// Последняя реплика закончилась — кнопка должна вернуться в исходное.
		var watch = setInterval(function () {
			if (!window.speechSynthesis.speaking && !window.speechSynthesis.pending) {
				clearInterval(watch);
				speaking = false;
				button.classList.remove('is-on');
				button.setAttribute('aria-pressed', 'false');
				button.title = say('speakOn');
				button.setAttribute('aria-label', say('speakOn'));
			}
		}, 700);
	}

	// Уход со страницы посреди чтения вслух оставлял бы голос говорить
	// в пустоту — браузеры держат синтез на уровне вкладки.
	window.addEventListener('beforeunload', function () {
		if (speaking && 'speechSynthesis' in window) { window.speechSynthesis.cancel(); }
	});

	/* ── Догрузка оглавления ────────────────────────────────── */

	var tocLoading = false;

	function loadToc(direction, button) {
		var nav = doc.querySelector('[data-xv-toc]');
		if (!nav || tocLoading) { return; }

		var novel = parseInt(nav.getAttribute('data-xv-toc'), 10);
		var offset = parseInt(nav.getAttribute('data-xv-toc-offset'), 10) || 0;
		var total = parseInt(nav.getAttribute('data-xv-toc-total'), 10) || 0;
		var shown = nav.querySelectorAll('[data-xv-toc-pos]').length;
		var step = 100;

		var from = direction === 'up' ? Math.max(0, offset - step) : offset + shown;
		var length = direction === 'up' ? offset - from : step;

		if (length < 1 || from >= total) { return; }

		tocLoading = true;
		if (button) { button.disabled = true; }
		nav.setAttribute('aria-busy', 'true');

		fetch(window.xvData.root + 'toc?novel=' + novel + '&offset=' + from + '&length=' + length + '&current=' + (data ? data.chapter : 0))
			.then(function (response) { return response.json(); })
			.then(function (result) {
				var holder = doc.createElement('div');
				holder.innerHTML = result.html;

				var links = Array.prototype.slice.call(holder.children);

				if (direction === 'up') {
					var anchor = nav.querySelector('[data-xv-toc-up]');
					var fragment = doc.createDocumentFragment();
					links.forEach(function (link) { fragment.appendChild(link); });
					nav.insertBefore(fragment, anchor.nextSibling);
					nav.setAttribute('data-xv-toc-offset', String(from));

					if (from === 0) { anchor.remove(); }
				} else {
					var down = nav.querySelector('[data-xv-toc-down]');
					links.forEach(function (link) { nav.insertBefore(link, down); });

					if (from + links.length >= total) { down.remove(); }
				}
			})
			.catch(function () {})
			.finally(function () {
				tocLoading = false;
				nav.removeAttribute('aria-busy');
				if (button && button.isConnected) { button.disabled = false; }
			});
	}

	/* ── Клики по панели ────────────────────────────────────── */

	doc.addEventListener('click', function (event) {
		var font = event.target.closest('[data-xv-font]');

		if (font) {
			fontSize(font.getAttribute('data-xv-font') === '+' ? 1 : -1);
			return;
		}

		if (event.target.closest('[data-xv-width]')) {
			cycle(WIDTHS, 'xv-reader-width', 'data-reader-width');
			return;
		}

		if (event.target.closest('[data-xv-paper]')) {
			cycle(PAPERS, 'xv-paper', 'data-paper');
			return;
		}

		var speaker = event.target.closest('[data-xv-speak]');

		if (speaker) {
			speak(speaker);
			return;
		}

		if (event.target.closest('[data-xv-immersive]')) {
			var reader = doc.querySelector('[data-xv-reader]');

			if (reader) {
				var on = reader.classList.toggle('no-side');
				write('xv-immersive', on ? '1' : '');
				syncPreferenceButtons();
			}

			return;
		}

		if (event.target.closest('[data-xv-top]')) {
			window.scrollTo({ top: 0, behavior: 'smooth' });
			return;
		}

		if (event.target.closest('[data-xv-toc-up]')) {
			loadToc('up', event.target.closest('[data-xv-toc-up]'));
			return;
		}

		if (event.target.closest('[data-xv-toc-down]')) {
			loadToc('down', event.target.closest('[data-xv-toc-down]'));
		}
	});

	/* ── Клавиши ────────────────────────────────────────────── */

	doc.addEventListener('keydown', function (event) {
		var tag = (event.target.tagName || '').toLowerCase();

		if (tag === 'input' || tag === 'textarea' || event.target.isContentEditable) { return; }
		if (event.metaKey || event.ctrlKey || event.altKey) { return; }

		if (event.key === 'ArrowLeft') {
			var prev = doc.querySelector('[data-xv-prev]:not([hidden])');
			if (prev) { prev.click(); }
			return;
		}

		if (event.key === 'ArrowRight') {
			var next = doc.querySelector('[data-xv-next]:not([hidden])');
			if (next) { next.click(); }
			return;
		}

		if (event.key === '+' || event.key === '=') { fontSize(1); }
		if (event.key === '-') { fontSize(-1); }
		if (event.key === 'Escape') { closeTools(); }
	});
})();
