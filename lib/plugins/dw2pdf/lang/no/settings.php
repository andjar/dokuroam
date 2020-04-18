<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Christopher Schive <chschive@frisurf.no>
 * @author Daniel Raknes <rada@jbv.no>
 */
$lang['pagesize']              = 'Sideformatet som fungerer med mPDF. Vanligvis <code>A4</code> eller <code>brev</code>';
$lang['orientation']           = 'Sideretning.';
$lang['orientation_o_portrait'] = 'Portrett';
$lang['orientation_o_landscape'] = 'Landskap';
$lang['doublesided']           = 'Dobbelt-sidige dokumenter starter på oddetall, og har sider med både partall og oddetall. En-sidige dokumenter har kun oddetalls-sider.';
$lang['toc']                   = 'Legg til en autogenerert innholdsfortegnelse i PDF (NB! Blanke sider kan legges til for å starte på en oddetalls-side og innholdsfortegnelsen skal alltid legges alltid inn på partals-sider, innholdsfortegnelsen har ikke eget sidenummer)  ';
$lang['toclevels']             = 'Definer toppnivå og maksimal nivådybde som skal legges inn i innholdsfortegnelsen. Standard nivå for innholdsfortegnelse er <a href="#config___toptoclevel">toppnivå</a> og <a href="#config___maxtoclevel">max nivåer</a> brukes. Format: <code><i>&lt;top&gt;</i>-<i>&lt;max&gt;</i></code>';
$lang['maxbookmarks']          = 'Hvor mange seksjonsnivåer skal benyttes i PDF-bokmerkene? <small>(0=ingen, 5=alle)</small>';
$lang['template']              = 'Hvilken mal skal brukes for formattering av PDF-er?';
$lang['output']                = 'Hvordan skal PDF-filen presenteres for brukeren?';
$lang['output_o_browser']      = 'Vis i nettleseren';
$lang['output_o_file']         = 'Last ned PDF-filen';
$lang['usecache']              = 'Skal PDF-er caches? Innkapslede bilder blir da ikke kontrollert av ACL, deaktivér hvis det er et sikkerhetsproblem for deg.';
$lang['usestyles']             = 'Du kan angi en kommaseparert liste med tillegg hvor <code>style.css</code> eller <code>screen.css</code> skal benyttes til generering av PDF. Som standard benyttes kun <code>print.css</code> and <code>pdf.css</code>.';
$lang['qrcodesize']            = 'Størrelse på innkapslet QR-kode (i piksler <code><i>bredde</i><b>x</b><i>høyde</i></code>). Ingen verdi vil deaktivere.';
$lang['showexportbutton']      = 'Vis PDF-eksportknapp (bare når det er støttet av valgt mal)';
