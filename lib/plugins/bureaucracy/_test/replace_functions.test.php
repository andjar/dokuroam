<?php

namespace dokuwiki\plugin\bureaucracy\test;

use \Doku_Form;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_replace_functions_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                '@curNS@',
                '',
                '',
                '@curNS@',
                [],
                '"@function@" shouldn\'t be replaced.',
            ],
            [
                '@curNS()@',
                '',
                '',
                '',
                [],
                '"@function()@" should return empty string.',
            ],
            [
                '(@noNS(test):page)@)', //test doubled bracket
                '',
                '',
                '(page)',
                [],
                '"@curNS(test:page))@" should return empty string.',
            ],
            [
                '@curNS(test:static:value)@',
                '',
                '',
                'static',
                [],
                '@curNS()@ doesn\'t work.',
            ],
            [
                '@curNS(@@page@@)@',
                'textbox page',
                'some:test:page',
                'test',
                [],
                '@curNS()@ doesn\'t work.',
            ],
            [
                '@getNS(@@page@@)@',
                'textbox page',
                'some:test:page',
                'some:test',
                [],
                '@getNS()@ doesn\'t work.',
            ],
            [
                '@getNS(test:static:value)@',
                '',
                '',
                'test:static',
                [],
                '@getNS()@ doesn\'t work.',
            ],
            [
                '@noNS(test:static:value)@',
                '',
                '',
                'value',
                [],
                '@noNS()@ doesn\'t work.',
            ],
            [
                '@noNS(@@page@@)@',
                'textbox page',
                'some:test:page',
                'page',
                [],
                '@noNS()@ doesn\'t work.',
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param string $formSyntax
     * @param string $postedValue
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_NS_functions(
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

        if (empty($expectedValidationErrors)) {
            $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        }
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }

    public function test_p_get_first_heading_function() {
        //create page with heading
        $wikitext = "====== Header 1 ======\n";
        $page = 'some:test:page';
        saveWikiText($page, $wikitext, 'summary');

        $actualValidationErrors = [];
        $actualWikiText = parent::send_form_action_template(
            'textbox page',
            '@p_get_first_heading(@@page@@)@',
            $actualValidationErrors,
            $page
        );

        $this->assertEquals('Header 1', $actualWikiText);
    }
}
