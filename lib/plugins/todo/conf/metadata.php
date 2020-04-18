<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the ToDo Plugin
 *
 * @author    Babbage <babbage@digitalbrink.com>
 */

$meta['AllowLinks'] = array('onoff');
$meta['ActionNamespace'] = array('string');
$meta['Strikethrough'] = array('onoff');
$meta['CheckboxText'] = array('onoff');
$meta['Checkbox'] = array('onoff');
$meta['Header'] = array('multichoice', '_choices' => array('id','firstheader','none'));
$meta['Username'] = array('multichoice', '_choices' => array('user','real','none'));
$meta['ShowdateTag'] = array('onoff');
$meta['ShowdateList'] = array('onoff');

//Setup VIM: ex: et ts=2 enc=utf-8 :
