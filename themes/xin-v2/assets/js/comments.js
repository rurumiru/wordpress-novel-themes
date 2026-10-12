/**
 * XIN-V2 — обсуждения: отправка без перезагрузки, ответы, отметки.
 */
(function () {
	'use strict';

	/* Подписи приходят из темы переведёнными: зашитая в скрипт строка
	   остаётся русской на любом языке сайта. */
	function say(key) {
		var dict = (window.xvData && window.xvData.i18n) || {};
		return dict[key] || '';
	}


	var root = document.querySelector('[data-xv-comments]');

	if (!root || !window.xvData) { return; }

	var postId = parseInt(root.getAttribute('data-xv-comments'), 10);
	var list = root.querySelector('[data-xv-cmt-list]');
	var counter = root.querySelector('[data-xv-comments-count]');

	function send(path, body) {
		return fetch(xvData.root + path, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': xvData.nonce },
			body: JSON.stringify(body)
		}).then(function (response) {
			return response.json().then(function (data) {
				if (!response.ok) { throw new Error(data.message || xvData.i18n.error); }
				return data;
			});
		});
	}

	function note(form, text, isError) {
		var box = form.querySelector('[data-xv-cmt-note]');
		if (!box) { return; }

		box.textContent = text || '';
		box.classList.toggle('is-error', !!isError);
	}

	function setCount(value) {
		if (counter) { counter.textContent = value.toLocaleString('ru-RU'); }
	}

	/* ── Отправка ───────────────────────────────────────────── */

	function submit(form, parent) {
		var field = form.querySelector('textarea');
		var text = field.value.trim();

		if (!text) {
			note(form, say('sayMore'), true);
			field.focus();
			return;
		}

		var button = form.querySelector('button[type="submit"]');
		button.disabled = true;
		note(form, say('sending'));

		send('comment', { post: postId, parent: parent || 0, text: text })
			.then(function (data) {
				// Премодерация: реплики в ленте ещё нет, и делать вид, что
				// она опубликована, нельзя.
				if (data.held) {
					field.value = '';
					note(form, say('held'));
					return;
				}

				var box = document.createElement('div');
				box.innerHTML = data.html.trim();
				var card = box.firstElementChild;

				if (data.parent) {
					var thread = root.querySelector('[data-xv-cmt-thread="' + data.parent + '"]');
					var children = thread && thread.querySelector('[data-xv-cmt-children]');

					if (children) { children.appendChild(card); }

					form.remove();
				} else {
					var empty = list.querySelector('.xv-cmt-empty');
					if (empty) { empty.remove(); }

					var thread2 = document.createElement('div');
					thread2.className = 'xv-cmt-thread';
					thread2.setAttribute('data-xv-cmt-thread', data.id);
					thread2.appendChild(card);

					var kids = document.createElement('div');
					kids.className = 'xv-cmt-children';
					kids.setAttribute('data-xv-cmt-children', '');
					thread2.appendChild(kids);

					list.insertBefore(thread2, list.firstChild);

					field.value = '';
					note(form, say('posted'));
					setTimeout(function () { note(form, ''); }, 2500);
				}

				setCount(data.count);
			})
			.catch(function (error) { note(form, error.message, true); })
			.finally(function () { button.disabled = false; });
	}

	root.addEventListener('submit', function (event) {
		var form = event.target.closest('[data-xv-cmt-form]');
		if (!form) { return; }

		event.preventDefault();
		submit(form, parseInt(form.getAttribute('data-parent'), 10) || 0);
	});

	/* ── Ответ ──────────────────────────────────────────────── */

	root.addEventListener('click', function (event) {
		var reply = event.target.closest('[data-xv-cmt-reply]');

		if (reply) {
			var id = reply.getAttribute('data-xv-cmt-reply');
			var thread = root.querySelector('[data-xv-cmt-thread="' + id + '"]');
			var existing = thread.querySelector('[data-xv-cmt-form]');

			if (existing) {
				existing.remove();
				return;
			}

			if (!xvData.loggedIn) {
				window.location.href = xvData.home + 'wp-login.php';
				return;
			}

			var form = document.createElement('form');
			form.className = 'xv-cmt-form is-reply';
			form.setAttribute('data-xv-cmt-form', '');
			form.setAttribute('data-parent', id);
			form.innerHTML =
				'<div class="xv-cmt-form-body">' +
				'<textarea class="xv-textarea" rows="2" placeholder="Ваш ответ…" required></textarea>' +
				'<div class="xv-cmt-form-foot">' +
				'<span class="xv-cmt-form-note" data-xv-cmt-note></span>' +
				'<button type="submit" class="xv-btn primary sm">Ответить</button>' +
				'</div></div>';

			thread.querySelector('[data-xv-cmt-children]').appendChild(form);
			form.querySelector('textarea').focus();
			return;
		}

		/* ── Отметка «полезно» ──────────────────────────────── */

		var like = event.target.closest('[data-xv-cmt-like]');

		if (like) {
			if (!xvData.loggedIn) {
				window.location.href = xvData.home + 'wp-login.php';
				return;
			}

			like.disabled = true;

			send('comment/like', { comment: parseInt(like.getAttribute('data-xv-cmt-like'), 10) })
				.then(function (data) {
					like.classList.toggle('is-on', data.on);
					like.setAttribute('aria-pressed', data.on ? 'true' : 'false');

					var out = like.querySelector('[data-xv-cmt-likes]');
					if (out) { out.textContent = data.likes; }
				})
				.catch(function () {})
				.finally(function () { like.disabled = false; });

			return;
		}

		/* ── Удаление ───────────────────────────────────────── */

		var remove = event.target.closest('[data-xv-cmt-delete]');

		if (remove) {
			if (!window.confirm(say('dropAsk'))) { return; }

			var commentId = parseInt(remove.getAttribute('data-xv-cmt-delete'), 10);
			remove.disabled = true;

			send('comment/delete', { comment: commentId })
				.then(function (data) {
					var card = root.querySelector('[data-xv-cmt="' + commentId + '"]');
					var thread = root.querySelector('[data-xv-cmt-thread="' + commentId + '"]');

					if (thread) {
						thread.remove();
					} else if (card) {
						card.remove();
					}

					setCount(data.count);

					if (!list.querySelector('.xv-cmt')) {
						var empty = document.createElement('p');
						empty.className = 'xv-cmt-empty';
						empty.textContent = say('quiet');
						list.appendChild(empty);
					}
				})
				.catch(function () { remove.disabled = false; });
		}
	});
})();
