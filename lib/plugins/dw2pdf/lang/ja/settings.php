<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author HokkaidoPerson <dosankomali@yahoo.co.jp>
 * @author Hideaki SAWADA <chuno@live.jp>
 */
$lang['pagesize']              = 'mPDF で対応しているページ書式。通常は <code>A4</code> か <code>letter</code>。';
$lang['orientation']           = 'ページの向き。';
$lang['orientation_o_portrait'] = '縦置き';
$lang['orientation_o_landscape'] = '横置き';
$lang['font-size']             = '通常のテキストに適切なフォントサイズ。';
$lang['doublesided']           = '両面原稿は、奇数ページから始まり、奇数ページと偶数ページが対になります。片面原稿は単独ページの集合です。';
$lang['toc']                   = 'PDF に自動生成の目次を追加する。（注：奇数ページで開始するために空白ページを追加し目次は常に偶数ページとすることができます。目次自体にページ番号はありません）';
$lang['toclevels']             = '目次の対象とする見出しのトップレベルと最大レベルを設定します。デフォルトでは Wiki の目次レベル <a href="#config___toptoclevel">toptoclevel</a> と <a href="#config___maxtoclevel">maxtoclevel</a> を使用します。書式：<code><i>&lt;トップ&gt;</i>-<i>&lt;最大&gt;</i></code>';
$lang['maxbookmarks']          = 'PDF bookmark に使用するセクションのレベル<small>（0=なし, 5=全部）</small>';
$lang['template']              = 'PDF の整形に使用するテンプレート';
$lang['output']                = 'PDF の提供方法？';
$lang['output_o_browser']      = 'ブラウザ上に表示する';
$lang['output_o_file']         = 'PDF ファイルとしてダウンロードする';
$lang['usecache']              = 'PDF をキャッシュしますか？埋込み画像は ACL 認証されません。セキュリティ上問題となる場合、無効にして下さい。';
$lang['usestyles']             = 'PDF 作成で使用する <code>style.css</code> や <code>screen.css</code> のカンマ区切り一覧。デフォルトでは <code>print.css</code> と <code>pdf.css</code> だけを使用します。';
$lang['qrcodesize']            = '埋込み QR コードの大きさ（<code><i>幅</i><b>x</b><i>高さ</i></code> のピクセル）。無効にするためには空にして下さい。';
$lang['showexportbutton']      = 'PDF 出力ボタンを表示する（テンプレートが対応してる場合のみ）';
