<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTParagraph:
 * Class containing static code for handling spans.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTSpan
{
    /**
     * Open a text span.
     *
     * @param string $styleName The style to use.
     */
    public static function spanOpen(ODTDocument $doc, &$content, $styleName){
        $span = new ODTElementSpan ($styleName);
        $doc->state->enter($span);
        $content .= $span->getOpeningTag();
    }

    /**
     * This function opens a new span using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'span' and the specified classes in $classes.
     * The property 'background-image' is not supported by an ODT span. This will be emulated
     * by inserting an image manually in the span. If the url from the CSS should be converted to
     * a local path, then the caller can specify a $baseURL. The full path will then be $baseURL/background-image.
     *
     * This function calls _odtSpanOpenUseProperties. See the function description for supported properties.
     *
     * The span should be closed by calling '_odtSpanClose'.
     *
     * @author LarsDW223
     *
     * @param helper_plugin_odt_cssimport $import
     * @param $classes
     * @param $baseURL
     * @param $element
     */
    public static function spanOpenUseCSS(ODTDocument $doc, &$content, $attributes=NULL, cssimportnew $import=NULL){
        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'span';
        //}
        //$this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        self::spanOpenUseProperties($doc, $content, $properties);
    }

    /**
     * This function opens a new span using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * The property 'background-image' is not supported by an ODT span. This will be emulated
     * by inserting an image manually in the span.
     *
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, background-image (emulated)
     *
     * The span should be closed by calling '_odtSpanClose'.
     *
     * @author LarsDW223
     *
     * @param array $properties
     */
    public static function spanOpenUseProperties(ODTDocument $doc, &$content, $properties){
        $disabled = array ();

        $odt_bg = $properties ['background-color'];
        $picture = $properties ['background-image'];

        if ( !empty ($picture) ) {
            // If a picture/background-image is set, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.

            // Define graphic style for picture
            $style_name = ODTStyle::getNewStylename('span_graphic');
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="'.$doc->getStyleName('graphics').'"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            // (as unknown style because style-family graphic is not supported)
            $style_obj = ODTUnknownStyle::importODTStyle($image_style);
            $doc->addAutomaticStyle($style_obj);
            ODTImage::addImage ($doc, $content, $picture,NULL,NULL,NULL,NULL,$style_name);
        }

        // Create a text style for our span
        $disabled ['background-image'] = 1;
        $style_obj = ODTTextStyle::createTextStyle ($properties, $disabled);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open span
        self::spanOpen($doc, $content, $style_name);
    }

    /**
     * Close a text span.
     *
     * @param string $style_name The style to use.
     */    
    public static function spanClose(ODTDocument $doc, &$content) {
        $doc->closeCurrentElement($content);
    }
}
