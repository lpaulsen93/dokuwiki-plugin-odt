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
    public static function tableOpen(ODTInternalParams $params, $maxcols = NULL, $numrows = NULL, $tableStyleName=NULL, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'table';
        }
        $elementObj = $params->elementObj;

        // Close any open paragraph.
        $params->document->paragraphClose();
        
        // Do additional actions if the parent element is a list.
        // In this case we need to finish the list and re-open it later
        // after the table has been closed! --> tables may not be part of a list item in ODT!

        $interrupted = false;
        if ($tableStyleName == NULL) {
            $tableStyleName = $params->document->getStyleName('table');
        }

        $list_item = $params->document->state->getCurrentListItem();
        if ($list_item != NULL) {
            // We are in a list item. Query indentation settings.
            $list = $list_item->getList();
            if ($list != NULL) {
                $list_style_name = $list->getStyleName();
                $list_style = $params->document->getStyle($list_style_name);
                if ($list_style != NULL) {
                    // The list level stored in the list item/from the parser
                    // might not be correct. Count 'list' states to get level.
                    $level = $params->document->state->countClass('list');

                    // Create a table style for indenting the table.
                    // We try to achieve this by substracting the list indentation
                    // from the width of the table and right align it!
                    // (if not done yet, the name must be unique!)
                    $count = $params->document->state->getElementCount('table')+1;
                    $style_name = 'Table'.$count.'_Indentation_Level'.$level;
                    if (!$params->document->styleExists($style_name)) {
                        $style_obj = clone $params->document->getStyle($tableStyleName);
                        $style_obj->setProperty('style-name', $style_name);
                        if ($style_obj != NULL) {
                            $max = $params->document->getAbsWidthMindMargins();
                            $indent = 0 + ODTUnits::getDigits($list_style->getPropertyFromLevel($level, 'margin-left'));
                            $style_obj->setProperty('margin-left', ($indent).'cm');
                            if ($style_obj->getProperty('width') == NULL && $style_obj->getProperty('rel-width')) {
                                $style_obj->setProperty('width', ($max-$indent).'cm');
                            }
                            $style_obj->setProperty('align', 'left');
                            $params->document->addAutomaticStyle($style_obj);
                        }
                    }
                    $tableStyleName = $style_name;
                }
            }

            // Close all open lists and remember their style (may be nested!)
            $lists = array();
            $first = true;
            $iterations = 0;
            $list = $params->document->state->getCurrentList();
            while ($list != NULL)
            {
                // Close list items
                if ($first == true) {
                    $first = false;
                    $params->document->listContentClose();
                }
                $params->document->listItemClose();
                
                // Now we are in the list state!
                // Get the lists style name before closing it.
                $lists [] = $list->getStyleName();
                // Reset saved last paragraph position to -1 to prevent change of the paragraph style
                $list->setListLastParagraphPosition(-1);
                $params->document->listClose();
                
                if ($params->document->state == NULL ||
                    $params->document->state->getCurrent()->getElementName() == 'root') {
                    break;
                }

                // List has been closed (and removed from stack). Get next.
                $list = $params->document->state->getCurrentList();

                // Just to prevent endless loops in case of an error!
                $iterations++;
                if ($iterations == 50) {
                    $params->content .= 'Error: ENDLESS LOOP!';
                    break;
                }
            }

            $interrupted = true;
        }

        if ($elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        $table = new ODTElementTable($tableStyleName, $maxcols, $numrows);
        $params->document->state->enter($table);
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
        $table->setHTMLElement ($element);
        
        $params->content .= $table->getOpeningTag();
    }

    /**
     * Close/finish a table
     */
    public static function tableClose(ODTInternalParams $params){
        $table = $params->document->state->getCurrentTable();
        if ($table == NULL) {
            // ??? Error. Not table found.
            return;
        }

        if ($params->document->state->getInTableRow()) {
            // If we are still inside a table row then close it first,
            // to prevent an error or broken document.
            $params->document->tableRowClose();
        }

        $interrupted = $table->getListInterrupted();
        $lists = NULL;
        if ($interrupted) {
            $lists = $table->getTemp();
        }

        // Eventually adjust table width.
        $table->adjustWidth ($params);

        // Close the table.
        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->content .= $table->getClosingTag($params->content);
        $params->document->state->leave();

        // Do additional actions required if we interrupted a list,
        // see table_open()
        if ($interrupted) {
            // Re-open list(s) with original style!
            // (in revers order of lists array)
            $max = count($lists);
            for ($index = $max ; $index > 0 ; $index--) {
                $params->document->listOpen(true, $lists [$index-1]);
                
                // If this is not the most inner list then we need to open
                // a list item too!
                if ($index > 0) {
                    $params->document->listItemOpen($max-$index);
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
    public static function tableAddColumn (ODTInternalParams $params, $styleNameSet=NULL, &$styleNameGet=NULL){
        // Create new column
        $column = new ODTElementTableColumn();
        $params->document->state->enter($column);

        if ($styleNameSet != NULL) {
            // Change automatically assigned style name.
            $column->setStyleName($styleNameSet);
        }

        // Return style name to caller.
        $styleNameGet = $column->getStyleName();

        // Never create any new document content here!!!
        // Columns have already been added on table open or are
        // re-written on table close.
        $params->document->state->leave();
    }

    /**
     * Open a table row
     */
    public static function tableRowOpen(ODTInternalParams $params, $styleName=NULL, $element=NULL, $attributes=NULL){
        if ($params->document->state->getInTableRow()) {
            // If we are still inside a table row then close it first,
            // to prevent an error or broken document.
            $params->document->tableRowClose();
        }

        if ($element == NULL) {
            $element = 'tr';
        }

        if ($params->elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        $row = new ODTElementTableRow($styleName);
        $params->document->state->enter($row);
        $params->content .= $row->getOpeningTag();
        $row->setHTMLElement ($element);
    }

    /**
     * Close a table row
     */
    public static function tableRowClose(ODTInternalParams $params){
        if ($params->document->state->getInTableCell()) {
            // If we are still inside a table cell then close it first,
            // to prevent an error or broken document.
            $params->document->tableCellClose();
        }

        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();
    }

    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param int    $rowspan
     * @param string $align left|center|right
     */
    public static function tableHeaderOpen(ODTInternalParams $params, $colspan = 1, $rowspan = 1, $align = "left", $cellStyle=NULL, $paragraphStyle=NULL, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'th';
        }
        // Are style names given? If not, use defaults.
        if (empty($cellStyle)) {
            $cellStyle = $params->document->getStyleName('table header');
        }
        if (empty($paragraphStyle)) {
            $paragraphStyle = $params->document->getStyleName('table heading');
        }

        if ($params->elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        // ODT has no element for the table header.
        // We mark the state with a differnt class to be able
        // to differ between a normal cell and a header cell.
        $header_cell = new ODTElementTableHeaderCell
            ($cellStyle, $colspan, $rowspan);
        $params->document->state->enter($header_cell);
        $header_cell->setHTMLElement ($element);

        // Encode table (header) cell.
        $params->content .= $header_cell->getOpeningTag();

        // Open new paragraph with table heading style.
        $params->document->paragraphOpen($paragraphStyle);
    }

    /**
     * Close a table header cell
     */
    public static function tableHeaderClose(ODTInternalParams $params){
        $params->document->paragraphClose();

        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param int    $rowspan
     * @param string $align left|center|right
     */
    public static function tableCellOpen(ODTInternalParams $params, $colspan = 1, $rowspan = 1, $align = "left", $cellStyle=NULL, $paragraphStyle=NULL, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'td';
        }

        if ($params->document->state->getInTableCell()) {
            // If we are still inside a table cell then close it first,
            // to prevent an error or broken document.
            $params->document->tableCellClose();
        }

        // Are style names given? If not, use defaults.
        if (empty($cellStyle)) {
            $cellStyle = $params->document->getStyleName('table cell');
        }
        if (empty($paragraphStyle)) {
            // Open paragraph with required alignment.
            if (!$align) $align = "left";
            $paragraphStyle = $params->document->getStyleName('tablealign '.$align);
        }

        if ($params->elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        $cell = new ODTElementTableCell
            ($cellStyle, $colspan, $rowspan);
        $params->document->state->enter($cell);
        $cell->setHTMLElement ($element);

        // Encode table cell.
        $params->content .= $cell->getOpeningTag();

        // Open paragraph.
        $params->document->paragraphOpen($paragraphStyle);
    }

    /**
     * Close a table cell
     */
    public static function tableCellClose(ODTInternalParams $params){
        $params->document->paragraphClose();

        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();
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
    public static function tableOpenUseCSS(ODTInternalParams $params, $maxcols=NULL, $numrows=NULL, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'table';
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::tableOpenUseProperties($params, $properties, $maxcols, $numrows);
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
    public static function tableOpenUseProperties (ODTInternalParams $params, $properties, $maxcols = 0, $numrows = 0){
        $elementObj = $params->elementObj;

        // Eventually adjust table width.
        if ( !empty ($properties ['width']) ) {
            if ( $properties ['width'] [strlen($properties ['width'])-1] != '%' ) {
                // Width has got an absolute value.
                // Some units are not supported by ODT for table width (e.g. 'px').
                // So we better convert it to points.
                $properties ['width'] = $params->document->toPoints($properties ['width'], 'x');
            }
        }
        
        // Create style.
        // FIXME: fix disabled_props, ask state for current max width...
        $style_obj = ODTTableStyle::createTableTableStyle($properties, NULL, 17);
        $params->document->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open the table referencing our style.
        $params->elementObj = $elementObj;
        self::tableOpen($params, $maxcols, $numrows, $style_name);
    }

    /**
     * @param array $properties
     */
    public static function tableAddColumnUseProperties (ODTInternalParams $params, array $properties = NULL){
        // Add column and set/query assigned style name
        $styleName = $properties ['style-name'];
        $styleNameGet = '';
        self::tableAddColumn ($params, $styleName, $styleNameGet);

        // Overwrite/Create column style for actual column
        $properties ['style-name'] = $styleNameGet;
        $style_obj = ODTTableColumnStyle::createTableColumnStyle ($properties);
        $params->document->addAutomaticStyle($style_obj);
    }

    /**
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param null $baseURL
     * @param null $element
     * @param int $colspan
     * @param int $rowspan
     */
    public static function tableHeaderOpenUseCSS(ODTInternalParams $params, $colspan = 1, $rowspan = 1, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'th';
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::tableHeaderOpenUseProperties($params, $properties, $colspan, $rowspan);
    }

    /**
     * @param null $properties
     * @param int $colspan
     * @param int $rowspan
     */
    public static function tableHeaderOpenUseProperties (ODTInternalParams $params, $properties = NULL, $colspan = 1, $rowspan = 1){
        // Open cell, second parameter MUST BE true to indicate we are in the header.
        self::tableCellOpenUsePropertiesInternal ($params, $properties, true, $colspan, $rowspan);
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
    public static function tableRowOpenUseCSS(ODTInternalParams $params, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'tr';
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::tableRowOpenUseProperties($params, $properties);
    }

    /**
     * @param array $properties
     */
    public static function tableRowOpenUseProperties (ODTInternalParams $params, $properties){
        // Create style.
        $style_obj = ODTTableRowStyle::createTableRowStyle ($properties);
        $params->document->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open table row with our new style.
        self::tableRowOpen($params, $style_name);
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
    public static function tableCellOpenUseCSS(ODTInternalParams $params, $element=NULL, $attributes=NULL, $colspan = 1, $rowspan = 1){
        if ($element == NULL) {
            $element = 'td';
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::tableCellOpenUseProperties($params, $properties, $colspan, $rowspan);
    }

    /**
     * @param $properties
     */
    public static function tableCellOpenUseProperties (ODTInternalParams $params, $properties = NULL, $colspan = 1, $rowspan = 1){
        self::tableCellOpenUsePropertiesInternal ($params, $properties, false, $colspan, $rowspan);
    }

    /**
     * @param $properties
     * @param bool $inHeader
     * @param int $colspan
     * @param int $rowspan
     */
    static protected function tableCellOpenUsePropertiesInternal (ODTInternalParams $params, $properties, $inHeader = false, $colspan = 1, $rowspan = 1){
        $disabled = array ();

        // Create style name. (Re-enable background-color!)
        $style_obj = ODTTableCellStyle::createTableCellStyle ($properties);
        $params->document->addAutomaticStyle($style_obj);
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
        $params->document->addAutomaticStyle($style_obj);
        $style_name_paragraph = $style_obj->getProperty('style-name');
    
        // Open header or normal cell.
        if ($inHeader) {
            self::tableHeaderOpen($params, $colspan, $rowspan, NULL, $style_name, $style_name_paragraph);
        } else {
            self::tableCellOpen($params, $colspan, $rowspan, NULL, $style_name, $style_name_paragraph);
        }

        // There might be properties in the table header cell/normal cell which in ODT belong to the
        // column, e.g. 'width'. So eventually adjust column style.
        self::adjustColumnStyle($params, $properties);
    }

    static protected function adjustColumnStyle(ODTInternalParams $params, array $properties) {
        $table = $params->document->state->getCurrentTable();
        if ($table == NULL) {
            // ??? Error. Not table found.
            return;
        }
        $curr_column = $table->getTableCurrentColumn();
        $table_column_styles = $table->getTableColumnStyles();
        $style_name = $table_column_styles [$curr_column-1];
        $style_obj = $params->document->getStyle($style_name);

        if ($style_obj != NULL) {
            if (!empty($properties ['width'])) {
                $width = $properties ['width'];
                $length = strlen ($width);
                $width = $params->document->toPoints($width, 'x');
                $style_obj->setProperty('column-width', $width);
            }
        } else {
            self::tableAddColumnUseProperties ($params, $properties);
        }
    }
}
