<footer class="site-foot">
	<span class="crest">献</span><?php echo esc_html( UCHIMENU_SITE_NAME ); ?> ── 今夜の献立、10秒で決まる。
	<div class="foot-links">
		<a href="<?php echo esc_url( home_url( '/#install' ) ); ?>">アプリとして使う（ホーム画面に追加）</a>
		<a href="<?php echo uchimenu_app_url(); ?>">献立アプリ「<?php echo esc_html( UCHIMENU_APP_NAME ); ?>」を開く</a>
	</div>
</footer>
<!-- 画面下の小さなアプリ設置バー（スマホのみ・スクロール35%以降・閉じたら30日出さない） -->
<div id="umAppBar" class="um-appbar">
	<img src="<?php echo esc_url( get_template_directory_uri() . '/app-icon.png?v=' . uchimenu_asset_ver( 'app-icon.png' ) ); ?>" alt="" width="34" height="34">
	<div class="tx"><b>ホーム画面に追加できます</b><span>1タップで献立ガチャ・無料</span></div>
	<a class="go" href="<?php echo uchimenu_app_url( array( 'install' => '1' ) ); ?>">追加</a>
	<button class="close" aria-label="閉じる">✕</button>
</div>
<?php wp_footer(); ?>
</body>
</html>
