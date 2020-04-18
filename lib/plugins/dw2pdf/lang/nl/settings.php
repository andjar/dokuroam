<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author mark prins <mprins@users.sf.net>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author Wouter Wijsman <wwijsman@live.nl>
 * @author Johan Wijnker <johan@wijnker.eu>
 * @author Peter van Diest <peter.van.diest@xs4all.nl>
 */
$lang['pagesize']              = 'Het pagina formaat zoals dat door mPDF wordt ondersteund. Normaliter <code>A4</code> of <code>letter</code>.';
$lang['orientation']           = 'Pagina oriëntatie.';
$lang['orientation_o_portrait'] = 'Staand';
$lang['orientation_o_landscape'] = 'Liggend';
$lang['font-size']             = 'De tekstgrootte voor normale tekst in pt.';
$lang['doublesided']           = 'Dubbelzijdige documenten starten met oneven pagina, en heeft paren van oneven en even pagina\'s. Enkelzijdig document heeft alleen oneven pagina\'s.';
$lang['toc']                   = 'Voeg een automatisch gegenereerde inhoudsopgave toe aan PDF (let op: Dit kan lege pagina\'s toevoegen indien gestart op een oneven genummerde pagina. De inhoudsopgave wordt altijd toegevoegd op een even genummerde pagina. Pagina\'s van de inhoudsopgave krijgen geen paginanummers)';
$lang['toclevels']             = 'Definieer bovenste niveau en maximaal onderliggende niveau\'s welke aan de inhoudsopgave worden toegevoegd.
Standaard worden de wiki niveau\'s <a href="#config___toptoclevel"> en <a href="#config___maxtoclevel">maxtoclevel</a> gebruikt. Formaat: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'Hoe veel paragraafniveau\'s moeten worden gebruikt in de PDF bladwijzers? <small>(0=geen, 5=alle)</small>';
$lang['template']              = 'Welke template moet worden gebruikt bij het creëren van PDF\'s?';
$lang['output']                = 'Hoe moet de PDF worden gepresenteerd aan de gebruiker?';
$lang['output_o_browser']      = 'Weergeven in de browser';
$lang['output_o_file']         = 'Download de PDF';
$lang['usecache']              = 'Moeten PDF\'s gebufferd worden? Ingebedde afbeeldingen zullen niet  op toegangsrechten worden gecontroleerd, schakel bufferen uit als dit een beveiligingsprobleem oplevert voor jou.';
$lang['usestyles']             = 'Je kunt een komma gescheiden lijst van plugins opgeven waarvan de  <code>style.css</code> of <code>screen.css</code> moeten worden gebruikt bij het genereren van de PDF. Standaard worden alleen <code>print.css</code> en <code>pdf.css</code> gebruikt.';
$lang['qrcodesize']            = 'Afmetingen van de ingebedde QR code (in pixels <code><i>breedte</i><b>x</b><i>hoogte</i></code>). Uit te schakelen door leeglaten.';
$lang['showexportbutton']      = 'PDF export knop weergeven (alleen als je template dat ondersteund)';
