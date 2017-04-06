<?php

/**
 * Tests to ensure that table of contents are handled correctly by the renderer
 *
 * @group plugin_odt_renderer
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_renderer_toc_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();

        // Copy test media files to test wiki namespace
        // So far not required for table tests
        //ODTTestUtils::rcopyMedia('wiki', dirname(__FILE__) . '/data/media/wiki/');
    }

    /**
     * This function checks if a toc using all options can be rendered
     * without crashing.
     */
    public function test_toc() {
        $testpage = '
{{odt>toc:pagebreak=true;maxlevel=2;title=My ToC;leader_sign=-;indents=0,2,2,2,2,2,2,2,2,2,2;styleH="color: red;";
styleL1="color: chartreuse;";styleL2="color: coral;";styleL3="color: royalblue;";}}

====== Chapter 1 Headline ======

{{odt>chapter-index:pagebreak=false;}}

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

===== Chapter 1.1 Headline =====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

==== Chapter 1.1.1 Headline ====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

==== Chapter 1.1.2 Headline ====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

==== Chapter 1.1.3 Headline ====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

===== Chapter 1.2 Headline =====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

===== Chapter 1.3 Headline =====

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

====== Chapter 2 Headline ======

{{odt>chapter-index:pagebreak=false;}}

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

====== Chapter 3 Headline ======

{{odt>chapter-index:pagebreak=false;}}

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
';

        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, $testpage);
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');
    }
}
