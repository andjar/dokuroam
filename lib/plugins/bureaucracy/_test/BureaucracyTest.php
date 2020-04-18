<?php

namespace dokuwiki\plugin\bureaucracy\test;


class BureaucracyTest extends \DokuWikiTest
{

    const FORM_PREFIX_HTML = '<form class="bureaucracy__plugin" id="bureaucracy__plugin1" enctype="multipart/form-data" method="post" action="" accept-charset="utf-8"><div class="no">
<input type="hidden" name="sectok" value="" /><input type="hidden" name="bureaucracy[$$id]" value="1" /><fieldset ><legend></legend>';
    const FORM_SUFFIX_HTML = '</fieldset>
</div></form>';

    protected $pluginsEnabled = ['bureaucracy'];

    /**
     * Simulate sending of bureaucracy form
     *
     * @param string|array $form_syntax         syntax to build a bureaucracy form
     * @param string       $template_syntax     syntax used as a page template for the "action template"
     * @param array        & $validation_errors field labels that were invalid
     * @param string|array ...$values           values passed to form handler
     *
     * @return string content of newly created page
     */
    protected function send_form_action_template($form_syntax, $template_syntax, &$validation_errors, ...$values)
    {
        $id = uniqid('page', true);
        $template_id = uniqid('template', true);

        //create full form syntax
        if (is_array($form_syntax)) {
            $form_syntax = implode("\n", $form_syntax);
        }
        $form_syntax = "<form>\naction template $template_id $id\n$form_syntax\n</form>";

        saveWikiText($template_id, $template_syntax, 'summary');

        /** @var \syntax_plugin_bureaucracy $syntax_plugin */
        $syntax_plugin = plugin_load('syntax', 'bureaucracy');
        $data = $syntax_plugin->handle($form_syntax, 0, 0, new \Doku_Handler());

        $actionData = $data['actions'][0];
        /** @var \helper_plugin_bureaucracy_action $action */
        $action = plugin_load('helper', $actionData['actionname']);
        //this is the only form
        $form_id = 0;

        /** @var \helper_plugin_bureaucracy_field $field */
        foreach ($data['fields'] as $i => $field) {
            if (!isset($values[$i])) {
                $values[$i] = null;
            }

            $isValid = $field->handle_post($values[$i], $data['fields'], $i, $form_id);
            if (!$isValid) {
                $validation_errors[] = $field->getParam('label');
            }
        }

        $action->run(
            $data['fields'],
            $data['thanks'],
            $actionData['argv']
        );

        return rawWiki($id);
    }
}
