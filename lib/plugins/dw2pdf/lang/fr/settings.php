<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 * @author NicolasFriedli <nicolas@theologique.ch>
 * @author Fabrice Dejaigher <fabrice@chtiland.com>
 * @author tuxun <tuxuntrash@gmail.com>
 * @author olinuxx/trebmuh <trebmuh@tuxfamily.org>
 */
$lang['pagesize']              = 'Le format de page selon les options de mPDF. Généralement <code>A4</code> ou <code>letter</code>.';
$lang['orientation']           = 'Orientation de la page.';
$lang['orientation_o_portrait'] = 'Portrait';
$lang['orientation_o_landscape'] = 'Paysage';
$lang['font-size']             = 'Taille de police en points pour le texte ordinaire.';
$lang['doublesided']           = 'Un document recto-verso commence par une page impaire et possède des paires de pages paires et impaires. Un document simple face n\'a que des pages impaires. ';
$lang['toc']                   = 'Ajouter au PDF une table des matières générée automatiquement. (Note: Cela peut ajouter des pages blanches à cause du début en page impaire et du fait que la TdM contient toujours un nombre pair de pages. Les pages de la TdM elle même ne sont pas numérotées.)';
$lang['toclevels']             = 'Définit le plus haut niveau et la profondeur maximum des titres ajoutés à la TdM. Par défaut, les niveaux de la TdM du wiki <a href="#config___toptoclevel"><i>toptoclevel</i></a> et <a href="#config___maxtoclevel"><i>maxtocleve</i>l</a> sont utilisés.<br />Format&nbsp;: <code><i>&lt;niveau_haut&gt;</i>-<i>&lt;niveau_max&gt;</i></code>';
$lang['maxbookmarks']          = 'Combien de niveaux de section (titres) doivent être utilisés dans les marque-pages PDF ?
<small>(0=aucun, 5=tous)</small>';
$lang['template']              = 'Quel thème doit être utilisé pour présenter les PDF?';
$lang['output']                = 'Comment le PDF doit-il être présenté à l\'utilisateur?';
$lang['output_o_browser']      = 'Afficher dans le navigateur';
$lang['output_o_file']         = 'Télécharger le PDF';
$lang['usecache']              = 'Mettre les PDF en cache ? Les images incluses le seront, alors, sans vérification des droits (ACL), désactivez cette option si cela vous pose un problème de sécurité.';
$lang['usestyles']             = 'Vous pouvez préciser une liste d\'extensions dont les fichiers <code>style.css</code> ou <code>screen.css</code> doivent être utilisés pour générer les PDF. Par défaut,  seuls les fichiers  <code>print.css</code> et <code>pdf.css</code> sont utilisés.';
$lang['qrcodesize']            = 'Taille du code QR  (en pixels <code><i>largeur</i><b>x</b><i>hauteur</i></code>). Laisser vide pour le désactiver.';
$lang['showexportbutton']      = 'Afficher le bouton «Exporter en PDF». (seulement pour les thèmes validés et prenant en charge cette fonctionnalité)';
