<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

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
    public static function tableOpen(ODTDocument $doc, $maxcols = NULL, $numrows = NULL, &$content){
        // Close any open paragraph.
        $doc->paragraphClose($content);
        
        // Do additional actions if the parent element is a list.
        // In this case we need to finish the list and re-open it later
        // after the table has been closed! --> tables may not be part of a list item in ODT!

        $interrupted = false;
        $table_style_name = $doc->getStyleName('table');

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
                        $style_obj = clone $doc->getStyle($table_style_name);
                        $style_obj->setProperty('style-name', $style_name);
                        if ($style_obj != NULL) {
                            $max = $doc->page->getAbsWidthMindMargins();
                            $indent = 0 + ODTUnits::getDigits($list_style->getPropertyFromLevel($level, 'margin-left'));
                            $style_obj->setProperty('width', ($max-$indent).'cm');
                            $style_obj->setProperty('align', 'right');
                            $doc->addAutomaticStyle($style_obj);
                        }
                    }
                    $table_style_name = $style_name;
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

        $table = new ODTElementTable($table_style_name, $maxcols, $numrows);
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
     * Open a table row
     */
    public static function tableRowOpen(ODTDocument $doc, &$content){
        $row = new ODTElementTableRow();
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
    public static function tableHeaderOpen(ODTDocument $doc, $colspan = 1, $rowspan = 1, $align = "left", &$content){
        // ODT has no element for the table header.
        // We mark the state with a differnt class to be able
        // to differ between a normal cell and a header cell.
        $header_cell = new ODTElementTableHeaderCell
            ($doc->getStyleName('table header'), $colspan, $rowspan);
        $doc->state->enter($header_cell);

        // Encode table (header) cell.
        $content .= $header_cell->getOpeningTag();

        // Open new paragraph with table heading style.
        $doc->paragraphOpen($doc->getStyleName('table heading'), $content);
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
    public static function tableCellOpen(ODTDocument $doc, $colspan = 1, $rowspan = 1, $align = "left", &$content){
        $cell = new ODTElementTableCell
            ($doc->getStyleName('table cell'), $colspan, $rowspan);
        $doc->state->enter($cell);

        // Encode table cell.
        $content .= $cell->getOpeningTag();

        // Open paragraph with required alignment.
        if (!$align) $align = "left";
        $style = $doc->getStyleName('tablealign '.$align);
        $doc->paragraphOpen($style, $content);
    }

    /**
     * Close a table cell
     */
    public static function tableCellClose(ODTDocument $doc, &$content){
        $doc->paragraphClose($content);
        $doc->closeCurrentElement($content);
    }
}
