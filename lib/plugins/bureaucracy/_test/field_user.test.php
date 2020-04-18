<?php

namespace dokuwiki\plugin\bureaucracy\test;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class syntax_plugin_bureaucracy_fielduser_test extends BureaucracyTest
{

    /**
     * Create some users
     */
    public function setUp()
    {
        parent::setUp();

        /** @var \DokuWiki_Auth_Plugin $auth */
        global $auth;

        $auth->createUser("user1", "54321", "user1Name", "user1@example.com");
        $auth->createUser("user2", "543210", "user2Name", "user2@example.com");
        $auth->createUser("mwuser", "12345", "Wiki User", "wikiuser@example.com", ['group1', 'group2']);
    }

    public function dataProvider()
    {
        return [
            [
                'user:@@user@@',
                'user user',
                'mwuser',
                'user:mwuser',
                [],
                'default substitution',
            ],
            [
                'user:@@user@@',
                'user user',
                '',
                'user:',
                ['user'],
                'error for empty substitution',
            ],
            [
                'user:@@user@@',
                'user user !',
                '',
                'user:',
                [],
                'ok for empty substitution in optional field',
            ],
            [
                'user:@@user.name@@',
                'user user',
                'mwuser',
                'user:Wiki User',
                [],
                'name substitution',
            ],
            [
                'user:@@user.mail@@',
                'user user',
                'mwuser',
                'user:wikiuser@example.com',
                [],
                'mail substitution',
            ],
            [
                'user:@@user.grps@@',
                'user user',
                'mwuser',
                'user:group1, group2',
                [],
                'groups substitution',
            ],
            [
                'user:@@user.grps(;)@@',
                'user user',
                'mwuser',
                'user:group1;group2',
                [],
                'groups substitution custom delimiter',
            ],
            [
                'user:@@user.grps())@@',
                'user user',
                'mwuser',
                'user:group1)group2',
                [],
                'groups substitution custom delimiter with brackets',
            ],
            [
                'user:@@user.no_sutch_attribute@@',
                'user user',
                'mwuser',
                'user:@@user.no_sutch_attribute@@',
                [],
                'template unknown attribute substitution',
            ],
            [
                'user:##user##',
                'user user',
                'mwuser',
                'user:mwuser',
                [],
                'hash substitution',
            ],
            [
                'user:##user.mail##',
                'user user',
                'mwuser',
                'user:wikiuser@example.com',
                [],
                'hash substitution with attribute',
            ],
            [
                'user:##user@@',
                'user user',
                'mwuser',
                'user:##user@@',
                [],
                'hash substitution sign mismatch',
            ],
            [
                "user:@@user@@\n\nmail:@@user.mail@@\n\ngrps:@@user.grps(\n)@@",
                'user user',
                'mwuser',
                "user:mwuser\n\nmail:wikiuser@example.com\n\ngrps:group1\ngroup2",
                [],
                'multiple replacements',
            ],
            [
                "grps1:@@user.grps(\n)@@\n\ngrps2:@@user.grps(())@@",
                'user user',
                'mwuser',
                "grps1:group1\ngroup2\n\ngrps2:group1()group2",
                [],
                'groups twice',
            ],
            [
                'grps:@@user.grps(end))@@',
                'user user',
                'mwuser',
                'grps:group1end)group2',
                [],
                'groups special glue',
            ],
            [
                'grps:@@user.grps()@@',
                'user user',
                'mwuser',
                'grps:group1group2',
                [],
                'groups with empty delimiter',
            ],
            [
                'user:@@user@@',
                'user user',
                'non_existant_user',
                'user:non_existant_user',
                ['user'],
                'error for non existant user',
            ],
            [
                'user:@@user.name@@',
                'user user',
                'non_existant_user',
                'user:@@user.name@@',
                ['user'],
                'error for non existant user with attribute',
            ],
        ];
    }


    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param string $formSyntax
     * @param string $postedValue value of 'user' field
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_user(
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

        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
        $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
    }
}
