/* global XNIC */
(function () {
	'use strict';

	var box = document.querySelector('[data-xni-count]');
	if (!box) return;

	var clock = box.querySelector('.xni-count__clock');
	var target = parseInt(box.dataset.xniCount, 10) * 1000;

	// Тот же формат, что рисует сервер, только уже переведённый под язык сайта.
	var FORMAT = (window.XNIC && XNIC.format) || '%1$dд : %2$02dч : %3$02dм : %4$02dс';

	function pad(n) { return n < 10 ? '0' + n : String(n); }

	function render(d, h, m, s) {
		return FORMAT
			.replace('%1$d', String(d))
			.replace('%2$02d', pad(h))
			.replace('%3$02d', pad(m))
			.replace('%4$02d', pad(s))
			// на случай формата без ведущих нулей у переводчика
			.replace('%2$d', String(h))
			.replace('%3$d', String(m))
			.replace('%4$d', String(s));
	}

	function tick() {
		var left = Math.floor((target - Date.now()) / 1000);

		if (left <= 0) {
			box.classList.add('is-out');
			clock.textContent = render(0, 0, 0, 0);
			clearInterval(timer);
			// Сервер как раз публикует главу — страница должна её увидеть.
			setTimeout(function () { window.location.reload(); }, 5000);
			return;
		}

		clock.textContent = render(
			Math.floor(left / 86400),
			Math.floor((left % 86400) / 3600),
			Math.floor((left % 3600) / 60),
			left % 60
		);
	}

	var timer = setInterval(tick, 1000);
	tick();
})();
