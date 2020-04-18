<?php
/**
 * French language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Vincent Lecomte <vincent.lecomte@outlook.be>
 */
 
// ##################################
// #          Page Admin            #
// ##################################

$lang['AdminPageName'] = 'Extension 404Manager';

//Error Message
$lang['SameSourceAndTargetAndPage'] = 'Les pages cible et source sont les mêmes.';
$lang['NotInternalOrUrlPage'] = 'La page cible n\'existe pas et n\'est pas une URL valide.';
$lang['SourcePageExist'] = 'La page source existe.';

// Message de retour.
$lang['Saved']	= 'Sauvegardé';
$lang['Deleted'] = 'Supprimé';
$lang['Validated'] = 'Validé';

//En-tête du tableau dans la page d'administration.
$lang['SourcePage'] = 'Page Source';
$lang['TargetPage'] = 'Page Cible';
$lang['Valid'] = 'Validée';
$lang['CreationDate'] = 'Créé le';
$lang['LastRedirectionDate'] = 'Date Dern. Redirection';
$lang['LastReferrer'] = 'Dernier Référant';
$lang['Never'] = 'Jamais';
$lang['Direct Access'] = 'Accès Direct';
$lang['TargetPageType'] = 'Type Cible';
$lang['CountOfRedirection'] = 'Compteur Redirections';

// Titre des pages
$lang['AddModifyRedirection'] = "Ajouter/Modifier redirection";
$lang['ListOfRedirection'] = 'Liste des redirections';

// Message d'explication.
$lang['ExplicationValidateRedirection'] = 'Une redirection validée n\'affichera aucun message à l\'utilisateur, tandis qu\'une non-validée effectue l\'action "Aller à la meilleure correspondance" si possible.';
$lang['ValidateToSuppressMessage'] = "Vous devez approuver (valider) la redirection pour empêcher le message de s\'afficher.";

// Ajouter et modifier (formulaires).
$lang['source_page'] = 'Page Source';
$lang['target_page'] = 'Page Cible';
$lang['redirection_valid'] = 'Redirection valide';
$lang['yes'] = 'Oui';
$lang['Field'] = 'Champ' ;
$lang['Value'] = 'Valeur';
$lang['btn_addmodify'] = 'Ajouter/Modifier';

// ##################################
// ##      Messages d'action       ##
// ##################################

$lang['message_redirected_by_redirect'] = 'La page (%s) n\'existe pas. Vous avez été redirigé sur une page spécifique.';
$lang['message_redirected_to_edit_mode'] = 'La page n\'existe pas. Vous êtes maintenant en mode d\'édition afin de créer celle-ci.';
$lang['message_pagename_exist_one'] = 'La/Les page(s) suivante(s) existent déjà dans un autre espace de noms avec l\'un des mots suivants : ';
$lang['message_redirected_to_startpage'] = 'La page (%s) n\'existe pas. Vous avez été automatiquement redirigé vers la page initiale de l\'espace de noms.';
$lang['message_redirected_to_bestpagename'] = 'La page (%s) n\'existe pas. Vous avez été automatiquement redirigé vers la meilleure page correspondante.';
$lang['message_redirected_to_bestnamespace'] = 'La page (%s) n\'existe pas. Vous avez été automatiquement redirigé vers le meilleur.';
$lang['message_redirected_to_searchengine'] = 'La page (%s) n\'existe pas. Vous avez été automatiquement redirigé vers le moteur de recherche.';
$lang['message_come_from'] = 'Ce message a été envoyé par ';

?>
