<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Yuriy Skalko <yuriy.skalko@gmail.com>
 * @author Vasilyy Balyasnyy <v.balyasnyy@gmail.com>
 * @author RainbowSpike <1@2.ru>
 */
$lang['pagesize']              = 'Формат страницы, поддерживаемый mPDF. Обычно <code>A4</code> или <code>letter</code>.';
$lang['orientation']           = 'Ориентация страницы.';
$lang['orientation_o_portrait'] = 'Книжная';
$lang['orientation_o_landscape'] = 'Альбомная';
$lang['font-size']             = 'Размер шрифта для обычного текста в пунктах.';
$lang['doublesided']           = 'Двухсторонний документ начинается с нечётной страницы и далее чётные-нечётные пары. Односторонний документ имеет только нечётные страницы.';
$lang['toc']                   = 'Добавить автосодержание в PDF (:!: Можно добавить пустые страницы, чтобы начиналось с нечетной страницы и содержание всегда включало четное число страниц, На странице содержания номера нет)';
$lang['toclevels']             = 'Определить высший уровень и максимальное число уровней для включения в содержание. По умолчанию применяются настройки <a href="#config___toptoclevel">toptoclevel</a> и <a href="#config___maxtoclevel">maxtoclevel</a>. Формат: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'Сколько уровней вкладок должно быть использовано для закладок PDF? <small>(0=none, 5=all)</small>';
$lang['template']              = 'Какой шаблон должен быть выбран для форматирования PDF?';
$lang['output']                = 'Как PDF должен быть предоставлен пользователю?';
$lang['output_o_browser']      = 'Показать в браузере';
$lang['output_o_file']         = 'Скачать PDF';
$lang['usecache']              = 'Кэшировать PDF? Встроенные картинки могут быть не проверены ACL, отключить, если это не безопасно для вас.';
$lang['usestyles']             = 'Вы можете указать разделённый запятыми список плагинов, <code>style.css</code> или <code>screen.css</code> которых будут использованы для генерации PDF. По умолчанию используются только <code>print.css</code> и <code>pdf.css</code>.';
$lang['qrcodesize']            = 'Размер встроенного QR кода (в пикселях <code><i>ширина</i><b>x</b><i>высота</i></code>). Пропустить, если хотите отключить.';
$lang['showexportbutton']      = 'Показать кнопку экспорта PDF (если поддерживается текущим шаблоном)';
