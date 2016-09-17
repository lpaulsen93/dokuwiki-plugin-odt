<?php
/**
 * ODT Paragraph handling.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 * @package    ODT\Paragraph
 */

/** Include ODTDocument */
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
     * @param     ODTInternalParams $params     Commom params.
     * @param     string|null       $styleName  The style to use.
     * @param     string            $element    The element name, e.g. "div"
     * @param     string            $attributes The attributes belonging o the element, e.g. 'class="example"'
     */
    static public function paragraphOpen(ODTInternalParams $params, $styleName=NULL, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'p';
        }
        if ( empty($styleName) ) {
            $styleName = $params->document->getStyleName('body');
        }

        $list = NULL;
        $listItem = $params->document->state->getCurrentListItem();
        if ($listItem != NULL) {
            // We are in a list item. Is this the list start?
            $list = $listItem->getList();
            if ($list != NULL) {
                // Get list count and Flag if this is the first paragraph in the list
                $listCount = $params->document->state->countClass('list');
                $isFirst = $list->getListFirstParagraph();
                $list->setListFirstParagraph(false);

                // Create a style for putting a top margin for this first paragraph of the list
                // (if not done yet, the name must be unique!)
                if ($listCount == 1 && $isFirst) {
                    
                    // Has the style already been created...
                    $styleNameFirst = 'FirstListParagraph_'.$styleName;
                    if (!$params->document->styleExists($styleNameFirst)) {

                        // ...no, create style as copy of style 'list first paragraph'
                        $styleFirstTemplate = $params->document->getStyleByAlias('list first paragraph');
                        if ($styleFirstTemplate != NULL) {
                            $styleBody = $params->document->getStyle($styleName);
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
                                $params->document->addStyle($styleObj);
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
        $inParagraph = $params->document->state->getInParagraph();
        if (!$inParagraph) {
            if ( $params->document->pageFormatChangeIsPending() ) {
                $pageStyle = $params->document->doPageFormatChange($styleName);
                if ( $pageStyle != NULL ) {
                    $styleName = $pageStyle;
                    // Delete pagebreak, the format change will also introduce a pagebreak.
                    $params->document->setPagebreakPending(false);
                }
            }
            if ( $params->document->pagebreakIsPending() ) {
                $styleName = $params->document->createPagebreakStyle ($styleName);
                $params->document->setPagebreakPending(false);
            }
            
            // If we are in a list remember paragraph position
            if ($list != NULL) {
                $list->setListLastParagraphPosition(strlen($params->content));
            }

            if ($params->elementObj == NULL) {
                $properties = array();
                ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
            }

            $paragraph = new ODTElementParagraph($styleName);
            $params->document->state->enter($paragraph);
            $params->content .= $paragraph->getOpeningTag();
            $paragraph->setHTMLElement ($element);
        }
    }

    /**
     * Close a paragraph.
     * 
     * @param     ODTInternalParams $params     Commom params.
     */
    static public function paragraphClose(ODTInternalParams $params){
        $paragraph = $params->document->state->getCurrentParagraph();
        if ($paragraph != NULL) {
            ODTUtility::closeHTMLElement ($params, $paragraph->getHTMLElement());
            $params->content .= $paragraph->getClosingTag();
            $params->document->state->leave();
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
     * @author    LarsDW223
     * @param     ODTInternalParams $params     Commom params.
     * @param     string            $element    The element name, e.g. "div"
     * @param     string            $attributes The attributes belonging o the element, e.g. 'class="example"'
     */
    function paragraphOpenUseCSS(ODTInternalParams $params, $element=NULL, $attributes=NULL){
        $inParagraph = $params->document->state->getInParagraph();
        if ($inParagraph) {
            return;
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::paragraphOpenUseProperties($params, $properties);
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
     * @param     ODTInternalParams $params     Commom params.
     * @param     array             $properties Properties to use.
     */
    public static function paragraphOpenUseProperties(ODTInternalParams $params, $properties){
        $inParagraph = $params->document->state->getInParagraph();
        if ($inParagraph) {
            return;
        }
        $disabled = array ();

        $in_paragraph = $params->document->state->getInParagraph();
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
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="'.$params->document->getStyleName('graphics').'"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            // (as unknown style because style-family graphic is not supported)
            $style_obj = ODTUnknownStyle::importODTStyle($image_style);
            $params->document->addAutomaticStyle($style_obj);
            ODTImage::addImage ($params, $picture, NULL, NULL, NULL, NULL, $style_name);
        }

        // Create the style for the paragraph.
        //$disabled ['background-image'] = 1;
        //FIXME: pass $disabled
        $style_obj = ODTParagraphStyle::createParagraphStyle ($properties);
        $params->document->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open a paragraph
        self::paragraphOpen($params, $style_name);
    }
}
