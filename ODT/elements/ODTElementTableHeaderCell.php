<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTableHeaderCell:
 * Class for handling the table "header" cell element.
 * 
 * In ODT there is no header cell element so this is just a normal
 * table cell with some extra handling for the automatic column
 * count mode.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTableHeaderCell extends ODTElementTableCell
{
    /**
     * Constructor.
     */
    public function __construct($style_name=NULL, $colspan = 0, $rowspan = 0) {
        parent::__construct($style_name, $colspan, $rowspan);
        $this->setClass ('table-header');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $colspan = $this->getColumnSpan();
        $rowspan = $this->getRowSpan();

        // Get our corresponding table.
        $table = $this->getTable();
        $auto_columns = false;
        $count = 0;
        if ($table != NULL) {
            $auto_columns = $table->getTableAutoColumns();
            $count = $table->getCount();
        }

        $encoded =  '<table:table-cell office:value-type="'.$this->getValueType().'"';
        $encoded .= ' table:style-name="'.$this->getStyleName().'"';
        if ( $colspan > 1 ) {
            $encoded .= ' table:number-columns-spanned="'.$colspan.'"';
        } else if ($auto_columns === true && $colspan == 0) {
            $encoded .= ' table:number-columns-spanned="<MaxColsPlaceholder'.$count.'>"';
        }
        if ( $rowspan > 1 ) {
            $encoded .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $encoded .= '>';
        return $encoded;
    }
}
