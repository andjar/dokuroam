<?php

namespace dokuwiki\plugin\bureaucracy\test;

use \Doku_Form;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_email_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'Mail: @@emailLabel@@',
                'valid@example.com',
                'Mail: valid@example.com',
                [],
                'valid email',
            ],
            [
                'Mail: @@emailLabel@@',
                '@MAIL@',
                'Mail: @MAIL@',
                [],
                '@MAIL@ placeholder for user\'s email adress',
            ],
            [
                'Mail: @@emailLabel@@',
                'invalid@example',
                'Mail: invalid@example',
                [],
                'local email addresses are allowed',
            ],
            [
                'Mail: @@emailLabel@@',
                'invalid[at]example.com',
                null,
                ['emailLabel'],
                'invalid email',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param        $postedValue
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_email_submit(
        $templateSyntax,
        $postedValue,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $label = 'emailLabel';
        $actualWikiText = parent::send_form_action_template(
            "email \"$label\"",
            $templateSyntax,
            $actualValidationErrors,
            $postedValue
        );

        if (empty($expectedValidationErrors)) {
            $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        }
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }

    public function test_field_email_render()
    {
        $formSyntax = 'email emailLabel';
        $instr = p_get_instructions("<form>\n$formSyntax\n</form>");

        $actualHTML = p_render('xhtml', $instr, $info);

        $expectedFieldHTML = '<label><span>emailLabel <sup>*</sup></span> <input type="text" name="bureaucracy[0]" class="edit required" required="required" /></label>';
        $expectedHTML = self::FORM_PREFIX_HTML . "\n$expectedFieldHTML\n" . self::FORM_SUFFIX_HTML;
        $this->assertEquals(trim($expectedHTML), trim($actualHTML));
    }
}
