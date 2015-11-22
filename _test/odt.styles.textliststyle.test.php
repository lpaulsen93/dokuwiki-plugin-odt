<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTTextListStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_textliststyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import.
     */
    public function test_simple_odt_import() {
        $xml_code =
        '<text:list-style style:name="Numbering_20_1" style:display-name="Numbering 1">
            <text:list-level-style-number text:level="1" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.499cm" fo:text-indent="-0.499cm" fo:margin-left="0.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1cm" fo:text-indent="-0.499cm" fo:margin-left="1cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.499cm" fo:text-indent="-0.499cm" fo:margin-left="1.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2cm" fo:text-indent="-0.499cm" fo:margin-left="2cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.499cm" fo:text-indent="-0.499cm" fo:margin-left="2.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3cm" fo:text-indent="-0.499cm" fo:margin-left="3cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.5cm" fo:text-indent="-0.499cm" fo:margin-left="3.5cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.001cm" fo:text-indent="-0.499cm" fo:margin-left="4.001cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.5cm" fo:text-indent="-0.499cm" fo:margin-left="4.5cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.001cm" fo:text-indent="-0.499cm" fo:margin-left="5.001cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
        </text:list-style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);
        $dist = $style->getPropertyFromLevel(1, 'list-tab-stop-position');
        $this->assertEquals('0.499cm', $dist);
        $dist = $style->getPropertyFromLevel(2, 'list-tab-stop-position');
        $this->assertEquals('1cm', $dist);
        $dist = $style->getPropertyFromLevel(3, 'list-tab-stop-position');
        $this->assertEquals('1.499cm', $dist);
        $dist = $style->getPropertyFromLevel(4, 'list-tab-stop-position');
        $this->assertEquals('2cm', $dist);
        $dist = $style->getPropertyFromLevel(5, 'list-tab-stop-position');
        $this->assertEquals('2.499cm', $dist);
        $dist = $style->getPropertyFromLevel(6, 'list-tab-stop-position');
        $this->assertEquals('3cm', $dist);
        $dist = $style->getPropertyFromLevel(7, 'list-tab-stop-position');
        $this->assertEquals('3.5cm', $dist);
        $dist = $style->getPropertyFromLevel(8, 'list-tab-stop-position');
        $this->assertEquals('4.001cm', $dist);
        $dist = $style->getPropertyFromLevel(9, 'list-tab-stop-position');
        $this->assertEquals('4.5cm', $dist);
        $dist = $style->getPropertyFromLevel(10, 'list-tab-stop-position');
        $this->assertEquals('5.001cm', $dist);
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code =
        '<text:list-style style:name="List_20_1" style:display-name="List 1">
            <text:list-level-style-bullet text:level="1" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.4cm" fo:text-indent="-0.4cm" fo:margin-left="0.4cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="2" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.801cm" fo:text-indent="-0.4cm" fo:margin-left="0.801cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="StarSymbol1"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="3" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.199cm" fo:text-indent="-0.4cm" fo:margin-left="1.199cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="4" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.6cm" fo:text-indent="-0.4cm" fo:margin-left="1.6cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="5" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2cm" fo:text-indent="-0.4cm" fo:margin-left="2cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="6" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.401cm" fo:text-indent="-0.4cm" fo:margin-left="2.401cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="7" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.799cm" fo:text-indent="-0.4cm" fo:margin-left="2.799cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="8" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.2cm" fo:text-indent="-0.4cm" fo:margin-left="3.2cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="9" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.6cm" fo:text-indent="-0.4cm" fo:margin-left="3.6cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="10" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.001cm" fo:text-indent="-0.4cm" fo:margin-left="4.001cm"/>
                </style:list-level-properties>
                <style:text-properties style:font-name="OpenSymbol"/>
            </text:list-level-style-bullet>
        </text:list-style>';

        $expected  = '<text:list-style style:name="List_20_1" style:display-name="List 1" >'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="1" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.4cm" fo:text-indent="-0.4cm" fo:margin-left="0.4cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="2" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.801cm" fo:text-indent="-0.4cm" fo:margin-left="0.801cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="StarSymbol1" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="3" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.199cm" fo:text-indent="-0.4cm" fo:margin-left="1.199cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="4" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.6cm" fo:text-indent="-0.4cm" fo:margin-left="1.6cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="5" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2cm" fo:text-indent="-0.4cm" fo:margin-left="2cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="6" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.401cm" fo:text-indent="-0.4cm" fo:margin-left="2.401cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="7" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.799cm" fo:text-indent="-0.4cm" fo:margin-left="2.799cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="8" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.2cm" fo:text-indent="-0.4cm" fo:margin-left="3.2cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="9" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.6cm" fo:text-indent="-0.4cm" fo:margin-left="3.6cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '    <text:list-level-style-bullet text:level="10" text:style-name="Numbering_20_Symbols" text:bullet-char="•" >'."\n";
        $expected .= '        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment" >'."\n";
        $expected .= '            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.001cm" fo:text-indent="-0.4cm" fo:margin-left="4.001cm" />'."\n";
        $expected .= '        </style:list-level-properties>'."\n";
        $expected .= '        <style:text-properties style:font-name="OpenSymbol" />'."\n";
        $expected .= '    </text:list-level-style-bullet>'."\n";
        $expected .= '</text:list-style>'."\n";

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style_string = $style->toString();

        $this->assertEquals($expected, $style_string);
    }

    /**
     * Test set and get of a property.
     */
    public function test_set_and_get() {
        $xml_code =
        '<text:list-style style:name="Numbering_20_1" style:display-name="Numbering 1">
            <text:list-level-style-number text:level="1" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.499cm" fo:text-indent="-0.499cm" fo:margin-left="0.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1cm" fo:text-indent="-0.499cm" fo:margin-left="1cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="1.499cm" fo:text-indent="-0.499cm" fo:margin-left="1.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2cm" fo:text-indent="-0.499cm" fo:margin-left="2cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.499cm" fo:text-indent="-0.499cm" fo:margin-left="2.499cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3cm" fo:text-indent="-0.499cm" fo:margin-left="3cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.5cm" fo:text-indent="-0.499cm" fo:margin-left="3.5cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.001cm" fo:text-indent="-0.499cm" fo:margin-left="4.001cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.5cm" fo:text-indent="-0.499cm" fo:margin-left="4.5cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
            <text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                    <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.001cm" fo:text-indent="-0.499cm" fo:margin-left="5.001cm"/>
                </style:list-level-properties>
            </text:list-level-style-number>
        </text:list-style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style->setPropertyForLevel(5, 'list-tab-stop-position', '999cm');

        $dist = $style->getPropertyFromLevel(1, 'list-tab-stop-position');
        $this->assertEquals('0.499cm', $dist);
        $dist = $style->getPropertyFromLevel(2, 'list-tab-stop-position');
        $this->assertEquals('1cm', $dist);
        $dist = $style->getPropertyFromLevel(3, 'list-tab-stop-position');
        $this->assertEquals('1.499cm', $dist);
        $dist = $style->getPropertyFromLevel(4, 'list-tab-stop-position');
        $this->assertEquals('2cm', $dist);
        $dist = $style->getPropertyFromLevel(5, 'list-tab-stop-position');
        $this->assertEquals('999cm', $dist);
        $dist = $style->getPropertyFromLevel(6, 'list-tab-stop-position');
        $this->assertEquals('3cm', $dist);
        $dist = $style->getPropertyFromLevel(7, 'list-tab-stop-position');
        $this->assertEquals('3.5cm', $dist);
        $dist = $style->getPropertyFromLevel(8, 'list-tab-stop-position');
        $this->assertEquals('4.001cm', $dist);
        $dist = $style->getPropertyFromLevel(9, 'list-tab-stop-position');
        $this->assertEquals('4.5cm', $dist);
        $dist = $style->getPropertyFromLevel(10, 'list-tab-stop-position');
        $this->assertEquals('5.001cm', $dist);
    }
}
