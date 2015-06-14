<?php
/**
 * ODTStyleSet: Abstract class defining the interface a style set/template
 * needs to implement towards the ODT renderer.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
abstract class ODTStyleSet
{
    /**
     * Read/import style source.
     *
     * @param $source
     */
    abstract public function import($source);

    /**
     * Export styles to the destination
     * (in styles.xml format/as element office:document-styles).
     *
     * @param $destination
     */
    abstract public function export($destination);

    /**
     * The function needs to be able to return a style name
     * for the following basic styles used by the renderer:
     * - standard
     * - body
     * - heading1
     * - heading2
     * - heading3
     * - heading4
     * - heading5
     * - heading6
     * - list
     * - numbering
     * - table content
     * - table heading
     * - preformatted
     * - source code
     * - source file
     * - horizontal line
     * - footnote
     * - emphasis
     * - strong
     * - graphics
     * - monospace
     * - quotation1
     * - quotation2
     * - quotation3
     * - quotation4
     * - quotation5
     *
     * @param $style
     * @return mixed
     */
    abstract public function getStyleName($style);
}

