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
    static public function listOpen(ODTInternalParams $params, $continue=false, $styleName, $element=NULL, $attributes=NULL) {
        $params->document->paragraphClose();

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);

        $list = new ODTElementList($styleName, $continue);
        $params->document->state->enter($list);
        $list->setHTMLElement ($element);

        $params->content .= $list->getOpeningTag();
    }

    /**
     * Close a list
     */
    static public function listClose(ODTInternalParams $params) {
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }

        if ($params->document->state->getInListItem()) {
            // If we are still inside a list item then close it first,
            // to prevent an error or broken document.
            $params->document->listItemClose();
        }

        // Eventually modify last list paragraph first
        self::replaceLastListParagraph($params);

        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $list = $params->document->state->getCurrent();
        $params->content .= $list->getClosingTag();

        $position = $list->getListLastParagraphPosition();
        $params->document->state->leave();
        
        // If we are still in a list save the last paragraph position
        // in the current list (needed for nested lists!).
        $list = $params->document->state->getCurrentList();
        if ($list != NULL) {
            $list->setListLastParagraphPosition($position);
        }
    }

    /**
     * Open a list item
     *
     * @param int $level The nesting level
     */
    static public function listItemOpen(ODTInternalParams $params, $level, $element=NULL, $attributes=NULL) {
        if ($params->document->state == NULL ) {
            // ??? Can't be...
            return;
        }
        if ($element == NULL) {
            $element = 'li';
        }

        // Set marker that list interruption has stopped!!!
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL) {
            $table->setListInterrupted(false);
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);

        // Attention:
        // we save the list level here but it might be wrong.
        // Someone can start a list with level 2 without having created
        // a list with level 1 before.
        // When the correct list level is needed better use
        // $params->document->state->countClass('list'), see table_open().
        $list_item = new ODTElementListItem($level);
        $params->document->state->enter($list_item);
        $list_item->setHTMLElement ($element);

        $params->content .= $list_item->getOpeningTag();
    }

    /**
     * Close a list item
     */
    static public function listItemClose(ODTInternalParams $params) {
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }

        if ($params->document->state->getInListContent()) {
            // If we are still inside list content then close it first,
            // to prevent an error or broken document.
            $params->document->listContentClose();
        }

        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();
    }

    /**
     * Open a list header
     *
     * @param int $level The nesting level
     */
    static public function listHeaderOpen(ODTInternalParams $params, $level, $element=NULL, $attributes=NULL) {
        if ($params->document->state == NULL ) {
            // ??? Can't be...
            return;
        }
        if ($element == NULL) {
            $element = 'li';
        }

        // Set marker that list interruption has stopped!!!
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL) {
            $table->setListInterrupted(false);
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);

        // Attention:
        // we save the list level here but it might be wrong.
        // Someone can start a list with level 2 without having created
        // a list with level 1 before.
        // When the correct list level is needed better use
        // $params->document->state->countClass('list'), see table_open().
        $list_header = new ODTElementListHeader($level);
        $params->document->state->enter($list_header);
        $list_header->setHTMLElement ($element);

        $params->content .= $list_header->getOpeningTag();
    }

    /**
     * Close a list header
     */
    static public function listHeaderClose(ODTInternalParams $params) {
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();
    }

    /**
     * Open list content/a paragraph in a list item
     */
    static public function listContentOpen(ODTInternalParams $params, $element=NULL, $attributes=NULL) {
        // The default style for list content is body but it should always be
        // overwritten. It's just assigned here to guarantee some style name is
        // always set in case of an error also.
        $styleName = $params->document->getStyleName('body');
        $list = $params->document->state->getCurrentList();
        if ($list != NULL) {
            $listStyleName = $list->getStyleName();
            if ($listStyleName == $params->document->getStyleName('list')) {
                $styleName = $params->document->getStyleName('list content');
            }
            if ($listStyleName == $params->document->getStyleName('numbering')) {
                $styleName = $params->document->getStyleName('numbering content');
            }
        }

        $params->document->paragraphOpen($styleName);
    }

    /**
     * Close list content/a paragraph in a list item
     */
    static public function listContentClose(ODTInternalParams $params) {
        $table = $params->document->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        $params->document->paragraphClose();
    }

    /**
     * The function replaces the last paragraph of a list
     * with a style having the properties of 'List_Last_Paragraph'.
     *
     * The function does NOT change the last paragraph of nested lists.
     */
    static protected function replaceLastListParagraph(ODTInternalParams $params) {
        $list = $params->document->state->getCurrentList();
        if ($list != NULL) {
            // We are in a list.
            $list_count = $params->document->state->countClass('list');
            $position = $list->getListLastParagraphPosition();

            if ($list_count != 1 || $position == -1) {
                // Do nothing if this is a nested list or the position was not saved
                return;
            }

            $last_p_style = NULL;
            if (preg_match('/<text:p text:style-name="[^"]*">/', $params->content, $matches, 0, $position) === 1) {
                $last_p_style = substr($matches [0], strlen('<text:p text:style-name='));
                $last_p_style = trim($last_p_style, '">');
            } else {
                // Nothing found???
                return;
            }

            // Create a style for putting a bottom margin for this last paragraph of the list
            // (if not done yet, the name must be unique!)
            $style_name = 'LastListParagraph_'.$last_p_style;
            $style_last = $params->document->getStyleByAlias('list last paragraph');
            if (!$params->document->styleExists($style_name)) {
                if ($style_last != NULL) {
                    $style_body = $params->document->getStyle($last_p_style);
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
                        $params->document->addStyle($style_obj);
                    }
                }
            }
            
            // Finally replace style name of last paragraph.
            $params->content = substr_replace ($params->content, 
                '<text:p text:style-name="'.$style_name.'">',
                $position, strlen($matches[0]));
        }
    }
}
