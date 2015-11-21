<?php

require_once DOKU_INC.'lib/plugins/odt/helper/units.php';

/**
 * Tests to ensure functionality of the units helper class.
 *
 * @group plugin_odt_units
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_units_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Ensure that stripDigits() strips all digits from the left correctly.
     */
    public function test_stripDigits() {
        $units = new helper_plugin_odt_units ();

        $this->assertEquals($units->stripDigits('1cm'), 'cm');
        $this->assertEquals($units->stripDigits('12mm'), 'mm');
        $this->assertEquals($units->stripDigits('123in'), 'in');
        $this->assertEquals($units->stripDigits('1234pt'), 'pt');
        $this->assertEquals($units->stripDigits('12345pc'), 'pc');
        $this->assertEquals($units->stripDigits('123456px'), 'px');
        $this->assertEquals($units->stripDigits('1234567em'), 'em');
        $this->assertEquals($units->stripDigits('9m'), 'm');
        $this->assertEquals($units->stripDigits('9km'), 'km');
        $this->assertEquals($units->stripDigits('9mi'), 'mi');
        $this->assertEquals($units->stripDigits('9ft'), 'ft');
        $this->assertEquals($units->stripDigits('9ft23'), 'ft23');
    }

    /**
     * Ensure that hasValidXSLUnit() recongizes XSL units correctly.
     */
    public function test_hasValidXSLUnit() {
        $units = new helper_plugin_odt_units ();

        $this->assertEquals($units->hasValidXSLUnit('1cm'), true);
        $this->assertEquals($units->hasValidXSLUnit('12mm'), true);
        $this->assertEquals($units->hasValidXSLUnit('123in'), true);
        $this->assertEquals($units->hasValidXSLUnit('1234pt'), true);
        $this->assertEquals($units->hasValidXSLUnit('12345pc'), true);
        $this->assertEquals($units->hasValidXSLUnit('123456px'), true);
        $this->assertEquals($units->hasValidXSLUnit('1234567em'), true);
        $this->assertEquals($units->hasValidXSLUnit('9m'), false);
        $this->assertEquals($units->hasValidXSLUnit('9km'), false);
        $this->assertEquals($units->hasValidXSLUnit('9mi'), false);
        $this->assertEquals($units->hasValidXSLUnit('9ft'), false);
    }

    /**
     * Ensure that pixelToPoints function convert to points correctly.
     */
    public function test_pixelToPoints() {
        $units = new helper_plugin_odt_units ();

        // First with default values.
        $this->assertEquals($units->pixelToPointsX('1px'), '0.8pt');
        $this->assertEquals($units->pixelToPointsY('1px'), '1pt');

        // Then with set values.
        $units->setTwipsPerPixelX(32);
        $units->setTwipsPerPixelY(40);

        $this->assertEquals($units->getTwipsPerPixelX(), 32);
        $this->assertEquals($units->getTwipsPerPixelY(), 40);
        $this->assertEquals($units->pixelToPointsX('1px'), '1.6pt');
        $this->assertEquals($units->pixelToPointsY('1px'), '2pt');
    }

    /**
     * Ensure that conversion to points works correctly.
     */
    public function test_toPoints() {
        $units = new helper_plugin_odt_units ();

        // Set base values.
        $units->setTwipsPerPixelX(16);
        $units->setTwipsPerPixelY(20);
        $units->setPixelPerEm(14);

        $this->assertEquals($units->getPixelPerEm(), 14);

        $this->assertEquals($units->toPoints('1cm'), '28.346456514353pt');
        $this->assertEquals($units->toPoints('1mm'), '2.8346456514353pt');
        $this->assertEquals($units->toPoints('1in'), '0.089605556pt');
        $this->assertEquals($units->toPoints('1pc'), '12pt');
        $this->assertEquals($units->toPoints('1px', 'x'), '0.8pt');
        $this->assertEquals($units->toPoints('1px', 'y'), '1pt');
        $this->assertEquals($units->toPoints('1em'), '14pt');
    }
}
