<?php
/**
 * トップページ（和モダンポップ）
 */
get_header();
?>

<div class="hero wrap">
	<span class="blob a"></span><span class="blob b"></span>
	<div class="hero-grid">
		<div>
			<span class="eyebrow rv"><i></i>登録不要・無料のWebアプリ</span>
			<h1 class="rv">今夜の献立、<br><span class="shu">ガチャ</span>で決めよう。</h1>
			<p class="lede rv">毎日の「夕飯なに作ろう…」に、もう悩まない。185品の家庭料理から、あなたの目標カロリーに合う献立を10秒で。</p>
			<a class="cta rv" href="<?php echo uchimenu_app_url(); ?>">🎲 無料で献立ガチャを回す</a>
			<div class="cta-note rv">ダウンロード不要・<b>10秒</b>で今夜が決まります</div>
		</div>
		<div class="demo rv">
			<div class="demo-h">今夜の献立をお試し</div>
			<div class="slot"><span class="cat c1">主菜</span><span class="name" id="s0">鶏の唐揚げ</span><span class="kc">400kcal</span></div>
			<div class="slot"><span class="cat c2">副菜</span><span class="name" id="s1">ほうれん草のおひたし</span><span class="kc">30kcal</span></div>
			<div class="slot"><span class="cat c3">汁物</span><span class="name" id="s2">豆腐とわかめの味噌汁</span><span class="kc">50kcal</span></div>
			<div class="demo-total"><span>合計（目安）</span><span><b id="tt">480</b> kcal</span></div>
			<button class="demo-btn" id="spinBtn">🎲 もう一回まわす</button>
		</div>
	</div>
</div>

<div class="marq" aria-hidden="true"><div class="marq-track" id="marq"></div></div>

<div class="lunch wrap">
	<div class="lunch-grid">
		<div>
			<span class="kick rv">LUNCH LOG</span>
			<h2 class="rv">昼の記録が、<br><span class="yama">夜の献立</span>になる。</h2>
			<p class="rv">外食やコンビニ飯をタップで記録すると、「今夜あと何kcal食べられるか」を自動計算。その残りに収まる献立を、夜のガチャが選んでくれます。</p>
			<div class="feat">
				<div class="rv"><i style="background:var(--shu)">1</i>昼を記録する<span>── コンビニ・外食の定番32種をタップするだけ</span></div>
				<div class="rv"><i style="background:var(--yama)">2</i>残りが出る<span>── 身長・体重・減量目標から自動計算</span></div>
				<div class="rv"><i style="background:var(--matcha)">3</i>夜はガチャ<span>── 誤差50kcal以内の献立を提案</span></div>
			</div>
			<a class="sub-cta rv" href="<?php echo uchimenu_app_url( array( 'mode' => 'diet', 'page' => 'lunch' ) ); ?>">ランチ記録をつかってみる →</a>
		</div>
		<div class="phone-wr rv">
			<div class="phone"><div class="screen">
				<div class="p-head"><span class="p-dot"></span>うちめにゅー</div>
				<div class="p-body">
					<div class="p-tiles">
						<div class="p-tile"><div class="n num" data-count="820">0</div><div class="l">今日食べた kcal</div></div>
						<div class="p-tile"><div class="n num" data-count="12">0</div><div class="l">連続記録 日</div></div>
					</div>
					<div class="p-card">
						<div class="t">今夜、あと何kcal食べられる？</div>
						<div class="p-big"><span class="n num" data-count="610">0</span> <span>kcal</span></div>
						<div class="p-bar"><i id="pbar"></i></div>
						<div class="p-bar-l"><span>使った 820</span><span>1日の目安 1910</span></div>
					</div>
					<div class="p-btn">🎲 残り 610 kcal で夕食ガチャ</div>
					<div class="p-quick">
						<span class="p-pill">＋ おにぎり・180</span><span class="p-pill">＋ サラダチキン・120</span>
						<span class="p-pill">＋ カップ麺・400</span><span class="p-pill">＋ 牛丼・700</span>
					</div>
				</div>
			</div></div>
		</div>
	</div>
</div>

<div class="wrap">
	<div class="sec-head rv"><h2>新着記事</h2><span class="en">NEW ARTICLES</span></div>
	<div class="grid">
		<?php
		$q = new WP_Query( array( 'posts_per_page' => 6, 'ignore_sticky_posts' => true ) );
		$i = 0;
		if ( $q->have_posts() ) :
			while ( $q->have_posts() ) : $q->the_post();
				uchimenu_card( $i );
				$i++;
			endwhile;
			wp_reset_postdata();
		else :
			echo '<p style="color:var(--sub)">記事はこれから公開されます。おたのしみに。</p>';
		endif;
		?>
	</div>

	<div class="appbn rv">
		<div class="icon">🍚</div>
		<div><b>献立ガチャ「うちめにゅー」</b>
		<span>定食・一皿・カスタマイズの3モード。ランチを記録すれば「今夜あと何kcal食べられるか」も自動計算。</span></div>
		<a class="go" href="<?php echo uchimenu_app_url(); ?>">アプリを使ってみる →</a>
	</div>
</div>

<?php get_footer(); ?>
