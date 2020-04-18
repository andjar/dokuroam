<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the tag plugin
 *
 * @author    Esther Brunner <wikidesign@gmail.com>
 */

$meta['namespace']          = array('string');
$meta['sortkey']            = array('multichoice',
                                    '_choices' => array('cdate', 'mdate', 'pagename', 'id', 'ns', 'title'));
$meta['sortorder']          = array('multichoice',
                                    '_choices' => array('ascending', 'descending'));
$meta['pagelist_flags']     = array('string');
$meta['toolbar_icon']       = array('onoff');
$meta['list_tags_of_subns'] = array('onoff');
$meta['tags_list_css']      = array('multichoice',
                                    '_choices' => array('tags', 'tagstop'));

//Setup VIM: ex: et ts=2 :
