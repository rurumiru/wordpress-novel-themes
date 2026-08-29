(function () {
	'use strict';

	var LS_PREFS = 'xin-reader';
	var root = document.querySelector('[data-xin-reader]');
	if (!root) return;

	var defaults = { size: 19, height: 1.9, width: 720, font: 'serif', paper: 'default' };
	if (window.XIN && window.XIN.read) {
		defaults = {
			size: parseInt(window.XIN.read.size, 10) || defaults.size,
			height: parseFloat(window.XIN.read.height) || defaults.height,
			width: parseInt(window.XIN.read.width, 10) || defaults.width,
			font: window.XIN.read.font || defaults.font,
			paper: window.XIN.read.paper || defaults.paper
		};
	}
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
	var sheets = $$('[data-xin-rd-sheet]');

	function closePanels() {
		sheets.forEach(function (sheet) { sheet.classList.remove('is-open'); });
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

	window.xinReader = { open: openPanel, close: closePanels };

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

	(function paragraphTools() {
		if (!text) return;
		var tools = document.querySelector('[data-xin-ptools]');
		if (!tools) return;

		var i18n = (window.XIN && window.XIN.i18n) || {};
		var loggedIn = !!(window.XIN && window.XIN.loggedIn);
		var chapterId = String(meta.chapterId || '0');
		var selected = null;
		var clickAt = 0;
		var toastTimer = 0;
		var jumpBtn = document.querySelector('[data-xin-jump-bm]');
		var suggest = document.querySelector('[data-xin-suggest]');
		var ttsBar = document.querySelector('[data-xin-tts]');
		var synth = window.speechSynthesis || null;
		var utter = null;
		var ttsQueue = [];
		var ttsCurrent = null;
		var ttsPausedAt = -1;
		var ttsLang = (text.getAttribute('lang') || document.documentElement.lang || 'en').replace('_', '-');
		var ttsVoices = [];
		var ttsStudioOpen = false;
		var ttsPrefs = { voiceURI: '', rate: 1, pitch: 1, volume: 1, localOnly: true };
		var BM = 'xin-para-bm';
		var KIT = '.xin-pkit, .xin-suggest, .xin-tts';

		try { Object.assign(ttsPrefs, JSON.parse(localStorage.getItem('xin-tts') || 'null') || {}); } catch (e) {}

		function store(key, val) {
			try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) {}
		}
		function load(key, fallback) {
			try { return JSON.parse(localStorage.getItem(key) || 'null') || fallback; } catch (e) { return fallback; }
		}
		function qs(sel, rootEl) { return (rootEl || document).querySelector(sel); }
		function qsa(sel, rootEl) { return Array.prototype.slice.call((rootEl || document).querySelectorAll(sel)); }
		function escHtml(s) {
			return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}
		function toast(msg) {
			if (!msg) return;
			var el = qs('.xin-ptools-toast') || document.body.appendChild(Object.assign(document.createElement('div'), { className: 'xin-ptools-toast' }));
			el.textContent = msg;
			el.hidden = false;
			clearTimeout(toastTimer);
			toastTimer = setTimeout(function () { el.hidden = true; }, 2400);
		}
		function paraText(p) {
			function walk(node, acc) {
				if (!node) return acc;
				if (node.nodeType === 3) return acc + node.textContent.replace(/\r?\n/g, ' ');
				if (node.nodeType !== 1) return acc;
				if (node !== p && node.closest && node.closest(KIT)) return acc;
				if (/^(BUTTON|SVG|TEXTAREA|INPUT|SELECT)$/.test(node.tagName)) return acc;
				if (node.tagName === 'BR') return acc + ' ';
				for (var i = 0; i < node.childNodes.length; i++) acc = walk(node.childNodes[i], acc);
				return acc;
			}
			return walk(p, '').replace(/\s+/g, ' ').trim();
		}
		function readBm() { return load(BM, {}); }
		function syncKit() {
			var rec = readBm()[chapterId];
			var pin = qs('[data-xin-ptool="bookmark"]', tools);
			if (pin) pin.classList.toggle('is-on', !!(selected && rec && String(rec.id) === selected.getAttribute('data-paragraph-id')));
			qsa('[data-xin-bm-color]', tools).forEach(function (dot) {
				dot.classList.toggle('is-on', !!(rec && selected && String(rec.id) === selected.getAttribute('data-paragraph-id') && dot.getAttribute('data-xin-bm-color') === (rec.color || 'default')));
			});
		}
		function paintBookmarks() {
			var rec = readBm()[chapterId];
			qsa('p[data-paragraph-id]', text).forEach(function (p) {
				var on = !!(rec && String(p.getAttribute('data-paragraph-id')) === String(rec.id));
				p.classList.toggle('is-bookmarked', on);
				if (on) p.setAttribute('data-bm-color', rec.color || 'default');
				else p.removeAttribute('data-bm-color');
			});
			if (jumpBtn) jumpBtn.hidden = !rec;
			syncKit();
		}
		function closeTools() {
			if (selected) selected.classList.remove('is-selected');
			selected = null;
			tools.hidden = true;
			if (tools.parentNode !== text.parentNode) text.parentNode.appendChild(tools);
		}
		function openTools(p) {
			if (selected === p) return closeTools();
			if (selected) selected.classList.remove('is-selected');
			selected = p;
			p.classList.add('is-selected');
			p.appendChild(tools);
			tools.hidden = false;
			tools.classList.remove('is-above', 'is-end');
			syncKit();
			var v = currentVoice();
			setVoiceName(v ? v.name : '');
			requestAnimationFrame(function () {
				var r = tools.getBoundingClientRect();
				if (r.bottom > innerHeight - 80) tools.classList.add('is-above');
				if (r.right > innerWidth - 12) tools.classList.add('is-end');
			});
		}
		function copy(str, okMsg) {
			function done() { toast(okMsg || i18n.copied); }
			if (navigator.clipboard && isSecureContext && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(str).then(done).catch(function () { fallback(str); done(); });
			} else { fallback(str); done(); }
			function fallback(s) {
				var ta = document.createElement('textarea');
				ta.value = s;
				ta.setAttribute('readonly', '');
				ta.style.cssText = 'position:fixed;left:0;top:0;width:1px;height:1px;opacity:0';
				document.body.appendChild(ta);
				ta.select();
				try { document.execCommand('copy'); } catch (err) { window.prompt('', s); }
				document.body.removeChild(ta);
			}
		}
		function appendTalk(chunk) {
			var box = qs('[data-xin-talk-form] textarea[name="comment"]');
			if (!box) {
				copy(chunk, loggedIn ? i18n.copied : i18n.loginTalk);
				return false;
			}
			box.value = (box.value ? box.value.replace(/\s+$/, '') + '\n' : '') + chunk + '\n';
			box.style.height = 'auto';
			box.style.height = Math.min(box.scrollHeight, 280) + 'px';
			box.focus();
			box.scrollIntoView({ behavior: 'smooth', block: 'center' });
			return true;
		}
		function quotePayload(p) {
			var full = paraText(p);
			var sel = (window.getSelection ? String(window.getSelection()) : '').replace(/\s+/g, ' ').trim();
			var body = full;
			if (full.length > 16 && sel.replace(/\s/g, '')) {
				var t = Math.ceil(sel.length * 0.25);
				var ell = i18n.quoteEllipsis || '…';
				body = (sel.indexOf(full.substring(0, t + 1)) === 0 ? '' : ell) + sel + (t && sel.slice(-t) === full.slice(-t) ? '' : ell);
			}
			return body + ' [anchor]' + p.id + '[/anchor]';
		}
		function prettyDiff(a, b) {
			var at = a.split(/(\s+)/);
			var bt = b.split(/(\s+)/);
			var n = at.length;
			var m = bt.length;
			var dp = [];
			var i, j;
			for (i = 0; i <= n; i++) dp[i] = new Uint16Array(m + 1);
			for (i = 1; i <= n; i++) for (j = 1; j <= m; j++) {
				dp[i][j] = at[i - 1] === bt[j - 1] ? dp[i - 1][j - 1] + 1 : Math.max(dp[i - 1][j], dp[i][j - 1]);
			}
			var out = [];
			i = n; j = m;
			while (i > 0 && j > 0) {
				if (at[i - 1] === bt[j - 1]) { out.push({ op: 0, t: at[i - 1] }); i--; j--; }
				else if (dp[i - 1][j] >= dp[i][j - 1]) out.push({ op: -1, t: at[--i] });
				else out.push({ op: 1, t: bt[--j] });
			}
			while (i > 0) out.push({ op: -1, t: at[--i] });
			while (j > 0) out.push({ op: 1, t: bt[--j] });
			return out.reverse().map(function (d) {
				var t = escHtml(d.t);
				return d.op === 1 ? '<ins>' + t + '</ins>' : d.op === -1 ? '<del>' + t + '</del>' : t;
			}).join('');
		}
		function htmlToBb(html) {
			return html.replace(/<br\s*\/?>/gi, '\n')
				.replace(/<ins>/gi, '[ins]').replace(/<\/ins>/gi, '[/ins]')
				.replace(/<del>/gi, '[del]').replace(/<\/del>/gi, '[/del]')
				.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		}

		function currentVoice() {
			if (!ttsVoices.length) return null;
			var exact = ttsPrefs.voiceURI && ttsVoices.filter(function (v) { return v.voiceURI === ttsPrefs.voiceURI; })[0];
			if (exact) return exact;
			var lang = (ttsLang || 'en').toLowerCase();
			return ttsVoices.slice().sort(function (a, b) {
				function score(v) {
					var vl = (v.lang || '').toLowerCase();
					return (v.localService ? 8 : 0) + (vl === lang ? 6 : vl.indexOf(lang.split('-')[0]) === 0 ? 4 : 0) + (/en-us|en_us/.test(vl) ? 1 : 0);
				}
				return score(b) - score(a);
			})[0] || null;
		}
		function setVoiceName(name) {
			qsa('[data-xin-tts-voice-name]').forEach(function (el) {
				el.textContent = name || i18n.chooseVoice || 'Choose a voice';
			});
		}
		function dressUtter(u) {
			var v = currentVoice();
			if (v) { u.voice = v; u.lang = v.lang || ttsLang; }
			else u.lang = ttsLang;
			u.rate = +ttsPrefs.rate || 1;
			u.pitch = +ttsPrefs.pitch || 1;
			u.volume = +ttsPrefs.volume || 1;
			return u;
		}
		function setDial(sel, val, text) {
			var el = ttsBar && qs(sel, ttsBar);
			if (el) el[el.tagName === 'OUTPUT' ? 'textContent' : 'value'] = text != null ? text : val;
		}
		function renderVoices() {
			var list = ttsBar && qs('[data-xin-tts-list]', ttsBar);
			if (!list) return;
			var q = ((qs('[data-xin-tts-filter]', ttsBar) || {}).value || '').toLowerCase();
			var localOnly = !!(qs('[data-xin-tts-local]', ttsBar) || { checked: true }).checked;
			ttsPrefs.localOnly = localOnly;
			store('xin-tts', ttsPrefs);
			var rows = ttsVoices.filter(function (v) {
				return (!localOnly || v.localService) && (!q || (v.name + ' ' + v.lang).toLowerCase().indexOf(q) !== -1);
			});
			list.innerHTML = '';
			if (!rows.length) {
				list.innerHTML = '<p class="xin-tts__hint" style="padding:12px">' + escHtml(i18n.voiceEmpty || 'No voices') + '</p>';
			} else {
				var groups = {};
				rows.forEach(function (v) {
					var g = (v.lang || '').split(/[-_]/)[0] || 'other';
					try { g = new Intl.DisplayNames(['en'], { type: 'language' }).of(g) || g; } catch (err) {}
					(groups[g] = groups[g] || []).push(v);
				});
				Object.keys(groups).sort().forEach(function (g) {
					var h = document.createElement('div');
					h.className = 'xin-tts__group';
					h.textContent = g;
					list.appendChild(h);
					groups[g].forEach(function (v) {
						var b = document.createElement('button');
						b.type = 'button';
						b.className = 'xin-tts__voice' + (ttsPrefs.voiceURI === v.voiceURI ? ' is-on' : '');
						b.setAttribute('data-xin-tts-pick', v.voiceURI);
						b.innerHTML = '<b></b><span class="xin-tts__badge' + (v.localService ? ' xin-tts__badge--local' : '') + '"></span>';
						b.querySelector('b').textContent = v.name;
						b.querySelector('span').textContent = v.localService ? (i18n.voiceLocal || 'On device') : (i18n.voiceNet || 'Browser');
						list.appendChild(b);
					});
				});
			}
			var hint = qs('[data-xin-tts-hint]', ttsBar);
			if (hint) hint.textContent = (i18n.voiceCount || '%d voices on this device').replace('%d', String(ttsVoices.filter(function (v) { return v.localService; }).length));
			var cur = currentVoice();
			setVoiceName(cur ? cur.name : '');
			setDial('[data-xin-tts-rate]', ttsPrefs.rate);
			setDial('[data-xin-tts-pitch]', ttsPrefs.pitch);
			setDial('[data-xin-tts-vol]', Math.round((ttsPrefs.volume || 1) * 100));
			setDial('[data-xin-tts-rate-out]', null, Number(ttsPrefs.rate).toFixed(1));
			setDial('[data-xin-tts-pitch-out]', null, Number(ttsPrefs.pitch).toFixed(1));
			setDial('[data-xin-tts-vol-out]', null, String(Math.round((ttsPrefs.volume || 1) * 100)));
		}
		function loadVoices() {
			if (!synth) return;
			ttsVoices = synth.getVoices() || [];
			if (ttsVoices.length && !ttsPrefs.voiceURI) {
				var pick = currentVoice();
				if (pick) ttsPrefs.voiceURI = pick.voiceURI;
				store('xin-tts', ttsPrefs);
			}
			renderVoices();
		}
		function setTtsPlaying(playing, paused) {
			if (!ttsBar) return;
			ttsBar.hidden = !(playing || paused || ttsStudioOpen);
			ttsBar.classList.toggle('is-playing', !!playing && !paused);
			ttsBar.classList.toggle('is-paused', !!paused);
			document.body.classList.toggle('xin-tts-open', !ttsBar.hidden);
		}
		function stopTts() {
			if (synth) {
				if (utter) utter.onend = null;
				synth.cancel();
			}
			ttsQueue = [];
			qsa('p.is-speaking', text).forEach(function (p) { p.classList.remove('is-speaking'); });
			ttsCurrent = null;
			setTtsPlaying(false, false);
		}
		function speakNext() {
			if (!synth) return;
			if (!ttsQueue.length) return stopTts();
			var item = ttsQueue.shift();
			qsa('p.is-speaking', text).forEach(function (p) { p.classList.remove('is-speaking'); });
			ttsCurrent = item.el;
			if (item.el) item.el.classList.add('is-speaking');
			var label = ttsBar && qs('[data-xin-tts-label]', ttsBar);
			if (label) label.textContent = (item.text || '').slice(0, 72);
			utter = dressUtter(new SpeechSynthesisUtterance(item.text));
			window.__xinUtter = utter;
			utter.onend = speakNext;
			utter.onerror = speakNext;
			setTtsPlaying(true, false);
			synth.speak(utter);
		}
		function startTts(fromP) {
			if (!synth) return toast(i18n.ttsOff);
			stopTts();
			for (var node = fromP; node; node = node.nextElementSibling) {
				if (node.matches && node.matches('p[data-paragraph-id]')) {
					var t = paraText(node);
					if (t) ttsQueue.push({ el: node, text: t });
				}
			}
			if (ttsQueue.length) speakNext();
		}
		function openStudio() {
			if (!ttsBar) return;
			ttsStudioOpen = true;
			ttsBar.hidden = false;
			document.body.classList.add('xin-tts-open');
			var studio = qs('[data-xin-tts-studio]', ttsBar);
			if (studio) studio.hidden = false;
			loadVoices();
		}
		function closeStudio() {
			ttsStudioOpen = false;
			if (!ttsBar) return;
			var st = qs('[data-xin-tts-studio]', ttsBar);
			if (st) st.hidden = true;
			if (!ttsBar.classList.contains('is-playing') && !ttsBar.classList.contains('is-paused')) {
				ttsBar.hidden = true;
				document.body.classList.remove('xin-tts-open');
			}
		}

		var suggestPara = null;
		var suggestOriginal = '';
		function openSuggest(p) {
			if (!suggest) return;
			suggestPara = p;
			suggestOriginal = paraText(p);
			var orig = qs('[data-xin-suggest-original]', suggest);
			var input = qs('[data-xin-suggest-input]', suggest);
			var diff = qs('[data-xin-suggest-diff]', suggest);
			if (orig) orig.textContent = suggestOriginal;
			if (input) {
				input.value = suggestOriginal;
				input.focus();
				input.setSelectionRange(input.value.length, input.value.length);
			}
			if (diff) diff.innerHTML = escHtml(suggestOriginal);
			suggest.hidden = false;
			document.body.classList.add('xin-no-scroll');
			closeTools();
		}
		function closeSuggest() {
			if (!suggest) return;
			suggest.hidden = true;
			document.body.classList.remove('xin-no-scroll');
			suggestPara = null;
			suggestOriginal = '';
		}

		paintBookmarks();
		if (/^#paragraph-\d+$/.test(location.hash)) {
			var jump = qs(location.hash, text);
			if (jump) {
				jump.scrollIntoView({ behavior: 'smooth', block: 'center' });
				jump.classList.add('is-selected');
			}
		}

		text.addEventListener('pointerdown', function () { clickAt = Date.now(); });
		text.addEventListener('click', function (e) {
			if (e.target.closest(KIT) || e.target.closest('a, button, input, textarea, select')) return;
			if (window.getSelection && String(window.getSelection()).trim()) return;
			if (Date.now() - clickAt > 450) return;
			var p = e.target.closest('p[data-paragraph-id]');
			if (!p || !paraText(p)) return;
			e.preventDefault();
			openTools(p);
		});
		document.addEventListener('click', function (e) {
			if (selected && !e.target.closest(KIT + ', p.is-selected')) closeTools();
			if (ttsStudioOpen && !e.target.closest('.xin-tts')) closeStudio();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { closeSuggest(); closeTools(); closeStudio(); }
		});

		tools.addEventListener('click', function (e) {
			var colorDot = e.target.closest('[data-xin-bm-color]');
			var btn = e.target.closest('[data-xin-ptool]');
			if ((!btn && !colorDot) || !selected) return;
			e.preventDefault();
			e.stopPropagation();
			var action = colorDot ? 'bookmark' : btn.getAttribute('data-xin-ptool');
			var pid = selected.getAttribute('data-paragraph-id');
			if (action === 'close') return closeTools();
			if (action === 'bookmark') {
				var map = readBm();
				var color = colorDot ? colorDot.getAttribute('data-xin-bm-color') : 'none';
				var same = map[chapterId] && String(map[chapterId].id) === String(pid);
				if (same && (color === 'none' || color === (map[chapterId].color || 'default'))) {
					delete map[chapterId];
					toast(i18n.bookmarkOff);
				} else if (same && color !== 'none') {
					map[chapterId].color = color;
					toast(i18n.bookmarked);
				} else {
					map[chapterId] = {
						id: pid,
						color: color === 'none' ? 'default' : color,
						text: paraText(selected).slice(0, 180),
						title: meta.chapterTitle || '',
						novel: meta.novelTitle || '',
						url: location.pathname,
						at: Date.now()
					};
					toast(i18n.bookmarked);
				}
				store(BM, map);
				paintBookmarks();
				return;
			}
			if (action === 'voices') { openStudio(); closeTools(); return; }
			if (action === 'quote') {
				toast(appendTalk('\n[quote]' + quotePayload(selected) + '[/quote]\n') ? i18n.quoted : (loggedIn ? i18n.copied : i18n.loginTalk));
				closeTools();
				return;
			}
			if (action === 'suggestion') return openSuggest(selected);
			if (action === 'tts') { startTts(selected); closeTools(); return; }
			if (action === 'link') copy(location.protocol + '//' + location.host + location.pathname + '#' + selected.id, i18n.linkCopied);
		});

		if (jumpBtn) jumpBtn.addEventListener('click', function () {
			var rec = readBm()[chapterId];
			var p = rec && qs('p[data-paragraph-id="' + rec.id + '"]', text);
			if (p) p.scrollIntoView({ behavior: 'smooth', block: 'center' });
		});

		if (suggest) {
			var sInput = qs('[data-xin-suggest-input]', suggest);
			if (sInput) sInput.addEventListener('input', function () {
				var diff = qs('[data-xin-suggest-diff]', suggest);
				if (diff) diff.innerHTML = prettyDiff(suggestOriginal, sInput.value);
			});
			var sReset = qs('[data-xin-suggest-reset]', suggest);
			if (sReset) sReset.addEventListener('click', function () {
				if (sInput) sInput.value = suggestOriginal;
				sInput && sInput.dispatchEvent(new Event('input'));
			});
			var sClose = qs('[data-xin-suggest-close]', suggest);
			if (sClose) sClose.addEventListener('click', closeSuggest);
			suggest.addEventListener('click', function (e) { if (e.target === suggest) closeSuggest(); });
			var sSubmit = qs('[data-xin-suggest-submit]', suggest);
			if (sSubmit) sSubmit.addEventListener('click', function () {
				var diffEl = qs('[data-xin-suggest-diff]', suggest);
				var bb = htmlToBb(diffEl ? diffEl.innerHTML : escHtml(sInput ? sInput.value : ''));
				if (suggestPara) bb += ' [anchor]' + suggestPara.id + '[/anchor]';
				var ok = appendTalk('\n[quote]' + bb + '[/quote]\n');
				closeSuggest();
				toast(ok ? i18n.suggested : (loggedIn ? i18n.copied : i18n.loginTalk));
			});
		}

		if (ttsBar) {
			function on(sel, fn) {
				var el = qs(sel, ttsBar);
				if (el) el.addEventListener('click', fn);
			}
			on('[data-xin-tts-play]', function () {
				if (!synth) return;
				if (ttsPausedAt !== -1 && Date.now() - ttsPausedAt > 10000 && ttsCurrent) {
					ttsQueue.unshift({ el: ttsCurrent, text: paraText(ttsCurrent) });
					ttsPausedAt = -1;
					if (utter) utter.onend = null;
					synth.cancel();
					return speakNext();
				}
				synth.resume();
				setTtsPlaying(true, false);
			});
			on('[data-xin-tts-pause]', function () {
				if (!synth) return;
				synth.pause();
				ttsPausedAt = Date.now();
				setTtsPlaying(true, true);
			});
			on('[data-xin-tts-stop]', stopTts);
			on('[data-xin-tts-skip]', function () {
				if (!synth) return;
				if (utter) utter.onend = null;
				synth.cancel();
				speakNext();
			});
			window.addEventListener('beforeunload', stopTts);
			var studio = qs('[data-xin-tts-studio]', ttsBar);
			on('[data-xin-tts-studio-toggle]', function () {
				if (!studio) return;
				if (studio.hidden) openStudio();
				else closeStudio();
			});
			var filter = qs('[data-xin-tts-filter]', ttsBar);
			if (filter) filter.addEventListener('input', renderVoices);
			var localOnly = qs('[data-xin-tts-local]', ttsBar);
			if (localOnly) {
				localOnly.checked = ttsPrefs.localOnly !== false;
				localOnly.addEventListener('change', renderVoices);
			}
			ttsBar.addEventListener('click', function (e) {
				var pick = e.target.closest('[data-xin-tts-pick]');
				if (!pick) return;
				ttsPrefs.voiceURI = pick.getAttribute('data-xin-tts-pick');
				store('xin-tts', ttsPrefs);
				renderVoices();
			});
			function bindDial(sel, key, outSel, scale) {
				var el = qs(sel, ttsBar);
				if (!el) return;
				el.addEventListener('input', function () {
					var n = parseFloat(el.value);
					ttsPrefs[key] = scale ? n / scale : n;
					store('xin-tts', ttsPrefs);
					var out = qs(outSel, ttsBar);
					if (out) out.textContent = scale ? String(Math.round(n)) : n.toFixed(1);
				});
			}
			bindDial('[data-xin-tts-rate]', 'rate', '[data-xin-tts-rate-out]');
			bindDial('[data-xin-tts-pitch]', 'pitch', '[data-xin-tts-pitch-out]');
			bindDial('[data-xin-tts-vol]', 'volume', '[data-xin-tts-vol-out]', 100);
			on('[data-xin-tts-preview]', function () {
				if (!synth) return;
				synth.cancel();
				utter = dressUtter(new SpeechSynthesisUtterance(i18n.previewSample || 'The seal was broken a thousand years ago so no one could hold it whole.'));
				window.__xinUtter = utter;
				utter.onend = null;
				synth.speak(utter);
			});
			if (synth) {
				loadVoices();
				if (synth.addEventListener) synth.addEventListener('voiceschanged', loadVoices);
			}
		} else if (synth && synth.addEventListener) {
			synth.addEventListener('voiceschanged', loadVoices);
		}
	})();

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
