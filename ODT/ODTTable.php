<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTTable:
 * Class containing static code for handling tables.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTTable
{
    /**
     * Open/start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     */
    public static function tableOpen(ODTDocument $doc, $maxcols = NULL, $numrows = NULL, &$content, $tableStyleName=NULL){
        // Close any open paragraph.
        $doc->paragraphClose($content);
        
        // Do additional actions if the parent element is a list.
        // In this case we need to finish the list and re-open it later
        // after the table has been closed! --> tables may not be part of a list item in ODT!

        $interrupted = false;
        if ($tableStyleName == NULL) {
            $tableStyleName = $doc->getStyleName('table');
        }

        $list_item = $doc->state->getCurrentListItem();
        if ($list_item != NULL) {
            // We are in a list item. Query indentation settings.
            $list = $list_item->getList();
            if ($list != NULL) {
                $list_style_name = $list->getStyleName();
                $list_style = $doc->getStyle($list_style_name);
                if ($list_style != NULL) {
                    // The list level stored in the list item/from the parser
                    // might not be correct. Count 'list' states to get level.
                    $level = $doc->state->countClass('list');

                    // Create a table style for indenting the table.
                    // We try to achieve this by substracting the list indentation
                    // from the width of the table and right align it!
                    // (if not done yet, the name must be unique!)
                    $style_name = 'Table_Indentation_Level'.$level;
                    if (!$doc->styleExists($style_name)) {
                        $style_obj = clone $doc->getStyle($tableStyleName);
                        $style_obj->setProperty('style-name', $style_name);
                        if ($style_obj != NULL) {
                            $max = $doc->getAbsWidthMindMargins();
                            $indent = 0 + ODTUnits::getDigits($list_style->getPropertyFromLevel($level, 'margin-left'));
                            $style_obj->setProperty('width', ($max-$indent).'cm');
                            $style_obj->setProperty('align', 'right');
                            $doc->addAutomaticStyle($style_obj);
                        }
                    }
                    $tableStyleName = $style_name;
                }
            }

            // Close all open lists and remember their style (may be nested!)
            $lists = array();
            $first = true;
            $iterations = 0;
            $list = $doc->state->getCurrentList();
            while ($list != NULL)
            {
                // Close list items
                if ($first == true) {
                    $first = false;
                    $doc->listContentClose($content);
                }
                $doc->listItemClose($content);
                
                // Now we are in the list state!
                // Get the lists style name before closing it.
                $lists [] = $doc->state->getStyleName();
                $doc->listClose($content);
                
                if ($doc->state == NULL || $doc->state->getElement() == 'root') {
                    break;
                }

                // List has been closed (and removed from stack). Get next.
                $list = $doc->state->getCurrentList();

                // Just to prevent endless loops in case of an error!
                $iterations++;
                if ($iterations == 50) {
                    $content .= 'Error: ENDLESS LOOP!';
                    break;
                }
            }

            $interrupted = true;
        }

        $table = new ODTElementTable($tableStyleName, $maxcols, $numrows);
        $doc->state->enter($table);
        if ($interrupted == true) {
            // Set marker that list has been interrupted
            $table->setListInterrupted(true);

            // Save the lists array as temporary data
            // in THIS state because this is the state that we get back
            // to in table_close!!!
            // (we closed the ODT list, we can't access its state info anymore!
            //  So we use the table state to save the style name!)
            $table->setTemp($lists);
        }
        
        $content .= $table->getOpeningTag();
    }

    /**
     * Close/finish a table
     */
    public static function tableClose(ODTDocument $doc, &$content){
        $table = $doc->state->getCurrentTable();
        if ($table == NULL) {
            // ??? Error. Not table found.
            return;
        }

        $interrupted = $table->getListInterrupted();
        $lists = NULL;
        if ($interrupted) {
            $lists = $table->getTemp();
        }

        // Eventually replace table width.
        self::replaceTableWidth ($doc, $table);

        // Close the table.
        $content .= $table->getClosingTag($content);
        $doc->state->leave();

        // Do additional actions required if we interrupted a list,
        // see table_open()
        if ($interrupted) {
            // Re-open list(s) with original style!
            // (in revers order of lists array)
            $max = count($lists);
            for ($index = $max ; $index > 0 ; $index--) {
                $doc->listOpen(true, $lists [$index-1], $content);
                
                // If this is not the most inner list then we need to open
                // a list item too!
                if ($index > 0) {
                    $doc->listItemOpen($max-$index, $content);
                }
            }

            // DO NOT set marker that list is not interrupted anymore, yet!
            // The renderer will still call listcontent_close and listitem_close!
            // The marker will only be reset on the next call from the renderer to listitem_open!!!
            //$table->setListInterrupted(false);
        }
    }

    /**
     * @param array $properties
     */
    public static function tableAddColumn (ODTDocument $doc, $styleNameSet=NULL, &$styleNameGet=NULL){
        // Create new column
        $column = new ODTElementTableColumn();
        $doc->state->enter($column);

        if ($styleNameSet != NULL) {
            // Change automatically assigned style name.
            $column->setStyleName($styleNameSet);
        }

        // Return style name to caller.
        $styleNameGet = $column->getStyleName();

        // Never create any new document content here!!!
        // Columns have already been added on table open or are
        // re-written on table close.
        $doc->state->leave();
    }

    /**
     * Open a table row
     */
    public static function tableRowOpen(ODTDocument $doc, &$content, $styleName=NULL){
        $row = new ODTElementTableRow($styleName);
        $doc->state->enter($row);
        $content .= $row->getOpeningTag();
    }

    /**
     * Close a table row
     */
    public static function tableRowClose(ODTDocument $doc, &$content){
        $doc->closeCurrentElement($content);
    }

    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param int    $rowspan
     * @param string $align left|center|right
     */
    public static function tableHeaderOpen(ODTDocument $doc, $colspan = 1, $rowspan = 1, $align = "left", &$content, $cellStyle=NULL, $paragraphStyle=NULL){
        // Are style names given? If not, use defaults.
        if (empty($cellStyle)) {
            $cellStyle = $doc->getStyleName('table header');
        }
        if (empty($paragraphStyle)) {
            $paragraphStyle = $doc->getStyleName('table heading');
        }

        // ODT has no element for the table header.
        // We mark the state with a differnt class to be able
        // to differ between a normal cell and a header cell.
        $header_cell = new ODTElementTableHeaderCell
            ($cellStyle, $colspan, $rowspan);
        $doc->state->enter($header_cell);

        // Encode table (header) cell.
        $content .= $header_cell->getOpeningTag();

        // Open new paragraph with table heading style.
        $doc->paragraphOpen($paragraphStyle, $content);
    }

    /**
     * Close a table header cell
     */
    public static function tableHeaderClose(ODTDocument $doc, &$content){
        $doc->paragraphClose($content);
        $doc->closeCurrentElement($content);
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param int    $rowspan
     * @param string $align left|center|right
     */
    public static function tableCellOpen(ODTDocument $doc, $colspan = 1, $rowspan = 1, $align = "left", &$content, $cellStyle=NULL, $paragraphStyle=NULL){
        // Are style names given? If not, use defaults.
        if (empty($cellStyle)) {
            $cellStyle = $doc->getStyleName('table cell');
        }
        if (empty($paragraphStyle)) {
            // Open paragraph with required alignment.
            if (!$align) $align = "left";
            $paragraphStyle = $doc->getStyleName('tablealign '.$align);
        }

        $cell = new ODTElementTableCell
            ($cellStyle, $colspan, $rowspan);
        $doc->state->enter($cell);

        // Encode table cell.
        $content .= $cell->getOpeningTag();

        // Open paragraph.
        $doc->paragraphOpen($paragraphStyle, $content);
    }

    /**
     * Close a table cell
     */
    public static function tableCellClose(ODTDocument $doc, &$content){
        $doc->paragraphClose($content);
        $doc->closeCurrentElement($content);
    }

    /**
     * This function opens a new table using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableOpenUseProperties. See the function description for supported properties.
     *
     * The table should be closed by calling 'table_close()'.
     *
     * @author LarsDW223
     *
     * @param cssimportnew $import
     * @param $classes
     * @param null $baseURL
     * @param null $element
     * @param null $maxcols
     * @param null $numrows
     */
    public static function tableOpenUseCSS(ODTDocument $doc, &$content, $maxcols=NULL, $numrows=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $doc->import;
        }

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //$properties = array();
        //if ( empty($element) ) {
        //    $element = 'table';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::tableOpenUseProperties($doc, $content, $properties, $maxcols, $numrows);
    }

    /**
     * This function opens a new table using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'width'.
     *
     * The currently supported properties are:
     * width, border-collapse, background-color
     *
     * The table must be closed by calling 'table_close'.
     *
     * @author LarsDW223
     *
     * @param array $properties
     * @param null $maxcols
     * @param null $numrows
     */
    public static function tableOpenUseProperties (ODTDocument $doc, &$content, $properties, $maxcols = 0, $numrows = 0){
        $doc->paragraphClose($content);

        // Eventually adjust table width.
        if ( !empty ($properties ['width']) ) {
            if ( $properties ['width'] [$properties ['width']-1] != '%' ) {
                // Width has got an absolute value.
                // Some units are not supported by ODT for table width (e.g. 'px').
                // So we better convert it to points.
                $properties ['width'] = $doc->toPoints($properties ['width'], 'x');
            }
        }
        
        // Create style.
        // FIXME: fix disabled_props, ask state for current max width...
        $style_obj = ODTTableStyle::createTableTableStyle($properties, NULL, 17);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open the table referencing our style.
        self::tableOpen($doc, $maxcols, $numrows, $content, $style_name);
    }

    public static function tableAddColumnUseCSS(ODTDocument $doc, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $doc->import;
        }

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        self::tableAddColumnUseProperties($doc, $properties);
    }

    /**
     * @param array $properties
     */
    public static function tableAddColumnUseProperties (ODTDocument $doc, array $properties = NULL){
        // Add column and set/query assigned style name
        $styleName = $properties ['style-name'];
        $styleNameGet = '';
        self::tableAddColumn ($doc, $styleName, $styleNameGet);

        // Overwrite/Create column style for actual column
        $properties ['style-name'] = $styleNameGet;
        $style_obj = ODTTableColumnStyle::createTableColumnStyle ($properties);
        $doc->addAutomaticStyle($style_obj);
    }

    /**
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param null $baseURL
     * @param null $element
     * @param int $colspan
     * @param int $rowspan
     */
    public static function tableHeaderOpenUseCSS(ODTDocument $doc, &$content, $colspan = 1, $rowspan = 1, $attributes=NULL, cssimportnew $import=NULL){
        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'th';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::tableHeaderOpenUseProperties($doc, $content, $properties, $colspan, $rowspan);
    }

    /**
     * @param null $properties
     * @param int $colspan
     * @param int $rowspan
     */
    public static function tableHeaderOpenUseProperties (ODTDocument $doc, &$content, $properties = NULL, $colspan = 1, $rowspan = 1){
        // Open cell, second parameter MUST BE true to indicate we are in the header.
        self::tableCellOpenUsePropertiesInternal ($doc, $content, $properties, true, $colspan, $rowspan);
    }

    /**
     * This function opens a new table row using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableRowOpenUseProperties. See the function description for supported properties.
     *
     * The row should be closed by calling 'tablerow_close()'.
     *
     * @author LarsDW223
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param null $baseURL
     * @param null $element
     */
    public static function tableRowOpenUseCSS(ODTDocument $doc, &$content, $attributes=NULL, cssimportnew $import=NULL){
        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'tr';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::tableRowOpenUseProperties($doc, $content, $properties);
    }

    /**
     * @param array $properties
     */
    public static function tableRowOpenUseProperties (ODTDocument $doc, &$content, $properties){
        // Create style.
        $style_obj = ODTTableRowStyle::createTableRowStyle ($properties);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open table row with our new style.
        self::tableRowOpen($doc, $content, $style_name);
    }

    /**
     * This function opens a new table cell using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableCellOpenUseProperties. See the function description for supported properties.
     *
     * The cell should be closed by calling 'tablecell_close()'.
     *
     * @author LarsDW223
     *
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param null $baseURL
     * @param null $element
     */
    public static function tableCellOpenUseCSS(ODTDocument $doc, &$content, $attributes=NULL, cssimportnew $import=NULL, $colspan = 1, $rowspan = 1){
        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'td';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::tableCellOpenUseProperties($doc, $content, $properties, $colspan, $rowspan);
    }

    /**
     * @param $properties
     */
    public static function tableCellOpenUseProperties (ODTDocument $doc, &$content, $properties = NULL, $colspan = 1, $rowspan = 1){
        self::tableCellOpenUsePropertiesInternal ($doc, $content, $properties, false, $colspan, $rowspan);
    }

    /**
     * @param $properties
     * @param bool $inHeader
     * @param int $colspan
     * @param int $rowspan
     */
    static protected function tableCellOpenUsePropertiesInternal (ODTDocument $doc, &$content, $properties, $inHeader = false, $colspan = 1, $rowspan = 1){
        $disabled = array ();

        // Create style name. (Re-enable background-color!)
        $style_obj = ODTTableCellStyle::createTableCellStyle ($properties);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Create a paragraph style for the paragraph within the cell.
        // Disable properties that belong to the table cell style.
        $disabled ['border'] = 1;
        $disabled ['border-left'] = 1;
        $disabled ['border-right'] = 1;
        $disabled ['border-top'] = 1;
        $disabled ['border-bottom'] = 1;
        $disabled ['background-color'] = 1;
        $disabled ['background-image'] = 1;
        $disabled ['vertical-align'] = 1;
        $style_obj = ODTParagraphStyle::createParagraphStyle ($properties, $disabled);
        $doc->addAutomaticStyle($style_obj);
        $style_name_paragraph = $style_obj->getProperty('style-name');
    
        // Open header or normal cell.
        if ($inHeader) {
            self::tableHeaderOpen($doc, $colspan, $rowspan, NULL, $content, $style_name, $style_name_paragraph);
        } else {
            self::tableCellOpen($doc, $colspan, $rowspan, NULL, $content, $style_name, $style_name_paragraph);
        }
    }

    /**
     * This function replaces the width of $table with the
     * value of all column width added together. If a column has
     * no width set then the function will abort and change nothing.
     * 
     * @param ODTDocument $doc The current document
     * @param ODTElementTable $table The table to be adjusted
     */
    static protected function replaceTableWidth (ODTDocument $doc, ODTElementTable $table) {
        if ($table == NULL) {
            // ??? Should not happen.
            return;
        }
        $matches = array ();

        $table_style_name = $table->getStyleName();
        if (empty($table_style_name)) {
            return;
        }

        // Search through all column styles for the column width ('style:width="..."').
        // If every column has a absolute width set, add them all and replace the table
        // width with the result.
        // Abort if a column has no width.
        $sum = 0;
        $table_column_styles = $table->getTableColumnStyles();
        for ($index = 0 ; $index < $table->getTableMaxColumns() ; $index++ ) {
            $style_name = $table_column_styles [$index];
            $style_obj = $doc->getStyle($style_name);
            if ($style_obj != NULL && $style_obj->getProperty('column-width') != NULL) {
                $width = $style_obj->getProperty('column-width');
                $length = strlen ($width);
                $width = $doc->toPoints($width, 'x');
                $sum += (float) trim ($width, 'pt');
            } else {
                return;
            }
        }

        $style_obj = $doc->getStyle($table_style_name);
        if ($style_obj != NULL) {
            $style_obj->setProperty('width', $sum.'pt');
        }
    }
}
