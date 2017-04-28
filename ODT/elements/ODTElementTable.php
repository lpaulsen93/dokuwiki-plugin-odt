<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';
require_once DOKU_PLUGIN.'odt/ODT/elements/ODTContainerElement.php';

/**
 * ODTElementTable:
 * Class for handling the table element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTable extends ODTStateElement implements iContainerAccess
{
    // Table specific state data
    protected $container = NULL;
    protected $containerPos = NULL;
    protected $table_column_styles = array ();
    protected $table_style = NULL;
    protected $table_autocols = false;
    protected $table_maxcols = 0;
    protected $table_curr_column = 0;
    protected $table_row_count = 0;
    protected $own_max_width = NULL;

    // Flag indicating that a table was created inside of a list
    protected $list_interrupted = false;

    /**
     * Constructor.
     * ($numrows is currently unused)
     */
    public function __construct($style_name=NULL, $maxcols = 0, $numrows = 0) {
        parent::__construct();
        $this->setClass ('table');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
        $this->setTableMaxColumns($maxcols);
        if ($maxcols == 0) {
            $this->setTableAutoColumns(true);
        }
        $this->container = new ODTContainerElement($this);
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('table:table');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $style_name = $this->getStyleName();
        if ($style_name == NULL) {
            $encoded = '<table:table>';
        } else {
            $encoded .= '<table:table table:style-name="'.$style_name.'">';
        }
        $maxcols = $this->getTableMaxColumns();
        $count = $this->getCount();
        if ($maxcols == 0) {
            // Try to automatically detect the number of columns.
            $this->setTableAutoColumns(true);
        } else {
            $this->setTableAutoColumns(false);
        }
        
        // Add column definitions placeholder.
        // This will be replaced on tabelClose()/getClosingTag()
        $encoded .= '<ColumnsPlaceholder'.$count.'>';

        // We start with the first column
        $this->setTableCurrentColumn(0);

        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag (&$content = NULL) {
        // Generate table column definitions and replace the placeholder with it
        $count = $this->getCount();
        $max = $this->getTableMaxColumns();
        if ($max > 0 && $content != NULL) {
            $column_defs = '';
            for ($index = 0 ; $index < $max ; $index++) {
                $styleName = $this->getTableColumnStyleName($index);
                if (!empty($styleName)) {
                    $column_defs .= '<table:table-column table:style-name="'.$styleName.'"/>';
                } else {
                    $column_defs .= '<table:table-column/>';
                }
            }
            $content =
                str_replace ('<ColumnsPlaceholder'.$count.'>', $column_defs, $content);
            $content =
                str_replace ('<MaxColsPlaceholder'.$count.'>', $max, $content);
        }

        return '</table:table>';
    }

    /**
     * Are we in a paragraph or not?
     * As a table we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table the previous element is our parent.
     * 
     * If the table is nested in another table, then the surrounding
     * table is the parent!
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->container->determineParent($previous);
        if ($this->isNested ()) {
            $this->containerPos = array();
            $this->getParent()->determinePositionInContainer($this->containerPos, $previous);
        }
    }

    /**
     * Set table column styles 
     * 
     * @param array $value
     */
    public function setTableColumnStyles($value) {
        $this->table_column_styles = $value;
    }

    /**
     * Set table column style for $column
     * 
     * @param array $value
     */
    public function setTableColumnStyleName($column, $style_name) {
        $this->table_column_styles [$column] = $style_name;
    }

    /**
     * Get table column styles
     * 
     * @return array
     */
    public function getTableColumnStyles() {
        return $this->table_column_styles;
    }

    /**
     * Set table column style for $column
     * 
     * @param array $value
     */
    public function getTableColumnStyleName($column) {
        return $this->table_column_styles [$column];
    }

    /**
     * Set flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @param boolean $value
     */
    public function setTableAutoColumns($value) {
        $this->table_autocols = $value;
    }

    /**
     * Get flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @return boolean
     */
    public function getTableAutoColumns() {
        return $this->table_autocols;
    }

    /**
     * Set maximal number of columns.
     * 
     * @param integer $value
     */
    public function setTableMaxColumns($value) {
        $this->table_maxcols = $value;
    }

    /**
     * Get maximal number of columns.
     * 
     * @return integer
     */
    public function getTableMaxColumns() {
        return $this->table_maxcols;
    }

    /**
     * Set current column.
     * 
     * @param integer $value
     */
    public function setTableCurrentColumn($value) {
        $this->table_curr_column = $value;
    }

    /**
     * Get current column.
     * 
     * @return integer
     */
    public function getTableCurrentColumn() {
        return $this->table_curr_column;
    }

    /**
     * Get the predefined style name for the current
     * table column.
     * 
     * @return string
     */
    public function getCurrentTableColumnStyleName() {
        $table_column_styles = $this->getTableColumnStyles();
        $curr_column = $this->getTableCurrentColumn();
        return $table_column_styles [$curr_column];
    }

    /**
     * Set flag if current list is interrupted (by a table) or not.
     * 
     * @param boolean $value
     */
    public function setListInterrupted($value) {
        $this->list_interrupted = $value;
    }

    /**
     * Get flag if current list is interrupted (by a table) or not.
     * 
     * @return boolean
     */
    public function getListInterrupted() {
        return $this->list_interrupted;
    }

    /**
     * Increae the number of rows
     * 
     * @param boolean $value
     */
    public function increaseRowCount() {
        $this->table_row_count++;
    }

    public function getRowCount() {
        return $this->table_row_count;
    }

    /**
     * Is this table a nested table (inserted into another table)?
     * 
     * @return boolean
     */
    public function isNested () {
        return $this->container->isNested();
    }

    public function addNestedContainer (iContainerAccess $nested) {
        $this->container->addNestedContainer ($nested);
    }

    public function getNestedContainers () {
        return $this->container->getNestedContainers ();
    }

    public function determinePositionInContainer (array &$data, ODTStateElement $current) {
        $data ['column'] = $this->getTableCurrentColumn();
        $cell = NULL;
        while ($current != NULL) {
            if ($current->getClass() == 'table-cell') {
                $cell = $current;
                break;
            }
            if ($current->getClass() == 'table') {
                break;
            }
            $current = $current->getParent();
        }
        if ($cell !== NULL) {
            $data ['cell'] = $cell;
        }
    }

    public function getMaxWidthOfNestedContainer (ODTInternalParams $params, array $data) {
        if ($this->own_max_width === NULL) {
            // We do not know our own width yet. Calculate it first.
            $this->own_max_width = $this->getMaxWidth($params);
        }

        $column = $data ['column'];
        $cell = $data ['cell'];

        $cell_style = $cell->getStyle();
        $padding = 0;
        if ($cell_style->getProperty('padding-left') != NULL
            ||
            $cell_style->getProperty('padding-right') != NULL) {
            $value = $cell_style->getProperty('padding-left');
            $value = $params->document->toPoints($value, 'y');
            $padding += $value;
            $value = $cell_style->getProperty('padding-right');
            $value = $params->document->toPoints($value, 'y');
            $padding += $value;
        } else if ($cell_style->getProperty('padding') != NULL) {
            $value = $cell_style->getProperty('padding');
            $value = $params->document->toPoints($value, 'y');
            $padding += 2 * $value;
        }

        $table_column_styles = $this->getTableColumnStyles();
        $style_name = $table_column_styles [$column-1];
        $style_obj = $params->document->getStyle($style_name);
        if ($style_obj !== NULL) {
            $width = $style_obj->getProperty('column-width');
            $width = trim ($width, 'pt');
            $width -= $padding;
        }

        // Compare with total table width
        if ($this->own_max_width !== NULL) {
            $table_width = $params->units->getDigits ($params->units->toPoints($this->own_max_width));

            if ($table_width < $width) {
                $width = $table_width;
            }
        }

        return $width.'pt';
    }

    public function getMaxWidth (ODTInternalParams $params) {
        $tableStyle = $this->getStyle();
        if (!$this->isNested ()) {
            // Get max page width in points.
            $maxPageWidth = $params->document->getAbsWidthMindMargins ();
            $maxPageWidthPt = $params->units->getDigits ($params->units->toPoints($maxPageWidth.'cm'));

            // Get table left margin
            $leftMargin = $tableStyle->getProperty('margin-left');
            if ($leftMargin === NULL) {
                $leftMarginPt = 0;
            } else {
                $leftMarginPt = $params->units->getDigits ($params->units->toPoints($leftMargin));
            }

            // Get table right margin
            $rightMargin = $tableStyle->getProperty('margin-right');
            if ($rightMargin === NULL) {
                $rightMarginPt = 0;
            } else {
                $rightMarginPt = $params->units->getDigits ($params->units->toPoints($rightMargin));
            }

            // Get table width
            $width = $tableStyle->getProperty('width');
            if ($width !== NULL) {
                $widthPt = $params->units->getDigits ($params->units->toPoints($width));
            }

            if ($width === NULL) {
                $width = $maxPageWidthPt - $leftMarginPt - $rightMarginPt;
            } else {
                $width = $widthPt;
            }
            $width = $width.'pt';
        } else {
            // If this table is nested in another container we have to ask it's parent
            // for the allowed max width
            $width = $this->getParent()->getMaxWidthOfNestedContainer($params, $this->containerPos);
        }

        return $width;
    }

    /**
     * This function replaces the width of $table with the
     * value of all column width added together. If a column has
     * no width set then the function will abort and change nothing.
     * 
     * @param ODTDocument $doc The current document
     * @param ODTElementTable $table The table to be adjusted
     */
    public function adjustWidth (ODTInternalParams $params, $allowNested=false) {
        if ($this->isNested () && !$allowNested) {
            // Do not do anything if this is a nested table.
            // Only if the function is called for the parent/root table
            // then the width of the nested tables will be calculated.
            return;
        }
        $matches = array ();

        $table_style_name = $this->getStyleName();
        if (empty($table_style_name)) {
            return;
        }

        $max_width = $this->getMaxWidth($params);
        $width = $this->adjustWidthInternal ($params, $max_width);

        $style_obj = $params->document->getStyle($table_style_name);
        if ($style_obj != NULL) {
            $style_obj->setProperty('width', $width.'pt');
            if (!$this->isNested ()) {
                // Calculate rel width in relation to maximum page width
                $maxPageWidth = $params->document->getAbsWidthMindMargins ();
                $maxPageWidth = $params->units->getDigits ($params->units->toPoints($maxPageWidth.'cm'));
                if ($maxPageWidth != 0) {
                    $rel_width = round(($width * 100)/$maxPageWidth);
                }
            } else {
                // Calculate rel width in relation to maximum table width
                if ($max_width != 0) {
                    $rel_width = round(($width * 100)/$max_width);
                }
            }
            $style_obj->setProperty('rel-width', $rel_width.'%');
        }

        // Now adjust all nested containers too
        $nested = $this->getNestedContainers ();
        foreach ($nested as $container) {
            $container->adjustWidth ($params, true);
        }
    }

    public function adjustWidthInternal (ODTInternalParams $params, $maxWidth) {
        $empty = array();
        $relative = array();
        $anyWidthFound = false;
        $onlyAbsWidthFound = true;

        $tableStyle = $this->getStyle();

        // First step:
        // - convert all absolute widths to points
        // - build the sum of all absolute column width values (if any)
        // - build the sum of all relative column width values (if any)
        $abs_sum = 0;
        $table_column_styles = $this->getTableColumnStyles();
        $replace = true;
        for ($index = 0 ; $index < $this->getTableMaxColumns() ; $index++ ) {
            $style_name = $table_column_styles [$index];
            $style_obj = $params->document->getStyle($style_name);
            if ($style_obj != NULL) {
                if ($style_obj->getProperty('rel-column-width') != NULL) {
                    $width = $style_obj->getProperty('rel-column-width');
                    $length = strlen ($width);
                    $width = trim ($width, '*');

                    // Add column style object to relative array
                    // We need convert it to an absolute width
                    $entry = array();
                    $entry ['width'] = $width;
                    $entry ['obj'] = $style_obj;
                    $relative [] = $entry;

                    $abs_sum += (($width/10)/100) * $maxWidth;
                    $onlyAbsWidthFound = false;
                    $anyWidthFound = true;
                } else if ($style_obj->getProperty('column-width') != NULL) {
                    $width = $style_obj->getProperty('column-width');
                    $length = strlen ($width);
                    $width = $params->document->toPoints($width, 'x');
                    $abs_sum += (float) trim ($width, 'pt');
                    $anyWidthFound = true;
                } else {
                    // Add column style object to empty array
                    // We need to assign a width to this column
                    $empty [] = $style_obj;
                    $onlyAbsWidthFound = false;
                }
            }
        }

        // Convert max width to points
        $maxWidth = $params->units->toPoints($maxWidth);
        $maxWidth = $params->units->getDigits($maxWidth);

        // The remaining absolute width is the max width minus the sum of
        // all absolute width values
        $absWidthLeft = $maxWidth - $abs_sum;

        // Calculate the relative width left
        // (e.g. if the absolute width is the half of the max width
        //  then the relative width left if 50%)
        if ($maxWidth != 0) {
            $relWidthLeft = 100-(($absWidthLeft/$maxWidth)*100);
        }

        // Give all empty columns a width
        $maxEmpty = count($empty);
        foreach ($empty as $column) {
            //$width = ($relWidthLeft/$maxEmpty) * $absWidthLeft;
            $width = $absWidthLeft/$maxEmpty;
            $column->setProperty('column-width', $width.'pt');
            $column->setProperty('rel-column-width', NULL);
        }

        // Convert all relative width to absolute
        foreach ($relative as $column) {
            $width = (($column ['width']/10)/100) * $maxWidth;
            $column ['obj']->setProperty('column-width', $width.'pt');
            $column ['obj']->setProperty('rel-column-width', NULL);
        }

        // If all columns have a fixed absolute width set then that means
        // the table shall have the width of all comuns added together
        // and not the maximum available width. Return $abs_sum.
        if ($onlyAbsWidthFound && $anyWidthFound) {
            return $abs_sum;
        }
        return $maxWidth;
    }
}
