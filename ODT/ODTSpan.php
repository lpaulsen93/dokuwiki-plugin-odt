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
    public static function spanOpen(ODTInternalParams $params, $styleName, $element=NULL, $attributes=NULL){
        if ($element == NULL) {
            $element = 'span';
        }
        if ($params->elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        $span = new ODTElementSpan ($styleName);
        $params->document->state->enter($span);
        $params->content .= $span->getOpeningTag();
        $span->setHTMLElement ($element);
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
    public static function spanOpenUseCSS(ODTInternalParams $params, $element=NULL, $attributes=NULL){
        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::spanOpenUseProperties($params, $properties);
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
    public static function spanOpenUseProperties(ODTInternalParams $params, $properties){
        $disabled = array ();

        $odt_bg = $properties ['background-color'];
        $picture = $properties ['background-image'];

        if ( !empty ($picture) ) {
            // If a picture/background-image is set, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.

            // Define graphic style for picture
            $style_name = ODTStyle::getNewStylename('span_graphic');
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="'.$params->document->getStyleName('graphics').'"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            // (as unknown style because style-family graphic is not supported)
            $style_obj = ODTUnknownStyle::importODTStyle($image_style);
            $params->document->addAutomaticStyle($style_obj);
            ODTImage::addImage ($params, $picture, NULL, NULL, NULL, NULL, $style_name);
        }

        // Create a text style for our span
        $disabled ['background-image'] = 1;
        $style_obj = ODTTextStyle::createTextStyle ($properties, $disabled);
        $params->document->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        // Open span
        self::spanOpen($params, $style_name);
    }

    /**
     * Close a text span.
     *
     * @param string $style_name The style to use.
     */    
    public static function spanClose(ODTInternalParams $params) {
        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement($params->content);
    }

    protected static function createSpanInternal (ODTInternalParams $params, $attributes) {
        // Get properties
        $properties = array();        
        ODTUtility::getHTMLElementProperties ($params, $properties, 'span', $attributes);

        // Create automatic style
        $properties ['style-name'] = ODTStyle::getNewStylename ('span');
        $params->document->createTextStyle($properties, false);
        
        // Return style name
        return $properties ['style-name'];
    }

    public static function generateSpansfromHTMLCode(ODTInternalParams $params, $HTMLCode){
        $spans = array ('sup' => array ('open' => '<text:span text:style-name="sup">',
                                        'close' => '</text:span>'),
                        'sub' => array ('open' => '<text:span text:style-name="sub">',
                                        'close' => '</text:span>'),
                        'u' => array ('open' => '<text:span text:style-name="underline">',
                                      'close' => '</text:span>'),
                        'em' => array ('open' => '<text:span text:style-name="Emphasis">',
                                       'close' => '</text:span>'),
                        'strong' => array ('open' => '<text:span text:style-name="Strong_20_Emphasis">',
                                             'close' => '</text:span>'),
                        'del' => array ('open' => '<text:span text:style-name="del">',
                                        'close' => '</text:span>'),
                       );
        $parsed = array();

        // First examine $HTMLCode and differ between normal content,
        // opening tags and closing tags.
        $max = strlen ($HTMLCode);
        $pos = 0;
        while ($pos < $max) {
            $found = ODTUtility::getNextTag($HTMLCode, $pos);
            if ($found !== false) {
                $entry = array();
                $entry ['content'] = substr($HTMLCode, $pos, $found [0]-$pos);
                if (!empty($entry ['content'])) {
                    $parsed [] = $entry;
                }

                $tagged = substr($HTMLCode, $found [0], $found [1]-$found [0]+1);
                $entry = array();

                if ($HTMLCode [$found[1]-1] == '/') {
                    // Element without content <abc/>, doesn'T make sense, save as content
                    $entry ['content'] = $tagged;
                } else {
                    if ($HTMLCode [$found[0]+1] != '/') {
                        $parts = explode(' ', trim($tagged, '<> '), 2);
                        $entry ['tag-open'] = $parts [0];
                        if ($parts [1] != NULL ) {
                            $entry ['attributes'] = $parts [1];
                        }
                        $entry ['tag-orig'] = $tagged;
                    } else {
                        $entry ['tag-close'] = trim ($tagged, '<>/ ');
                        $entry ['tag-orig'] = $tagged;
                    }
                }
                $parsed [] = $entry;

                $pos = $found [1]+1;
            } else {
                $entry = array();
                $entry ['content'] = substr($HTMLCode, $pos);
                $parsed [] = $entry;
                break;
            }
        }

        // Check each array entry.
        $checked = array();
        for ($out = 0 ; $out < count($parsed) ; $out++) {
            if ($checked [$out] != NULL) {
                continue;
            }
            $found = $parsed [$out];
            if ($found ['content'] != NULL) {
                $checked [$out] = $params->document->replaceXMLEntities($found ['content']);
            } else if ($found ['tag-open'] != NULL) {
                $closed = false;

                for ($in = $out+1 ; $in < count($parsed) ; $in++) {
                    $search = $parsed [$in];
                    if ($search ['tag-close'] != NULL &&
                        $found ['tag-open'] == $search ['tag-close'] &&
                        (array_key_exists($found ['tag-open'], $spans) || $found ['tag-open'] == 'span')) {
                        $closed = true;

                        // Known and closed tag, convert to ODT
                        if ($found ['tag-open'] != 'span') {
                            $checked [$out] = $spans [$found ['tag-open']]['open'];
                            $checked [$in] = $spans [$found ['tag-open']]['close'];
                        } else {
                            $style_name = self::createSpanInternal ($params, $found ['attributes']);
                            $checked [$out] = '<text:span text:style-name="'.$style_name.'">';
                            $checked [$in] = '</text:span>';
                        }
                        break;
                    }
                }

                // Known tag? Closing tag found?
                if (!$closed) {
                    // No, save as content
                    $checked [$out] = $params->document->replaceXMLEntities($found ['tag-orig']);
                }
            } else if ($found ['tag-close'] != NULL) {
                // If we find a closing tag it means it did not match
                // an opening tag. Convert to content!
                $checked [$out] = $params->document->replaceXMLEntities($found ['tag-orig']);
            }
        }

        // Add checked entries to content
        for ($index = 0 ; $index < count($checked) ; $index++) {
            $params->content .= $checked [$index];
        }
    }
}
