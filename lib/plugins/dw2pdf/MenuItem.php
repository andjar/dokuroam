<?php

namespace dokuwiki\plugin\dw2pdf;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Class MenuItem
 *
 * Implements the PDF export button for DokuWiki's menu system
 *
 * @package dokuwiki\plugin\dw2pdf
 */
class MenuItem extends AbstractItem {

    /** @var string do action for this plugin */
    protected $type = 'export_pdf';

    /** @var string icon file */
    protected $svg = __DIR__ . '/file-pdf.svg';

    /**
     * MenuItem constructor.
     */
    public function __construct() {
        parent::__construct();
        global $REV, $DATE_AT;

        if($DATE_AT) {
            $this->params['at'] = $DATE_AT;
        } elseif($REV) {
            $this->params['rev'] = $REV;
        }
    }

    /**
     * Get label from plugin language file
     *
     * @return string
     */
    public function getLabel() {
        $hlp = plugin_load('action', 'dw2pdf');
        return $hlp->getLang('export_pdf_button');
    }
}
