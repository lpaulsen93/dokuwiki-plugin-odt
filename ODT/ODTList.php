<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTList:
 * Class containing static code for handling lists.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
class ODTList
{
    /**
     * Opens a list.
     * The list style specifies if the list is an ordered or unordered list.
     * 
     * @param bool $continue Continue numbering?
     * @param string $styleName Name of style to use for the list
     */
    static public function listOpen(ODTDocument $doc, $continue=false, $styleName, &$content, $element=NULL, $attributes=NULL) {
        $doc->paragraphClose($content);

        $list = new ODTElementList($styleName, $continue);
        $doc->state->enter($list);

        $content .= $list->getOpeningTag();
    }

    /**
     * Close a list
     */
    static public function listClose(ODTDocument $doc, &$content) {
        $table = $doc->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }

        // Eventually modify last list paragraph first
        self::replaceLastListParagraph($doc, $content);

        $list = $doc->state->getCurrent();
        $content .= $list->getClosingTag();

        $position = $list->getListLastParagraphPosition();
        $doc->state->leave();
        
        // If we are still in a list save the last paragraph position
        // in the current list (needed for nested lists!).
        $list = $doc->state->getCurrentList();
        if ($list != NULL) {
            $list->setListLastParagraphPosition($position);
        }
    }

    /**
     * Open a list item
     *
     * @param int $level The nesting level
     */
    static public function listItemOpen(ODTDocument $doc, $level, &$content, $element=NULL, $attributes=NULL) {
        if ($doc->state == NULL ) {
            // ??? Can't be...
            return;
        }

        // Set marker that list interruption has stopped!!!
        $table = $doc->state->getCurrentTable();
        if ($table != NULL) {
            $table->setListInterrupted(false);
        }

        // Attention:
        // we save the list level here but it might be wrong.
        // Someone can start a list with level 2 without having created
        // a list with level 1 before.
        // When the correct list level is needed better use
        // $doc->state->countClass('list'), see table_open().
        $list_item = new ODTElementListItem($level);
        $doc->state->enter($list_item);

        $content .= $list_item->getOpeningTag();
    }

    /**
     * Close a list item
     */
    static public function listItemClose(ODTDocument $doc, &$content) {
        $table = $doc->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        $doc->closeCurrentElement($content);
    }

    /**
     * Open list content/a paragraph in a list item
     */
    static public function listContentOpen(ODTDocument $doc, &$content, $element=NULL, $attributes=NULL) {
        // The default style for list content is body but it should always be
        // overwritten. It's just assigned here to guarantee some style name is
        // always set in case of an error also.
        $styleName = $doc->getStyleName('body');
        $list = $doc->state->getCurrentList();
        if ($list != NULL) {
            $listStyleName = $list->getStyleName();
            if ($listStyleName == $doc->getStyleName('list')) {
                $styleName = $doc->getStyleName('list content');
            }
            if ($listStyleName == $doc->getStyleName('numbering')) {
                $styleName = $doc->getStyleName('numbering content');
            }
        }

        $doc->paragraphOpen($styleName, $content);
    }

    /**
     * Close list content/a paragraph in a list item
     */
    static public function listContentClose(ODTDocument $doc, &$content) {
        $table = $doc->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        $doc->paragraphClose($content);
    }

    /**
     * The function replaces the last paragraph of a list
     * with a style having the properties of 'List_Last_Paragraph'.
     *
     * The function does NOT change the last paragraph of nested lists.
     */
    static protected function replaceLastListParagraph(ODTDocument $doc, &$content) {
        $list = $doc->state->getCurrentList();
        if ($list != NULL) {
            // We are in a list.
            $list_count = $doc->state->countClass('list');
            $position = $list->getListLastParagraphPosition();

            if ($list_count != 1 || $position == -1) {
                // Do nothing if this is a nested list or the position was not saved
                return;
            }

            $last_p_style = NULL;
            if (preg_match('/<text:p text:style-name="[^"]*">/', $content, $matches, 0, $position) === 1) {
                $last_p_style = substr($matches [0], strlen('<text:p text:style-name='));
                $last_p_style = trim($last_p_style, '">');
            } else {
                // Nothing found???
                return;
            }

            // Create a style for putting a bottom margin for this last paragraph of the list
            // (if not done yet, the name must be unique!)
            $style_name = 'LastListParagraph_'.$last_p_style;
            $style_last = $doc->getStyleByAlias('list last paragraph');
            if (!$doc->styleExists($style_name)) {
                if ($style_last != NULL) {
                    $style_body = $doc->getStyle($last_p_style);
                    $style_display_name = 'Last '.$style_body->getProperty('style-display-name');
                    $style_obj = clone $style_last;
                    if ($style_obj != NULL) {
                        $style_obj->setProperty('style-name', $style_name);
                        $style_obj->setProperty('style-parent', $last_p_style);
                        $style_obj->setProperty('style-display-name', $style_display_name);
                        $top = $style_last->getProperty('margin-top');
                        if ($top === NULL) {
                            $style_obj->setProperty('margin-top', $style_body->getProperty('margin-top'));
                        }
                        $doc->addStyle($style_obj);
                    }
                }
            }
            
            // Finally replace style name of last paragraph.
            $content = substr_replace ($content, 
                '<text:p text:style-name="'.$style_name.'">',
                $position, strlen($matches[0]));
        }
    }
}
