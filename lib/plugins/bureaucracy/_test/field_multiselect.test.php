<?php

namespace dokuwiki\plugin\bureaucracy\test;

use Doku_Form;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_multiselect_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'fruits: @@multiSelectLabel@@',
                'multiselect "multiSelectLabel" "Peaches|Apples|Oranges" =Peaches,Oranges',
                ['Peaches','Apples'],
                'fruits: Peaches, Apples',
                [],
                'default separator',
            ],
            [
                'fruits: @@multiSelectLabel(;)@@',
                'multiselect "multiSelectLabel" "Peaches|Apples|Oranges" =Peaches,Oranges',
                ['Peaches','Apples'],
                'fruits: Peaches;Apples',
                [],
                'custom separator',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param string $formSyntax
     * @param        $postedValue
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_multiselect_submit(
        $templateSyntax,
        $formSyntax,
        $postedValue,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $actualWikiText = parent::send_form_action_template(
            $formSyntax,
            $templateSyntax,
            $actualValidationErrors,
            $postedValue
        );

        $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }

    public function test_field_multiselect_render()
    {
        $formSyntax = 'multiselect "multiSelectLabel" "Peaches|Apples|Oranges" =Peaches,Oranges';
        $instr = p_get_instructions("<form>\n$formSyntax\n</form>");

        $actualHTML = p_render('xhtml', $instr, $info);

        $expectedFieldHTML = '<label><span>multiSelectLabel <sup>*</sup></span> <select name="bureaucracy[0][]" multiple="multiple">
<option value="Peaches" selected="selected">Peaches</option><option value="Apples">Apples</option><option value="Oranges" selected="selected">Oranges</option>
</select></label>';
        $expectedHTML = self::FORM_PREFIX_HTML . "\n$expectedFieldHTML\n" . self::FORM_SUFFIX_HTML;
        $this->assertEquals(trim($expectedHTML), trim($actualHTML));
    }
}
