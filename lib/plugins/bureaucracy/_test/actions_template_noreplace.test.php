<?php

namespace dokuwiki\plugin\bureaucracy\test;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_actions_template_test extends BureaucracyTest {

    public function dataProvider()
    {
        return [
            [
                'This is <noreplace>test</noreplace>.',
                '',
                '',
                'This is test.',
                [],
                '<noreplace></noreplace> not removed.',
            ],
            [
                '%Y-%m-%d <noreplace>%Y-%m-%d</noreplace>.',
                '',
                '',
                date('Y-m-d') . ' %Y-%m-%d.',
                [],
                'Date replaced inside <noreplace></noreplace>.',
            ],
            [
                '@@test@@ <noreplace>@@test@@</noreplace>.',
                'textbox test',
                'something',
                'something @@test@@.',
                [],
                'Field value replaced inside <noreplace></noreplace>.',
            ],
            [
                '<noreplace>@ID@ @USER@ @MAIL@</noreplace>',
                '',
                '',
                '@ID@ @USER@ @MAIL@',
                [],
                'DokuWiki replacement paterns for templates replaced inside <noreplace></noreplace>.',
            ],
            [
                '<noreplace>@FORMPAGE_ID@ @FORMPAGE_NS@ @FORMPAGE_CURNS@</noreplace>',
                '',
                '',
                '@FORMPAGE_ID@ @FORMPAGE_NS@ @FORMPAGE_CURNS@',
                [],
                '@FORMPAGE_*@ replacement paterns replaced inside <noreplace></noreplace>.',
            ],
            [
                '<noreplace><noinclude>TEST</noinclude></noreplace>',
                '',
                '',
                '<noinclude>TEST</noinclude>',
                [],
                'noinclude tag inside <replaced inside <noreplace></noreplace>.',
            ],
            [
                '<noreplace>@NSBASE@</noreplace>',
                '',
                '',
                '@NSBASE@',
                [],
                '"@NSBASE@" replaced inside <noreplace></noreplace>.',
            ],
            [
                '<noreplace>%%</noreplace>',
                '',
                '',
                '%%',
                [],
                '"%%" replaced inside <noreplace></noreplace>.',
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
     * @param string $msg
     *
     */
    public function test_noreplace_tag(
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
}
