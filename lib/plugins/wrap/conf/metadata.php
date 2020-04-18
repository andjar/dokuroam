<?php
/*
 * Wrap plugin, configuration metadata
 *
 */

$meta['noPrefix'] = array('string');
$meta['restrictedClasses'] = array('string');
$meta['restrictionType'] = array('multichoice','_choices' => array(0,1));
$meta['syntaxDiv'] = array('multichoice','_choices' => array('WRAP','block', 'div'));
$meta['syntaxSpan'] = array('multichoice','_choices' => array('wrap', 'inline', 'span'));
$meta['darkTpl'] = array('onoff');
$meta['emulatedHeadlines'] = array('onoff');
