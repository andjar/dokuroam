<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Tor Härnqvist <tor@harnqvist.se>
 */
$lang['pagesize']              = 'Sidformatet såsom det stöds av mPDF. Normal <code>A4</code> eller <code>letter</code>.';
$lang['orientation']           = 'Sidorientering';
$lang['orientation_o_portrait'] = 'Porträtt';
$lang['orientation_o_landscape'] = 'Landskap';
$lang['font-size']             = 'Teckenstorlek för brödtext angivet i punkter';
$lang['doublesided']           = 'Dubbelsidigt dokument inled med udda sida och har par av udda eller jämna sidor. Enkelsidiga dokument har bara udda sidor.';
$lang['toc']                   = 'Lägg till en automatiskt genererad innehållsförteckning (notera att detta kan lägga till blanksidor då det inleds på udda nummer och innehållsförteckningen alltid inkluderas på jämna sidnummer, innehållsförteckningssidor i sig har inga sidnummer)  ';
$lang['toclevels']             = 'Definiera översta nivån och maximalt nivådjup som läggs till i innehållsförteckning. Standard innehållsförteckningsnivåer för wiki<a href="#config___toptoclevel">toptoclevel</a> och <a href="#config___maxtoclevel">maxtoclevel</a> används. Format: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'Hur många nivåer ska användas i PDF-bokmärken? <small>(0=inga, 5=alla)</small>';
$lang['template']              = 'Vilket templat ska användas för formatering av PDF?';
$lang['output']                = 'Hur ska PDF visas för användaren?';
$lang['output_o_browser']      = 'Visa i webbläsare';
$lang['output_o_file']         = 'Ladda ner PDF';
$lang['usecache']              = 'Skall PDF-filerna cachas? Inbäddade bilder kommer då inte att ACL-kontrolleras, avaktivera om det innebär säkerhetsbetänkligheter för dig.';
$lang['usestyles']             = 'Du kan specificera en kommaseparerad lista på de plugin som <code>style.css</code> eller <code>screen.css</code> skall använda för PDF-generering. Normalt används bara <code>print.css</code> och <code>pdf.css</code>.';
$lang['qrcodesize']            = 'Storlek på inbäddad QR-kod (i pixlar <code><i>&lt;bredd&gt;</i><b>x</b><i>&lt;höjd&gt;</i></code>). Lämna tom för att avaktivera';
$lang['showexportbutton']      = 'Visa PDF-exportknapp (bara när det stöds av ditt templat)';
