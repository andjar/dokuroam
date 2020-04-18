<?php
/**
 * English language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Nicolas GERARD <gerardnico@gmail.com>
 */

// ##################################
// ############ Admin Page ##########
// ##################################

$lang['AdminPageName'] = '404Manager Plugin';

//Error Message
$lang['SameSourceAndTargetAndPage'] = 'The target page and the source page are the same.';
$lang['NotInternalOrUrlPage'] = 'The target page don\'t exist and is not a valid URL';
$lang['SourcePageExist'] = 'The Source Page exist.';

//FeedBack Message
$lang['Saved']	= 'Saved';
$lang['Deleted'] = 'Deleted';
$lang['Validated'] = 'Validated';

//Array Header of the Admin Page
$lang['SourcePage'] = 'Source Page';
$lang['TargetPage'] = 'Target Page';
$lang['Valid'] = 'Valid';
$lang['CreationDate'] = 'Creation Date';
$lang['LastRedirectionDate'] = 'Last Redirection Date';
$lang['LastReferrer'] = 'Last Referrer';
$lang['Never'] = 'Never';
$lang['Direct Access'] = 'Direct Access';
$lang['TargetPageType'] = 'Target Page Type';
$lang['CountOfRedirection'] = 'Count Of Redirection';

// Head Titles
$lang['AddModifyRedirection'] = "Add/Modify Redirection";
$lang['ListOfRedirection'] = 'List of Redirections';

//Explication Message
$lang['ExplicationValidateRedirection'] = 'A validate redirection don\'t show any warning message. A unvalidated redirection is a proposition which comes from an action "Go to best page".';
$lang['ValidateToSuppressMessage'] = "You must approve (validate) the redirection to suppress the message of redirection.";

// Forms Add/Modify Value
$lang['source_page'] = 'Source Page';
$lang['source_page_info'] = 'The full path of the Source Page (Ex: namespace:page)';
$lang['target_page'] = 'Target Page';
$lang['target_page_info'] = 'The full path of the Target Page (Ex: namespace:page) or an URL (https://gerardnico.com)';
$lang['yes'] = 'Yes';
$lang['Field'] = 'Field' ;
$lang['Value'] = 'Value';
$lang['Information'] = 'Information';
$lang['btn_addmodify'] = 'Add/Modify';

// ##################################
// ######### Action Message #########
// ##################################

$lang['message_redirected_by_redirect'] = 'The page (%s) doesn\'t exist. You have been redirected automatically to the redirect page.';
$lang['message_redirected_to_edit_mode'] = 'This page doesn\'t exist. You have been redirected automatically in the edit mode.';
$lang['message_pagename_exist_one'] = 'The following page(s) exists already in other namespace(s) with the same name part: ';
$lang['message_redirected_to_startpage'] = 'The page (%s) doesn\'t exist. You have been redirected automatically to the start page of the namespace.';
$lang['message_redirected_to_bestpagename'] = 'The page (%s) doesn\'t exist. You have been redirected automatically to the best page.';
$lang['message_redirected_to_bestnamespace'] = 'The page (%s) doesn\'t exist. You have been redirected automatically to the best namespace.';
$lang['message_redirected_to_searchengine'] = 'The page (%s) doesn\'t exist. You have been redirected automatically to the search engine.';
$lang['message_come_from'] = 'This message was fired by the ';

$lang['SqliteMandatory'] = 'The <a href="https://www.dokuwiki.org/plugin:404manager">404 Manager plugin</a> uses the <a href="https://www.dokuwiki.org/plugin:sqlite">sqlite plugin</a> for all new functions above the version <a href="https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/CHANGES.md#v110-09-07-2016">v1.1.0 (09-07-2016)</a>. You need do install it if you want to use them. See the <a href="https://github.com/gerardnico/dokuwiki-plugin-404manager/blob/master/CHANGES.md#v110-09-07-2016">changes file</a> for the list of new functionalities.';

?>
