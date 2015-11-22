<?php

/**
 * Tests to ensure that images are handled correctly by the renderer
 *
 * @group plugin_odt_renderer
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_renderer_image_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();

        // copy test files to test directory
        TestUtils::rcopy(TMP_DIR, dirname(__FILE__) . '/data/');
    }

    /**
     * This function checks if a image is included with the correct size.
     * Therefore an test image with known size is choosen (TestPicture100x50.png).
     */
    public function test_image_size() {
        $units = new helper_plugin_odt_units ();
        $renderer = new renderer_plugin_odt_page();
        $renderer->document_start();
        $renderer->_odtAddImage(TMP_DIR.'/data/TestPicture100x50.png');

        // There should be a frame
        $start = strpos($renderer->doc, '<draw:frame');
        $end = strpos($renderer->doc, '</draw:frame>');
        $frame = substr($renderer->doc, $start, $end+strlen('</draw:frame>')-$start);
        $this->assertFalse(empty($frame));

        // Check that the width has the unit 'cm' and that it is
        // calculated according to the formula ($width/96.0)*2.54
        $result = preg_match('/svg:width="[^"]*"/', $renderer->doc, $widths);
        $this->assertEquals($result, 1);

        $unit = substr($widths [0], strlen('svg:width='));
        $unit = trim($unit, '"');
        $width = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($width, (100/96.0)*2.54);

        // Check that the height has the unit 'cm' and that it is
        // calculated according to the formula ($height/96.0)*2.54
        $result = preg_match('/svg:height="[^"]*"/', $renderer->doc, $heights);
        $this->assertEquals($result, 1);

        $unit = substr($heights [0], strlen('svg:height='));
        $unit = trim($unit, '"');
        $height = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($height, (50/96.0)*2.54);
    }

    /**
     * This function checks if a image is included with the correct size.
     * Therefore an test image with known size is choosen (TestPicture500x256.png).
     */
    public function test_image_size_2() {
        $units = new helper_plugin_odt_units ();
        $renderer = new renderer_plugin_odt_page();
        $renderer->document_start();
        $renderer->_odtAddImage(TMP_DIR.'/data/TestPicture500x256.png');

        // There should be a frame
        $start = strpos($renderer->doc, '<draw:frame');
        $end = strpos($renderer->doc, '</draw:frame>');
        $frame = substr($renderer->doc, $start, $end+strlen('</draw:frame>')-$start);
        $this->assertFalse(empty($frame));

        // Check that the width has the unit 'cm' and that it is
        // calculated according to the formula ($width/96.0)*2.54
        $result = preg_match('/svg:width="[^"]*"/', $renderer->doc, $widths);
        $this->assertEquals($result, 1);

        $unit = substr($widths [0], strlen('svg:width='));
        $unit = trim($unit, '"');
        $width = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($width, (500/96.0)*2.54);

        // Check that the height has the unit 'cm' and that it is
        // calculated according to the formula ($height/96.0)*2.54
        $result = preg_match('/svg:height="[^"]*"/', $renderer->doc, $heights);
        $this->assertEquals($result, 1);

        $unit = substr($heights [0], strlen('svg:height='));
        $unit = trim($unit, '"');
        $height = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($height, (256/96.0)*2.54);
    }
}
