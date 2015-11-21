<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTableCellStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_tablecellstyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code = '<style:style style:name="Table1.A1" style:family="table-cell">
                         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
                     </style:style>';


        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $this->assertEquals($style->getFamily(), 'table-cell');
        $this->assertEquals($style->getProperty('style-name'), 'Table1.A1');
        $this->assertEquals($style->getPropertySection('style-name'), 'style');
        $this->assertEquals($style->getProperty('style-family'), 'table-cell');
        $this->assertEquals($style->getPropertySection('style-family'), 'style');
        $this->assertEquals($style->getProperty('padding'), '0.097cm');
        $this->assertEquals($style->getPropertySection('padding'), 'table-cell');

        $this->assertEquals($style->getProperty('border-left'), '0.05pt solid #000000');
        $this->assertEquals($style->getPropertySection('border-left'), 'table-cell');
        $this->assertEquals($style->getProperty('border-right'), 'none');
        $this->assertEquals($style->getPropertySection('border-right'), 'table-cell');
        $this->assertEquals($style->getProperty('border-top'), '0.05pt solid #000000');
        $this->assertEquals($style->getPropertySection('border-top'), 'table-cell');
        $this->assertEquals($style->getProperty('border-bottom'), '0.05pt solid #000000');
        $this->assertEquals($style->getPropertySection('border-bottom'), 'table-cell');
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<style:style style:name="Table1.A1" style:family="table-cell">
                         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
                     </style:style>';
        $expected  = '<style:style style:name="Table1.A1" style:family="table-cell" >'."\n";
        $expected .= '    <style:table-cell-properties fo:border-top="0.05pt solid #000000" fo:border-right="none" fo:border-bottom="0.05pt solid #000000" fo:border-left="0.05pt solid #000000" fo:padding="0.097cm" />'."\n";
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
        $xml_code = '<style:style style:name="Table1.A1" style:family="table-cell">
                         <style:table-cell-properties fo:padding="0.097cm" fo:border-left="0.05pt solid #000000" fo:border-right="none" fo:border-top="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000"/>
                     </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setProperty('padding', '5cm');

        $this->assertEquals($style->getProperty('padding'), '5cm');
    }

    /**
     * Test properties import and conversion to string.
     */
    public function test_import_properties_and_to_string() {
        $properties = array();
        $properties ['style-name']    = 'Table1.A1';
        $properties ['border-top']    = '0.05pt solid #000000';
        $properties ['border-right']  = 'none';
        $properties ['border-left']   = '0.05pt solid #000000';
        $properties ['border-bottom'] = '0.05pt solid #000000';
        $properties ['padding']       = '0.097cm';

        $expected  = '<style:style style:name="Table1.A1" style:family="table-cell" >'."\n";
        $expected .= '    <style:table-cell-properties fo:border-top="0.05pt solid #000000" fo:border-right="none" fo:border-left="0.05pt solid #000000" fo:border-bottom="0.05pt solid #000000" fo:padding="0.097cm" />'."\n";
        $expected .= '</style:style>'."\n";

        $style = new ODTTableCellStyle();
        $this->assertNotNull($style);

        $style->importProperties($properties, NULL);
        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }
}
