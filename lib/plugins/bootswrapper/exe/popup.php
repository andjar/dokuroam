<?php
/**
 * Bootstrap Wrapper: Popup helper
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/../../../../');
define('DOKU_MEDIAMANAGER', 1); // needed to get proper CSS/JS

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/template.php');

global $lang;
global $conf;
global $JSINFO;

$JSINFO['id']        = '';
$JSINFO['namespace'] = '';

$tmp = array();
trigger_event('MEDIAMANAGER_STARTED', $tmp);
session_write_close();  //close session

if ($conf['template'] == 'bootstrap3') {

  include_once(DOKU_INC.'lib/tpl/'.$conf['template'].'/tpl_functions.php');
  include_once(DOKU_INC.'lib/tpl/'.$conf['template'].'/tpl_global.php');

  $syntax = array();

  foreach (scandir(dirname(__FILE__) . '/../syntax/') as $file) {

    if ($file == '.' || $file == '..') continue;

    $file = str_replace('.php', '', $file);
    $syntax_class_name = "syntax_plugin_bootswrapper_$file";
    $syntax_class      = new $syntax_class_name;

    if ($tag_name = $syntax_class->tag_name) {
      $tag_attributes = $syntax_class->tag_attributes;
      if ($tag_name == 'pills' || $tag_name == 'tabs') {
        unset($tag_attributes['type']);
      }
      $syntax[$tag_name] = $tag_attributes;
    }

  }

  ksort($syntax);

}

header('Content-Type: text/html; charset=utf-8');
header('X-UA-Compatible: IE=edge,chrome=1');

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>" lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>" class="no-js">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Bootstrap Wrapper Plugin</title>
  <script>(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)</script>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <?php echo tpl_favicon(array('favicon', 'mobile')) ?>
  <?php tpl_metaheaders() ?>
  <!--[if lt IE 9]>
  <script type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script type="text/javascript">

    jQuery(document).ready(function() {

      var $component = jQuery('#component'),
          $output    = jQuery('#output'),
          $preview   = jQuery('#preview');

      $component.val(jQuery('ul.nav .active a').data('component'));

      jQuery('ul.nav a').on('click', function() {

        $component.val(jQuery(this).data('component'));
        jQuery('.preview-box').removeClass('hide');

        jQuery(document).trigger('popup:reset');
        jQuery(document).trigger('popup:buildTag');

      });

      jQuery(document).on('popup:reset', function() {
        jQuery('form').each(function(){
          jQuery(this)[0].reset();
        });
        $output.val('');
        $preview.text('');
      });

      jQuery(document).on('popup:buildTag', function() {

        var component = $component.val(),
            tag       = [ '<', component ];

        jQuery('#tab-'+component+' .attribute').each(function() {

          var $attribute = jQuery(this),
              data       = $attribute.data();

          if (data.attributeType == 'boolean') {
            if ($attribute.find('input:checked').val()) {
              tag.push(' '+ data.attributeName + '="true"');
            }
          } else {
            if ($attribute.find('input,select').val()) {
              tag.push(' '+ data.attributeName + '="' + $attribute.find('input,select').val() + '"');
            }
          }

        });

        tag.push('></'+component+'>');

        $output.val(tag.join(''));
        $preview.text(tag.join(''));

      });

      jQuery('#btn-reset').on('click', function() {
        jQuery(document).trigger('popup:reset');
        jQuery(document).trigger('popup:buildTag');
      });

      jQuery('form input,form select').on('change', function() {
        jQuery(document).trigger('popup:buildTag');
      });

      jQuery('#btn-preview, #btn-insert').on('click', function() {

        jQuery(document).trigger('popup:buildTag');

        if (jQuery(this).attr('id') === 'btn-insert') {
          opener.insertAtCarret('wiki__text', $output.val());
          opener.focus();
        }

      });

    });
  </script>
  <style>
    body { padding-top: 10px; }
    footer { padding-top: 100px; }
    aside .nav li { margin: 0; }
    aside .nav li a { padding: 1px 5px !important; }
    pre#preview { white-space: pre-wrap; }
  </style>
</head>
<body class="container-fluid dokuwiki">

  <div class="row">
    <aside class="small col-xs-2">
      <ul class="nav nav-pills nav-stacked" role="tablist">

        <?php foreach (array_keys($syntax) as $tag) :?>
        <li>
          <a data-toggle="tab" href="#tab-<?php echo $tag ?>" data-component="<?php echo $tag ?>"><?php echo $tag ?></a>
        </li>
        <?php endforeach ?>

      </ul>
    </aside>

    <main class="col-xs-10 tab-content">

      <?php foreach ($syntax as $tag => $item) :?>
      <div id="tab-<?php echo $tag ?>" class="tab-pane fade">

        <h3><?php echo $tag ?></h3>

        <form class="form-horizontal">
          <?php foreach ($item as $type => $data): ?>
            <div class="form-group">
              <label class="col-sm-2 control-label"><?php echo $type ?></label>
              <div class="col-sm-10 attribute" data-attribute-type="<?php echo $data['type'] ?>" data-attribute-name="<?php echo $type ?>">
                <?php
                  switch ($data['type']) {

                    case 'string':
                      if (is_array($data['values'])) {
                        echo '<select class="form-control">';
                        echo '<option></option>';
                        foreach ($data['values'] as $value) {
                          echo '<option '.(($data['default'] == $value) ? 'selected="selected"' : '').' value="'. $value .'" class="text-'. $value .'">'. $value .'</option>';
                        }
                        echo '</select>';
                      } else {
                        echo '<input type="text" class="form-control" />';
                      }
                      break;

                    case 'boolean':
                      echo '<input type="checkbox" class="checkbox-inline" />';
                      break;

                    case 'integer':
                      echo '<input type="number" min="'. $data['min'] .'" max="'. $data['max'] .'" value="'. $data['default'] .'" class="form-control" />';
                      break;
                  }
                ?>

              </div>
            </div>
          <?php endforeach; ?>
        </form>

      </div>
      <?php endforeach; ?>

      <div class="preview-box hide">

        <h5>Preview</h5>

        <pre id="preview"></pre>

        <input type="hidden" id="output" />
        <input type="hidden" id="component" />

      </div>

    </main>

  </div>

  <footer>
    <nav class="navbar navbar-default navbar-fixed-bottom">
      <div class="container-fluid">
        <div class="navbar-text">
          <button type="button" id="btn-preview" class="hidden btn btn-default">Preview code</button>
          <button type="button" id="btn-insert" class="btn btn-success">Insert</button>
          <button type="button" id="btn-reset" class="btn btn-default">Reset</button>
        </div>
      </div>
    </nav>
  </footer>

</body>
</html>
