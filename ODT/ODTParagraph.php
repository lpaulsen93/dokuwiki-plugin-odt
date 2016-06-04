<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTParagraph:
 * Class containing static code for handling paragraphs.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTParagraph
{
    /**
     * Open a paragraph
     *
     * @param string $styleName The style to use.
     */
    static public function paragraphOpen(ODTDocument $doc, $styleName=NULL, &$content){
        if ( empty($styleName) ) {
            $styleName = $doc->getStyleName('body');
        }

        $list = NULL;
        $listItem = $doc->state->getCurrentListItem();
        if ($listItem != NULL) {
            // We are in a list item. Is this the list start?
            $list = $listItem->getList();
            if ($list != NULL) {
                // Get list count and Flag if this is the first paragraph in the list
                $listCount = $doc->state->countClass('list');
                $isFirst = $list->getListFirstParagraph();
                $list->setListFirstParagraph(false);

                // Create a style for putting a top margin for this first paragraph of the list
                // (if not done yet, the name must be unique!)
                if ($listCount == 1 && $isFirst) {
                    
                    // Has the style already been created...
                    $styleNameFirst = 'FirstListParagraph_'.$styleName;
                    if (!$doc->styleExists($styleNameFirst)) {

                        // ...no, create style as copy of style 'list first paragraph'
                        $styleFirstTemplate = $doc->getStyleByAlias('list first paragraph');
                        if ($styleFirstTemplate != NULL) {
                            $styleBody = $doc->getStyle($styleName);
                            $styleDisplayName = 'First '.$styleBody->getProperty('style-display-name');
                            $styleObj = clone $styleFirstTemplate;
                            if ($styleObj != NULL) {
                                $styleObj->setProperty('style-name', $styleNameFirst);
                                $styleObj->setProperty('style-parent', $styleName);
                                $styleObj->setProperty('style-display-name', $styleDisplayName);
                                $bottom = $styleFirstTemplate->getProperty('margin-bottom');
                                if ($bottom === NULL) {
                                    $styleObj->setProperty('margin-bottom', $styleBody->getProperty('margin-bottom'));
                                }
                                $doc->addStyle($styleObj);
                                $styleName = $styleNameFirst;
                            }
                        }
                    } else {
                        // ...yes, just use the name
                        $styleName = $styleNameFirst;
                    }
                }
            }
        }
        
        // Opening a paragraph inside another paragraph is illegal
        $inParagraph = $doc->state->getInParagraph();
        if (!$inParagraph) {
            if ( $doc->changePageFormat != NULL ) {
                $pageStyle = $doc->doPageFormatChange($styleName);
                if ( $pageStyle != NULL ) {
                    $styleName = $pageStyle;
                    // Delete pagebreak, the format change will also introduce a pagebreak.
                    $doc->setPagebreakPending(false);
                }
            }
            if ( $doc->pagebreakIsPending() ) {
                $styleName = $doc->createPagebreakStyle ($styleName);
                $doc->setPagebreakPending(false);
            }
            
            // If we are in a list remember paragraph position
            if ($list != NULL) {
                $list->setListLastParagraphPosition(strlen($content));
            }

            $paragraph = new ODTElementParagraph($styleName);
            $doc->state->enter($paragraph);
            $content .= $paragraph->getOpeningTag();
        }
    }

    /**
     * Close a paragraph
     */
    static public function paragraphClose(ODTDocument $doc, &$content){
        $paragraph = $doc->state->getCurrentParagraph();
        if ($paragraph != NULL) {
            $content .= $paragraph->getClosingTag();
            $doc->state->leave();
        }
    }
}
