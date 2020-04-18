<?php
/**
 * Slovak language file
 *
 * @author Tibor Repček <tiborepcek@gmail.com>
 */

// for the configuration manager

$lang['pagesize']                = 'Formát stránky ako podporuje mPDF. Väčšinou <code>A4</code> alebo <code>letter</code>.';
$lang['orientation']             = 'Orientácia stránky.';
$lang['orientation_o_portrait']  = 'Na výšku';
$lang['orientation_o_landscape'] = 'Na šírku';
$lang['font-size']               = 'Veľkosť písma pre bežný text v bodoch.';
$lang['doublesided']             = 'Obojstranný dokument začína nepárnou stranou a má páry párnych a nepárnych strán. Jednostranný dokument má iba nepárne stránky.';
$lang['toc']                     = 'Pridať do PDF automaticky vytvorený obsah. Poznámka: Môže pridať prázdne strany kvôli začatiu nepárnou stranou a obsah vždy zahŕňa párny počet strán. Obsah samotný nemá číslo strany.';
$lang['toclevels']               = 'Zadajte najvyššiu a maximálnu hĺbku vnorenia položiek obsahu. Používajú sa prednastavené hĺbky obsahu <a href="#config___toptoclevel">najvyššieho</a> a <a href="#config___maxtoclevel">maximálneho</a>. Formát: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']            = 'Koľko úrovní má byť použitých v PDF záložkách? <small>(0 = žiadne, 5 = všetky)</small>';
$lang['template']                = 'Ktorá téma má byť použitá na formátovanie PDF dokumentov?';
$lang['output']                  = 'Ako sa má PDF dokument používateľovi prezentovať?';
$lang['output_o_browser']        = 'Zobraziť v prehliadači';
$lang['output_o_file']           = 'Stiahnuť PDF súbor';
$lang['usecache']                = 'Majú sa PDF súbory ukladať do vyrovnávacej pamäte? Vložené obrázky nebudú skontrolované cez ACL. Zakážte, ak je to pre vás bezpečnostné riziko.';
$lang['usestyles']               = 'Môžete zadať čiarkou oddelený zoznam rozšírení (pluginov), na ktoré sa pri generovaní PDF dokumentu bude vzťahovať <code>style.css</code> alebo <code>screen.css</code>. Prednastavená možnosť je, že sa používajú iba <code>print.css</code> a <code>pdf.css</code>.';
$lang['qrcodesize']              = 'Veľkosť vloženého QR kódu (v pixeloch <code><i>&lt;šírka&gt;</i><b>x</b><i>&lt;výška&gt;</i></code>). Nevypĺňajte, ak chcete QR kód zakázať.';
$lang['showexportbutton']        = 'Zobraziť tlačidlo na export do PDF (iba ak podporuje vaša téma)';
