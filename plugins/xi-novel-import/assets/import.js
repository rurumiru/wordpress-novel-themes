/* global XNI */
(function () {
	'use strict';

	var wrap = document.querySelector('.xni');
	if (!wrap) return;

	var progress = document.getElementById('xni-progress');
	var bar = progress.querySelector('.xni-bar i');
	var stat = progress.querySelector('.xni-stat');
	var log = progress.querySelector('.xni-log:not(.xni-log--issues)');
	var issues = progress.querySelector('.xni-issues');
	var issuesList = progress.querySelector('.xni-log--issues');
	var issuesHead = progress.querySelector('.xni-issues__head');
	var files = { import: null, fix: null };
	var running = false;

	// --- drop zones ---------------------------------------------------------
	Array.prototype.forEach.call(wrap.querySelectorAll('[data-xni-drop]'), function (zone) {
		var kind = zone.dataset.xniDrop;
		var input = zone.querySelector('input[type=file]');
		var label = zone.querySelector('.xni-picked');

		function take(file) {
			if (!file) return;
			if (!/\.zip$/i.test(file.name)) {
				label.textContent = XNI.needZip;
				files[kind] = null;
				return;
			}
			files[kind] = file;
			label.textContent = file.name + ' · ' + Math.round(file.size / 1024) + ' KB';
		}

		zone.addEventListener('click', function () { input.click(); });
		input.addEventListener('change', function () { take(input.files[0]); });

		['dragenter', 'dragover'].forEach(function (e) {
			zone.addEventListener(e, function (ev) { ev.preventDefault(); zone.classList.add('is-over'); });
		});
		['dragleave', 'drop'].forEach(function (e) {
			zone.addEventListener(e, function (ev) { ev.preventDefault(); zone.classList.remove('is-over'); });
		});
		zone.addEventListener('drop', function (ev) {
			take(ev.dataTransfer.files && ev.dataTransfer.files[0]);
		});
	});

	// --- «что получится» ----------------------------------------------------
	// Судьбу глав решают три поля сразу, и раньше это приходилось держать в
	// голове. Здесь она проговаривается словами и пересчитывается на лету.
	var outcome = document.getElementById('xni-outcome');

	function mode() {
		var picked = wrap.querySelector('input[name="xni-mode"]:checked');
		return picked ? picked.value : 'publish';
	}

	function money() {
		var el = document.getElementById('xni-price');
		var n = el ? parseFloat(el.value) : 0;
		return isNaN(n) || n <= 0 ? 0 : n;
	}

	function paintOutcome() {
		if (!outcome) return;

		var m = mode();
		var price = money();
		var unlock = document.getElementById('xni-unlock');
		var parts = [];

		if (m === 'draft') {
			parts.push(XNI.outDraft);
		} else if (m === 'queue') {
			var days = outcome.dataset.days;
			var times = outcome.dataset.times;
			var first = outcome.dataset.first;
			if (!days || !times || !first) {
				parts.push(XNI.outNoSched);
			} else {
				parts.push(XNI.outQueue.replace('%1$s', first).replace('%2$s', days).replace('%3$s', times));
			}
		} else {
			parts.push(XNI.outPublish);
		}

		// Доступ. В очереди бесплатность зависит ещё и от флага расписания.
		// У черновика говорить «открыты всем» бессмысленно — его никто не видит.
		if (price > 0) {
			parts.push(XNI.outPaid.replace('%s', String(price)));
		} else if (m === 'queue' && outcome.dataset.free !== '1') {
			parts.push(XNI.outLocked);
		} else if (m !== 'draft') {
			parts.push(XNI.outFree);
		}

		if (price > 0 && unlock && unlock.value) {
			parts.push(XNI.outUnlock.replace('%s', unlock.value.replace('T', ' ')));
		}

		outcome.querySelector('.xni-outcome__text').textContent = parts.join(' ');
		outcome.dataset.mode = m;
	}

	wrap.addEventListener('change', function (e) {
		if (e.target.matches('input[name="xni-mode"], #xni-price, #xni-unlock')) paintOutcome();
	});
	wrap.addEventListener('input', function (e) {
		if (e.target.matches('#xni-price, #xni-unlock')) paintOutcome();
	});
	paintOutcome();

	// --- job ----------------------------------------------------------------
	function post(action, body) {
		body.append('action', action);
		body.append('_ajax_nonce', XNI.nonce);
		return fetch(XNI.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); });
	}

	function paint(job) {
		var pct = job.total ? Math.round((job.cursor / job.total) * 100) : 0;
		bar.style.width = pct + '%';
		stat.textContent = XNI.progress
			.replace('%1$s', job.cursor)
			.replace('%2$s', job.total)
			.replace('%3$s', job.created)
			.replace('%4$s', job.updated)
			.replace('%5$s', job.skipped)
			.replace('%6$s', job.failed);

		function fill(target, rows) {
			target.innerHTML = '';
			rows.forEach(function (row) {
				var li = document.createElement('li');
				li.className = 'is-' + row.state;
				li.textContent = row.name + ' — ' + row.note;
				target.appendChild(li);
			});
		}

		fill(log, job.log.slice(-40).reverse());

		// Пропуски и ошибки — отдельным списком: в хвосте журнала их не видно.
		var bad = job.issues || [];
		issues.hidden = bad.length === 0;
		if (bad.length) {
			issuesHead.textContent = XNI.issues.replace('%s', bad.length);
			fill(issuesList, bad);
		}
	}

	function step() {
		return post('xni_step', new FormData()).then(function (res) {
			if (!res.success) throw new Error(res.data && res.data.message);
			paint(res.data);
			if (res.data.done) {
				running = false;
				stat.textContent = XNI.finished + ' ' + stat.textContent;
				return;
			}
			// Уступаем сервер между порциями: следующая идёт не мгновенно.
			return new Promise(function (r) { setTimeout(r, 350); }).then(step);
		});
	}

	function go(kind) {
		if (running) return;
		var file = files[kind];
		if (!file) { window.alert(XNI.pickFirst); return; }
		if (kind === 'fix' && !window.confirm(XNI.confirmFix)) return;

		var body = new FormData();
		body.append('zip', file);
		body.append('type', kind);
		body.append('novel_id', document.getElementById('xni-novel').value);

		if (kind === 'import') {
			var m = mode();
			// Сервер по-прежнему принимает статус и очередь по отдельности.
			body.append('status', m === 'draft' ? 'draft' : 'publish');
			if (m === 'queue') body.append('queue', '1');
			body.append('start', document.getElementById('xni-start').value);
			body.append('price', document.getElementById('xni-price').value);
			body.append('unlock_at', document.getElementById('xni-unlock').value);
			if (document.getElementById('xni-skip').checked) body.append('skip_dupes', '1');
			if (document.getElementById('xni-autosort').checked) body.append('autosort', '1');
		}

		running = true;
		progress.hidden = false;
		log.innerHTML = '';
		bar.style.width = '0%';
		stat.textContent = XNI.uploading;
		progress.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

		post('xni_start', body).then(function (res) {
			if (!res.success) throw new Error(res.data && res.data.message);
			paint(res.data);
			return step();
		}).catch(function (err) {
			running = false;
			stat.textContent = (err && err.message) || XNI.failed;
		});
	}

	Array.prototype.forEach.call(wrap.querySelectorAll('[data-xni-go]'), function (btn) {
		btn.addEventListener('click', function () { go(btn.dataset.xniGo); });
	});

	var cancel = wrap.querySelector('[data-xni-cancel]');
	if (cancel) {
		cancel.addEventListener('click', function () {
			running = false;
			post('xni_cancel', new FormData()).then(function () {
				stat.textContent = XNI.cancelled;
			});
		});
	}
})();
