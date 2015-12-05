<?php

require_once 'ODTTestUtils.php';

/**
 * Tests to ensure that the start of the document is rendered correctly.
 *
 * @group plugin_odt_renderer
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_renderer_start_test extends DokuWikiTest {
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
     * This function checks that the document does not include extra
     * paragraphs if the wiki page starts with simple text.
     * 
     * Extra paragraphs without text content would cause the document to
     * start with an empty line(s).
     */
    public function test_start_simple_text () {
        // Render document with one text line only.
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, 'This is the first line.');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);
        $this->assertFalse($paragraph != 'This is the first line.',
                           "First paragraph does not include first line!");
    }

    /**
     * This function checks that the document does not include extra
     * paragraphs if the wiki page starts with a heading.
     * 
     * Extra paragraphs without text content would cause the document to
     * start with an empty line(s).
     */
    public function test_start_heading () {
        // Render document with one heading only.
        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, '====== This is the first line ======');
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text, $paragraph_end);

        // Get first heading
        $heading = XMLUtil::getElementContent('text:h', $office_text, $heading_end);
        $this->assertFalse($heading == NULL, "Heading not found!");
        $found = preg_match('/This is the first line/', $heading);
        $this->assertFalse($found != 1, "Heading does not include first line!");

        // If there is a paragraph, it should start behind the heading
        if ( $paragraph !== NULL ) {
            $this->assertFalse($paragraph_end >= $heading_end, "First paragraph not found!");
        }
    }

    /**
     * This function checks that the document does not include extra
     * paragraphs if the wiki page starts with a list.
     * 
     * Extra paragraphs without text content would cause the document to
     * start with an empty line(s).
     */
    public function test_start_list () {
        // Render document with one list only.
        $files = array();
        $page  = "  *List item 1\n";
        $page .= "  *List item 2\n";
        $ok = ODTTestUtils::getRenderedODTDocument($files, $page);
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);

        // Get first list and first list paragraph
        $list = XMLUtil::getElementContent('text:list', $office_text);
        $list_paragraph = XMLUtil::getElementContent('text:p', $list);
        $this->assertFalse($list_paragraph != 'List item 1',
                           "First list paragraph does not include first line!");
        $this->assertFalse($list_paragraph != $paragraph,
                           "First list paragraph is not the first paragraph!");
    }

    /**
     * This function checks that the document does not include extra
     * paragraphs if the wiki page starts with a ordered list.
     * 
     * Extra paragraphs without text content would cause the document to
     * start with an empty line(s).
     */
    public function test_start_ordered_list () {
        // Render document with one list only.
        $files = array();
        $page  = "  -List item 1\n";
        $page .= "  -List item 2\n";
        $ok = ODTTestUtils::getRenderedODTDocument($files, $page);
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);

        // Get first list and first list paragraph
        $list = XMLUtil::getElementContent('text:list', $office_text);
        $list_paragraph = XMLUtil::getElementContent('text:p', $list);
        $this->assertFalse($list_paragraph != 'List item 1',
                           "First list paragraph does not include first line!");
        $this->assertFalse($list_paragraph != $paragraph,
                           "First list paragraph is not the first paragraph!");
    }

    /**
     * This function checks that the document does not include extra
     * paragraphs if the wiki page starts with a table.
     * 
     * Extra paragraphs without text content would cause the document to
     * start with an empty line(s).
     */
    public function test_start_table () {
        // Render document with one table only.
        $files = array();
        $page  = "^ Heading 1      ^ Heading 2       ^ Heading 3          ^\n";
        $page .= "| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |\n";
        $ok = ODTTestUtils::getRenderedODTDocument($files, $page);
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');

        // Examine ODT XML content.
        // Get office:text
        $office_text = XMLUtil::getElementContent('office:text', $files['content-xml']);
        $this->assertFalse($office_text == NULL, 'Element "office:text" not found!');

        // Get first paragraph
        $paragraph = XMLUtil::getElementContent('text:p', $office_text);

        // Get first table and first table paragraph
        $table = XMLUtil::getElementContent('table:table', $office_text);
        $table_paragraph = XMLUtil::getElementContent('text:p', $table);
        $found = preg_match('/Heading 1/', $table_paragraph);
        $this->assertFalse($found != 1,
                           "First table paragraph does not include first heading!");
        $this->assertFalse($table_paragraph != $paragraph,
                           "First table paragraph is not the first paragraph!");
    }
}
