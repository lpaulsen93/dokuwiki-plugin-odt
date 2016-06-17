<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTableCell:
 * Class for handling the table cell element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTableCell extends ODTStateElement
{
    protected $colspan;
    protected $rowspan;
    protected $value_type = 'string';

    /**
     * Constructor.
     */
    public function __construct($style_name=NULL, $colspan = 1, $rowspan = 1) {
        parent::__construct();
        $this->setClass ('table-cell');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
        $this->setColumnSpan($colspan);
        $this->setRowSpan($rowspan);
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('table:table-cell');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $colspan = $this->getColumnSpan();
        $rowspan = $this->getRowSpan();

        $encoded =  '<table:table-cell office:value-type="'.$this->getValueType().'"';
        $encoded .= ' table:style-name="'.$this->getStyleName().'"';
        if ( $colspan > 1 ) {
            $encoded .= ' table:number-columns-spanned="'.$colspan.'"';
        }
        if ($rowspan > 1) {
            $encoded .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $encoded .= '>';
        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</table:table-cell>';
    }

    /**
     * Are we in a paragraph or not?
     * As a table cell we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table cell our parent is the table element.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        while ($previous != NULL) {
            if ($previous->getClass() == 'table') {
                break;
            }
            $previous = $previous->getParent();
        }
        $this->setParent($previous);

        $curr_column = $previous->getTableCurrentColumn();
        $curr_column++;
        $previous->setTableCurrentColumn($curr_column);

        // Eventually increase max columns if out range
        $max_columns = $previous->getTableMaxColumns();
        if ( $curr_column > $max_columns ) {
            $previous->setTableMaxColumns($max_columns + 1);
        }
    }

    /**
     * Return the table to which this column belongs.
     * 
     * @return ODTStateElement
     */
    public function getTable () {
        return $this->getParent();
    }

    /**
     * Set the number of columns spanned by this cell.
     * 
     * @param integer $value
     */
    public function setColumnSpan($value) {
        $this->colspan = $value;
    }

    /**
     * Get the number of columns spanned by this cell.
     * 
     * @return integer
     */
    public function getColumnSpan() {
        return $this->colspan;
    }

    /**
     * Set the number of rows spanned by this cell.
     * 
     * @param integer $value
     */
    public function setRowSpan($value) {
        $this->rowspan = $value;
    }

    /**
     * Get the number of rows spanned by this cell.
     * 
     * @return integer
     */
    public function getRowSpan() {
        return $this->rowspan;
    }

    /**
     * Set the office value type for this cell.
     * 
     * @param string $value
     */
    public function setValueType($value) {
        $this->value_type = $value;
    }

    /**
     * Get the office value type for this cell.
     * 
     * @return string
     */
    public function getValueType() {
        return $this->value_type;
    }
}
