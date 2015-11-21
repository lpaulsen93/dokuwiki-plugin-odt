<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTableStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_tablestyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code = '<style:style style:name="TableTest" style:family="table">
                         <style:table-properties table:border-model="collapsing"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $this->assertEquals($style->getFamily(), 'table');
        $this->assertEquals($style->getProperty('style-name'), 'TableTest');
        $this->assertEquals($style->getPropertySection('style-name'), 'style');
        $this->assertEquals($style->getProperty('style-family'), 'table');
        $this->assertEquals($style->getPropertySection('style-family'), 'style');
        $this->assertEquals($style->getProperty('border-model'), 'collapsing');
        $this->assertEquals($style->getPropertySection('border-model'), 'table');
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<style:style style:name="TableTest" style:family="table">
                         <style:table-properties table:border-model="collapsing"/>
                     </style:style>';
        $expected  = '<style:style style:name="TableTest" style:family="table" >'."\n";
        $expected .= '    <style:table-properties table:border-model="collapsing" />'."\n";
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
        $xml_code = '<style:style style:name="TableTest" style:family="table">
                         <style:table-properties table:border-model="collapsing"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setProperty('border-model', 'separating');

        $this->assertEquals($style->getProperty('border-model'), 'separating');
    }

    /**
     * Test properties import and conversion to string.
     */
    public function test_import_properties_and_to_string() {
        $properties = array();
        $properties ['style-name']   = 'TableTest';
        $properties ['border-model'] = 'collapsing';

        $expected  = '<style:style style:name="TableTest" style:family="table" >'."\n";
        $expected .= '    <style:table-properties table:border-model="collapsing" />'."\n";
        $expected .= '</style:style>'."\n";

        $style = new ODTTableStyle();
        $this->assertNotNull($style);

        $style->importProperties($properties, NULL);
        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }
}
