/* global XNM, wp */
(function () {
	'use strict';

	var form = document.getElementById('xnm-form');
	if (!form) return;

	var action = document.getElementById('xnm-action');
	var checkAll = document.getElementById('xnm-check-all');
	var allMatching = document.getElementById('xnm-all-matching');
	var picked = document.getElementById('xnm-picked');

	function boxes() {
		return Array.prototype.slice.call(form.querySelectorAll('input[name="xnm_ids[]"]:not(:disabled)'));
	}

	function countPicked() {
		return boxes().filter(function (b) { return b.checked; }).length;
	}

	function paintCount() {
		var n = countPicked();
		picked.textContent = n ? ' · ' + n : '';
		if (checkAll) {
			var all = boxes();
			checkAll.checked = all.length > 0 && n === all.length;
			checkAll.indeterminate = n > 0 && n < all.length;
		}
	}

	if (checkAll) {
		checkAll.addEventListener('change', function () {
			boxes().forEach(function (b) { b.checked = checkAll.checked; });
			paintCount();
		});
	}

	form.addEventListener('change', function (e) {
		if (e.target.name === 'xnm_ids[]') paintCount();
	});

	// Shift-click ticks the run between the last box and this one, the way the
	// posts list behaves.
	var lastIndex = null;
	form.addEventListener('click', function (e) {
		if (e.target.name !== 'xnm_ids[]') return;
		var all = boxes();
		var index = all.indexOf(e.target);
		if (e.shiftKey && lastIndex !== null) {
			var from = Math.min(lastIndex, index);
			var to = Math.max(lastIndex, index);
			for (var i = from; i <= to; i++) all[i].checked = e.target.checked;
			paintCount();
		}
		lastIndex = index;
	});

	// Only the field the chosen action needs is on screen.
	var NEEDS = {
		novel_status: 'novel_status',
		genre_add: 'terms', genre_remove: 'terms', genre_replace: 'terms',
		tag_add: 'terms', tag_remove: 'terms', tag_replace: 'terms',
		owner: 'owner',
		translator: 'translator',
		cover_set: 'cover'
	};

	function paintFields() {
		var need = NEEDS[action.value] || '';
		Array.prototype.forEach.call(form.querySelectorAll('.xnm-field'), function (el) {
			el.hidden = el.dataset.xnmFor !== need;
		});
	}
	action.addEventListener('change', paintFields);
	paintFields();

	// Cover picker, through the media library the site already has.
	var coverBtn = document.getElementById('xnm-cover-pick');
	var coverId = document.getElementById('xnm-cover-id');
	var coverName = document.getElementById('xnm-cover-name');
	if (coverBtn && window.wp && wp.media) {
		var frame = null;
		coverBtn.addEventListener('click', function () {
			if (!frame) {
				frame = wp.media({
					title: XNM.pickCover,
					button: { text: XNM.useCover },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var img = frame.state().get('selection').first().toJSON();
					coverId.value = img.id;
					coverName.textContent = img.filename || img.title || '';
				});
			}
			frame.open();
		});
	}

	form.addEventListener('submit', function (e) {
		if (!action.value) {
			e.preventDefault();
			window.alert(XNM.noAction);
			return;
		}

		var everything = allMatching && allMatching.checked;
		if (!everything && countPicked() === 0) {
			e.preventDefault();
			window.alert(XNM.nothingPicked);
			return;
		}

		// Losing a set of titles by mis-clicking a dropdown would be miserable, so
		// the two destructive actions ask first — and the permanent one says so.
		var ask = null;
		if (action.value === 'trash') ask = XNM.confirmTrash;
		if (action.value === 'delete') ask = XNM.confirmDelete;
		if (ask && !window.confirm(ask)) e.preventDefault();
	});

	paintCount();
})();
