<?php
/**
 * Handle ODT Table row elements.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 * @package    ODT\Elements\ODTElementTableRow
 */

/** Include ODTStateElement */
require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTableRow:
 * Class for handling the table row element.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */
class ODTElementTableRow extends ODTStateElement
{
    /**
     * Constructor.
     * 
     * @param    string    $style_name    Name of the style
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('table-row');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
    }

    /**
     * Return the elements name.
     * 
     * @return    string    The ODT XML element name.
     */
    public function getElementName () {
        return ('table:table-row');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return    string    The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $style_name = $this->getStyleName();
        if ($style_name != NULL) {
            return '<table:table-row table:style-name="'.$style_name.'">';
        }
        return '<table:table-row>';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return    string    The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</table:table-row>';
    }

    /**
     * Are we in a paragraph or not?
     * As a table row we are not.
     * 
     * @return    boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table row our parent is the table element.
     *
     * @param    ODTStateElement    $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $table = $previous;
        while ($table != NULL) {
            if ($table->getClass() == 'table') {
                break;
            }
            $table = $table->getParent();
        }
        $this->setParent($table);

        if ($table == NULL) {
            // ??? Should not be...
            return;
        }

        // A new row, we are back in the first column again.
        $table->increaseRowCount();
        $table->setTableCurrentColumn(0);
    }

    /**
     * Return the table to which this column belongs.
     * 
     * @return    ODTStateElement
     */
    public function getTable () {
        return $this->getParent();
    }
}
