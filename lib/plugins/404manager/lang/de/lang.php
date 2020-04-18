<?php
/**
 * English language file
 *
 * @license      GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author       Nicolas GERARD <gerardnico@gmail.com>
 * @translation	 Dominik Reichardt <dominik@reichardt-online.it>
 */
 
// ##################################
// ############ Admin Page ##########
// ##################################

$lang['AdminPageName'] = '404Manager Plugin';

//Error Message
$lang['SameSourceAndTargetAndPage'] = 'Die Quell und die Zielseite sind identisch.';
$lang['NotInternalOrUrlPage'] = 'Die Zielseite existiert nicht oder besitzt keine gültige URL';
$lang['SourcePageExist'] = 'Die Quellseite existiert.';

//FeedBack Message
$lang['Saved']	= 'Gespeichert';
$lang['Deleted'] = 'Gelöscht';
$lang['Validated'] = 'Validiert';

//Array Header of the Admin Page
$lang['SourcePage'] = 'Quell Seite';
$lang['TargetPage'] = 'Ziel Seite';
$lang['Valid'] = 'Validieren';
$lang['CreationDate'] = 'Erstellungs Datum';
$lang['LastRedirectionDate'] = 'Datum letzte Weiterleitung';
$lang['LastReferrer'] = 'Last Referrer';
$lang['Never'] = 'Niemals';
$lang['Direct Access'] = 'Direkter Zugriff';
$lang['TargetPageType'] = 'Ziel Seiten Typ';
$lang['CountOfRedirection'] = 'Anzahl der Weiterleitungen';

// Head Titles
$lang['AddModifyRedirection'] = "Hinzufügen/Bearbeiten von Weiterleitungen";
$lang['ListOfRedirection'] = 'Liste der Weiterleitungen';

//Explication Message
$lang['ExplicationValidateRedirection'] = 'Eine Genehmigte (validierte) Seite zeigt keinen Hinweis bei Umleitung. Eine nicht validierte Seite zeigt den Hinweis "Zur besten Seite wechseln".';
$lang['ValidateToSuppressMessage'] = "Sie müssen die Umleitung genehmigen (Validieren), um die Benachrichtigung bei der Umleitung zu unterdrücken.";

// Forms Add/Modify Value
$lang['source_page'] = 'Quell Seite';
$lang['target_page'] = 'Ziel Seite';
$lang['redirection_valid'] = 'Weiterleitung Validiert';
$lang['yes'] = 'Ja';
$lang['Field'] = 'Feld' ;
$lang['Value'] = 'Wert';
$lang['btn_addmodify'] = 'Hinzufügen/Bearbeiten';

// ##################################
// ######### Action Message #########
// ##################################

$lang['message_redirected_by_redirect'] = 'Die Seite (%s) existiert nicht. Sie wurden zu der Weiterleitungsseite weitergeleitet.';
$lang['message_redirected_to_edit_mode'] = 'Diese Seite existiert nicht. Sie wurden automatisch in den Bearbeitungsmodus weitergeleitet.';
$lang['message_pagename_exist_one'] = 'Die Seite(%s) existiert bereits in einem anderen Namensraum : ';
$lang['message_redirected_to_startpage'] = 'Die Seite (%s) existiert nicht. Sie wurden automatisch zu der Startseite des Namensraums weitergeleitet..';
$lang['message_redirected_to_bestpagename'] = 'Die Seite (%s) existiert nicht. Sie wurden automatisch zu dem nächst besten Treffer weitergeleitet.';
$lang['message_redirected_to_bestnamespace'] = 'Die Seite (%s) existiert nicht. Sie wurden zu dem nächst besten Namensraum weitergeleitet.';
$lang['message_redirected_to_searchengine'] = 'Die Seite (%s) existiert nicht. Sie wurden automatisch auf die Seite der Suchergebnisse weitergeleitet.';
$lang['message_come_from'] = 'Diese Nachricht kommt von ';

?>
