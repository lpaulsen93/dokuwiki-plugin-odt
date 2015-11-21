<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * Tests to ensure functionality of the ODTUnknownStyle class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_unknownstyle_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string() {
        $xml_code = '<style:default-style style:family="graphic">
            <style:graphic-properties draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm" draw:start-line-spacing-horizontal="0.283cm" draw:start-line-spacing-vertical="0.283cm" draw:end-line-spacing-horizontal="0.283cm" draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false"/>
            <style:paragraph-properties style:text-autospace="ideograph-alpha" style:line-break="strict" style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
                <style:tab-stops/>
            </style:paragraph-properties>
            <style:text-properties style:use-window-font-color="true" fo:font-size="12pt" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
        </style:default-style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style_string = $style->toString();

        $this->assertEquals($xml_code."\n", $style_string);
    }

    /**
     * Test ODT XML style definition import and conversion to string.
     */
    public function test_import_and_to_string_2() {
        $xml_code = '<style:style style:family="NothingIKnow">
            <style:graphic-properties draw:shadow-offset-x="0.3cm" draw:shadow-offset-y="0.3cm" draw:start-line-spacing-horizontal="0.283cm" draw:start-line-spacing-vertical="0.283cm" draw:end-line-spacing-horizontal="0.283cm" draw:end-line-spacing-vertical="0.283cm" style:flow-with-text="false"/>
            <style:paragraph-properties style:text-autospace="ideograph-alpha" style:line-break="strict" style:writing-mode="lr-tb" style:font-independent-line-spacing="false">
                <style:tab-stops/>
            </style:paragraph-properties>
            <style:text-properties style:use-window-font-color="true" fo:font-size="12pt" style:font-size-asian="12pt" style:font-size-complex="12pt"/>
        </style:style>';

        $style = ODTStyle::importODTStyle($xml_code);
        $this->assertNotNull($style);

        $style_string = $style->toString();

        $this->assertEquals($xml_code."\n", $style_string);
    }
}
