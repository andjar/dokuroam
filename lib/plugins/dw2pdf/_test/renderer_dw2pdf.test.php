<?php
/**
 * General tests for the dw2pdf renderer of dw2pdf plugin
 *
 * @group plugin_dw2pdf
 * @group plugins
 */

class renderer_plugin_dw2pdfdummy extends renderer_plugin_dw2pdf {

    /**
     * @param int $level 1-6
     * @return int
     */
    public function getCalculateBookmarklevel($level) {
        return $this->calculateBookmarklevel($level);
    }
}

/**
 * Class dw2pdf_renderer_dw2pdf_test
 */
class dw2pdf_renderer_dw2pdf_test extends DokuWikiTest {

    public function setUp() {
        parent::setUp();
    }


    public function test() {
        $Renderer = new renderer_plugin_dw2pdfdummy();

        $levels = [
            1,2,2,2,3,4,5,6,5,4,3,2,1, // index:0-12
            3,4,3,1,                   // 13-16
            2,3,4,2,3,4,1,             // 17-23
            3,4,3,2,1,                 // 24-28
            3,4,2,1,                   // 29-32
            3,5,6,5,6,4,6,3,1,         // 33-41
            3,6,4,5,6,4,3,6,2,1,       // 42-51
            2,3,2,3,3                  // 52-56
        ];
        $expectedbookmarklevels = [
            0,1,1,1,2,3,4,5,4,3,2,1,0,
            1,2,1,0,
            1,2,3,1,2,3,0,
            1,2,1,1,0,
            1,2,1,0,
            1,2,3,2,3,2,3,2,0,
            1,2,2,3,4,2,2,3,1,0,
            1,2,1,2,2
        ];
        foreach($levels as $i => $level) {
            $actualbookmarklevel = $Renderer->getCalculateBookmarklevel($level);
            $this->assertEquals($expectedbookmarklevels[$i], $actualbookmarklevel,"index:$i, lvl:$level");
        }
    }

}
