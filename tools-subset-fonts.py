"""index.html の見出し・ボタン等で使う文字だけに絞ったフォントを data URI で埋め込む。
外部通信は発生しない（ルール1を守る）。"""
import re, base64, io, os, sys
from fontTools.ttLib import TTFont
from fontTools.subset import Subsetter, Options

S = os.path.dirname(os.path.abspath(__file__))
HTML = "/home/user/uchimenu/index.html"
html = open(HTML, encoding="utf-8").read()

# ---- 表示用フォントを当てる箇所の文字を集める（固定のUI文言のみ）----
body = re.sub(r"<script>.*?</script>", "", html, flags=re.S)
ui = []
for pat in [r"<h1[^>]*>(.*?)</h1>", r"<h2[^>]*>(.*?)</h2>", r"<summary[^>]*>(.*?)</summary>",
            r"<button[^>]*>(.*?)</button>", r"<option[^>]*>(.*?)</option>",
            r'<div class="sheet-head"[^>]*>(.*?)</div>', r"<b[^>]*>(.*?)</b>"]:
    ui += re.findall(pat, body, re.S)
# JS内の見出し・ボタン文言（オンボーディング等）も拾う
ui += re.findall(r"<h3>(.*?)</h3>", html, re.S)
ui += re.findall(r'textContent\s*=\s*"([^"]*)"', html)
text = re.sub(r"<[^>]+>", "", "".join(ui))

extra = ("".join(chr(c) for c in range(0x3041, 0x30FF))        # ひらがな・カタカナ
         + "".join(chr(c) for c in range(0x20, 0x7F))          # 英数記号
         + "、。・「」『』（）〜ー…！？：／％＋−×÷©°"
         + "今夜献立目標品数一皿定食主菜副菜汁物主食記録連続日週間買物予算体重合計約分秒無料追加登録削除"
         + "使方法画面共有保存印刷家名前月年時間中設定必要注意確認完了開始終了次前戻進表示非")
chars = set(text) | set(extra)
chars = {c for c in chars if c.strip() and ord(c) > 0x1F}
print("対象文字数:", len(chars))

def ranges_to_set(ur):
    pts = set()
    for part in ur.split(","):
        part = part.strip().upper().replace("U+", "")
        if "-" in part:
            a, b = part.split("-"); pts.update(range(int(a, 16), int(b, 16) + 1))
        elif "?" in part:
            pts.update(range(int(part.replace("?", "0"), 16), int(part.replace("?", "F"), 16) + 1))
        else:
            pts.add(int(part, 16))
    return pts

def faces(pkg, weight):
    css = open(f"{S}/node_modules/@fontsource/{pkg}/{weight}.css", encoding="utf-8").read()
    out = []
    for m in re.finditer(r"@font-face\s*{(.*?)}", css, re.S):
        u = re.search(r"url\(['\"]?\./files/([^'\")]+\.woff2)", m.group(1))
        r = re.search(r"unicode-range:\s*([^;]+);", m.group(1))
        if u and r: out.append((u.group(1), r.group(1).strip()))
    return out

def build(pkg, weight, family, css_weight, wanted):
    css_out, total = [], 0
    for fname, ur in faces(pkg, weight):
        need = {ord(c) for c in wanted} & ranges_to_set(ur)
        if not need: continue
        f = TTFont(f"{S}/node_modules/@fontsource/{pkg}/files/{fname}", fontNumber=0)
        opt = Options(); opt.layout_features = ["*"]; opt.notdef_outline = True
        opt.desubroutinize = True; opt.hinting = False
        sub = Subsetter(options=opt); sub.populate(unicodes=need); sub.subset(f)
        buf = io.BytesIO(); f.flavor = "woff2"; f.save(buf); data = buf.getvalue()
        if len(f.getGlyphOrder()) <= 1: continue
        total += len(data)
        css_out.append("@font-face{font-family:'%s';font-style:normal;font-weight:%s;font-display:swap;"
                       "src:url(data:font/woff2;base64,%s) format('woff2');unicode-range:%s}"
                       % (family, css_weight, base64.b64encode(data).decode(), ur))
    print(f"{family}: {len(css_out)}チャンク / {total/1024:.1f}KB")
    return "".join(css_out)

css = build("zen-maru-gothic", 700, "UM Round", 700, chars)
css += build("archivo-black", 400, "UM Num", 400, set("0123456789.,%+-/kcalKCALgGmM "))
open("fonts.css", "w", encoding="utf-8").write(css)
print("生成CSS:", len(css)/1024, "KB(base64込み)")

# ---- チャンクを1本に結合して軽くする ----
def build_merged(pkg, weight, family, css_weight, wanted, tmp):
    from fontTools.merge import Merger
    os.makedirs(tmp, exist_ok=True)
    parts = []
    for i, (fname, ur) in enumerate(faces(pkg, weight)):
        need = {ord(c) for c in wanted} & ranges_to_set(ur)
        if not need: continue
        f = TTFont(f"{S}/node_modules/@fontsource/{pkg}/files/{fname}", fontNumber=0)
        opt = Options(); opt.layout_features = []; opt.notdef_outline = True
        opt.desubroutinize = True; opt.hinting = False; opt.drop_tables += ["GSUB","GPOS","GDEF","kern"]
        sub = Subsetter(options=opt); sub.populate(unicodes=need); sub.subset(f)
        cm = f.getBestCmap() or {}
        if not cm: continue
        p = f"{tmp}/p{i}.ttf"; f.save(p); parts.append(p)
    m = Merger(); merged = m.merge(parts)
    buf = io.BytesIO(); merged.flavor = "woff2"; merged.save(buf); data = buf.getvalue()
    print(f"{family}: {len(parts)}チャンク結合 -> {len(data)/1024:.1f}KB / 収録{len(merged.getBestCmap() or {})}文字")
    return ("@font-face{font-family:'%s';font-style:normal;font-weight:%s;font-display:swap;"
            "src:url(data:font/woff2;base64,%s) format('woff2')}"
            % (family, css_weight, base64.b64encode(data).decode()))

css2 = build_merged("zen-maru-gothic", 700, "UM Round", 700, chars, "/tmp/umfont")
css2 += build_merged("archivo-black", 400, "UM Num", 400, set("0123456789.,%+-/kcalKCALgGmM "), "/tmp/umnum")
open("fonts_merged.css", "w", encoding="utf-8").write(css2)
print("結合版CSS:", round(len(css2)/1024,1), "KB(base64込み)")
