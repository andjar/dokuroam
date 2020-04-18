<?php
$meta['pagesize']         = array('string');
$meta['orientation']      = array('multichoice', '_choices' => array('portrait', 'landscape'));
$meta['font-size']        = array('numeric');
$meta['doublesided']      = array('onoff');
$meta['toc']              = array('onoff');
$meta['toclevels']        = array('string', '_pattern' => '/^(|[1-5]-[1-5])$/');
$meta['maxbookmarks']     = array('numeric');
$meta['template']         = array('dirchoice', '_dir' => DOKU_PLUGIN . 'dw2pdf/tpl/');
$meta['output']           = array('multichoice', '_choices' => array('browser', 'file'));
$meta['usecache']         = array('onoff');
$meta['usestyles']        = array('string');
$meta['qrcodesize']       = array('string', '_pattern' => '/^(|\d+x\d+)$/');
$meta['showexportbutton'] = array('onoff');
