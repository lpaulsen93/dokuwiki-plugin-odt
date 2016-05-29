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
    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('table-column');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
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
        $curr_column = $table->getTableCurrentColumn();

        // Set our style name to a predefined name
        // and also set it in the table (if not done yet)
        $style_name = $table->getTableColumnStyleName($curr_column);
        if (empty($style_name)) {
            $style_name = 'odt_auto_style_table_column_'.$table->getCount().'_'.($curr_column+1);
            $table->setTableColumnStyleName($curr_column, $style_name);
        }
        $this->setStyleName ($style_name);

        $curr_column++;
        $table->setTableCurrentColumn($curr_column);

        // Eventually add a new temp column if in auto mode
        if ( $auto_columns === true ) {
            if ( $curr_column > $max_columns ) {
                // Add temp column.
                $column_defs = $table->getTableColumnDefs();
                $column_defs .= '<table:table-column table:style-name="'.$style_name.'"/>';
                //$column_defs .= '<table:table-column table:style-name="Peng"/>';
                $table->setTableColumnDefs($column_defs);
                $table->setTableMaxColumns($max_columns + 1);
            }
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
