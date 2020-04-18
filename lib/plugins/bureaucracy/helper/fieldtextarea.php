<?php
/**
 * Class helper_plugin_bureaucracy_fieldtextarea
 *
 * Creates a multi-line input field
 */
class helper_plugin_bureaucracy_fieldtextarea extends helper_plugin_bureaucracy_field {
    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - x123 (optional) as number of lines
     *  - ^ (optional)
     */
    public function initialize($args) {
        parent::initialize($args);
        $this->opt['class'] .= ' textareafield';
    }

    protected $tpl =
'<label class="@@CLASS@@">
    <span>@@DISPLAY@@</span>
    <textarea name="@@NAME@@" id="@@ID@@" rows="@@ROWS|10@@" cols="10" class="edit @@OPTIONAL|required" required="required@@">@@VALUE@@</textarea>
</label>';

}
