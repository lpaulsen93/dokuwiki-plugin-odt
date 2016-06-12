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
    static public function paragraphOpen(ODTDocument $doc, $styleName=NULL, &$content, $element=NULL, $attributes=NULL){
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
            if ( $doc->pageFormatChangeIsPending() ) {
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

    /**
     * This function opens a new paragraph using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'p' and the specified classes in $classes.
     * The property 'background-image' is emulated by inserting an image manually in the paragraph.
     * If the url from the CSS should be converted to a local path, then the caller can specify a $baseURL.
     * The full path will then be $baseURL/background-image.
     *
     * This function calls _odtParagraphOpenUseProperties. See the function description for supported properties.
     *
     * The span should be closed by calling '_odtParagraphClose'.
     *
     * @author LarsDW223
     *
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param $baseURL
     * @param $element
     */
    function paragraphOpenUseCSS(ODTDocument $doc, &$content, $element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'p';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::paragraphOpenUseProperties($doc, $content, $properties);
    }

    /**
     * This function opens a new paragraph using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * The property 'background-image' is emulated by inserting an image manually in the paragraph.
     *
     * The currently supported properties are:
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, line-height, background-image (emulated)
     *
     * The paragraph must be closed by calling 'p_close'.
     *
     * @author LarsDW223
     *
     * @param array $properties
     */
    public static function paragraphOpenUseProperties(ODTDocument $doc, &$content, $properties){
        $disabled = array ();

        $in_paragraph = $doc->state->getInParagraph();
        if ($in_paragraph) {
            // opening a paragraph inside another paragraph is illegal
            return;
        }

        $odt_bg = $properties ['background-color'];
        $picture = $properties ['background-image'];

        if ( !empty ($picture) ) {
            // If a picture/background-image is set, than we insert it manually here.
            // This is a workaround because ODT background-image works different than in CSS.

            // Define graphic style for picture
            $style_name = ODTStyle::getNewStylename('span_graphic');
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="'.$doc->getStyleName('graphics').'"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            // (as unknown style because style-family graphic is not supported)
            $style_obj = ODTUnknownStyle::importODTStyle($image_style);
            $this->document->addAutomaticStyle($style_obj);
            ODTImage::addImage ($doc, $content, $picture, NULL, NULL, NULL, NULL, $style_name);
        }

        // Create the style for the paragraph.
        //$disabled ['background-image'] = 1;
        //FIXME: pass $disabled
        $style_obj = ODTParagraphStyle::createParagraphStyle ($properties);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open a paragraph
        $doc->paragraphOpen($style_name, $content);
    }
}
