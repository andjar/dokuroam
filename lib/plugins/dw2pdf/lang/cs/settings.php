<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Kamil Nešetřil <kamil.nesetril@volny.cz>
 * @author Jaroslav Lichtblau <jlichtblau@seznam.cz>
 */
$lang['pagesize']              = 'The page format as supported by mPDF. Usually <code>A4</code> or <code>letter</code>.';
$lang['orientation']           = 'The page orientation.';
$lang['orientation_o_portrait'] = 'Portrait';
$lang['orientation_o_landscape'] = 'Landscape';
$lang['font-size']             = 'Velikost fontu normálního písma v bodech.';
$lang['doublesided']           = 'Dvoustránkový dokument začíná přidáním liché strany a obsahuje páry sudých a lichých stran. Jednostránkový dokument obsahuje pouze liché strany.';
$lang['toc']                   = 'Vložit automaticky vytvořený Obsah do PDF (poznámka: může způsobit přidání prázdných stránek při začátku na liché straně, obsah je vždy na sudé straně a nemá žádné vlastní číslo strany)';
$lang['toclevels']             = 'Určit horní úroveň a maximální hloubku podúrovní přidaných do Obsahu. Výchozí použité úrovně Obsahu wiki jsou <a href="#config___toptoclevel">toptoclevel</a> a <a href="#config___maxtoclevel">maxtoclevel</a>. Formát: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'How many section levels should be used in the PDF bookmarks? <small>(0=none, 5=all)</small>';
$lang['template']              = 'Which template should be used for formatting the PDFs?';
$lang['output']                = 'How should the PDF be presented to the user?';
$lang['output_o_browser']      = 'Show in browser';
$lang['output_o_file']         = 'Download the PDF';
$lang['usecache']              = 'Should PDFs be cached? Embedded images won\'t be ACL checked then, disable if that\'s a security concern for you.';
$lang['usestyles']             = 'You can give a comma separated list of plugins of which the <code>style.css</code> or <code>screen.css</code> should be used for PDF generation. By default only <code>print.css</code> and <code>pdf.css</code> are used.';
$lang['qrcodesize']            = 'Size of embedded QR code (in pixels <code><i>width</i><b>x</b><i>height</i></code>). Empty to disable';
$lang['showexportbutton']      = 'Show PDF export button (only when supported by your template)';
