<?php

require_once 'ODTTestUtils.php';

/**
 * Tests to ensure that the text fomrating of the document is rendered correctly.
 * (bold, italic, underlined.. text)
 *
 * @group plugin_odt_renderer
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_renderer_format_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();

        // copy test files to test directory
        TestUtils::rcopy(TMP_DIR, dirname(__FILE__) . '/data/');
    }
    
    /**
     * This function checks the rendering of bold text.
     */
    public function test_bold_text () {
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '**This is bold text.**');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph == NULL, 'Element "text:p" not found!');

        // The paragraph shouild have a text span.
        $span_attrs = XMLUtil::getElementOpenTag('text:span', $office_text);
        $span_content = XMLUtil::getElementContent('text:span', $office_text);
        $this->assertFalse($span_content == NULL, 'Element "text:p" not found!');

        // The span should have our text content and the style for bold text
        $this->assertEquals($span_content, 'This is bold text.');
        $attributes = array();
        $found = XMLUtil::getAttributes($attributes, $span_attrs);
        $this->assertEquals(1, $found);
        $this->assertEquals('Strong_20_Emphasis', $attributes['text:style-name']);
    }

    /**
     * This function checks the rendering of italic text.
     */
    public function test_italic_text () {
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '//This is italic text.//');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph == NULL, 'Element "text:p" not found!');

        // The paragraph shouild have a text span.
        $span_attrs = XMLUtil::getElementOpenTag('text:span', $office_text);
        $span_content = XMLUtil::getElementContent('text:span', $office_text);
        $this->assertFalse($span_content == NULL, 'Element "text:p" not found!');

        // The span should have our text content and the style for bold text
        $this->assertEquals($span_content, 'This is italic text.');
        $attributes = array();
        $found = XMLUtil::getAttributes($attributes, $span_attrs);
        $this->assertEquals(1, $found);
        $this->assertEquals('Emphasis', $attributes['text:style-name']);
    }

    /**
     * This function checks the rendering of underlined text.
     */
    public function test_underlined_text () {
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '__This is underlined text.__');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph == NULL, 'Element "text:p" not found!');

        // The paragraph shouild have a text span.
        $span_attrs = XMLUtil::getElementOpenTag('text:span', $office_text);
        $span_content = XMLUtil::getElementContent('text:span', $office_text);
        $this->assertFalse($span_content == NULL, 'Element "text:p" not found!');

        // The span should have our text content and the style for bold text
        $this->assertEquals($span_content, 'This is underlined text.');
        $attributes = array();
        $found = XMLUtil::getAttributes($attributes, $span_attrs);
        $this->assertEquals(1, $found);
        $this->assertEquals('underline', $attributes['text:style-name']);
    }

    /**
     * This function checks the rendering of monospaced text.
     */
    public function test_monospaced_text () {
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, "''This is monospaced text.''");
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph == NULL, 'Element "text:p" not found!');

        // The paragraph shouild have a text span.
        $span_attrs = XMLUtil::getElementOpenTag('text:span', $office_text);
        $span_content = XMLUtil::getElementContent('text:span', $office_text);
        $this->assertFalse($span_content == NULL, 'Element "text:p" not found!');

        // The span should have our text content and the style for bold text
        $this->assertEquals($span_content, 'This is monospaced text.');
        $attributes = array();
        $found = XMLUtil::getAttributes($attributes, $span_attrs);
        $this->assertEquals(1, $found);
        $this->assertEquals('Source_20_Text', $attributes['text:style-name']);
    }

    /**
     * This function checks the rendering of deleted text.
     */
    public function test_deleted_text () {
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '<del>This is strike-through text.</del>');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph == NULL, 'Element "text:p" not found!');

        // The paragraph shouild have a text span.
        $span_attrs = XMLUtil::getElementOpenTag('text:span', $office_text);
        $span_content = XMLUtil::getElementContent('text:span', $office_text);
        $this->assertFalse($span_content == NULL, 'Element "text:p" not found!');

        // The span should have our text content and the style for bold text
        $this->assertEquals($span_content, 'This is strike-through text.');
        $attributes = array();
        $found = XMLUtil::getAttributes($attributes, $span_attrs);
        $this->assertEquals(1, $found);
        $this->assertEquals('del', $attributes['text:style-name']);
    }
}
