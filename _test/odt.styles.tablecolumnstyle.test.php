<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTableColumnStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_tablecolumnstyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code = '<style:style style:name="Table1.A" style:family="table-column">
                         <style:table-column-properties style:column-width="5.609cm"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $this->assertEquals($style->getFamily(), 'table-column');
        $this->assertEquals($style->getProperty('style-name'), 'Table1.A');
        $this->assertEquals($style->getPropertySection('style-name'), 'style');
        $this->assertEquals($style->getProperty('style-family'), 'table-column');
        $this->assertEquals($style->getPropertySection('style-family'), 'style');
        $this->assertEquals($style->getProperty('column-width'), '5.609cm');
        $this->assertEquals($style->getPropertySection('column-width'), 'table-column');
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<style:style style:name="Table1.A" style:family="table-column">
                         <style:table-column-properties style:column-width="5.609cm"/>
                     </style:style>';
        $expected  = '<style:style style:name="Table1.A" style:family="table-column" >'."\n";
        $expected .= '    <style:table-column-properties style:column-width="5.609cm" />'."\n";
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
        $xml_code = '<style:style style:name="Table1.A" style:family="table-column">
                         <style:table-column-properties style:column-width="5.609cm"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setProperty('column-width', '12.345cm');

        $this->assertEquals($style->getProperty('column-width'), '12.345cm');
    }

    /**
     * Test properties import and conversion to string.
     */
    public function test_import_properties_and_to_string() {
        $properties = array();
        $properties ['style-name']   = 'Table1.A';
        $properties ['column-width'] = '5.609cm';

        $expected  = '<style:style style:name="Table1.A" style:family="table-column" >'."\n";
        $expected .= '    <style:table-column-properties style:column-width="5.609cm" />'."\n";
        $expected .= '</style:style>'."\n";

        $style = new ODTTableColumnStyle();
        $this->assertNotNull($style);

        $style->importProperties($properties, NULL);
        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }
}
