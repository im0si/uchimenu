<?php
/**
 * うちめにゅー テーマ
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'UCHIMENU_APP_PATH', '/app/' ); // アプリの設置パス

/* 名称の使い分け（WPの「サイトのタイトル」設定に依存させない）
   ・Webメディア（このサイト） = うちめにゅー Magazine
   ・アプリ（/app/）           = うちめにゅー                     */
define( 'UCHIMENU_SITE_NAME', 'うちめにゅー Magazine' );
define( 'UCHIMENU_APP_NAME', 'うちめにゅー' );

/** ブラウザのタブ・検索結果のタイトルもサイト名に合わせる */
function uchimenu_document_title( $parts ) {
	$parts['site'] = UCHIMENU_SITE_NAME;
	return $parts;
}
add_filter( 'document_title_parts', 'uchimenu_document_title' );

function uchimenu_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	set_post_thumbnail_size( 800, 450, true );
}
add_action( 'after_setup_theme', 'uchimenu_setup' );

/**
 * CSS / JS のバージョン文字列（キャッシュ対策）
 * ファイルの更新時刻を使うので、デプロイするたびに ?ver= が変わり、
 * 閲覧者のブラウザが自動で最新を取り直す。手動でのキャッシュ削除は不要。
 */
function uchimenu_asset_ver( $file ) {
	$path = get_template_directory() . '/' . ltrim( $file, '/' );
	$mt   = file_exists( $path ) ? filemtime( $path ) : 0;
	return $mt ? (string) $mt : wp_get_theme()->get( 'Version' );
}

function uchimenu_scripts() {
	wp_enqueue_style( 'uchimenu-fonts',
		'https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700;900&family=Archivo+Black&display=swap',
		array(), null );
	wp_enqueue_style( 'uchimenu-style', get_stylesheet_uri(), array( 'uchimenu-fonts' ),
		uchimenu_asset_ver( 'style.css' ) );
	wp_enqueue_script( 'uchimenu-front', get_template_directory_uri() . '/front.js', array(),
		uchimenu_asset_ver( 'front.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'uchimenu_scripts' );

/** アプリのURL（ディープリンク引数つき） */
function uchimenu_app_url( $args = array() ) {
	$url = home_url( UCHIMENU_APP_PATH );
	if ( $args ) $url = add_query_arg( $args, $url );
	return esc_url( $url );
}

/**
 * サイト共通ナビ（カテゴリ）
 * このサイト（記事・カテゴリ・固定ページ）はすべて「うちめにゅー Magazine」の中。
 * どのページからでも同じ入口が並ぶようヘッダー直下に置く。
 */
function uchimenu_nav() {
	$cats = get_categories( array( 'hide_empty' => false, 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' ) );
	if ( ! $cats ) return;
	$here = is_home() || is_front_page();
	echo '<nav class="site-nav" aria-label="カテゴリ"><div class="wrap nav-in">';
	echo '<a class="nav-i' . ( $here ? ' on' : '' ) . '" href="' . esc_url( home_url( '/' ) ) . '">ホーム</a>';
	foreach ( $cats as $c ) {
		$on = is_category( $c->term_id ) ? ' on' : '';
		echo '<a class="nav-i' . $on . '" href="' . esc_url( get_category_link( $c ) ) . '">'
			. '<i class="' . esc_attr( uchimenu_cat_class( $c->name ) ) . '"></i>' . esc_html( $c->name ) . '</a>';
	}
	echo '</div></nav>';
}

/**
 * パンくず（うちめにゅー Magazine › カテゴリ › 記事）
 * トップでは出さない。アプリ（/app/）はこのサイトの外なので対象外。
 */
function uchimenu_breadcrumb() {
	if ( is_front_page() ) return;
	$items = array( array( 'name' => UCHIMENU_SITE_NAME, 'url' => home_url( '/' ) ) );
	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) $items[] = array( 'name' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
		$items[] = array( 'name' => get_the_title(), 'url' => '' );
	} elseif ( is_category() ) {
		$items[] = array( 'name' => single_term_title( '', false ), 'url' => '' );
	} elseif ( is_page() ) {
		$items[] = array( 'name' => get_the_title(), 'url' => '' );
	} elseif ( is_home() ) {
		$items[] = array( 'name' => '記事一覧', 'url' => '' );
	} else {
		$items[] = array( 'name' => '検索・アーカイブ', 'url' => '' );
	}
	echo '<div class="crumb"><div class="wrap crumb-in">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $it ) {
		if ( $i ) echo '<span class="sep">›</span>';
		if ( $it['url'] && $i !== $last ) {
			echo '<a href="' . esc_url( $it['url'] ) . '">' . esc_html( $it['name'] ) . '</a>';
		} else {
			echo '<span class="cur">' . esc_html( $it['name'] ) . '</span>';
		}
	}
	echo '</div></div>';
}

/** OGP（共有時の表示名も「うちめにゅー Magazine」に揃える） */
function uchimenu_ogp() {
	$title = is_front_page() ? UCHIMENU_SITE_NAME : wp_get_document_title();
	$desc  = is_singular() && has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' );
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	echo '<meta property="og:site_name" content="' . esc_attr( UCHIMENU_SITE_NAME ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'uchimenu_ogp', 5 );

/** カテゴリ名 → 色クラス */
function uchimenu_cat_class( $name ) {
	$map = array(
		// SEO戦略書（articles/STRATEGY.md）のカテゴリ設計に対応
		'献立の決め方'         => 'g-shu',
		'カロリー別 夕食献立'  => 'g-matcha',
		'食べすぎリカバリー'   => 'g-ai',
		'1週間の献立と買い物'  => 'g-yama',
		'うちめにゅーの使い方' => 'g-sakura',
	);
	if ( isset( $map[ $name ] ) ) return $map[ $name ];
	$pool = array( 'g-shu', 'g-ai', 'g-matcha', 'g-yama', 'g-sakura' );
	return $pool[ abs( crc32( $name ) ) % 5 ];
}

/** 記事カードのフォールバックサムネイル（自作SVG） */
function uchimenu_fallback_thumb( $i ) {
	$svgs = array(
		'<svg viewBox="0 0 100 100"><rect x="18" y="18" width="64" height="64" rx="16" fill="#fffdf9" stroke="#e0cfc9" stroke-width="2"/><circle cx="36" cy="36" r="6" fill="#bf4433"/><circle cx="64" cy="36" r="6" fill="#46608c"/><circle cx="50" cy="50" r="6" fill="#d9982f"/><circle cx="36" cy="64" r="6" fill="#5b7a52"/><circle cx="64" cy="64" r="6" fill="#c9737f"/></svg>',
		'<svg viewBox="0 0 100 100"><rect x="12" y="26" width="76" height="52" rx="10" fill="#8c6b4a"/><rect x="17" y="31" width="66" height="42" rx="6" fill="#6e5138"/><rect x="20" y="34" width="28" height="36" rx="4" fill="#fffdf9"/><circle cx="34" cy="52" r="5.5" fill="#bf4433"/><rect x="51" y="34" width="29" height="17" rx="4" fill="#e3b95c"/><rect x="51" y="53" width="13" height="17" rx="4" fill="#7d9a6a"/><rect x="66" y="53" width="14" height="17" rx="4" fill="#d08a4f"/></svg>',
		'<svg viewBox="0 0 100 100"><path d="M15 48a35 35 0 0 0 70 0z" fill="#5b7a52"/><path d="M15 48a35 35 0 0 1 70 0" fill="#f7f4ea"/><path d="M30 44c4-8 12-12 20-12s16 4 20 12" fill="none" stroke="#8fae76" stroke-width="5" stroke-linecap="round"/><circle cx="34" cy="42" r="5" fill="#bf4433"/><circle cx="62" cy="39" r="5" fill="#bf4433"/></svg>',
		'<svg viewBox="0 0 100 100"><path d="M14 46a36 36 0 0 0 72 0z" fill="#b6423a"/><ellipse cx="50" cy="46" rx="34" ry="8" fill="#f5e9c8"/><path d="M26 44q6-6 12 0M44 42q6-6 12 0M62 44q6-6 12 0" fill="none" stroke="#dfc383" stroke-width="3" stroke-linecap="round"/><circle cx="58" cy="40" r="7" fill="#fffdf9"/><path d="M55 40a3 3 0 0 0 6 0a3 3 0 0 0-6 0" fill="none" stroke="#c9737f" stroke-width="2"/></svg>',
		'<svg viewBox="0 0 100 100"><ellipse cx="50" cy="58" rx="38" ry="16" fill="#fffdf9"/><ellipse cx="50" cy="55" rx="38" ry="16" fill="#efe9da"/><path d="M32 50c-2-8 4-14 11-13c1-6 12-7 15-1c7-3 14 3 12 10c4 4 1 11-5 12c-9 3-26 3-30-1c-4 0-5-4-3-7z" fill="#cf8636"/><path d="M70 62c6-2 10 1 10 5" fill="none" stroke="#e3c94b" stroke-width="5" stroke-linecap="round"/></svg>',
		'<svg viewBox="0 0 100 100"><path d="M18 52a32 32 0 0 0 64 0z" fill="#46608c"/><path d="M24 52c0-14 10-22 26-22s26 8 26 22" fill="#fffdf9"/><path d="M40 22c-3-5 3-7 0-12M52 20c-3-5 3-7 0-12M64 22c-3-5 3-7 0-12" fill="none" stroke="#cabfa4" stroke-width="3" stroke-linecap="round"/></svg>',
	);
	return $svgs[ $i % 6 ];
}

/** 記事カード1枚を出力 */
function uchimenu_card( $i = 0 ) {
	$cats = get_the_category();
	$cat  = $cats ? $cats[0] : null;
	?>
	<a class="card rv" href="<?php the_permalink(); ?>">
		<div class="thumb t<?php echo ( $i % 6 ) + 1; ?>">
			<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); }
			else { echo uchimenu_fallback_thumb( $i ); } ?>
		</div>
		<div class="card-b">
			<?php if ( $cat ) : ?>
				<span class="tag <?php echo esc_attr( uchimenu_cat_class( $cat->name ) ); ?>"><?php echo esc_html( $cat->name ); ?></span>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
			<div class="meta"><span><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span></div>
		</div>
	</a>
	<?php
}

/**
 * ショートコード [gacha_cta]
 * 例: [gacha_cta kcal="600" dishes="3" label="600kcal・3品でガチャを回す"]
 */
function uchimenu_gacha_cta_sc( $atts ) {
	$a = shortcode_atts( array(
		'kcal'   => '',
		'dishes' => '',
		'mode'   => 'diet',
		'page'   => '',
		'label'  => '🎲 今夜の献立ガチャを回す',
		'note'   => '登録不要・無料・10秒で決まります',
	), $atts );
	$args = array();
	if ( $a['kcal'] )   { $args['mode'] = $a['mode']; $args['kcal'] = $a['kcal']; }
	elseif ( $a['mode'] !== 'diet' ) { $args['mode'] = $a['mode']; }
	if ( $a['dishes'] ) $args['dishes'] = $a['dishes'];
	if ( $a['page'] )   $args['page'] = $a['page'];
	$url = uchimenu_app_url( $args );
	return '<div class="um-cta-box"><span class="lbl">▼ 読むより回すのが早いです</span>'
		. '<a class="um-cta-btn" href="' . $url . '">' . esc_html( $a['label'] ) . '</a>'
		. '<div class="um-cta-note">' . esc_html( $a['note'] ) . '</div></div>';
}
add_shortcode( 'gacha_cta', 'uchimenu_gacha_cta_sc' );

/**
 * ショートコード [install_guide]
 * ホーム画面への追加（アプリ化）手順。iPhone / Android の両方を並べる。
 */
function uchimenu_install_guide_sc( $atts ) {
	$a = shortcode_atts( array(
		'title' => 'スマホのアプリとして使う（無料・ダウンロード不要）',
	), $atts );
	$url = uchimenu_app_url( array( 'install' => '1' ) );
	ob_start(); ?>
	<div class="um-install" id="install">
		<div class="um-install-h">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/app-icon.png?v=' . uchimenu_asset_ver( 'app-icon.png' ) ); ?>" alt="" width="64" height="64">
			<div><b><?php echo esc_html( $a['title'] ); ?></b>
				<span>ホーム画面に置くと、アイコンから1タップで起動。アドレスバーが消えて全画面になり、電波がなくても開けます。</span></div>
		</div>
		<div class="um-install-cols">
			<div class="um-install-col">
				<div class="h"><span class="ic"></span>iPhone・iPad（Safari）</div>
				<ol>
					<li>下のボタンでアプリを開く</li>
					<li>画面下の共有ボタン <b>⬆︎</b> を押す</li>
					<li>「ホーム画面に追加」を選ぶ</li>
					<li>右上の「追加」で完了</li>
				</ol>
			</div>
			<div class="um-install-col">
				<div class="h"><span class="ic"></span>Android・パソコン（Chrome）</div>
				<ol>
					<li>下のボタンでアプリを開く</li>
					<li>「この端末に追加する」を押す</li>
					<li>確認画面で「インストール」を選ぶ</li>
					<li>ホーム画面にアイコンができます</li>
				</ol>
			</div>
		</div>
		<a class="um-cta-btn" href="<?php echo $url; ?>">アプリを開いて追加する</a>
		<div class="um-cta-note">登録不要・無料。記録はお使いの端末に保存されます</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'install_guide', 'uchimenu_install_guide_sc' );

/** 記事本文の末尾にアプリ誘導を自動挿入 */
function uchimenu_after_content( $content ) {
	if ( is_singular( 'post' ) && in_the_loop() && is_main_query() ) {
		$content .= do_shortcode( '[gacha_cta label="🎲 無料で献立ガチャを回してみる"]' );
	}
	return $content;
}
add_filter( 'the_content', 'uchimenu_after_content' );

/** 抜粋 */
add_filter( 'excerpt_length', function () { return 60; } );
add_filter( 'excerpt_more', function () { return '…'; } );
