# CLAUDE.md

このファイルは、Claude Code がこのリポジトリで作業する際のガイドラインです。

## プロジェクト概要

「うちめにゅー」— 今夜の献立をガチャで決める、単一HTML完結のPWA。
GitHub Pages（https://im0si.github.io/uchimenu/）で公開している。

- `index.html` … アプリ本体（HTML/CSS/JS をすべて内包）
- `sw.js` … Service Worker（オフラインキャッシュ、ネット優先・失敗時キャッシュ）
- `manifest.webmanifest` … PWA マニフェスト（`start_url` / `scope` は `./`）
- `icon-192.png` / `icon-512.png` / `apple-touch-icon.png` … アイコン類
- `.github/workflows/deploy-xserver.yml` … main への push 時、アプリ6ファイルをエックスサーバー（WPサイトの `/app/`）へFTPS自動転送。接続情報は GitHub Secrets（`XSERVER_FTP_HOST` / `XSERVER_FTP_USER` / `XSERVER_FTP_PASSWORD` / `XSERVER_REMOTE_DIR`）。未設定時は何もしない

ビルド工程・パッケージマネージャ・テストは存在しない。ブラウザで `index.html` を開けば動く。

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

### 5. デザインのトーンを守る
- 白基調：背景 `#fafafa`（`--bg`）、カードは白。
- 角丸は 18px（`--r`）を基本とする。
- アクセントの Instagram風グラデーション `linear-gradient(45deg,#833ab4,#fd1d1d,#fcb045)`（`--grad`）は**ロゴとメインボタン（ガチャボタン等の `.gobtn`、統計数字などのごく一部）にのみ**使用する。乱用しない。
- 既存の CSS 変数（`--bg` `--card` `--ink` `--sub` `--line` `--pink` `--grad` `--shadow` `--r`）を使う。

### 6. 日本語フォントはシステムフォント
- `-apple-system, BlinkMacSystemFont, "Hiragino Sans", "Noto Sans JP", system-ui, sans-serif` を維持する。
- Webフォント（Google Fonts 等）を読み込まない（ルール1・外部通信禁止にも抵触する）。
- おしながき部分の明朝体（`Hiragino Mincho ProN` 等）もシステムフォント指定のままにする。

## index.html の構成（要約）

### 画面構成

アプリはヘッダー・`<main>`（ここだけスクロール）・下部ナビの3段 flex レイアウト。
`<main>` 内に5ページ分の `.wrap` があり、下部ナビでの切り替えは `hidden` クラスの付け外しで行う（SPA遷移なし）。

| ページ | 要素ID | 内容 |
|---|---|---|
| ガチャ | `#page-gacha` | スタイル選択（定食🍱／一皿⚡／カスタマイズ🎯）、抽選元切替（定番／うちの料理）、スロット演出付きガチャ、共有・決定ボタン |
| ランチ | `#page-lunch` | 外食・コンビニ飯の記録（定番タップ追加 `LUNCH_GROUPS`・写真つき手入力）、今日の合計と連続記録日数、プロフィールがあれば「今夜あと何kcal食べられるか」の夕食予算カード（ワンタップでカスタマイズガチャに目標をセット） |
| めにゅー | `#page-menu` | レパートリー統計、定番からのタップ追加（`#quickList`）、自作料理の登録フォーム（写真・カテゴリ・kcal）、登録済み一覧（お気に入り⭐・削除） |
| おしながき | `#page-oshi` | 「◯◯家のお品書き」和紙風表示（`.paper`）、Canvas での画像保存、印刷/PDF、実物お届けサービスの案内 |
| きろく | `#page-log` | 「これに決めた」で保存された献立履歴（直近30件） |

このほか、初回起動時のみ表示されるオンボーディング全画面（`#onboard`、3スライド）、トースト通知（`#toast`）、目標カロリー計算のボトムシート（`#profile`、ダイエットモードの「身長・体重から自動計算」ボタンで開く）がある。

### 主要関数

- `store.get(k,def)` / `store.set(k,v)` … localStorage ラッパー。例外時は `mem` にフォールバック
- `pick(pool,exclude)` … 重み付き抽選。直近に出た料理（`recent`）は出にくく（1日以内×0.15、3日以内×0.5）、お気に入りは出やすく（×1.4）
- `poolOf(cat)` … カテゴリ別の抽選プール。`source==="mine"` なら自分の登録料理を優先（2品未満なら定番で補完）
- `drawMenu()` … モード別の献立生成。定食＝主菜/副菜/汁物/主食の4品、一皿＝1品、カスタマイズ（内部キーは diet）＝「目標-50〜目標kcal」の窓に収まる献立を探索。品数は `dietDishes`（おまかせ/1〜4品）で指定でき、2品=主食+主菜、3品=主食+主菜+副菜と主食を必ず含める。おまかせは品数の多い構成から順に試す（4品→3品→2品→一皿→主菜のみ）。窓より下に留まった献立は `fitUp()` が1品ずつ差し替えて窓へ引き上げる。窓に入らなければ目標以下で最も近い献立、それも無理なときは最軽量の組み合わせ＋トースト通知。`pick()` はダイエットモード中ヘルシー料理を優先（×1.6）
- `renderSlots()` の各スロットには抽選母数（`poolOf(cat).length`＝「◯品から抽選」）を表示する
- `renderLunch()` / `addLunch()` / `dinnerBudget()` … ランチ記録の描画・追加・夕食予算計算。予算＝1日の目安×(1−朝食割合 `prof.bf`: 0/0.15/0.25)−今日の記録合計（下限200）。`shrinkPhoto()` は写真縮小の共通関数
- `profCalc()` / `profSync()` … プロフィール（性別・年齢・身長・体重・活動量・減量目標）から目標カロリーを計算。基礎代謝は Mifflin-St Jeor 式、1日消費＝基礎代謝×活動量（1.5/1.75/2.0）、減量は脂肪1kg≒7200kcalで日割り。安全ガードとして減量ペースは月3kg相当（1日720kcal減）まで、1日摂取は女性1200/男性1500kcalを下限に丸める。夕食は1日の35%
- `renderSlots(menu,spin)` / `spinTo(menu)` / `reroll(i)` … スロット演出の描画、回転アニメーション、1品だけの引き直し
- `renderHist()` … 履歴一覧と「今月つくった」数の描画
- `handlePhoto(e)` … 写真を Canvas で最大320pxに縮小し JPEG(0.7) の dataURL 化（localStorage 容量対策）
- `renderMy()` / `renderQuick()` … 登録済み一覧・定番タップ追加の描画。8品以上で「うちの料理から」トグルを表示
- `renderOshi()` … お品書きHTMLの描画。`#oshiImgBtn` のハンドラで 1080×1350 の Canvas 画像を生成し Web Share / ダウンロード
- `goPage(page)` … タブ切り替え
- `obRender()` / `obFinish()` … オンボーディングの描画と完了処理
- `refreshAll()` … `renderMy()`＋`renderOshi()` の一括再描画

### データ構造

**定番料理マスタ `PRESET`**（静的辞書、通信なし）：
`[名前, カテゴリ, kcal, 分, ヘルシー]` の配列を `{id:"p"+i, name, cat, kcal, min, healthy}` に変換した約185品（主菜60・副菜44・汁物19・主食14・一皿49）。主食はごはん半膳120kcal〜炊き込みごはん300kcalまで。低カロリー麺類（糖質0麺）は単体でなく「冷やし中華」「ラーメン」など食べられる形の一皿として登録する。
カテゴリは `CATS = ["主菜","副菜","汁物","主食","一皿"]` の5種。ダイエットモードの精度に関わるため、kcal帯（特に低kcal帯）が偏らないよう品を揃えている。

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

**実行時状態**（保存されない）：`mode`（teishoku/quick/diet=カスタマイズ）、`source`（all/mine）、`dietDishes`（ダイエットの品数: auto/1〜4）、`current`（現在のガチャ結果）、`decided`（二重記録防止）、`photoData`（登録フォームの写真）、`obStep` / `obPicked`（オンボーディング）。

### ディープリンク（SEO記事などからの導線）

URLパラメータでアプリの初期状態を指定できる（外部通信なし・パース処理のみ）。
`?mode=diet|teishoku|quick&kcal=200..2000&dishes=auto|1..4&page=gacha|lunch|menu|oshi|log`
例: `./?mode=diet&kcal=600&dishes=3` … 記事のCTAから目標セット済みガチャへ直行。
mode か kcal 付きの訪問では初回オンボーディングを抑制する（完了フラグは立てない）。

### PWA

- `sw.js` を相対パスで登録（`http(s)` プロトコル時のみ）。キャッシュ名 `uchimenu-v1`、ネット優先・失敗時キャッシュ戦略。
- キャッシュ対象を増やす場合は `sw.js` の `ASSETS` に相対パスで追記し、必要ならキャッシュ名のバージョンを上げる。
