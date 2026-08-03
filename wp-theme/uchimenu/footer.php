<footer class="site-foot">
	<div class="wrap foot-cols">
		<div class="foot-col">
			<div class="foot-h"><span class="crest">献</span><?php echo esc_html( UCHIMENU_SITE_NAME ); ?></div>
			<p>献立の決め方・カロリー・作りおきなど、毎日のごはんを軽くする記事をお届けします。</p>
			<div class="foot-links">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">ホーム</a>
				<?php foreach ( get_categories( array( 'hide_empty' => false, 'number' => 6 ) ) as $c ) : ?>
					<a href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="foot-col">
			<div class="foot-h">献立アプリ「<?php echo esc_html( UCHIMENU_APP_NAME ); ?>」</div>
			<p>今夜の献立を10秒で。登録不要・無料。記事とは別のアプリとして使えます。</p>
			<div class="foot-links">
				<a href="<?php echo uchimenu_app_url(); ?>">アプリを開く</a>
				<a href="<?php echo esc_url( home_url( '/#install' ) ); ?>">ホーム画面に追加する</a>
			</div>
		</div>
	</div>
	<div class="foot-copy">© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( UCHIMENU_SITE_NAME ); ?></div>
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
