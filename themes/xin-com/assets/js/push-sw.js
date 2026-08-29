/**
 * XIN-Com push notification service worker.
 */

self.addEventListener('push', function (event) {
	let data = {
		title: 'XIN-Com',
		body: 'A new chapter is available.',
		url: '/',
		icon: '',
		tag: 'xin-push'
	};

	if (event.data) {
		try {
			data = Object.assign(data, event.data.json());
		} catch (error) {
			data.body = event.data.text();
		}
	}

	const options = {
		body: data.body,
		icon: data.icon || undefined,
		badge: data.icon || undefined,
		tag: data.tag || 'xin-push',
		renotify: true,
		data: {
			url: data.url || '/'
		}
	};

	event.waitUntil(self.registration.showNotification(data.title, options));
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();

	const targetUrl = event.notification.data && event.notification.data.url
		? event.notification.data.url
		: '/';

	event.waitUntil(
		clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
			for (let i = 0; i < clientList.length; i++) {
				const client = clientList[i];
				if (client.url === targetUrl && 'focus' in client) {
					return client.focus();
				}
			}

			if (clients.openWindow) {
				return clients.openWindow(targetUrl);
			}

			return undefined;
		})
	);
});
