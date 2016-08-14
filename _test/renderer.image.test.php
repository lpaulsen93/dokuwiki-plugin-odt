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

        // Copy test media files to test wiki namespace
        ODTTestUtils::rcopyMedia('wiki', dirname(__FILE__) . '/data/media/wiki/');
    }

    /**
     * This function checks if a image is included with the correct size.
     * Therefore an test image with known size is choosen (TestPicture100x50.png).
     */
    public function test_image_size() {
        $units = new helper_plugin_odt_units ();

        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '{{wiki:TestPicture100x50.png}}');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');
        $encoded = $files['content-xml'];

        // There should be a frame
        $start = strpos($encoded, '<draw:frame');
        $end = strpos($encoded, '</draw:frame>');
        $frame = substr($encoded, $start, $end+strlen('</draw:frame>')-$start);
        $this->assertFalse(empty($frame));

        // Check that the width has the unit 'cm' and that it is
        // calculated according to the formula ($width/96.0)*2.54
        $result = preg_match('/svg:width="[^"]*"/', $encoded, $widths);
        $this->assertEquals($result, 1);

        $unit = substr($widths [0], strlen('svg:width='));
        $unit = trim($unit, '"');
        $width = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($width, (100/96.0)*2.54);

        // Check that the height has the unit 'cm' and that it is
        // calculated according to the formula ($height/96.0)*2.54
        $result = preg_match('/svg:height="[^"]*"/', $encoded, $heights);
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

        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '{{wiki:TestPicture500x256.png}}');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');
        $encoded = $files['content-xml'];

        // There should be a frame
        $start = strpos($encoded, '<draw:frame');
        $end = strpos($encoded, '</draw:frame>');
        $frame = substr($encoded, $start, $end+strlen('</draw:frame>')-$start);
        $this->assertFalse(empty($frame));

        // Check that the width has the unit 'cm' and that it is
        // calculated according to the formula ($width/96.0)*2.54
        $result = preg_match('/svg:width="[^"]*"/', $encoded, $widths);
        $this->assertEquals($result, 1);

        $unit = substr($widths [0], strlen('svg:width='));
        $unit = trim($unit, '"');
        $width = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($width, (500/96.0)*2.54);

        // Check that the height has the unit 'cm' and that it is
        // calculated according to the formula ($height/96.0)*2.54
        $result = preg_match('/svg:height="[^"]*"/', $encoded, $heights);
        $this->assertEquals($result, 1);

        $unit = substr($heights [0], strlen('svg:height='));
        $unit = trim($unit, '"');
        $height = $units->getDigits($unit);
        $unit = $units->stripDigits($unit);

        $this->assertEquals($unit, 'cm');
        $this->assertEquals($height, (256/96.0)*2.54);
    }
}
