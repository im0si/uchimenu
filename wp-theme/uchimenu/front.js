/* うちめにゅーテーマ: トップのガチャデモ・ティッカー・出現アニメ・カウントアップ */
(function () {
	var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* 出現アニメ + カウントアップ + 予算バー（全ページ共通で安全に動く） */
	var io = new IntersectionObserver(function (es) {
		es.forEach(function (e) {
			if (!e.isIntersecting) return;
			e.target.classList.add('in');
			e.target.querySelectorAll('[data-count]').forEach(countUp);
			var pb = e.target.querySelector('#pbar');
			if (pb) setTimeout(function () { pb.style.width = '57%'; }, 300);
			io.unobserve(e.target);
		});
	}, { threshold: 0.15 });
	document.querySelectorAll('.rv').forEach(function (el, i) {
		el.style.transitionDelay = (i % 6) * 60 + 'ms';
		io.observe(el);
	});
	function countUp(el) {
		if (el.dataset.done) return;
		el.dataset.done = 1;
		var to = parseInt(el.dataset.count, 10);
		if (reduced) { el.textContent = to; return; }
		var t0 = performance.now(), dur = 1100;
		requestAnimationFrame(function f(t) {
			var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
			el.textContent = Math.round(to * e);
			if (p < 1) requestAnimationFrame(f);
		});
	}

	/* ガチャデモ（トップページのみ要素がある） */
	var spinBtn = document.getElementById('spinBtn');
	if (spinBtn) {
		var POOLS = [
			[["鶏の唐揚げ",400],["肉じゃが",250],["麻婆豆腐",300],["鮭の塩焼き",200],["豚の生姜焼き",350],["ぶりの照り焼き",280],["回鍋肉",350],["豆腐ハンバーグ",250]],
			[["ほうれん草のおひたし",30],["きんぴらごぼう",90],["冷やしトマト",30],["ポテトサラダ",180],["無限ピーマン",100],["もやしナムル",60],["白和え",100]],
			[["豆腐とわかめの味噌汁",50],["豚汁",180],["かき玉スープ",70],["ミネストローネ",120],["なめこの味噌汁",40],["すまし汁",20]]
		];
		var spinning = false;
		var spin = function () {
			if (spinning) return;
			spinning = true;
			var picks = POOLS.map(function (p) { return p[Math.floor(Math.random() * p.length)]; });
			POOLS.forEach(function (p, i) {
				var el = document.getElementById('s' + i), slot = el.closest('.slot');
				var done = function () {
					el.textContent = picks[i][0];
					slot.querySelector('.kc').textContent = picks[i][1] + 'kcal';
				};
				if (reduced) { done(); return; }
				slot.classList.add('spin');
				el.classList.remove('land');
				var iv = setInterval(function () {
					el.textContent = p[Math.floor(Math.random() * p.length)][0];
				}, 70);
				setTimeout(function () {
					clearInterval(iv);
					done();
					el.classList.add('land');
					slot.classList.remove('spin');
					if (i === 2) {
						document.getElementById('tt').textContent =
							picks.reduce(function (a, x) { return a + x[1]; }, 0);
						spinning = false;
					}
				}, 700 + i * 380);
			});
			if (reduced) {
				document.getElementById('tt').textContent =
					picks.reduce(function (a, x) { return a + x[1]; }, 0);
				spinning = false;
			}
		};
		spinBtn.addEventListener('click', spin);
		if (!reduced) setInterval(function () {
			if (document.visibilityState === 'visible') spin();
		}, 5200);
	}

	/* アプリ設置バー（画面下・小さめ）
	   Googleは「画面を覆う大きなポップアップ」を検索順位で不利に扱うため、
	   ブラウザ標準のアプリバナー相当の小さい帯にとどめ、閉じたら30日出さない。 */
	var bar = document.getElementById('umAppBar');
	if (bar) {
		var KEY = 'um_wpbar_until';
		var standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
		var snoozed = false;
		try { snoozed = parseInt(localStorage.getItem(KEY) || '0', 10) > Date.now(); } catch (e) {}
		if (!standalone && !snoozed && innerWidth <= 820) {
			var shown = false;
			var maybeShow = function () {
				if (shown) return;
				var h = document.documentElement;
				var p = h.scrollTop / Math.max(1, h.scrollHeight - h.clientHeight);
				if (p < 0.35) return;          /* 読み始めを邪魔しない */
				shown = true;
				bar.classList.add('on');
				removeEventListener('scroll', maybeShow);
			};
			addEventListener('scroll', maybeShow, { passive: true });
			setTimeout(maybeShow, 25000);      /* 長く読んでいる人にも出す */
			bar.querySelector('.close').addEventListener('click', function () {
				bar.classList.remove('on');
				try { localStorage.setItem(KEY, Date.now() + 30 * 86400000); } catch (e) {}
			});
			bar.querySelector('.go').addEventListener('click', function () {
				if (typeof gtag === 'function') gtag('event', 'pwa_bar_click');
			});
		}
	}

	/* 料理名ティッカー */
	var marq = document.getElementById('marq');
	if (marq) {
		var D = "肉じゃが・鶏の唐揚げ・麻婆豆腐・ぶりの照り焼き・回鍋肉・おでん・寄せ鍋・すき焼き・コロッケ・ニラ玉・エビチリ・酢豚・豚汁・けんちん汁・きんぴらごぼう・かぼちゃの煮物・カレーライス・親子丼・オムライス・冷やし中華・ざるそば・お好み焼き・ハヤシライス・グラタン・";
		var seg = '<b>全185品</b>' + D;
		marq.innerHTML = seg + seg + seg;
	}
})();
