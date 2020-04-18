<?php
/**
 * Tests to ensure wrap syntax is correctly processed
 *
 * @group plugin_wrap
 * @group plugins
 */
class plugin_wrap_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'wrap';
        parent::setUp();
    }

    public function test_nestedheading() {
        $instructions = p_get_instructions("<WRAP>\n==== Heading ====\n\nSome text\n</WRAP>");
        $expected =
            array(
                array(
                    'document_start',
                    array(),
                    0
                ),
                array(
                    'plugin',
                    array(
                        'wrap_divwrap',
                        array(
                            DOKU_LEXER_ENTER,
                            '<wrap'
                        ),
                        DOKU_LEXER_ENTER,
                        '<WRAP>'
                    ),
                    1
                ),
                array(
                    'header',
                    array(
                        'Heading',
                        3,
                        8
                    ),
                    8
                ),
                array(
                    'plugin',
                    array(
                        'wrap_closesection',
                        array(),
                        DOKU_LEXER_SPECIAL,
                        false
                    ),
                    8
                ),
                array(
                    'p_open',
                    array(),
                    8
                ),
                array(
                    'cdata',
                    array(
                        'Some text'
                    ),
                    27
                ),
                array(
                    'p_close',
                    array(),
                    37
                ),
                array(
                    'plugin',
                    array(
                        'wrap_divwrap',
                        array(
                            DOKU_LEXER_EXIT,
                            ''
                        ),
                        DOKU_LEXER_EXIT,
                        '</WRAP>'
                    ),
                    37
                ),
                array(
                    'document_end',
                    array(),
                    37
                )
            );
        $this->assertEquals($expected, $instructions);
    }

    public function test_blocknesting() {
        $instructions = p_get_instructions("<WRAP>\nFoo\n\n</div> </block> Bar\n</WRAP>");
        $expected =
            array(
                array(
                    'document_start',
                    array(),
                    0
                ),
                array(
                    'plugin',
                    array(
                        'wrap_divwrap',
                        array(
                            DOKU_LEXER_ENTER,
                            '<wrap'
                        ),
                        DOKU_LEXER_ENTER,
                        '<WRAP>'
                    ),
                    1
                ),
                array(
                    'p_open',
                    array(
                    ),
                    1
                ),
                array(
                    'cdata',
                    array(
                        'Foo'
                    ),
                    8
                ),
                array(
                    'p_close',
                    array(),
                    11
                ),
                array(
                    'p_open',
                    array(
                    ),
                    11
                ),
                array(
                    'cdata',
                    array(
                        '</div> </block> Bar'
                    ),
                    13
                ),
                array(
                    'p_close',
                    array(),
                    33
                ),
                array(
                    'plugin',
                    array(
                        'wrap_divwrap',
                        array(
                            DOKU_LEXER_EXIT,
                            ''
                        ),
                        DOKU_LEXER_EXIT,
                        '</WRAP>'
                    ),
                    33
                ),
                array(
                    'document_end',
                    array(),
                    33
                )
            );
        $this->assertEquals($expected, $instructions);
    }

    public function test_inlinenesting() {
        $instructions = p_get_instructions("<wrap>Foo </span> </inline> Bar</wrap>");
        $expected =
            array(
                array(
                    'document_start',
                    array(),
                    0
                ),
                array(
                    'p_open',
                    array(
                    ),
                    0
                ),
                array(
                    'plugin',
                    array(
                        'wrap_spanwrap',
                        array(
                            DOKU_LEXER_ENTER,
                            '<wrap'
                        ),
                        DOKU_LEXER_ENTER,
                        '<wrap>'
                    ),
                    1
                ),
                array(
                    'cdata',
                    array(
                        'Foo </span> </inline> Bar'
                    ),
                    7
                ),
                array(
                    'plugin',
                    array(
                        'wrap_spanwrap',
                        array(
                            DOKU_LEXER_EXIT,
                            ''
                        ),
                        DOKU_LEXER_EXIT,
                        '</wrap>'
                    ),
                    32
                ),
                array(
                    'cdata',
                    array(
                        ''
                    ),
                    39
                ),
                array(
                    'p_close',
                    array(),
                    39
                ),
                array(
                    'document_end',
                    array(),
                    39
                )
            );
        $this->assertEquals($expected, $instructions);
    }

}