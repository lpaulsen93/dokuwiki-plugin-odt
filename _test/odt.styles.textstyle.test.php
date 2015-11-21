<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTextStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_textstyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code = '<style:style style:name="test" style:family="text">
                         <style:text-properties fo:color="#ff0000"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $this->assertEquals($style->getFamily(), 'text');
        $this->assertEquals($style->getProperty('style-name'), 'test');
        $this->assertEquals($style->getPropertySection('style-name'), 'style');
        $this->assertEquals($style->getProperty('style-family'), 'text');
        $this->assertEquals($style->getPropertySection('style-family'), 'style');
        $this->assertEquals($style->getProperty('color'), '#ff0000');
        $this->assertEquals($style->getPropertySection('color'), 'text');
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<style:style style:name="test" style:family="text">
                         <style:text-properties fo:color="#ff0000"/>
                     </style:style>';
        $expected  = '<style:style style:name="test" style:family="text" >'."\n";
        $expected .= '    <style:text-properties fo:color="#ff0000" />'."\n";
        $expected .= '</style:style>'."\n";

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }

    /**
     * Test set and get of a property.
     */
    public function test_set_and_get() {
        $xml_code = '<style:style style:name="test" style:family="text">
                         <style:text-properties fo:color="#ffffff"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setProperty('color', '#000000');

        $this->assertEquals($style->getProperty('color'), '#000000');
    }

    /**
     * Test properties import and conversion to string.
     */
    public function test_import_properties_and_to_string() {
        $properties = array();
        $properties ['style-name'] = 'test';
        $properties ['color']      = '#ff0000';

        $expected  = '<style:style style:name="test" style:family="text" >'."\n";
        $expected .= '    <style:text-properties fo:color="#ff0000" />'."\n";
        $expected .= '</style:style>'."\n";

        $style = new ODTTextStyle();
        $this->assertNotNull($style);

        $style->importProperties($properties, NULL);
        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }
}
