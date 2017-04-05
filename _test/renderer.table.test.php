<?php

/**
 * Tests to ensure that tables are handled correctly by the renderer
 *
 * @group plugin_odt_renderer
 * @group plugin_odt
 * @group plugins
 */
class plugin_odt_renderer_table_test extends DokuWikiTest {
    public function setUp() {
        $this->pluginsEnabled[] = 'odt';
        parent::setUp();
    }

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();

        // Copy test media files to test wiki namespace
        // So far not required for table tests
        //ODTTestUtils::rcopyMedia('wiki', dirname(__FILE__) . '/data/media/wiki/');
    }

    /**
     * This function checks if a image is included with the correct size.
     * Therefore an test image with known size is choosen (TestPicture100x50.png).
     */
    public function test_tables() {
        $testpage = '
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

^ Spalte 1 ^ Lange Spalte 2 ^ Spalte 3 ^
| Test Test Test | | |
| | Test Test Test | Test Test Test |

^ Spalte 1 ^ Lange Spalte 2 ^ Spalte 3 ^
| Test Test Test | | |
| | Test Test Test | Test Test Test |

^ Heading 1      ^ Heading 2       ^ Heading 3          ^
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
| Row 2 Col 1    | some colspan (note the double pipe) ||
| Row 3 Col 1    | Row 3 Col 2     | Row 3 Col 3        |

|              ^ Heading 1            ^ Heading 2          ^
^ Heading 3    | Row 1 Col 2          | Row 1 Col 3        |
^ Heading 4    | no colspan this time |                    |
^ Heading 5    | Row 2 Col 2          | Row 2 Col 3        |

^ Heading 1      ^ Heading 2                  ^ Heading 3          ^
| Row 1 Col 1    | this cell spans vertically | Row 1 Col 3        |
| Row 2 Col 1    | :::                        | Row 2 Col 3        |
| Row 3 Col 1    | :::                        | Row 2 Col 3        |

^           Table with alignment           ^^^
|         right|    center    |left          |
|left          |         right|    center    |
| xxxxxxxxxxxx | xxxxxxxxxxxx | xxxxxxxxxxxx |
';

        $files = array();
        $ok = ODTTestUtils::getRenderedODTDocument($files, $testpage);
        $this->assertFalse($ok == false, 'Error rendering, creating, unpacking, reading ODT doc!');
    }
}
