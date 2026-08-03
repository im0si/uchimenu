# CLAUDE.md

このファイルは、Claude Code がこのリポジトリで作業する際のガイドラインです。

## プロジェクト概要

「うちめにゅー」— 今夜の献立をガチャで決める、単一HTML完結のPWA。
GitHub Pages（https://im0si.github.io/uchimenu/）で公開している。
本番サイトは https://uchimenu.run-digital.com/ （WordPressの記事メディア＋ `/app/` にこのアプリ。main への push で自動FTPS転送される）。

- `index.html` … アプリ本体（HTML/CSS/JS をすべて内包）
- `sw.js` … Service Worker（オフラインキャッシュ、ネット優先・失敗時キャッシュ）
- `.htaccess` … 本番 `/app/` 用のキャッシュ設定。HTML/manifest/sw.js は毎回サーバーに確認（更新が即反映）、画像は1時間。`mod_headers` が無くても落ちないよう `IfModule` で囲ってある
- `manifest.webmanifest` … PWA マニフェスト（`start_url` / `scope` は `./`）
- `icon-192.png` / `icon-512.png` / `apple-touch-icon.png` … アイコン類（`?v=N` 付きで参照している。**アイコンを差し替えたら index.html・manifest・sw.js の `?v=` をまとめて上げ、`sw.js` のキャッシュ名も上げること**。ファイル名が同じだと端末に古い画像が residual で残る）（珊瑚 `#ff6f5e`→山吹 `#ffc247` の明るいグラデに、白い茶碗＋湯気＋ほお紅。UIの `--grad`（朱→山吹）より明るめにして小さい表示でも沈まないようにしている。`scratchpad` の SVG から生成。デザイン変更時は3サイズと `wp-theme/uchimenu/app-icon.png` を必ず揃える）
- `shot-gacha.png` / `shot-lunch.png` / `shot-week.png` … manifest の `screenshots`（414×896）。Chrome のインストール画面に表示される。UIを大きく変えたら撮り直す
- `.github/workflows/deploy-xserver.yml` … main への push 時、アプリ6ファイルをエックスサーバー（WPサイトの `/app/`）へFTPS自動転送。接続情報は GitHub Secrets（`XSERVER_FTP_HOST` / `XSERVER_FTP_USER` / `XSERVER_FTP_PASSWORD`。転送先パスはワークフロー内に直書き）。未設定時は何もしない
- `articles/` … SEO記事の原稿（HTML。冒頭に `<!--meta {...} -->` でタイトル・スラッグ・カテゴリ・抜粋）。`post-articles.yml`（手動実行）が WordPress REST API 経由で**下書き**として投稿する。Secrets: `WP_APP_USER` / `WP_APP_PASS`。同スラッグ既存ならスキップ
- `wp-theme/uchimenu/` … WPサイト（uchimenu.run-digital.com）用テーマ「和モダンポップ」。和紙×朱×山吹、トップにガチャデモ・スマホモック。`deploy-theme.yml` で `wp-content/themes/uchimenu/` へ自動転送。アプリの「必ず守るルール」はこのテーマには適用されない（Google Fonts使用可）が、色・世界観はアプリと揃えること。記事内CTAはショートコード `[gacha_cta kcal="600" dishes="3" label="…"]`、アプリ化の手順は `[install_guide]`（トップの「アプリとして使う」`#install` と同じ内容。`/app/?install=1` へ送る）
  - CSS/JS は `uchimenu_asset_ver()`（ファイルの更新時刻）で `?ver=` を付けている。**バージョンを手で書かないこと**。デプロイのたびに値が変わり、閲覧者のキャッシュが自動で更新される（`wp_get_theme()->get('Version')` は固定値なので使わない）
  - 下部のアプリ設置バー（`#umAppBar`。footer.php＋front.js）は、スマホのみ・スクロール35%以降または25秒後に表示し、✕で閉じたら30日出さない。Googleは検索流入ページで画面を覆う大きなポップアップを順位で不利に扱うため、**全画面のインタースティシャルにしないこと**（ブラウザ標準のアプリバナー相当の小さい帯までは許容される）。
  - レイアウト注意: `.wrap`（左右 22px）と同じ要素に付けるクラス（`.hero` `.lunch` `.head-in` など）で `padding` のショートハンドを書くと左右余白が消える。必ず `padding:○○ 22px ○○` の形で書く。また装飾の `.blob` など画面外にはみ出す要素は横スクロール（iOSでページ全体が縮小表示される）の原因になるため、`html/body` の `overflow-x:clip` と ≤780px 用の位置調整を維持する

ビルド工程・パッケージマネージャ・テストは存在しない。ブラウザで `index.html` を開けば動く。

## 名称の使い分け

- **Webメディア（uchimenu.run-digital.com）= 「うちめにゅー Magazine」**。テーマ定数 `UCHIMENU_SITE_NAME` で管理し、ヘッダーのロゴ・フッター・ブラウザのタイトル（`document_title_parts` フィルタ）に反映する。WP管理画面の「サイトのタイトル」設定には依存させない。
- **アプリ（/app/）= 「うちめにゅー」**。テーマ側で参照するときは定数 `UCHIMENU_APP_NAME`。
- サイトのヘッダーCTAは「アプリを開く」。記事内CTAはガチャの文言のままでよい。
- **このサイト（トップ・記事・カテゴリ・固定ページ）はすべて Magazine の中**。共通ヘッダー（ロゴ＋カテゴリナビ `uchimenu_nav()`）とパンくず `uchimenu_breadcrumb()`（うちめにゅー Magazine › カテゴリ › 記事）を全ページで出す。**Magazine の外は `/app/` のアプリだけ**。
- フッターは2列。左＝Magazine（説明＋カテゴリリンク）、右＝アプリ（開く／ホーム画面に追加）。OGPの `og:site_name` も `UCHIMENU_SITE_NAME`。
- アプリ側（index.html）に Magazine のブランド表記は置かないが、ヘッダー右端に本のアイコンボタン `#magBtn` だけを置く（`?` の左）。リンクは相対パス `../`・別タブで開き、`/app/` 配下に置かれているときのみ表示する（GitHub Pages 単体公開では親サイトが無いので出さない）。

## 事業方針（収益）

- 基本無料のWebサービス。将来の収益は**月額サブスクリプション（フリーミアム）**で立てる方針（Phase 2以降で Supabase＋Stripe を導入予定。その際「サーバー費用ゼロ」ルールは「固定費ゼロ・売上連動費のみ可」に改訂する）。
- **アフィリエイト・広告はアプリに一切入れない**（オーナーの決定。提案も不要）。
- プレミアム候補: クラウド同期、週間ガチャ+買い物リスト、体重レポート、除外設定、おしながき拡張。現時点では全機能無料で提供し、定着を優先する。

## 必ず守るルール

### 1. サーバー費用ゼロが絶対条件
- 有料API・外部CDN・外部サーバーへの通信を**一切追加しない**。
- `fetch` / `XMLHttpRequest` / 外部への `<script src>` / `<link href>` / Webフォント読み込みなどを新規に入れない。
- データはすべて静的辞書（`PRESET`）とローカル保存でまかなう。

### 2. 単一HTML完結の構成を維持する
- HTML / CSS / JS はすべて `index.html` に内包する。
- CSS・JS を別ファイルに分割しない。フレームワークやビルドツールを導入しない。
- 例外は既存の `sw.js` と `manifest.webmanifest` のみ（PWAの仕様上、別ファイルが必須なため）。

### 3. パスは必ず相対パス（`./`）で書く
- サブパス公開（`/uchimenu/`）と将来の独自ドメインの両方で動かすため。
- `/icon-192.png` のようなルート絶対パスは禁止。`./icon-192.png` または `icon-192.png` と書く。
- `sw.js` のキャッシュ対象、`manifest.webmanifest` の `start_url` / `scope` も相対パスを維持する。

### 4. データ保存は localStorage＋メモリ内フォールバック
- 保存は必ず `store.get()` / `store.set()`（キー接頭辞 `um_`）を経由する。
- localStorage が使えない環境（プライベートモード等）では `mem` オブジェクトへのメモリ内フォールバックで動作を継続する。この仕組みを壊さない。
- `localStorage` を直接呼ぶコードを新規に書かない。

### 5. デザインのトーンを守る（WPテーマ「和モダンポップ」と統一）
- 和紙基調：背景 `#f6f1e6`（`--bg`、data URI の SVG ノイズテクスチャ付き）、カードは生成り `#fffdf9`（`--card`）。文字色は墨 `#2b2620`（`--ink`）。
- 角丸は 18px（`--r`）を基本とする。
- アクセントの朱→山吹グラデーション `linear-gradient(45deg,#bf4433,#d9982f)`（`--grad`）は**ロゴとメインボタン（ガチャボタン等の `.gobtn`、統計数字などのごく一部）にのみ**使用する。乱用しない。
- カテゴリ色は 主菜=朱 `--shu` ／副菜=抹茶 `--matcha` ／汁物=藍 `--ai` ／主食=山吹 `--yama` ／一皿=桜 `--sakura`。Canvas（共有カード）内の `CATC` も同じ値に揃える。
- 既存の CSS 変数（`--bg` `--card` `--ink` `--sub` `--line` `--pink`(朱の別名) `--shu` `--yama` `--matcha` `--ai` `--sakura` `--grad` `--shadow` `--r`）を使う。色を追加するときは wp-theme/uchimenu/style.css のパレットから選ぶ。

### 6. フォント（本文はシステム、見出しは埋め込みサブセット）
- **本文はシステムフォント**を維持する（`-apple-system, BlinkMacSystemFont, "Hiragino Sans", "Noto Sans JP", system-ui, sans-serif`）。WPサイトも本文はシステムフォントなので見え方が揃う。
- **見出し・ボタン・タブ・カテゴリ札は `--disp`（`UM Round`＝Zen Maru Gothic）**、**統計や合計の数字は `--num`（`UM Num`＝Archivo Black）**。どちらも「使う文字だけ」に絞った woff2 を data URI で `index.html` に直接埋め込んでいる（結合済み・計約53KB）。**外部から読み込んではいけない**（ルール1）。
- 料理名など可変のテキストには表示用フォントを当てない（サブセットに無い文字が混ざり、書体がバラつくため）。`.pill` や `.slot .name` は本文フォントのまま。
- 見出し文言を増やしてサブセットに無い文字が出た場合は、フォントを作り直して差し替える。手順はリポジトリの `tools-subset-fonts.py` に入れてある（`npm i @fontsource/zen-maru-gothic @fontsource/archivo-black` と `pip install fonttools brotli` の後に実行。チャンクごとに subset → 1本に merge → woff2 → base64）。安易に外部フォントへ戻さない。
- おしながき部分の明朝体（`Hiragino Mincho ProN` 等）はシステムフォント指定のままにする。

## index.html の構成（要約）

### 画面構成

アプリはヘッダー・`<main>`（ここだけスクロール）・下部ナビの3段 flex レイアウト。
`<main>` 内に5ページ分の `.wrap` があり、下部ナビでの切り替えは `hidden` クラスの付け外しで行う（SPA遷移なし）。

| ページ | 要素ID | 内容 |
|---|---|---|
| ガチャ | `#page-gacha` | スタイル選択（定食🍱／一皿⚡／カスタマイズ🎯）、抽選元切替（定番／うちの料理）、スロット演出付きガチャ、買い物・共有・決定ボタン、1週間まとめてガチャ（日別引き直し・週の買い物リスト） |
| ランチ | `#page-lunch` | 外食・コンビニ飯の記録（定番タップ追加 `LUNCH_GROUPS`・写真つき手入力）、今日の合計と連続記録日数、夕食予算カード、たいじゅうメモ（1日1回・同日上書き・SVGグラフ・減量目標ライン） |
| めにゅー | `#page-menu` | レパートリー統計、定番からのタップ追加（`#quickList`＝定番62品のみ）、ジャンル別追加（`#genSeg`／`#genList`）、自作料理の登録フォーム（写真・カテゴリ・kcal）、登録済み一覧（お気に入り⭐・削除）。登録フォーム `#formBox` は `open` 属性で最初から開いた状態にする |
| おしながき | `#page-oshi` | 「◯◯家のお品書き」和紙風表示（`.paper`）、Canvas での画像保存、印刷/PDF、実物お届けサービスの案内 |
| きろく | `#page-log` | 「これに決めた」で保存された献立履歴（直近30件、✕ボタンで1件削除・confirm付き）、常設のインストール導線カード（`#instCard`） |

ヘッダー右端には3つのアイコンボタンが並ぶ（`#headInst` アプリにする／`#magBtn` 記事を読む／`#helpBtn` 使い方）。380px未満では `#headInst` のラベルを隠してアイコンだけにし、ロゴの折り返しを防ぐ。

このほか、初回起動時のみ表示されるオンボーディング全画面（`#onboard`、3スライド）、トースト通知（`#toast`）、ボトムシート2種（`#profile` 目標カロリー計算、`#shop` 買い物リスト。共通クラス `.sheetwrap`）、PWAインストール促進バナー（`#pwaBn`。下部ナビの上に固定表示）がある。
ランチの連続記録日数にはバッジを併記する（3日🌱／7日🔥／14日⭐／30日🏆／100日👑）。

### 主要関数

- `store.get(k,def)` / `store.set(k,v)` … localStorage ラッパー。例外時は `mem` にフォールバック
- `pick(pool,exclude)` … 重み付き抽選。直近に出た料理（`recent`）は出にくく（1日以内×0.15、3日以内×0.5）、お気に入りは出やすく（×1.4）
- `poolOf(cat)` … カテゴリ別の抽選プール。`source==="mine"` なら自分の登録料理を優先（2品未満なら定番で補完）
- `drawMenu()` … モード別の献立生成。定食＝主菜/副菜/汁物/主食の4品、一皿＝1品、カスタマイズ（内部キーは diet）＝「目標-50〜目標kcal」の窓に収まる献立を探索。品数は `dietDishes`（おまかせ/1〜4品）で指定でき、2品=主食+主菜、3品=主食+主菜+副菜と主食を必ず含める。おまかせは品数の多い構成から順に試す（4品→3品→2品→一皿→主菜のみ）。窓より下に留まった献立は `fitUp()` が1品ずつ差し替えて窓へ引き上げる。窓に入らなければ目標以下で最も近い献立、それも無理なときは最軽量の組み合わせ＋トースト通知。`pick()` はダイエットモード中ヘルシー料理を優先（×1.6）
- `renderSlots()` の各スロットには抽選母数（`poolOf(cat).length`＝「◯品から抽選」）を表示する
- `renderLunch()` / `addLunch()` / `dinnerBudget()` … ランチ記録の描画・追加・夕食予算計算。予算＝1日の目安×(1−朝食割合 `prof.bf`: 0/0.15/0.25)−今日の記録合計（下限200）。`shrinkPhoto()` は写真縮小の共通関数
- `profCalc()` / `profSync()` … プロフィール（性別・年齢・身長・体重・活動量・減量目標）から目標カロリーを計算。基礎代謝は Mifflin-St Jeor 式、1日消費＝基礎代謝×活動量（1.5/1.75/2.0）、減量は脂肪1kg≒7200kcalで日割り。安全ガードとして減量ペースは月3kg相当（1日720kcal減）まで、1日摂取は女性1200/男性1500kcalを下限に丸める。夕食は1日の35%
- `renderSlots(menu,spin)` / `spinTo(menu)` / `reroll(i)` … スロット演出の描画、回転アニメーション、1品だけの引き直し
- `renderHist()` … 履歴一覧と「今月つくった」数の描画。各行の ✕ ボタンで1件削除（confirm付き）
- `handlePhoto(e)` … 写真を Canvas で最大320pxに縮小し JPEG(0.7) の dataURL 化（localStorage 容量対策）
- `renderMy()` / `renderQuick()` / `renderGen()` … 登録済み一覧、定番タップ追加（`pop` の62品のみ）、ジャンル別追加（`GENRES` の8タブ・全186品から）の描画。共通の描画は `renderList(box,dishes)` と `dishPill(p)`。8品以上で「うちの料理から」トグルを表示
- `renderOshi()` … お品書きHTMLの描画。`#oshiImgBtn` のハンドラで 1080×1350 の Canvas 画像を生成し Web Share / ダウンロード
- `goPage(page)` … タブ切り替え
- `obRender()` / `obFinish()` … オンボーディングの描画と完了処理
- `refreshAll()` … `renderMy()`＋`renderOshi()` の一括再描画
- `drawWeek()` / `rerollWeekDay(i)` / `renderWeek()` … 1週間ガチャ。現在のモード設定で `drawMenu()` を7回呼ぶ（`recent` を都度更新して重複を抑制）
- `buildShop(nameLists,label)` / `renderShop()` … 料理名→`ING`（材料マスタ、全186品・主材料のみ調味料除く）で材料を集計（重複は ×N 表示）。チェック状態は再生成時も同名材料なら引き継ぐ
- `renderWeight()` … 体重グラフのSVG生成。減量目標（`prof.w - prof.kg`）があれば緑の目標ラインを描く
- `resultCard(menu)` … ガチャ結果を1080×1350のお品書き風Canvas画像に。共有ボタンは 画像+テキスト共有 → テキスト共有 → 画像DL → テキストコピー の順にフォールバック

### データ構造

**定番料理マスタ `PRESET`**（静的辞書、通信なし）：
`[名前, カテゴリ, kcal, 分, ヘルシー, 定番, ジャンル]` の配列を `{id:"p"+i, name, cat, kcal, min, healthy, pop, gen}` に変換した186品（主菜60・副菜44・汁物19・主食14・一皿49）。主食はごはん半膳120kcal〜炊き込みごはん300kcalまで。低カロリー麺類（糖質0麺）は単体でなく「冷やし中華」「ラーメン」など食べられる形の一皿として登録する。
カテゴリは `CATS = ["主菜","副菜","汁物","主食","一皿"]` の5種。
`pop`（定番＝62品）は「かんたん追加」に出す料理の絞り込み用。`gen` はジャンル（`wa` 和食／`yo` 洋食／`ch` 中華／`ni` 肉／`sa` 魚／`ya` 野菜／`do` 丼・麺／`su` スープ。複数可）で「カテゴリーから追加」の絞り込みに使う。汁物は `su`、一皿は `do` 系に寄せてタブ間の重複を減らしてある。
**ガチャの抽選プールは常に186品すべて**（`pop` で絞らない）。カスタマイズの誤差50kcal保証はkcal帯の多さで成り立っているため、抽選対象は減らさない。ダイエットモードの精度に関わるため、kcal帯（特に低kcal帯）が偏らないよう品を揃えている。

**localStorage 保存データ**（キーはすべて `um_` 接頭辞、JSON文字列）：

| キー | 変数 | 内容 |
|---|---|---|
| `um_menu` | `myMenu` | 登録料理の配列 `{id:"m"+timestamp, name, cat, kcal, min, photo(dataURL or null), fav}` |
| `um_hist` | `history` | 決定した献立の配列 `{ts, items:[料理名], kcal}` |
| `um_recent` | `recent` | `{料理名: 最終抽選時刻(ms)}` — 抽選の重み付けに使用 |
| `um_family` | `family` | お品書きの家名（文字列） |
| `um_onboarded` | — | オンボーディング完了フラグ（1） |
| `um_profile` | `prof` | 目標カロリー計算用プロフィール `{sex, age, h, w, act, goal, kg, mon, bf(朝食割合)}` |
| `um_lunchlog` | `lunchLog` | 食事記録の配列 `{ts, name, kcal, photo(dataURL or null)}`（最新300件まで保持） |
| `um_week` | `week` | 週間献立 `{ts, days:[{items:[料理名], kcal}]×7}` |
| `um_shop` | `shop` | 買い物リスト `{items:{材料名:個数}, done:{材料名:1}, label, ts}` |
| `um_weight` | `weightLog` | 体重記録の配列 `{d:日付キー, ts, kg}`（1日1件・最新400件） |
| `um_visits` | — | 訪問回数カウンタ（PWAバナー表示判定用。2回目以降で表示） |
| `um_pwahide` | — | PWAバナーを閉じた／インストール済みフラグ（1で以後非表示） |

**実行時状態**（保存されない）：`mode`（teishoku/quick/diet=カスタマイズ）、`source`（all/mine）、`dietDishes`（ダイエットの品数: auto/1〜4）、`current`（現在のガチャ結果）、`decided`（二重記録防止）、`photoData`（登録フォームの写真）、`obStep` / `obPicked`（オンボーディング）。

### ディープリンク（SEO記事などからの導線）

URLパラメータでアプリの初期状態を指定できる（外部通信なし・パース処理のみ）。
`?mode=diet|teishoku|quick&kcal=200..2000&dishes=auto|1..4&page=gacha|lunch|menu|oshi|log`
例: `./?mode=diet&kcal=600&dishes=3` … 記事のCTAから目標セット済みガチャへ直行。
mode か kcal 付きの訪問では初回オンボーディングを抑制する（完了フラグは立てない）。

### PWA・インストール（ホーム画面に追加）

- `sw.js` を相対パスで登録（`http(s)` プロトコル時のみ）。キャッシュ名 `uchimenu-v2`、ネット優先・失敗時キャッシュ戦略。
- キャッシュ対象を増やす場合は `sw.js` の `ASSETS` に相対パスで追記し、必要ならキャッシュ名のバージョンを上げる。
- `manifest.webmanifest` には `id` / `screenshots`（3枚・narrow）/ `shortcuts`（ガチャ・ランチ・おしながき）/ `maskable` アイコン / `display_override` を入れてある。screenshots があると Chrome のインストール画面がリッチ表示になるので消さないこと。
- インストール導線は web.dev の推奨パターン（ヘッダーの常設ボタン／コンバージョン直後／一覧内カード／必ず閉じられる）に沿って4か所:
  1. ヘッダー「⤓ アプリにする」（`#headInst`。未インストール時のみ表示）
  2. 「これに決めた」の1.8秒後に出る下部バナー（`#pwaBn`）
  3. きろくタブの常設カード（`#instCard`）
  4. WPサイトからの `?install=1`（トップの案内・記事下の `[install_guide]`・下部バー）
- バナーの頻度制御: 起動時に出すのは「2回目以降の訪問 かつ 履歴あり」だけ。✕で閉じたら14日休止（`um_pwasnooze`）、3回閉じたら永久停止（`um_pwahide`）。`beforeinstallprompt` は保持して自前UIから `prompt()` する（1イベントにつき1回だけ呼べる）。
- iOS Safari では手順シートの「わかった、やってみる」で `#iosPtr`（共有ボタンを指す矢印）を9秒表示する。iOSは共有ボタンの場所が分からず脱落するため。
- 手順シート `#instSheet` は端末を判定して出し分ける（`openInst()` / `instHTML()`）。iOS Safari＝共有→ホーム画面に追加、iOSのChrome等＝Safariで開き直す案内、Chrome系＝`beforeinstallprompt` を保持してワンタップ、その他＝メニュー操作の案内。iOS には自動プロンプトが存在しないため、この案内を削らないこと。
- 新しいファイル（アイコン・スクショ等）を追加したら `.github/workflows/deploy-xserver.yml` の `paths` と `cp` の両方に追記する（追記しないと本番の `/app/` に配信されない）。
