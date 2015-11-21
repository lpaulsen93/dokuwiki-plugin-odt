<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTDefaultStyles.php';

/**
 * Tests to ensure functionality of the XMLUtil class.
 *
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_xmlutil_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    /**
     * Test function getElement()
     */
    public function test_getElement_1() {
        $xmlCode = '<a><b>Hallo</b></a>';

        $found = XMLUtil::getElement('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals($xmlCode, $found);
        $this->assertEquals(strlen($xmlCode), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElement_2() {
        $xmlCode = '<a peng><b>Hallo</b></a>';

        $found = XMLUtil::getElement('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals($xmlCode, $found);
        $this->assertEquals(strlen($xmlCode), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElement_3() {
        $xmlCode = '</peng><a peng><b>Hallo</b></a>';

        $found = XMLUtil::getElement('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<a peng><b>Hallo</b></a>', $found);
        $this->assertEquals(strlen($xmlCode), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElement_4() {
        $xmlCode = '</peng><a peng="5"><b>Hallo</b></a><anotherOne></anotherOne>';

        $found = XMLUtil::getElement('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<a peng="5"><b>Hallo</b></a>', $found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElement_5() {
        $xmlCode = '</peng><a peng="dsfg"/><anotherOne></anotherOne>';

        $found = XMLUtil::getElement('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<a peng="dsfg"/>', $found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
        $this->assertEquals(23, $end);
    }

    /**
     * Test function getElementContent()
     */
    public function test_getElementContent_1() {
        $xmlCode = '<a><b>Hallo</b></a>';

        $found = XMLUtil::getElementContent('a', $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<b>Hallo</b>', $found);
        $this->assertEquals(strlen($xmlCode), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElementContent_2() {
        $xmlCode = '</peng><a peng="dsfg"/><anotherOne></anotherOne>';

        $found = XMLUtil::getElementContent('a', $xmlCode, $end);
        $this->assertNull($found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
    }

    /**
     * Test function getElement()
     */
    public function test_getElementContent_3() {
        $xmlCode = '</peng><abc peng="dsfg"></abc><anotherOne></anotherOne>';

        $found = XMLUtil::getElementContent('abc', $xmlCode, $end);
        $this->assertNull($found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
    }

    /**
     * Test function getNextElement()
     */
    public function test_getNextElement_1() {
        $xmlCode = '</peng><unknown peng="5"><b>Hallo</b></unknown><anotherOne></anotherOne>';

        $found = XMLUtil::getNextElement($element, $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<unknown peng="5"><b>Hallo</b></unknown>', $found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
        $this->assertEquals('unknown', $element);
    }

    /**
     * Test function getNextElement()
     */
    public function test_getNextElementContent_1() {
        $xmlCode = '</peng><unknown peng="5"><b>Hallo</b></unknown><anotherOne></anotherOne>';

        $found = XMLUtil::getNextElementContent($element, $xmlCode, $end);
        $this->assertNotNull($found);
        $this->assertEquals('<b>Hallo</b>', $found);
        $this->assertEquals(strlen($xmlCode)-strlen('<anotherOne></anotherOne>'), $end);
        $this->assertEquals('unknown', $element);
    }
}
