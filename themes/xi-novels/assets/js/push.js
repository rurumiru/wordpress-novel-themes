/**
 * XI Novels — browser push (novel Notify button).
 *
 * Full-page cache can embed a stale nonce. Always fetch a fresh one
 * from admin-ajax before save / status / unsubscribe.
 */
(function () {
	'use strict';

	if (typeof xinPush === 'undefined') {
		return;
	}

	const config = xinPush;
	const isSupportedFlag = config.supported === true || config.supported === 1 || config.supported === '1';

	let registration = null;
	let currentSubscription = null;
	let serverSubscribed = false;
	let busy = false;
	let liveNonce = config.nonce || '';
	let bound = false;

	function bells() {
		return Array.from(document.querySelectorAll('.xin-push-bell'));
	}

	function urlBase64ToUint8Array(base64String) {
		const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
		const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
		const raw = window.atob(base64);
		const output = new Uint8Array(raw.length);
		for (let i = 0; i < raw.length; i++) {
			output[i] = raw.charCodeAt(i);
		}
		return output;
	}

	function isApiAvailable() {
		return (
			isSupportedFlag &&
			!!config.vapidPublicKey &&
			window.isSecureContext !== false &&
			'serviceWorker' in navigator &&
			'PushManager' in window &&
			'Notification' in window
		);
	}

	function permission() {
		try {
			return Notification.permission;
		} catch (e) {
			return 'denied';
		}
	}

	function setIcon(bell, kind) {
		const wrap = bell.querySelector('.xin-push-bell-icon');
		const icons = config.icons || {};
		if (!wrap) {
			return;
		}
		if (kind === 'off' && icons.bellOff) {
			wrap.innerHTML = icons.bellOff;
		} else if (icons.bell) {
			wrap.innerHTML = icons.bell;
		}
	}

	function paint(state) {
		const perm = permission();
		const subscribed = !!state.subscribed;
		const unsupported = !isApiAvailable();
		const denied = perm === 'denied';
		const working = !!state.busy;

		bells().forEach(function (bell) {
			bell.classList.toggle('is-active', subscribed && !denied && !unsupported);
			bell.classList.toggle('is-denied', denied);
			bell.classList.toggle('is-unsupported', unsupported);
			bell.classList.toggle('is-busy', working);
			bell.setAttribute('aria-pressed', subscribed ? 'true' : 'false');
			bell.setAttribute('aria-busy', working ? 'true' : 'false');
			bell.disabled = unsupported || denied;

			if (denied || unsupported) {
				setIcon(bell, 'off');
			} else {
				setIcon(bell, 'on');
			}

			const label = bell.querySelector('.xin-push-bell-label');
			if (label) {
				if (working) {
					label.textContent = state.busyLabel || '…';
				} else if (state.errorLabel) {
					label.textContent = state.errorLabel;
				} else if (unsupported) {
					label.textContent = config.i18n.unsupported || 'Unavailable';
				} else if (denied) {
					label.textContent = config.i18n.denied || 'Blocked';
				} else if (subscribed) {
					label.textContent = config.i18n.enabled || 'Alerts on';
				} else {
					label.textContent = config.i18n.disabled || 'Notify';
				}
			}

			bell.title = state.hint || (label && label.textContent) || '';
		});
	}

	function showTempError(msg) {
		const short =
			String(msg || config.i18n.error || 'Failed')
				.replace(/\s+/g, ' ')
				.trim()
				.slice(0, 42) || 'Failed';
		paint({
			subscribed: serverSubscribed,
			busy: false,
			errorLabel: short,
			hint: String(msg || ''),
		});
		window.setTimeout(function () {
			paint({ subscribed: serverSubscribed, busy: false });
		}, 3500);
	}

	function refreshNonce() {
		const url = new URL(config.ajaxUrl, window.location.origin);
		url.searchParams.set('action', 'xin_push_nonce');
		url.searchParams.set('novel_id', String(config.novelId));
		url.searchParams.set('_', String(Date.now()));

		return fetch(url.toString(), {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
		}).then(function (res) {
			return res.json().then(function (data) {
				if (!res.ok || !data || !data.success || !data.data || !data.data.nonce) {
					const msg =
						(data && data.data && data.data.message) || 'Could not refresh security token';
					throw new Error(typeof msg === 'string' ? msg : 'nonce_failed');
				}
				liveNonce = data.data.nonce;
				config.nonce = liveNonce;
				return liveNonce;
			});
		});
	}

	function postAjax(action, extra, retried) {
		const formData = new FormData();
		formData.append('action', action);
		formData.append('novel_id', String(config.novelId));
		formData.append('nonce', liveNonce || config.nonce || '');
		if (extra) {
			Object.keys(extra).forEach(function (key) {
				if (extra[key] != null && extra[key] !== '') {
					formData.append(key, extra[key]);
				}
			});
		}

		return fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			cache: 'no-store',
			body: formData,
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
		}).then(function (response) {
			return response.text().then(function (text) {
				let data = null;
				try {
					data = text ? JSON.parse(text) : null;
				} catch (e) {
					if (text === '-1' || text === '0' || response.status === 403) {
						if (!retried) {
							return refreshNonce().then(function () {
								return postAjax(action, extra, true);
							});
						}
						throw new Error('Security check failed');
					}
					throw new Error(text ? text.slice(0, 80) : 'HTTP ' + response.status);
				}

				if (data && data.success === false) {
					const code = data.data && data.data.code;
					const msg = (data.data && data.data.message) || 'Request failed';
					if (code === 'bad_nonce' && !retried) {
						return refreshNonce().then(function () {
							return postAjax(action, extra, true);
						});
					}
					throw new Error(typeof msg === 'string' ? msg : 'Request failed');
				}

				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}

				return data;
			});
		});
	}

	function subscriptionPayload(subscription) {
		const json = subscription.toJSON ? subscription.toJSON() : subscription;
		const keys = json.keys || {};
		return {
			endpoint: json.endpoint || '',
			p256dh: keys.p256dh || '',
			auth: keys.auth || '',
		};
	}

	function saveSubscription(subscription) {
		const p = subscriptionPayload(subscription);
		if (!p.endpoint || !p.p256dh || !p.auth) {
			return Promise.reject(new Error('Invalid push keys from browser'));
		}
		return postAjax('xin_push_subscribe', p).then(function () {
			return true;
		});
	}

	function removeSubscription(endpoint) {
		return postAjax('xin_push_unsubscribe', { endpoint: endpoint });
	}

	function ensureRegistration() {
		if (registration) {
			return navigator.serviceWorker.ready.then(function (readyReg) {
				registration = readyReg || registration;
				return registration;
			});
		}
		if (!isApiAvailable()) {
			return Promise.reject(new Error('Push not supported in this browser'));
		}

		const candidates = [];
		if (config.swUrl) {
			candidates.push(config.swUrl);
		}
		candidates.push(
			window.location.origin + '/index.php?xin_push_sw=1',
			'/index.php?xin_push_sw=1'
		);

		function tryRegister(index) {
			if (index >= candidates.length) {
				return Promise.reject(
					new Error('Failed to register service worker (scope). Try a hard refresh.')
				);
			}
			const swUrl = candidates[index];
			return navigator.serviceWorker
				.register(swUrl, { scope: '/' })
				.then(function (reg) {
					registration = reg;
					return navigator.serviceWorker.ready;
				})
				.then(function (readyReg) {
					registration = readyReg || registration;
					return registration;
				})
				.catch(function (err) {
					const msg = (err && err.message) || String(err);
					if (
						index + 1 < candidates.length &&
						/scope|redirect|ServiceWorker|script/i.test(msg)
					) {
						return tryRegister(index + 1);
					}
					throw err;
				});
		}

		return tryRegister(0);
	}

	function refreshBrowserSubscription() {
		return ensureRegistration().then(function (reg) {
			return reg.pushManager.getSubscription().then(function (sub) {
				currentSubscription = sub;
				return sub;
			});
		});
	}

	function syncState() {
		return refreshNonce()
			.then(function () {
				return refreshBrowserSubscription();
			})
			.then(function (sub) {
				if (!sub) {
					serverSubscribed = false;
					paint({ subscribed: false, busy: false });
					return false;
				}
				const p = subscriptionPayload(sub);
				return postAjax('xin_push_status', { endpoint: p.endpoint })
					.then(function (data) {
						const on = !!(data && data.success && data.data && data.data.subscribed);
						if (on) {
							serverSubscribed = true;
							paint({ subscribed: true, busy: false });
							return true;
						}
						return saveSubscription(sub)
							.then(function () {
								serverSubscribed = true;
								paint({ subscribed: true, busy: false });
								return true;
							})
							.catch(function () {
								serverSubscribed = false;
								paint({ subscribed: false, busy: false });
								return false;
							});
					})
					.catch(function () {
						paint({ subscribed: serverSubscribed, busy: false });
						return serverSubscribed;
					});
			})
			.catch(function () {
				paint({ subscribed: false, busy: false });
				return false;
			});
	}

	function requestPermissionIfNeeded() {
		const p = permission();
		if (p === 'granted' || p === 'denied') {
			return Promise.resolve(p);
		}
		return Notification.requestPermission();
	}

	function doSubscribe() {
		return ensureRegistration()
			.then(function (reg) {
				return reg.pushManager.getSubscription().then(function (existing) {
					if (existing) {
						return existing;
					}
					try {
						return reg.pushManager.subscribe({
							userVisibleOnly: true,
							applicationServerKey: urlBase64ToUint8Array(config.vapidPublicKey),
						});
					} catch (err) {
						return Promise.reject(err);
					}
				});
			})
			.then(function (subscription) {
				if (!subscription) {
					throw new Error('No push subscription');
				}
				currentSubscription = subscription;
				return refreshNonce().then(function () {
					return saveSubscription(subscription);
				});
			})
			.then(function () {
				serverSubscribed = true;
				paint({ subscribed: true, busy: false });
				return true;
			})
			.catch(function (err) {
				const name = err && err.name;
				const msg = (err && err.message) || '';
				if (name === 'InvalidStateError' || /applicationServerKey|push service/i.test(msg)) {
					return refreshBrowserSubscription().then(function (sub) {
						if (sub) {
							return sub.unsubscribe().then(function () {
								currentSubscription = null;
								return doSubscribeFresh();
							});
						}
						return doSubscribeFresh();
					});
				}
				throw err;
			});
	}

	function doSubscribeFresh() {
		return ensureRegistration().then(function (reg) {
			return reg.pushManager
				.subscribe({
					userVisibleOnly: true,
					applicationServerKey: urlBase64ToUint8Array(config.vapidPublicKey),
				})
				.then(function (subscription) {
					currentSubscription = subscription;
					return refreshNonce().then(function () {
						return saveSubscription(subscription);
					});
				})
				.then(function () {
					serverSubscribed = true;
					paint({ subscribed: true, busy: false });
					return true;
				});
		});
	}

	function doUnsubscribe() {
		return refreshNonce()
			.then(function () {
				return refreshBrowserSubscription();
			})
			.then(function (sub) {
				const target = sub || currentSubscription;
				if (!target) {
					serverSubscribed = false;
					paint({ subscribed: false, busy: false });
					return false;
				}
				return removeSubscription(target.endpoint).then(function () {
					serverSubscribed = false;
					paint({ subscribed: false, busy: false });
					return false;
				});
			});
	}

	function runToggle() {
		if (busy) {
			return;
		}
		if (!isApiAvailable()) {
			showTempError(config.i18n.unsupported || 'Unavailable');
			return;
		}
		if (permission() === 'denied') {
			showTempError(config.i18n.denied || 'Blocked in browser');
			return;
		}

		const turningOff = serverSubscribed;
		busy = true;
		paint({
			subscribed: serverSubscribed,
			busy: true,
			busyLabel: turningOff
				? config.i18n.unsubscribing || 'Disabling…'
				: config.i18n.subscribing || 'Enabling…',
		});

		requestPermissionIfNeeded()
			.then(function (perm) {
				if (perm !== 'granted') {
					serverSubscribed = false;
					busy = false;
					showTempError(config.i18n.denied || 'Permission not granted');
					return null;
				}
				return turningOff ? doUnsubscribe() : doSubscribe();
			})
			.then(function () {
				busy = false;
				paint({ subscribed: serverSubscribed, busy: false });
			})
			.catch(function (err) {
				busy = false;
				const msg = (err && err.message) || config.i18n.error || 'Failed';
				showTempError(msg);
			});
	}

	function onClick(event) {
		const btn = event.target.closest ? event.target.closest('.xin-push-bell') : null;
		if (!btn) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		runToggle();
	}

	function init() {
		if (!bound) {
			bound = true;
			document.addEventListener('click', onClick, true);
		}

		if (!isApiAvailable()) {
			paint({ subscribed: false, busy: false });
			return;
		}

		paint({ subscribed: false, busy: false });

		syncState().catch(function () {
			/* ignore */
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
