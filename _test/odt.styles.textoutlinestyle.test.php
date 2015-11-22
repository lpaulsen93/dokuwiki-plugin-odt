<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTextOutlineStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_textoutlinestyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code = '<text:outline-style>
            <text:outline-level-style text:level="1" style:num-format="">
                <style:list-level-properties text:min-label-distance="10cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="2" style:num-format="">
                <style:list-level-properties text:min-label-distance="9cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="3" style:num-format="">
                <style:list-level-properties text:min-label-distance="8cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="4" style:num-format="">
                <style:list-level-properties text:min-label-distance="7cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="5" style:num-format="">
                <style:list-level-properties text:min-label-distance="6cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="6" style:num-format="">
                <style:list-level-properties text:min-label-distance="5cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="7" style:num-format="">
                <style:list-level-properties text:min-label-distance="4cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="8" style:num-format="">
                <style:list-level-properties text:min-label-distance="3cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="9" style:num-format="">
                <style:list-level-properties text:min-label-distance="2cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="10" style:num-format="">
                <style:list-level-properties text:min-label-distance="1cm"/>
            </text:outline-level-style>
        </text:outline-style>';


        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);
        $dist = $style->getPropertyFromLevel(1, 'text-min-label-distance');
        $this->assertEquals('10cm', $dist);
        $dist = $style->getPropertyFromLevel(2, 'text-min-label-distance');
        $this->assertEquals('9cm', $dist);
        $dist = $style->getPropertyFromLevel(3, 'text-min-label-distance');
        $this->assertEquals('8cm', $dist);
        $dist = $style->getPropertyFromLevel(4, 'text-min-label-distance');
        $this->assertEquals('7cm', $dist);
        $dist = $style->getPropertyFromLevel(5, 'text-min-label-distance');
        $this->assertEquals('6cm', $dist);
        $dist = $style->getPropertyFromLevel(6, 'text-min-label-distance');
        $this->assertEquals('5cm', $dist);
        $dist = $style->getPropertyFromLevel(7, 'text-min-label-distance');
        $this->assertEquals('4cm', $dist);
        $dist = $style->getPropertyFromLevel(8, 'text-min-label-distance');
        $this->assertEquals('3cm', $dist);
        $dist = $style->getPropertyFromLevel(9, 'text-min-label-distance');
        $this->assertEquals('2cm', $dist);
        $dist = $style->getPropertyFromLevel(10, 'text-min-label-distance');
        $this->assertEquals('1cm', $dist);
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<text:outline-style>
            <text:outline-level-style text:level="1" style:num-format="">
                <style:list-level-properties text:min-label-distance="10cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="2" style:num-format="">
                <style:list-level-properties text:min-label-distance="9cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="3" style:num-format="">
                <style:list-level-properties text:min-label-distance="8cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="4" style:num-format="">
                <style:list-level-properties text:min-label-distance="7cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="5" style:num-format="">
                <style:list-level-properties text:min-label-distance="6cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="6" style:num-format="">
                <style:list-level-properties text:min-label-distance="5cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="7" style:num-format="">
                <style:list-level-properties text:min-label-distance="4cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="8" style:num-format="">
                <style:list-level-properties text:min-label-distance="3cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="9" style:num-format="">
                <style:list-level-properties text:min-label-distance="2cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="10" style:num-format="">
                <style:list-level-properties text:min-label-distance="1cm"/>
            </text:outline-level-style>
        </text:outline-style>';
        $expected  = '<text:outline-style >'."\n";
        $expected .= '    <text:outline-level-style text:level="1" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="10cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="2" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="9cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="3" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="8cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="4" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="7cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="5" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="6cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="6" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="5cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="7" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="4cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="8" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="3cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="9" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="2cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '    <text:outline-level-style text:level="10" style:num-format="" >'."\n";
        $expected .= '        <style:list-level-properties text:min-label-distance="1cm" />'."\n";
        $expected .= '    </text:outline-level-style>'."\n";
        $expected .= '</text:outline-style>'."\n";

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }

    /**
     * Test set and get of a property.
     */
    public function test_set_and_get() {
        $xml_code = '<text:outline-style>
            <text:outline-level-style text:level="1" style:num-format="">
                <style:list-level-properties text:min-label-distance="10cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="2" style:num-format="">
                <style:list-level-properties text:min-label-distance="9cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="3" style:num-format="">
                <style:list-level-properties text:min-label-distance="8cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="4" style:num-format="">
                <style:list-level-properties text:min-label-distance="7cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="5" style:num-format="">
                <style:list-level-properties text:min-label-distance="6cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="6" style:num-format="">
                <style:list-level-properties text:min-label-distance="5cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="7" style:num-format="">
                <style:list-level-properties text:min-label-distance="4cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="8" style:num-format="">
                <style:list-level-properties text:min-label-distance="3cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="9" style:num-format="">
                <style:list-level-properties text:min-label-distance="2cm"/>
            </text:outline-level-style>
            <text:outline-level-style text:level="10" style:num-format="">
                <style:list-level-properties text:min-label-distance="1cm"/>
            </text:outline-level-style>
        </text:outline-style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setPropertyForLevel(5, 'text-min-label-distance', '999cm');

        $dist = $style->getPropertyFromLevel(1, 'text-min-label-distance');
        $this->assertEquals('10cm', $dist);
        $dist = $style->getPropertyFromLevel(2, 'text-min-label-distance');
        $this->assertEquals('9cm', $dist);
        $dist = $style->getPropertyFromLevel(3, 'text-min-label-distance');
        $this->assertEquals('8cm', $dist);
        $dist = $style->getPropertyFromLevel(4, 'text-min-label-distance');
        $this->assertEquals('7cm', $dist);
        $dist = $style->getPropertyFromLevel(5, 'text-min-label-distance');
        $this->assertEquals('999cm', $dist);
        $dist = $style->getPropertyFromLevel(6, 'text-min-label-distance');
        $this->assertEquals('5cm', $dist);
        $dist = $style->getPropertyFromLevel(7, 'text-min-label-distance');
        $this->assertEquals('4cm', $dist);
        $dist = $style->getPropertyFromLevel(8, 'text-min-label-distance');
        $this->assertEquals('3cm', $dist);
        $dist = $style->getPropertyFromLevel(9, 'text-min-label-distance');
        $this->assertEquals('2cm', $dist);
        $dist = $style->getPropertyFromLevel(10, 'text-min-label-distance');
        $this->assertEquals('1cm', $dist);
    }
}
