<?php
/**
 * Helper Component for the Wrap Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Anika Henke <anika@selfthinker.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_wrap extends DokuWiki_Plugin {
    static protected $boxes = array ('wrap_box', 'wrap_danger', 'wrap_warning', 'wrap_caution', 'wrap_notice', 'wrap_safety',
                                     'wrap_info', 'wrap_important', 'wrap_alert', 'wrap_tip', 'wrap_help', 'wrap_todo',
                                     'wrap_download', 'wrap_hi', 'wrap_spoiler');
    static protected $paragraphs = array ('wrap_leftalign', 'wrap_rightalign', 'wrap_centeralign', 'wrap_justify');
    static protected $column_count = 0;
    static $box_left_pos = 0;
    static $box_right_pos = 0;
    static $box_first = true;
    static $table_entr = 0;

    /**
     * get attributes (pull apart the string between '<wrap' and '>')
     *  and identify classes, width, lang and dir
     *
     * @author Anika Henke <anika@selfthinker.org>
     * @author Christopher Smith <chris@jalakai.co.uk>
     *   (parts taken from http://www.dokuwiki.org/plugin:box)
     */
    function getAttributes($data, $useNoPrefix=true) {

        $attr = array();
        $tokens = preg_split('/\s+/', $data, 9);

        // anonymous function to convert inclusive comma separated items to regex pattern
        $pattern = function ($csv) {
            return '/^(?:'. str_replace(['?','*',' ',','],
                                        ['.','.*','','|'], $csv) .')$/';
        };

        // noPrefix: comma separated class names that should be excluded from
        //   being prefixed with "wrap_",
        //   each item may contain wildcard (*, ?)
        $noPrefix = ($this->getConf('noPrefix') && $useNoPrefix) ? $pattern($this->getConf('noPrefix')) : '';

        // restrictedClasses : comma separated class names that should be checked
        //   based on restriction type (whitelist or blacklist),
        //   each item may contain wildcard (*, ?)
        $restrictedClasses = ($this->getConf('restrictedClasses')) ?
                            $pattern($this->getConf('restrictedClasses')) : '';
        $restrictionType = $this->getConf('restrictionType');

        foreach ($tokens as $token) {

            //get width
            if (preg_match('/^\d*\.?\d+(%|px|em|rem|ex|ch|vw|vh|pt|pc|cm|mm|in)$/', $token)) {
                $attr['width'] = $token;
                continue;
            }

            //get lang
            if (preg_match('/\:([a-z\-]+)/', $token)) {
                $attr['lang'] = trim($token,':');
                continue;
            }

            //get id
            if (preg_match('/#([A-Za-z0-9_-]+)/', $token)) {
                $attr['id'] = trim($token,'#');
                continue;
            }

            //get classes
            //restrict token (class names) characters to prevent any malicious data
            if (preg_match('/[^A-Za-z0-9_-]/',$token)) continue;
            if ($restrictedClasses) {
                $classIsInList = preg_match($restrictedClasses, $token);
                // either allow only certain classes or disallow certain classes
                if ($restrictionType xor $classIsInList) continue;
            }
            // prefix adjustment of class name
            $prefix = (preg_match($noPrefix, $token)) ? '' : 'wrap_';
            $attr['class'] = (isset($attr['class']) ? $attr['class'].' ' : '').$prefix.$token;
        }
        if ($this->getConf('darkTpl')) {
            $attr['class'] = (isset($attr['class']) ? $attr['class'].' ' : '').'wrap__dark';
        }
        if ($this->getConf('emulatedHeadings')) {
            $attr['class'] = (isset($attr['class']) ? $attr['class'].' ' : '').'wrap__emuhead';
        }

        //get dir
        if($attr['lang']) {
            $lang2dirFile = dirname(__FILE__).'/conf/lang2dir.conf';
            if (@file_exists($lang2dirFile)) {
                $lang2dir = confToHash($lang2dirFile);
                $attr['dir'] = strtr($attr['lang'],$lang2dir);
            }
        }

        return $attr;
    }

    /**
     * build attributes (write out classes, width, lang and dir)
     */
    function buildAttributes($data, $addClass='', $mode='xhtml') {

        $attr = $this->getAttributes($data);
        $out = '';

        if ($mode=='xhtml') {
            if($attr['class']) $out .= ' class="'.hsc($attr['class']).' '.$addClass.'"';
            // if used in other plugins, they might want to add their own class(es)
            elseif($addClass)  $out .= ' class="'.$addClass.'"';
            if($attr['id'])    $out .= ' id="'.hsc($attr['id']).'"';
            // width on spans normally doesn't make much sense, but in the case of floating elements it could be used
            if($attr['width']) {
                if (strpos($attr['width'],'%') !== false) {
                    $out .= ' style="width: '.hsc($attr['width']).';"';
                } else {
                    // anything but % should be 100% when the screen gets smaller
                    $out .= ' style="width: '.hsc($attr['width']).'; max-width: 100%;"';
                }
            }
            // only write lang if it's a language in lang2dir.conf
            if($attr['dir'])   $out .= ' lang="'.$attr['lang'].'" xml:lang="'.$attr['lang'].'" dir="'.$attr['dir'].'"';
        }

        return $out;
    }

    /**
     * render ODT element, Open
     * (get Attributes, select ODT element that fits, render it, return element name)
     */
    function renderODTElementOpen($renderer, $HTMLelement, $data) {
        $attr = $this->getAttributes($data, false);
        $attr_string = $this->buildAttributes($data);
        $classes = explode (' ', $attr['class']);

        // Get language
        $language = $attr['lang'];

        $is_indent    = in_array ('wrap_indent', $classes);
        $is_outdent   = in_array ('wrap_outdent', $classes);
        $is_column    = in_array ('wrap_column', $classes);
        $is_group     = in_array ('wrap_group', $classes);
        $is_pagebreak = in_array ('wrap_pagebreak', $classes);

        // Check for multicolumns
        $columns = 0;
        preg_match ('/wrap_col\d/', $attr ['class'], $matches);
        if ( empty ($matches [0]) === false ) {
            $columns = $matches [0] [strlen($matches [0])-1];
        }

        // Check for boxes
        $is_box = false;
        foreach (self::$boxes as $box) {
            if ( strpos ($attr ['class'], $box) !== false ) {
                $is_box = true;
                break;
            }
        }

        // Check for paragraphs
        $is_paragraph = false;
        if ( empty($language) === false ) {
            $is_paragraph = true;
        } else {
            foreach (self::$paragraphs as $paragraph) {
                if ( strpos ($attr ['class'], $paragraph) !== false ) {
                    $is_paragraph = true;
                    break;
                }
            }
        }

        $style = NULL;
        if ( empty($attr['width']) === false ) {
            $style = 'width: '.$attr['width'].';';
        }
        $attr ['class'] = 'dokuwiki '.$attr ['class'];

        // Call corresponding functions for current wrap class
        if ( $HTMLelement == 'span' ) {
            if ( $is_indent === false && $is_outdent === false ) {
                $this->renderODTOpenSpan ($renderer, $attr ['class'], $style, $language, $attr_string);
                return 'span';
            } else {
                $this->renderODTOpenParagraph ($renderer, $attr ['class'], $style, $attr ['dir'], $language, $is_indent, $is_outdent, true, $attr_string);
                return 'paragraph';
            }
        } else if ( $HTMLelement == 'div' ) {
            if ( $is_box === true ) {
                $wrap = $this->loadHelper('wrap');
                $fullattr = $wrap->buildAttributes($data, 'plugin_wrap');

                if ( method_exists ($renderer, 'getODTPropertiesFromElement') === false ) {
                    $this->renderODTOpenBox ($renderer, $attr ['class'], $style, $fullattr);
                } else {
                    $this->renderODTOpenTable ($renderer, $attr, $style,  $attr_string);
                }
                return 'box';
            } else if ( $columns > 0 ) {
                $this->renderODTOpenColumns ($renderer, $attr ['class'], $style);
                return 'multicolumn';
            } else if ( $is_paragraph === true || $is_indent === true || $is_outdent === true ) {
                $this->renderODTOpenParagraph ($renderer, $attr ['class'], $style, $attr ['dir'], $language, $is_indent, $is_outdent, false, $attr_string);
                return 'paragraph';
            } else if ( $is_pagebreak === true ) {
                $renderer->pagebreak ();
                // Pagebreak hasn't got a closing stack so we return/push 'other' on the stack
                return 'other';
            } else if ( $is_column === true ) {
                $this->renderODTOpenColumn ($renderer, $attr ['class'], $style, $attr_string);
                return 'column';
            } else if ( $is_group === true ) {
                $this->renderODTOpenGroup ($renderer, $attr ['class'], $style);
                return 'group';
            } else if (strpos ($attr ['class'], 'wrap_clear') !== false ) {
                $renderer->linebreak();
                $renderer->p_close();
                $renderer->p_open();

                self::$box_left_pos = 0;
                self::$box_right_pos = 0;
                self::$box_first = true;
            }
        }
        return 'other';
    }

    /**
     * render ODT element, Close
     */
    function renderODTElementClose($renderer, $element) {
        switch ($element) {
            case 'box':
                if ( method_exists ($renderer, 'getODTPropertiesFromElement') === false ) {
                    $this->renderODTCloseBox ($renderer);
                } else {
                    $this->renderODTCloseTable ($renderer);
                }
            break;
            case 'multicolumn':
                $this->renderODTCloseColumns($renderer);
            break;
            case 'paragraph':
                $this->renderODTCloseParagraph($renderer);
            break;
            case 'column':
                $this->renderODTCloseColumn($renderer);
            break;
            case 'group':
                $this->renderODTCloseGroup($renderer);
            break;
            case 'span':
                $this->renderODTCloseSpan($renderer);
            break;
            // No default by intention.
        }
    }

    function renderODTOpenBox ($renderer, $class, $style, $fullattr) {
        $properties = array ();

        if ( method_exists ($renderer, 'getODTProperties') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }

        // Get CSS properties for ODT export.
        $renderer->getODTProperties ($properties, 'div', $class, $style);

        if ( empty($properties ['background-image']) === false ) {
            $properties ['background-image'] =
                $renderer->replaceURLPrefix ($properties ['background-image'], DOKU_INC);
        }

        if ( empty($properties ['float']) === true ) {
            // If the float property is not set, set it to 'left' becuase the ODT plugin
            // would default to 'center' which is diffeent to the XHTML behaviour.
            if ( strpos ($class, 'wrap_center') === false ) {
                $properties ['float'] = 'left';
            } else {
                $properties ['float'] = 'center';
            }
        }

        // The display property has differing usage in CSS. So we better overwrite it.
        $properties ['display'] = 'always';
        if ( stripos ($class, 'wrap_noprint') !== false ) {
            $properties ['display'] = 'screen';
        }
        if ( stripos ($class, 'wrap_onlyprint') !== false ) {
            $properties ['display'] = 'printer';
        }

        $renderer->_odtDivOpenAsFrameUseProperties ($properties);
    }

    function renderODTCloseBox ($renderer) {
        if ( method_exists ($renderer, '_odtDivCloseAsFrame') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }
        $renderer->_odtDivCloseAsFrame ();
    }

    function renderODTOpenColumns ($renderer, $class, $style) {
        $properties = array ();

        if ( method_exists ($renderer, 'getODTProperties') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }

        // Get CSS properties for ODT export.
        $renderer->getODTProperties ($properties, 'div', $class, $style);

        $renderer->_odtOpenMultiColumnFrame($properties);
    }

    function renderODTCloseColumns ($renderer) {
        if ( method_exists ($renderer, '_odtCloseMultiColumnFrame') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }
        $renderer->_odtCloseMultiColumnFrame();
    }

    function renderODTOpenParagraph ($renderer, $class, $style, $dir, $language, $is_indent, $is_outdent, $indent_first, $attr=NULL) {
        $properties = array ();

        if ( method_exists ($renderer, 'getODTPropertiesFromElement') === true ) {
            // Get CSS properties for ODT export.
            // Set parameter $inherit=false to prevent changiung the font-size and family!
            $renderer->getODTPropertiesNew ($properties, 'p', $attr, NULL, false);
        } else if ( method_exists ($renderer, 'getODTProperties') === true ) {
            // Get CSS properties for ODT export (deprecated version).
            $renderer->getODTProperties ($properties, 'p', $class, $style);

            if ( empty($properties ['background-image']) === false ) {
                $properties ['background-image'] =
                    $renderer->replaceURLPrefix ($properties ['background-image'], DOKU_INC);
            }
        } else {
            // To old ODT plugin version.
            return;
        }

        if ( empty($properties ['text-align']) )
        {
            if ($dir == 'ltr') {
                $properties ['text-align'] = 'left';
                $properties ['writing-mode'] = 'lr';
            }
            if ($dir == 'rtl') {
                $properties ['text-align'] = 'right';
                $properties ['writing-mode'] = 'rl';
            }
        }

        $name = '';
        if ( empty($language) === false ) {
            $properties ['lang'] = $language;
            $name .= 'Language: '.$language;
        }

        if ( $indent_first === true ) {
            // Eventually indent or outdent first line only...
            if ( $is_indent === true ) {
                // FIXME: Has to be adjusted if test direction will be supported.
                // See all.css
                $properties ['text-indent'] = $properties ['padding-left'];
                $properties ['padding-left'] = 0;
                $name .= 'Indent first';
            }
            if ( $is_outdent === true ) {
                // FIXME: Has to be adjusted if text (RTL, LTR) direction will be supported.
                // See all.css
                $properties ['text-indent'] = $properties ['margin-left'];
                $properties ['margin-left'] = 0;
                $name .= 'Outdent first';
            }
        } else {
            // Eventually indent or outdent the whole paragraph...
            if ( $is_indent === true ) {
                // FIXME: Has to be adjusted if test direction will be supported.
                // See all.css
                $properties ['margin-left'] = $properties ['padding-left'];
                $properties ['padding-left'] = 0;
                $name .= 'Indent';
            }
            if ( $is_outdent === true ) {
                // Nothing to change: keep left margin property.
                // FIXME: Has to be adjusted if text (RTL, LTR) direction will be supported.
                // See all.css
                $name .= 'Outdent';
            }
        }

        $renderer->p_close();
        if ( method_exists ($renderer, 'createParagraphStyle') === false ) {
            // Older ODT plugin version.
            $renderer->_odtParagraphOpenUseProperties($properties);
        } else {
            // Newer version create our own common styles.

            // Create parent style to group the others beneath it
            if (!$renderer->styleExists('Plugin_Wrap_Paragraphs')) {
                $parent_properties = array();
                $parent_properties ['style-parent'] = NULL;
                $parent_properties ['style-class'] = 'Plugin Wrap Paragraphs';
                $parent_properties ['style-name'] = 'Plugin_Wrap_Paragraphs';
                $parent_properties ['style-display-name'] = 'Plugin Wrap';
                $renderer->createParagraphStyle($parent_properties);
            }

            $name .= $this->getODTCommonStyleName($class);
            $style_name = 'Plugin_Wrap_Paragraph_'.$name;
            if (!$renderer->styleExists($style_name)) {
                $properties ['style-parent'] = 'Plugin_Wrap_Paragraphs';
                $properties ['style-class'] = NULL;
                $properties ['style-name'] = $style_name;
                $properties ['style-display-name'] = $name;
                $renderer->createParagraphStyle($properties);
            }

            $renderer->p_open($style_name);
        }
    }

    function renderODTCloseParagraph ($renderer) {
        if ( method_exists ($renderer, 'p_close') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }
        $renderer->p_close();
    }

    function renderODTOpenColumn ($renderer, $class, $style, $attr) {
        $properties = array ();

        if ( method_exists ($renderer, 'getODTPropertiesFromElement') === true ) {
            // Get CSS properties for ODT export.
            $renderer->getODTPropertiesNew ($properties, 'div', $attr);
        } else if ( method_exists ($renderer, 'getODTProperties') === true ) {
            // Get CSS properties for ODT export (deprecated version).
            $renderer->getODTProperties ($properties, NULL, $class, $style);
        } else {
            // To old ODT plugin version.
            return;
        }

        // Frames/Textboxes still have some issues with formatting (at least in LibreOffice)
        // So as a workaround we implement columns as a table.
        // This is why we now use the margin of the div as the padding for the ODT table.
        $properties ['padding-left'] = $properties ['margin-left'];
        $properties ['padding-right'] = $properties ['margin-right'];
        $properties ['padding-top'] = $properties ['margin-top'];
        $properties ['padding-bottom'] = $properties ['margin-bottom'];
        $properties ['margin-left'] = NULL;
        $properties ['margin-right'] = NULL;
        $properties ['margin-top'] = NULL;
        $properties ['margin-bottom'] = NULL;

        // Percentage values are not supported for the padding. Convert to absolute values.
        $length = strlen ($properties ['padding-left']);
        if ( $length > 0 && $properties ['padding-left'] [$length-1] == '%' ) {
            $properties ['padding-left'] = trim ($properties ['padding-left'], '%');
            $properties ['padding-left'] = $renderer->_getAbsWidthMindMargins ($properties ['padding-left']).'cm';
        }
        $length = strlen ($properties ['padding-right']);
        if ( $length > 0 && $properties ['padding-right'] [$length-1] == '%' ) {
            $properties ['padding-right'] = trim ($properties ['padding-right'], '%');
            $properties ['padding-right'] = $renderer->_getAbsWidthMindMargins ($properties ['padding-right']).'cm';
        }
        $length = strlen ($properties ['padding-top']);
        if ( $length > 0 && $properties ['padding-top'] [$length-1] == '%' ) {
            $properties ['padding-top'] = trim ($properties ['padding-top'], '%');
            $properties ['padding-top'] = $renderer->_getAbsWidthMindMargins ($properties ['padding-top']).'cm';
        }
        $length = strlen ($properties ['padding-bottom']);
        if ( $length > 0 && $properties ['padding-bottom'] [$length-1] == '%' ) {
            $properties ['padding-bottom'] = trim ($properties ['padding-bottom'], '%');
            $properties ['padding-bottom'] = $renderer->_getAbsWidthMindMargins ($properties ['padding-bottom']).'cm';
        }

        $this->column_count++;
        if ( $this->column_count == 1 ) {
            // If this is the first column opened since the group was opened
            // then we have to open the table and a (single) row first.
            $properties ['width'] = '100%';
            $renderer->_odtTableOpenUseProperties($properties);
            $renderer->_odtTableRowOpenUseProperties($properties);
        }

        // We did not specify any max column value when we opened the table.
        // So we have to tell the renderer to add a column just now.
        unset($properties ['width']);
        $renderer->_odtTableAddColumnUseProperties($properties);

        // Open the cell.
        $renderer->_odtTableCellOpenUseProperties($properties);
    }

    function renderODTCloseColumn ($renderer) {
        if ( method_exists ($renderer, '_odtTableAddColumnUseProperties') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }

        $renderer->tablecell_close();
    }

    function renderODTOpenGroup ($renderer, $class, $style) {
        // Nothing to do for now.
    }

    function renderODTCloseGroup ($renderer) {
        // If a table has been opened in the group we close it now.
        if ( $this->column_count > 0 ) {
            // At last we need to close the row and the table!
            $renderer->tablerow_close();
            //$renderer->table_close();
            $renderer->_odtTableClose();
        }
        $this->column_count = 0;
    }

    function renderODTOpenSpan ($renderer, $class, $style, $language, $attr) {
        $properties = array ();

        if ( method_exists ($renderer, 'getODTPropertiesFromElement') === true ) {
            // Get CSS properties for ODT export.
            // Set parameter $inherit=false to prevent changiung the font-size and family!
            $renderer->getODTPropertiesNew ($properties, 'span', $attr, NULL, false);
        } else if ( method_exists ($renderer, 'getODTProperties') === true ) {
            // Get CSS properties for ODT export (deprecated version).
            $renderer->getODTProperties ($properties, 'span', $class, $style);

            if ( empty($properties ['background-image']) === false ) {
                $properties ['background-image'] =
                    $renderer->replaceURLPrefix ($properties ['background-image'], DOKU_INC);
            }
        } else {
            // To old ODT plugin version.
            return;
        }

        $name = '';
        if ( empty($language) === false ) {
            $properties ['lang'] = $language;
            $name .= 'Language: '.$language;
        }

        if ( method_exists ($renderer, 'getODTPropertiesFromElement') === false ) {
            // Older ODT plugin version.
            $renderer->_odtSpanOpenUseProperties($properties);
        } else {
            // Newer version create our own common styles.
            $properties ['font-size'] = NULL;

            // Create parent style to group the others beneath it
            if (!$renderer->styleExists('Plugin_Wrap_Spans')) {
                $parent_properties = array();
                $parent_properties ['style-parent'] = NULL;
                $parent_properties ['style-class'] = 'Plugin Wrap Spans';
                $parent_properties ['style-name'] = 'Plugin_Wrap_Spans';
                $parent_properties ['style-display-name'] = 'Plugin Wrap';
                $renderer->createTextStyle($parent_properties);
            }

            $name .= $this->getODTCommonStyleName($class);
            $style_name = 'Plugin_Wrap_Span_'.$name;
            if (!$renderer->styleExists($style_name)) {
                $properties ['style-parent'] = 'Plugin_Wrap_Spans';
                $properties ['style-class'] = NULL;
                $properties ['style-name'] = $style_name;
                $properties ['style-display-name'] = $name;
                $renderer->createTextStyle($properties);
            }

            if (!empty($properties ['background-image'])) {
                if (method_exists ($renderer, '_odtAddImageUseProperties') === true) {
                    $size = NULL;
                    if (!empty($properties ['font-size'])) {
                        $size = $properties ['font-size'];
                        $size = $renderer->addToValue($size, '2pt');
                    }
                    $properties ['width'] = $size;
                    $properties ['height'] = $size;
                    $properties ['title'] = NULL;
                    $renderer->_odtAddImageUseProperties ($properties ['background-image'],$properties);
                } else {
                    $renderer->_odtAddImage ($properties ['background-image'],NULL,NULL,NULL,NULL,NULL);
                }
            }
            $renderer->_odtSpanOpen($style_name);
        }
    }

    function renderODTCloseSpan ($renderer) {
        if ( method_exists ($renderer, '_odtSpanClose') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }
        $renderer->_odtSpanClose();
    }

    function renderODTOpenTable ($renderer, $attr, $style, $attr_string) {
        self::$table_entr += 1;

        $class = $attr ['class'];
        $css_properties = array ();

        if ( method_exists ($renderer, 'getODTPropertiesFromElement') === false ) {
            // Function is not supported by installed ODT plugin version, return.
            return;
        }

        // Get CSS properties for ODT export.
        $renderer->getODTPropertiesNew ($css_properties, 'div', $attr_string, NULL, true);

        if ( empty($css_properties ['float']) === true ) {
            // If the float property is not set, set it to 'left' becuase the ODT plugin
            // would default to 'center' which is diffeent to the XHTML behaviour.
            //$css_properties ['float'] = 'left';
            if (strpos ($class, 'wrap_left') !== false ) {
                $css_properties ['float'] = 'left';
            } else if (strpos ($class, 'wrap_center') !== false ) {
                $css_properties ['float'] = 'center';
            } else if (strpos ($class, 'wrap_right') !== false) {
                $css_properties ['float'] = 'right';
            }
        }

        // The display property has differing usage in CSS. So we better overwrite it.
        $css_properties ['display'] = 'always';
        if ( stripos ($class, 'wrap_noprint') !== false ) {
            $css_properties ['display'] = 'screen';
        }
        if ( stripos ($class, 'wrap_onlyprint') !== false ) {
            $css_properties ['display'] = 'printer';
        }

        $background_color = $css_properties ['background-color'];
        $image = $css_properties ['background-image'];
        $margin_top = $css_properties ['margin-top'];
        $margin_right = $css_properties ['margin-right'];
        $margin_bottom = $css_properties ['margin-bottom'];
        $margin_left = $css_properties ['margin-left'];
        $width = $attr ['width'];

        // Open 2x1 table if image is present
        // otherwise only a 1x1 table
        $properties = array();
        $properties ['width'] = '100%';
        $properties ['align'] = 'center';
        $properties ['margin-top'] = $margin_top;
        $properties ['margin-right'] = $margin_right;
        $properties ['margin-bottom'] = $margin_bottom;
        $properties ['margin-left'] = $margin_left;

        $frame_props = array();
        if (!empty($css_properties ['border'])) {
            $frame_props ['border'] = $css_properties ['border'];
        } else {
            $frame_props ['border'] = 'none';
        }
        $frame_props ['min-height'] = '1cm';
        $frame_props ['width'] = $attr ['width'];
        $frame_props ['float'] = $css_properties ['float'];
        if ( self::$table_entr > 1 ) {
            $frame_props ['anchor-type'] = 'as-char';
        } else {
            $frame_props ['anchor-type'] = 'paragraph';
        }
        $frame_props ['textarea-horizontal-align'] = 'left';
        $frame_props ['run-through'] = 'foreground';
        $frame_props ['vertical-pos'] = 'from-top';
        $frame_props ['vertical-rel'] = 'paragraph';
        $frame_props ['horizontal-pos'] = 'from-left';
        $frame_props ['horizontal-rel'] = 'paragraph';
        $frame_props ['wrap'] = 'parallel';
        $frame_props ['number-wrapped-paragraphs'] = 'no-limit';
        if (!empty($frame_props ['float']) &&
            $frame_props ['float'] != 'center') {
            $frame_props ['margin-top'] = '0cm';
            $frame_props ['margin-right'] = '0cm';
            $frame_props ['margin-bottom'] = '0cm';
            $frame_props ['margin-left'] = '0cm';
            $frame_props ['padding-top'] = '0cm';
            $frame_props ['padding-bottom'] = '0cm';
        } else {
            // No wrapping on not floating divs
            $frame_props ['wrap'] = 'none';
        }

        switch ($frame_props ['float']) {
            case 'left':
                if ( self::$table_entr == 1 ) {
                    $frame_props ['y'] = '0cm';
                    $frame_props ['x'] = self::$box_left_pos.'cm';
                    self::$box_left_pos += trim($frame_props ['width'], 'cm');
                }
                $frame_props ['padding-left'] = '0cm';
            break;
            case 'right':
                $frame_props ['horizontal-rel'] = 'paragraph';
                $frame_props ['horizontal-pos'] = 'right';
                $frame_props ['padding-right'] = '0cm';
            break;
            case 'center':
                $frame_props ['horizontal-pos'] = 'center';
            break;
            default:
                $frame_props ['padding-left'] = '0cm';
            break;
        }
        $renderer->_odtOpenTextBoxUseProperties($frame_props);

        $renderer->_odtTableOpenUseProperties($properties);

        if (!empty($image)) {
            $properties = array();
            $properties ['width'] = '2cm';
            $renderer->_odtTableAddColumnUseProperties($properties);
        }

        $properties = array();
        $renderer->_odtTableAddColumnUseProperties($properties);

        $renderer->tablerow_open();

        if (!empty($image)) {
            $properties = array();
            $properties ['vertical-align'] = 'middle';
            $properties ['text-align'] = 'center';
            $properties ['padding'] = '0.1cm';
            $properties ['background-color'] = $background_color;

            $renderer->_odtTableCellOpenUseProperties($properties);
            $renderer->_odtAddImage($image);
            $renderer->tablecell_close();
        }

        $properties = array();
        $properties ['vertical-align'] = 'middle';
        $properties ['padding'] = '0.3cm';
        $properties ['background-color'] = $background_color;
        $properties ['border'] = 'none';
        $renderer->_odtTableCellOpenUseProperties($properties);
    }

    function renderODTCloseTable ($renderer) {
        $renderer->tablecell_close();
        $renderer->tablerow_close();
        $renderer->_odtTableClose();
        $renderer->_odtCloseTextBox ();

        self::$table_entr -= 1;
    }

    protected function getODTCommonStyleName ($class_string) {
        static $map = array (
            'wrap_box' => 'Box', 'wrap_danger' => 'Danger', 'wrap_warning' => 'Warning',
            'wrap_caution' => 'Caution', 'wrap_notice' => 'Notice', 'wrap_safety' => 'Safety',
            'wrap_info' => 'Info', 'wrap_important' => 'Important', 'wrap_alert' => 'Alert',
            'wrap_tip' => 'Tip', 'wrap_help' => 'Help', 'wrap_todo' => 'To do',
            'wrap_download' => 'Download', 'wrap_hi' => 'Highlighted', 'wrap_spoiler' => 'Spoiler',
            'wrap_leftalign' => 'Left aligned', 'wrap_rightalign' => 'Right aligned',
            'wrap_centeralign' => 'Centered', 'wrap_justify' => 'Justify', 'wrap_em' => 'Emphasised',
            'wrap_lo' => 'Less significant');
        $classes = explode(' ', $class_string);
        $name = '';
        foreach ($classes as $class) {
            if (array_key_exists($class, $map)) {
                $name .= $map [$class];
            }
        }
        return ($name);
    }
}
