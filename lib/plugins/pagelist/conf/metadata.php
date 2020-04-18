<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the Pagelist Plugin
 *
 * @author    Esther Brunner <wikidesign@gmail.com>
 */

$meta['style']        = array('multichoice',
                          '_choices' => array('default', 'table', 'list', 'simplelist'));
$meta['showheader']   = array('onoff');
$meta['showdate']     = array('multichoice', '_choices' => array('0', '1', '2'));
$meta['showuser']     = array('multichoice', '_choices' => array('0', '1', '2'));
$meta['showdesc']     = array('multichoice', '_choices' => array('0', '160', '500'));
$meta['showcomments'] = array('onoff');
$meta['showlinkbacks']= array('onoff');
$meta['showtags']     = array('onoff');
$meta['showfirsthl']  = array('onoff');
$meta['showimage']    = array('onoff');
$meta['showdiff']     = array('onoff');
$meta['sort']         = array('onoff');
$meta['rsort']        = array('onoff');

//Setup VIM: ex: et ts=2 :
