<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTableColumn:
 * Class for handling the table column element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTableColumn extends ODTStateElement
{
    // Which column number in the corresponding table is this?
    // [Is set on enter() ==> determineParent()]
    protected $columnNumber = 0;
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setClass ('table-column');
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('table:table-column');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        return '<table:table-column table:style-name="'.$this->getStyleName().'"/>';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</table:table-column>';
    }

    /**
     * Are we in a paragraph or not?
     * As a table column we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table column our parent is the table element.
     *
     * @param ODTStateElement $previous
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

        // Overwrite/Create column style for actual column if $properties has any
        // meaningful params for a column-style (e.g. width).
        $table_column_styles = $table->getTableColumnStyles();
        $auto_columns = $table->getTableAutoColumns();
        $max_columns = $table->getTableMaxColumns();
        $row_count = $table->getRowCount();
        $curr_column = $table->getTableCurrentColumn();
        if ($row_count > 0) {
            $curr_column--;
        }

        // Set our column number.
        $this->columnNumber = $curr_column;

        // Set our style name to a predefined name
        // and also set it in the table (if not done yet)
        $style_name = $table->getTableColumnStyleName($curr_column);
        if (empty($style_name)) {
            $style_name = 'odt_auto_style_table_column_'.$table->getCount().'_'.($curr_column+1);
            $table->setTableColumnStyleName($curr_column, $style_name);
        }
        $this->setStyleName ($style_name);

        if ($row_count == 0) {
            // Only count columns here if not already a row has been opened.
            // Otherwise counting will be done in ODTElementTableCell!
            $curr_column++;
            $table->setTableCurrentColumn($curr_column);

            // Eventually increase max columns if out range
            if ( $curr_column > $max_columns ) {
                $table->setTableMaxColumns($max_columns + 1);
            }
        }
    }

    /**
     * Set the style name.
     * The method is overwritten to make the column also set the new
     * column style name in its corresponding table.
     * 
     * FIXME: it would be better to just have an array of object
     * pointers in the table and use them to query the names.
     * 
     * @param string $value Style name, e.g. 'body'
     */
    public function setStyleName($value) {
        parent::setStyleName($value);
        $table = $this->getParent();
        if ($table != NULL) {
            $table->setTableColumnStyleName($this->columnNumber, $value);
        } else {
            // FIXME: some error logging would be nice...
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
}
