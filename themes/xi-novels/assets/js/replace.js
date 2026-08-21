/**
 * Движок замены терминов.
 *
 * Один и тот же код правит текст в читалке у читателя и в редакторе у
 * переводчика: длинное правило выигрывает у короткого, результат замены
 * повторно не читается, регистр переносится, границы слова понимают
 * кириллицу и иероглифы.
 */
(function () {
	'use strict';

	var LETTER;
	try {
		LETTER = new RegExp('[\\p{L}\\p{N}_]', 'u');
	} catch (e) {
		LETTER = /[0-9A-Za-z_À-ʯͰ-ӿ԰-ۿऀ-෿Ḁ-῿぀-ヿ㐀-鿿가-힯]/;
	}

	function escapeRe(value) {
		return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}

	function isWord(ch) {
		return !!ch && LETTER.test(ch);
	}

	/**
	 * Готовит правила к работе: отбрасывает выключенные, сортирует по длине.
	 *
	 * @param {Array} rules Список правил.
	 * @return {Object|null} Матчер или null, если заменять нечего.
	 */
	function matcher(rules) {
		var active = (rules || []).filter(function (rule) {
			return rule && false !== rule.on && rule.from;
		});

		if (!active.length) return null;

		var prepared = active.slice()
			.sort(function (a, b) { return String(b.from).length - String(a.from).length; })
			.map(function (rule) {
				return {
					from: String(rule.from),
					low: String(rule.from).toLowerCase(),
					to: 'string' === typeof rule.to ? rule.to : '',
					ci: false !== rule.ci,
					whole: !!rule.whole
				};
			});

		var re;
		try {
			re = new RegExp(prepared.map(function (rule) { return escapeRe(rule.from); }).join('|'), 'gi');
		} catch (e) {
			return null;
		}

		return { re: re, rules: prepared };
	}

	/** Переносит регистр найденного куска на замену. */
	function recase(rule, matched) {
		var to = rule.to;
		if (!rule.ci || !to || matched === rule.from) return to;

		if (matched.length > 1 && matched === matched.toUpperCase() && matched !== matched.toLowerCase()) {
			return to.toUpperCase();
		}

		var first = matched.charAt(0);
		if (first !== first.toLowerCase()) {
			return to.charAt(0).toUpperCase() + to.slice(1);
		}

		return to;
	}

	/**
	 * Разбирает строку на куски: что осталось и что заменено.
	 *
	 * @param {string} text Исходная строка.
	 * @param {Object} mx   Матчер.
	 * @return {Object|null} {parts, count} или null, если совпадений нет.
	 */
	function scan(text, mx) {
		var re = mx.re;
		var rules = mx.rules;
		var parts = [];
		var count = 0;
		var last = 0;
		var found;

		re.lastIndex = 0;

		while ((found = re.exec(text)) !== null) {
			var at = found.index;
			var hit = null;
			var i;

			for (i = 0; i < rules.length; i++) {
				var rule = rules[i];
				var seg = text.substr(at, rule.from.length);

				if (seg.length !== rule.from.length) continue;
				if (rule.ci ? seg.toLowerCase() !== rule.low : seg !== rule.from) continue;
				if (rule.whole && (isWord(text.charAt(at - 1)) || isWord(text.charAt(at + seg.length)))) continue;

				hit = { rule: rule, text: seg };
				break;
			}

			if (!hit) {
				re.lastIndex = at + 1;
				continue;
			}

			if (at > last) parts.push({ hit: false, text: text.slice(last, at) });
			parts.push({ hit: true, text: recase(hit.rule, hit.text), from: hit.text });

			count++;
			last = at + hit.text.length;
			re.lastIndex = last;
		}

		if (!count) return null;
		if (last < text.length) parts.push({ hit: false, text: text.slice(last) });

		return { parts: parts, count: count };
	}

	/**
	 * Замена в обычной строке.
	 *
	 * @param {string} text  Строка.
	 * @param {Array}  rules Правила.
	 * @return {Object} {text, count}
	 */
	function inText(text, rules) {
		var mx = matcher(rules);
		var result = mx ? scan(String(text), mx) : null;

		if (!result) return { text: String(text), count: 0 };

		return {
			text: result.parts.map(function (part) { return part.text; }).join(''),
			count: result.count
		};
	}

	/**
	 * Замена внутри разметки: теги и атрибуты не трогаются.
	 *
	 * @param {string} html  Разметка.
	 * @param {Array}  rules Правила.
	 * @return {Object} {html, count}
	 */
	function inHtml(html, rules) {
		var mx = matcher(rules);
		if (!mx) return { html: String(html), count: 0 };

		var box = document.createElement('div');
		box.innerHTML = String(html);

		var walker = document.createTreeWalker(box, NodeFilter.SHOW_TEXT, null, false);
		var nodes = [];
		var node;

		while ((node = walker.nextNode())) nodes.push(node);

		var count = 0;

		nodes.forEach(function (item) {
			if (!item.nodeValue) return;

			var parent = item.parentNode;
			if (parent && parent.closest && parent.closest('script,style,code,pre')) return;

			var result = scan(item.nodeValue, mx);
			if (!result) return;

			count += result.count;
			item.nodeValue = result.parts.map(function (part) { return part.text; }).join('');
		});

		return { html: box.innerHTML, count: count };
	}

	window.xinReplace = {
		matcher: matcher,
		scan: scan,
		text: inText,
		html: inHtml,
		isWord: isWord,
		escape: escapeRe
	};
})();
