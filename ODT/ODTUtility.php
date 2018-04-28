<?php
/**
 * Utility functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/** Include csscolors */
require_once DOKU_PLUGIN . 'odt/ODT/css/csscolors.php';
/** Include cssborder */
require_once DOKU_PLUGIN . 'odt/ODT/css/cssborder.php';

/**
 * ODTUtility:
 * Class containing some internal utility functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 * @package    ODT\Utility
 */
class ODTUtility
{
    /**
     * Replace local links with bookmark references or text
     * 
     * @param    string $content          The document content
     * @param    array  $toc              The table of contents
     * @param    array  $bookmarks        List of bookmarks
     * @param    string $styleName        Link style name
     * @param    string $visitedStyleName Visited link style name
     */
    public static function replaceLocalLinkPlaceholders(&$content, array $toc, array $bookmarks, $styleName, $visitedStyleName) {
        $matches = array();
        $position = 0;
        $max = strlen ($content);
        $length = strlen ('<locallink>');
        $lengthWithName = strlen ('<locallink name=');
        while ( $position < $max ) {
            $first = strpos ($content, '<locallink', $position);
            if ( $first === false ) {
                break;
            }
            $endFirst = strpos ($content, '>', $first);
            if ( $endFirst === false ) {
                break;
            }
            $second = strpos ($content, '</locallink>', $endFirst);
            if ( $second === false ) {
                break;
            }

            // $match includes the whole tag '<locallink name="...">text</locallink>'
            // The attribute 'name' is optional!
            $match = substr ($content, $first, $second - $first + $length + 1);
            $text = substr ($match, $endFirst-$first+1, -($length + 1));
            $text = trim ($text, ' ');
            $text = strtolower ($text);
            $page = str_replace (' ', '_', $text);
            $opentag = substr ($match, 0, $endFirst-$first);
            $name = substr ($opentag, $lengthWithName);
            $name = trim ($name, '">');

            $linkStyle  = 'text:style-name="'.$styleName.'"';
            $linkStyle .= ' text:visited-style-name="'.$visitedStyleName.'"';

            $found = false;
            foreach ($toc as $item) {
                $params = explode (',', $item);

                if ( $page == $params [1] ) {
                    $found = true;
                    $link  = '<text:a xlink:type="simple" xlink:href="#'.$params [0].'" '.$linkStyle.'>';
                    if ( !empty($name) ) {
                        $link .= $name;
                    } else {
                        $link .= $text;
                    }
                    $link .= '</text:a>';

                    $content = str_replace ($match, $link, $content);
                    $position = $first + strlen ($link);
                }
            }

            if ( $found == false ) {
                // Nothing found yet, check the bookmarks too.
                foreach ($bookmarks as $item) {
                    if ( $page == $item ) {
                        $found = true;
                        $link  = '<text:a xlink:type="simple" xlink:href="#'.$item.'" '.$linkStyle.'>';
                        if ( !empty($name) ) {
                            $link .= $name;
                        } else {
                            $link .= $text;
                        }
                        $link .= '</text:a>';

                        $content = str_replace ($match, $link, $content);
                        $position = $first + strlen ($link);
                    }
                }
            }

            if ( $found == false ) {
                // If we get here, then the referenced target was not found.
                // There must be a bug manging the bookmarks or links!
                // At least remove the locallink element and insert text.
                if ( !empty($name) ) {
                    $content = str_replace ($match, $name, $content);
                } else {
                    $content = str_replace ($match, $text, $content);
                }
                $position = $first + strlen ($text);
            }
        }
    }

    /**
     * This function deletes the useless elements. Right now, these are empty paragraphs
     * or paragraphs that only include whitespace.
     *
     * IMPORTANT:
     * Paragraphs can be used for pagebreaks/changing page format.
     * Such paragraphs may not be deleted!
     * 
     * @param    string $docContent              The document content
     * @param    array  $preventDeletetionStyles Array of style names which may not be deleted
     */
    public static function deleteUselessElements(&$docContent, array $preventDeletetionStyles) {
        $length_open = strlen ('<text:p');
        $length_close = strlen ('</text:p>');
        $max = strlen ($docContent);
        $pos = 0;

        while ($pos < $max) {
            $start_open = strpos ($docContent, '<text:p', $pos);
            if ( $start_open === false ) {
                break;
            }
            $start_close = strpos ($docContent, '>', $start_open + $length_open);
            if ( $start_close === false ) {
                break;
            }
            $end = strpos ($docContent, '</text:p>', $start_close + 1);
            if ( $end === false ) {
                break;
            }

            $deleted = false;
            $length = $end - $start_open + $length_close;
            $content = substr ($docContent, $start_close + 1, $end - ($start_close + 1));

            if ( empty($content) || ctype_space ($content) ) {
                // Paragraph is empty or consists of whitespace only. Check style name.
                $style_start = strpos ($docContent, '"', $start_open);
                if ( $style_start === false ) {
                    // No '"' found??? Ignore this paragraph.
                    break;
                }
                $style_end = strpos ($docContent, '"', $style_start+1);
                if ( $style_end === false ) {
                    // No '"' found??? Ignore this paragraph.
                    break;
                }
                $style_name = substr ($docContent, $style_start+1, $style_end - ($style_start+1));

                // Only delete empty paragraph if not listed in 'Do not delete' array!
                if ( !in_array($style_name, $preventDeletetionStyles) )
                {
                    $docContent = substr_replace($docContent, '', $start_open, $length);

                    $deleted = true;
                    $max -= $length;
                    $pos = $start_open;
                }
            }

            if ( $deleted == false ) {
                $pos = $start_close;
            }
        }
    }

    /**
     * The function tries to examine the width and height
     * of the image stored in file $src.
     * 
     * @param  string $src The file name of image
     * @param  int    $maxwidth The maximum width the image shall have
     * @param  int    $maxheight The maximum height the image shall have
     * @return array  Width and height of the image in centimeters or
     *                both 0 if file doesn't exist.
     *                Just the integer value, no units included.
     */
    public static function getImageSize($src, $maxwidth=NULL, $maxheight=NULL){
        if (file_exists($src)) {
            $info  = getimagesize($src);
            if(!$width){
                $width  = $info[0];
                $height = $info[1];
            }else{
                $height = round(($width * $info[1]) / $info[0]);
            }

            if ($maxwidth && $width > $maxwidth) {
                $height = $height * ($maxwidth/$width);
                $width = $maxwidth;
            }
            if ($maxheight && $height > $maxheight) {
                $width = $width * ($maxheight/$height);
                $height = $maxheight;
            }

            // Convert from pixel to centimeters
            if ($width) $width = (($width/96.0)*2.54);
            if ($height) $height = (($height/96.0)*2.54);

            return array($width, $height);
        }

        return array(0, 0);
    }

    /**
     * Return the size of an image in centimeters.
     * 
     * @param  string       $src         Filepath of the image
     * @param  string|null  $width       Alternative width
     * @param  string|null  $height      Alternative height
     * @param  boolean|true $preferImage Prefer original image size
     * @param  ODTUnits     $units       $ODTUnits object for unit conversion
     * @return array
     */
    public static function getImageSizeString($src, $width = NULL, $height = NULL, $preferImage=true, ODTUnits $units){
        list($width_file, $height_file) = self::getImageSize($src);

        // Get original ratio if possible
        $ratio = 1;
        if ($width_file != 0 && $height_file != 0) {
            $ratio = $height_file/$width_file;
        }

        if ($width_file != 0 && $preferImage) {
            $width  = $width_file.'cm';
            $height = $height_file.'cm';
        } else {
            // convert from pixel to centimeters only if no unit is
            // specified or if unit is 'px'
            $unit_width = $units->stripDigits ($width);
            $unit_height = $units->stripDigits ($height);
            if ((empty($unit_width) && empty($unit_height)) ||
                ($unit_width == 'px' && $unit_height == 'px')) {
                if (!$height) {
                    $height = $width * $ratio;
                }
                $height = (($height/96.0)*2.54).'cm';
                if ($width) $width = (($width/96.0)*2.54).'cm';
            }
        }

        // At this point $width and $height should include a unit

        $width = str_replace(',', '.', $width);
        $height = str_replace(',', '.', $height);
        if ($width && $height) {
            // Don't be wider than the page
            if ($width >= 17){ // FIXME : this assumes A4 page format with 2cm margins
                $width = $width.'"  style:rel-width="100%';
                $height = $height.'"  style:rel-height="scale';
            } else {
                $width = $width;
                $height = $height;
            }
        } else {
            // external image and unable to download, fallback
            if (!$width) {
                $width = '" svg:rel-width="100%';
            }
            if (!$height) {
                $height = '" svg:rel-height="100%';
            }
        }
        return array($width, $height);
    }

    /**
     * Split $value by whitespace and convert any relative values (%)
     * into an absolute value. This is done by taking the percentage of
     * $maxWidthInPt.
     * 
     * @param  string       $value        String (Property value)
     * @param  integer      $maxWidthInPt Maximum width in points
     * @param  ODTUnits     $units        $ODTUnits object for unit conversion
     * @return string
     */
    protected static function adjustPercentageValueParts ($value, $maxWidthInPt, $units) {
        $values = preg_split ('/\s+/', $value);
        $value = '';
        foreach ($values as $part) {
            $length = strlen ($part);

            if ( $length > 1 && $part [$length-1] == '%' ) {
                $percentageValue = $units->getDigits($part);
                $part = (($percentageValue * $maxWidthInPt)/100) . 'pt';
                //$part = '5pt ';
            }

            $value .= ' '.$part;
        }
        $value = trim($value);
        $value = trim($value, '"');

        return $value;
    }

    /**
     * The function adjusts the properties values for ODT:
     * - 'em' units are converted to 'pt' units
     * - CSS color names are converted to its RGB value
     * - short color values like #fff are converted to the long format, e.g #ffffff
     * - some relative values are converted to absoulte depending on other
     *   values e.g. 'line-height' an 'font-size'
     *
     * @author LarsDW223
     *
     * @param  array    $properties Array with property value pairs
     * @param  ODTUnits $units      Units object to use for conversion
     * @param  integer  $maxWidth   Units object to use for conversion
     */
    public static function adjustValuesForODT (&$properties, ODTUnits $units, $maxWidth=NULL) {
        $adjustToMaxWidth = array('margin', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom');

        // Convert 'text-decoration'.
        if ( $properties ['text-decoration'] == 'line-through' ) {
            $properties ['text-line-through-style'] = 'solid';
        }
        if ( $properties ['text-decoration'] == 'underline' ) {
            $properties ['text-underline-style'] = 'solid';
        }
        if ( $properties ['text-decoration'] == 'overline' ) {
            $properties ['text-overline-style'] = 'solid';
        }

        // Normalize border properties
        cssborder::normalize($properties);

        // First do simple adjustments per property
        foreach ($properties as $property => $value) {
            $properties [$property] = self::adjustValueForODT ($property, $value, $units);
        }

        // Adjust relative margins if $maxWidth is given.
        // $maxWidth is expected to be the width of the surrounding element.
        if ($maxWidth != NULL) {
            $maxWidthInPt = $units->toPoints($maxWidth, 'y');
            $maxWidthInPt = $units->getDigits($maxWidthInPt);
            
            foreach ($adjustToMaxWidth as $property) {
                if (!empty($properties [$property])) {
                    $properties [$property] = self::adjustPercentageValueParts ($properties [$property], $maxWidthInPt, $units);
                }
            }
        }

        // Now we do the adjustments for which one value depends on another

        // Do we have font-size or line-height set?
        if ($properties ['font-size'] != NULL || $properties ['line-height'] != NULL) {
            // First get absolute font-size in points
            $base_font_size_in_pt = $units->getPixelPerEm ().'px';
            $base_font_size_in_pt = $units->toPoints($base_font_size_in_pt, 'y');
            $base_font_size_in_pt = $units->getDigits($base_font_size_in_pt);
            if ($properties ['font-size'] != NULL) {
                $font_size_unit = $units->stripDigits($properties ['font-size']);
                $font_size_digits = $units->getDigits($properties ['font-size']);
                if ($font_size_unit == '%') {
                    $base_font_size_in_pt = ($font_size_digits * $base_font_size_in_pt)/100;
                    $properties ['font-size'] = $base_font_size_in_pt.'pt';
                } elseif ($font_size_unit != 'pt') {
                    $properties ['font-size'] = $units->toPoints($properties ['font-size'], 'y');
                    $base_font_size_in_pt = $units->getDigits($properties ['font-size']);
                } else {
                    $base_font_size_in_pt = $units->getDigits($properties ['font-size']);
                }
            }

            // Convert relative line-heights to absolute
            if ($properties ['line-height'] != NULL) {
                $line_height_unit = $units->stripDigits($properties ['line-height']);
                $line_height_digits = $units->getDigits($properties ['line-height']);
                if ($line_height_unit == '%') {
                    $properties ['line-height'] = (($line_height_digits * $base_font_size_in_pt)/100).'pt';
                } elseif (empty($line_height_unit)) {
                    $properties ['line-height'] = ($line_height_digits * $base_font_size_in_pt).'pt';
                }
            }
        }
    }

    /**
     * The function adjusts the property value for ODT:
     * - 'em' units are converted to 'pt' units
     * - CSS color names are converted to its RGB value
     * - short color values like #fff are converted to the long format, e.g #ffffff
     *
     * @author LarsDW223
     *
     * @param  string   $property   The property name
     * @param  string   $value      The value
     * @param  ODTUnits $units      Units object to use for conversion
     * @return string   Converted value
     */
    public static function adjustValueForODT ($property, $value, ODTUnits $units) {
        if ($property == 'font-family') {
            // There might be several font/font-families included.
            // Only take the first one.
            $value = trim($value, '"');
            if (strpos($value, ',') !== false) {
                $values = explode(',', $value);
                $value = trim ($values [0], '"');
                $value = trim ($value, "'");
                $value = trim ($value);
            }
        } else {
            $values = preg_split ('/\s+/', $value);
            $value = '';
            foreach ($values as $part) {
                $length = strlen ($part);

                // If it is a short color value (#xxx) then convert it to long value (#xxxxxx)
                // (ODT does not support the short form)
                if ( $part [0] == '#' && $length == 4 ) {
                    $part = '#'.$part [1].$part [1].$part [2].$part [2].$part [3].$part [3];
                } else {
                    // If it is a CSS color name, get it's real color value
                    $color = csscolors::getColorValue ($part);
                    if ( $part == 'black' || $color != '#000000' ) {
                        $part = $color;
                    }
                }

                if ( $length > 2 && $part [$length-2] == 'e' && $part [$length-1] == 'm' ) {
                    $part = $units->toPoints($part, 'y');
                }

                if ( $length > 2 && ($part [$length-2] != 'p' || $part [$length-1] != 't') &&
                     strpos($property, 'border')!==false ) {
                    $part = $units->toPoints($part, 'y');
                }

                // Some values can have '"' in it. These need to be converted to '&apos;'
                // e.g. 'font-family' tp specify that '"Courier New"' is one font name not two
                $part = str_replace('"', '&apos;', $part);

                $value .= ' '.$part;
            }
            $value = trim($value);
            $value = trim($value, '"');
        }

        return $value;
    }

    /**
     * This function processes the CSS style declarations in $style and saves them in $properties
     * as key - value pairs, e.g. $properties ['color'] = 'red'. It also adjusts the values
     * for the ODT format and changes URLs to local paths if required, using $baseURL).
     *
     * @author LarsDW223
     * @param array       $properties
     * @param string      $style      The CSS style e.g. 'color:red;'
     * @param string|null $baseURL
     * @param ODTUnits    $units      Units object to use for conversion
     * @param integer     $maxWidth   MaximumWidth
     */
    public static function getCSSStylePropertiesForODT(&$properties, $style, $baseURL = NULL, ODTUnits $units, $maxWidth=NULL){
        // Create rule with selector '*' (doesn't matter) and declarations as set in $style
        $rule = new css_rule ('*', $style);
        $rule->getProperties ($properties);
        //foreach ($properties as $property => $value) {
        //    $properties [$property] = self::adjustValueForODT ($property, $value, $units);
        //}
        self::adjustValuesForODT ($properties, $units, $maxWidth);

        if ( !empty ($properties ['background-image']) ) {
            if ( !empty ($baseURL) ) {
                // Replace 'url(...)' with $baseURL
                $properties ['background-image'] = cssimportnew::replaceURLPrefix ($properties ['background-image'], $baseURL);
            }
        }
    }

    /**
     * The function opens/puts a new element on the HTML stack in $params->htmlStack.
     * The element name will be $element and it will be created with the attributes $attributes.
     * Then CSS matching is performed and the CSS properties are returned in $dest.
     * Finally the CSS properties are converted to ODT format if neccessary.
     *
     * @author LarsDW223
     * @param ODTInternalParams $params     Commom params.
     * @param array             $dest       Target array for properties storage
     * @param string            $element    The element's name
     * @param string            $attributes The element's attributes
     * @param integer           $maxWidth   Maximum Width
     */
    public static function openHTMLElement (ODTInternalParams $params, array &$dest, $element, $attributes, $maxWidth=NULL) {
        // Push/create our element to import on the stack
        $params->htmlStack->open($element, $attributes);
        $toMatch = $params->htmlStack->getCurrentElement();
        $params->import->getPropertiesForElement($dest, $toMatch, $params->units);

        // Adjust values for ODT
        self::adjustValuesForODT($dest, $params->units, $maxWidth);
    }

    /**
     * The function closes element with name $element on the HTML stack in $params->htmlStack.
     *
     * @author LarsDW223
     * @param ODTInternalParams $params     Commom params.
     * @param string            $element    The element's name
     */
    public static function closeHTMLElement (ODTInternalParams $params, $element) {
        $params->htmlStack->close($element);
    }

    /**
     * The function temporarily opens/puts a new element on the HTML stack in $params->htmlStack.
     * Before leaving the function the element is removed from the stack.
     * 
     * The element name will be $element and it will be created with the attributes $attributes.
     * After opening the element CSS matching is performed and the CSS properties are returned in $dest.
     * Finally the CSS properties are converted to ODT format if neccessary.
     *
     * @author LarsDW223
     * @param ODTInternalParams $params     Commom params.
     * @param array             $dest       Target array for properties storage
     * @param string            $element    The element's name
     * @param string            $attributes The element's attributes
     * @param integer           $maxWidth   Maximum Width
     * @param boolean           $inherit    Enable/disable CSS inheritance
     */
    public static function getHTMLElementProperties (ODTInternalParams $params, array &$dest, $element, $attributes, $maxWidth=NULL, $inherit=true) {
        // Push/create our element to import on the stack
        $params->htmlStack->open($element, $attributes);
        $toMatch = $params->htmlStack->getCurrentElement();
        $params->import->getPropertiesForElement($dest, $toMatch, $params->units, $inherit);

        // Adjust values for ODT
        self::adjustValuesForODT($dest, $params->units, $maxWidth);

        // Remove element from stack
        $params->htmlStack->removeCurrent();
    }

    /**
     * Small helper function for finding the next tag enclosed in <angle> brackets.
     * Returns beginning and end of the tag as an array [0] = start, [1] = end.
     * 
     * @author LarsDW223
     * @param string $content Code to search in.
     * @param string $pos     Start position for searching.
     * @return array
     */
    public static function getNextTag (&$content, $pos) {
        $start = strpos ($content, '<', $pos);
        if ($start === false) {
            return false;
        }
        $end = strpos ($content, '>', $pos);
        if ($end === false) {
            return false;
        }
        return array($start, $end);
    }

    /**
     * The function returns $value as a valid IRI and replaces some signs
     * if neccessary, e.g. '&' will be replaced by '&amp;'.
     * The function will not do double replacements, e.g. if the string
     * already includes a '&amp;' it will NOT become '&amp;amp;'.
     * 
     * @author LarsDW223
     * @param string $value String to be converted to IRI
     * @return string
     */
    public static function stringToIRI ($value) {
        $max = strlen ($value);
        for ($pos = 0 ; $pos < $max ; $pos++) {
            switch ($value [$pos]) {
                case '&':
                    if ($max - $pos >= 4 &&
                        $value [$pos+1] == '#' &&
                        $value [$pos+2] == '3' &&
                        $value [$pos+3] == '8' &&
                        $value [$pos+4] == ';') {
                        // '&#38;' must be replaced with "&amp;"
                        $value [$pos+1] = 'a';
                        $value [$pos+2] = 'm';
                        $value [$pos+3] = 'p';
                        $pos += 4;
                    } else if ($max - $pos < 4 ||
                        $value [$pos+1] != 'a' ||
                        $value [$pos+2] != 'm' ||
                        $value [$pos+3] != 'p' ||
                        $value [$pos+4] != ';' ) {
                        // '&' must be replaced with "&amp;"
                        $new = substr($value, 0, $pos+1);
                        $new .= 'amp;';
                        $new .= substr($value, $pos+1);
                        $value = $new;
                        $max += 4;
                        $pos += 4;
                    }
                    break;
            }
        }
        return $value;
    }

    protected static function getLinkURL ($search) {
        preg_match ('/href="[^"]*"/', $search, $matches);
        $url = substr ($matches[0], 5);
        $url = trim($url, '"');
        // Keep '&' and ':' in the link URL unescaped, otherwise url parameter passing will not work
        $url = str_replace('&amp;', '&', $url);
        $url = str_replace('%3A', ':', $url);

        return $url;
    }

    /**
     * static call back to replace spaces
     *
     * @param array $matches
     * @return string
     */
    protected static function _preserveSpace($matches){
        $spaces = $matches[1];
        $len    = strlen($spaces);
        return '<text:s text:c="'.$len.'"/>';
    }

    protected static function createTextStyle (ODTInternalParams $params, $element, $attributes, $styleName=NULL) {
        // Create automatic style
        if ($styleName == NULL || !$params->document->styleExists($styleName)) {
            // Get properties
            $properties = array();        
            self::getHTMLElementProperties ($params, $properties, $element, $attributes);

            if ($styleName == NULL) {
                $properties ['style-name'] = ODTStyle::getNewStylename ('span');
            } else {
                // Use callers style name. He needs to be sure that it's unique!
                $properties ['style-name'] = $styleName;
            }
            $params->document->createTextStyle($properties, false);

            // Return style name
            return $properties ['style-name'];
        } else {
            // Style already exists
            return $styleName;
        }
    }

    protected static function createParagraphStyle (ODTInternalParams $params, $element, $attributes, $styleName=NULL) {
        // Create automatic style
        if ($styleName == NULL || !$params->document->styleExists($styleName)) {
            // Get properties
            $properties = array();        
            self::getHTMLElementProperties ($params, $properties, $element, $attributes);

            if ($styleName == NULL) {
                $properties ['style-name'] = ODTStyle::getNewStylename ('span');
            } else {
                // Use callers style name. He needs to be sure that it's unique!
                $properties ['style-name'] = $styleName;
            }
            $params->document->createParagraphStyle($properties, false);

            // Return style name
            return $properties ['style-name'];
        } else {
            // Style already exists
            return $styleName;
        }
    }

    public static function generateODTfromHTMLCode(ODTInternalParams $params, $HTMLCode, array $options){
        $elements = array ('sup' => array ('open' => '<text:span text:style-name="sup">',
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
                           'span' => array ('open' => '',
                                         'close' => ''),
                           'a' => array ('open' => '',
                                         'close' => ''),
                           'ol' => array ('open' => '',
                                          'close' => ''),
                           'ul' => array ('open' => '',
                                          'close' => ''),
                           'li' => array ('open' => '<text:list-item><text:p text:style-name="Text_20_body">',
                                          'close' => '</text:p></text:list-item>'),
                           // In the moment only remove divs
                           'div' => array ('open' => '', 'close' => ''),
                       );
        $parsed = array();

        // remove useless leading and trailing whitespace-newlines
        $HTMLCode = preg_replace('/^&nbsp;\n/', '', $HTMLCode);
        $HTMLCode = preg_replace('/\n&nbsp;$/', '', $HTMLCode);
        $HTMLCode = str_replace('&nbsp;', '&#xA0;', $HTMLCode);

        // Get default paragraph style
        if (!empty($options ['p_style'])) {
            $p_style = $options ['p_style'];
        } else {
            $p_style = $params->document->getStyleName('body');
        }

        // Get default list style names
        if (!empty($options ['list_p_style'])) {
            $p_list_style = $options ['list_p_style'];
        } else {
            $p_list_style = $params->document->getStyleName('body');
        }
        if (!empty($options ['list_ol_style'])) {
            $ol_list_style = $options ['list_ol_style'];
        } else {
            $ol_list_style = $params->document->getStyleName('numbering');
        }
        if (!empty($options ['list_ul_style'])) {
            $ul_list_style = $options ['list_ul_style'];
        } else {
            $ul_list_style = $params->document->getStyleName('list');
        }

        // Set new media selector (remember old one)
        $media = $params->import->getMedia ();
        if (!empty($options ['media_selector'])) {
            $params->import->setMedia ($options ['media_selector']);
        }

        if (!empty($options ['element'])) {
            $params->htmlStack->open($options ['element'], $options ['attributes']);
        }

        // First examine $HTMLCode and differ between normal content,
        // opening tags and closing tags.
        $max = strlen ($HTMLCode);
        $pos = 0;
        while ($pos < $max) {
            $found = self::getNextTag($HTMLCode, $pos);
            if ($found !== false) {
                $entry = array();
                $entry ['content'] = substr($HTMLCode, $pos, $found [0]-$pos);
                if ($entry ['content'] === false) {
                    $entry ['content'] = '';
                }
                $parsed [] = $entry;

                $tagged = substr($HTMLCode, $found [0], $found [1]-$found [0]+1);
                $entry = array();

                if ($HTMLCode [$found[1]-1] == '/') {
                    // Element without content <abc/>, doesn'T make sense, save as content
                    $entry ['content'] = $tagged;
                } else {
                    if ($HTMLCode [$found[0]+1] != '/') {
                        $parts = explode(' ', trim($tagged, '<> '), 2);
                        $entry ['tag-open'] = $parts [0];
                        if ($parts [1] !== NULL ) {
                            $entry ['attributes'] = $parts [1];
                        }
                        $entry ['tag-orig'] = $tagged;
                    } else {
                        $entry ['tag-close'] = trim ($tagged, '<>/ ');
                        $entry ['tag-orig'] = $tagged;
                    }
                }
                $entry ['matched'] = false;
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
        $first = true;
        $firstTag = '';
        $olStartValue = NULL;
        for ($out = 0 ; $out < count($parsed) ; $out++) {
            if ($checked [$out] !== NULL) {
                continue;
            }
            $found = &$parsed [$out];
            if ($found ['content'] !== NULL) {
                if ($options ['escape_content'] !== 'false') {
                    $checked [$out] = $params->document->replaceXMLEntities($found ['content']);
                } else {
                    $checked [$out] = $found ['content'];
                }
            } else if ($found ['tag-open'] !== NULL) {
                $closed = false;

                for ($in = $out+1 ; $in < count($parsed) ; $in++) {
                    $search = &$parsed [$in];
                    if ($search ['tag-close'] !== NULL &&
                        $found ['tag-open'] == $search ['tag-close'] &&
                        $search ['matched'] === false &&
                        array_key_exists($found ['tag-open'], $elements)) {

                        $closed = true;
                        $search ['matched'] = true;

                        // Remeber the first element
                        if ($first) {
                            $first = false;
                            $firstTag = $found ['tag-open'];
                        }

                        // Known and closed tag, convert to ODT
                        switch ($found ['tag-open']) {
                            case 'span':
                                // Create ODT span using CSS style from attributes
                                if (!empty($options ['class'])) {
                                    if (preg_match('/class="[^"]*"/', $found ['attributes'], $matches) == 1) {
                                        $class_attr = substr($matches [0], 7);
                                        $class_attr = trim($class_attr, '"');
                                        $class_attr = 'class="'.$options ['class'].' '.$class_attr.'"';
                                        $found ['attributes'] = str_replace($matches [0], $class_attr, $found ['attributes']);
                                    }
                                }
                                $style_name = NULL;
                                if ($options ['style_names'] == 'prefix_and_class') {
                                    if (preg_match('/class="[^"]*"/', $found ['attributes'], $matches) == 1) {
                                        $class_attr = substr($matches [0], 7);
                                        $class_attr = trim($class_attr, '"');
                                        $style_name = $options ['style_names_prefix'].$class_attr;
                                    }
                                }
                                $style_name = self::createTextStyle ($params, 'span', $found ['attributes'], $style_name);
                                $checked [$out] = '<text:span text:style-name="'.$style_name.'">';
                                $checked [$in] = '</text:span>';
                                break;
                            case 'a':
                                $url = self::getLinkURL($found ['attributes']);
                                if (empty($url)) {
                                    $url = 'URLNotFoundInXHTMLLink';
                                }
                                $checked [$out] = $params->document->openHyperlink ($url, NULL, NULL, true);
                                $checked [$in] = $params->document->closeHyperlink (true);
                                break;
                            case 'ul':
                                $checked [$out] = '<text:list text:style-name="'.$ul_list_style.'" text:continue-numbering="false">';
                                $checked [$in] = '</text:list>';
                                break;
                            case 'ol':
                                $checked [$out] = '<text:list text:style-name="'.$ol_list_style.'" text:continue-numbering="false">';
                                $checked [$in] = '</text:list>';
                                if (preg_match('/start="[^"]*"/', $found ['attributes'], $matches) == 1) {
                                    $olStartValue = substr($matches [0], 7);
                                    $olStartValue = trim($olStartValue, '"');
                                }
                                break;
                            case 'li':
                                // Create ODT span using CSS style from attributes
                                $haveClass = false;
                                if (!empty($options ['class'])) {
                                    if (preg_match('/class="[^"]*"/', $found ['attributes'], $matches) == 1) {
                                        $class_attr = substr($matches [0], 7);
                                        $class_attr = trim($class_attr, '"');
                                        $class_attr = 'class="'.$options ['class'].' '.$class_attr.'"';
                                        $found ['attributes'] = str_replace($matches [0], $class_attr, $found ['attributes']);
                                        $haveClass = true;
                                    }
                                }
                                $style_name = NULL;
                                if ($options ['style_names'] == 'prefix_and_class') {
                                    if (preg_match('/class="[^"]*"/', $found ['attributes'], $matches) == 1) {
                                        $class_attr = substr($matches [0], 7);
                                        $class_attr = trim($class_attr, '"');
                                        $style_name = $options ['style_names_prefix'].$class_attr;
                                        $haveClass = true;
                                    }
                                }
                                if ($haveClass) {
                                    $style_name = self::createParagraphStyle ($params, 'li', $found ['attributes'], $style_name);
                                } else {
                                    $style_name = $p_list_style;
                                }

                                $checked [$out] = '<text:list-item';
                                if ($olStartValue !== NULL) {
                                    $checked [$out] .= ' text:start-value="'.$olStartValue.'"';
                                    $olStartValue = NULL;
                                }
                                $checked [$out] .= '><text:p text:style-name="'.$style_name.'">';
                                $checked [$in] = '</text:p></text:list-item>';
                                break;
                            default:
                                // Simple replacement
                                $checked [$out] = $elements [$found ['tag-open']]['open'];
                                $checked [$in] = $elements [$found ['tag-open']]['close'];
                                break;
                        }
                        break;
                    }
                }

                // Known tag? Closing tag found?
                if (!$closed) {
                    // No, save as content
                    if ($options ['escape_content'] !== 'false') {
                        $checked [$out] = $params->document->replaceXMLEntities($found ['tag-orig']);
                    } else {
                        $checked [$out] = $found ['tag-orig'];
                    }
                }
            } else if ($found ['tag-close'] !== NULL) {
                // If we find a closing tag it means it did not match
                // an opening tag. Convert to content!
                $checked [$out] = $params->document->replaceXMLEntities($found ['tag-orig']);
            }
        }

        // Eventually we need to create an enclosing element, open it
        switch ($firstTag) {
            case 'ol':
            case 'ul':
                // Close an eventually open paragraph
                $params->document->paragraphClose();
                break;
            default:
                $params->document->paragraphClose();
                $params->document->paragraphOpen($p_style);
                break;
        }


        // Add checked entries to content
        $content = '';
        for ($index = 0 ; $index < count($checked) ; $index++) {
            $content .= $checked [$index];
        }

        // Handle newlines
        if ($options ['linebreaks'] !== 'remove') {
            $content = str_replace("\n",'<text:line-break/>',$content);
        } else {
            $content = str_replace("\n",'',$content);
        }

        // Handle tabs
        if ($options ['tabs'] !== 'remove') {
            $content = str_replace("\t",'<text:tab/>',$content);
        } else {
            $content = str_replace("\t",'',$content);
        }

        // Preserve space?
        if ($options ['space'] === 'preserve') {
            $content = preg_replace_callback('/(  +)/',array(self,'_preserveSpace'),$content);
        }

        $params->content .= $content;


        // Eventually we need to create an enclosing element, close it
        switch ($firstTag) {
            case 'ol':
            case 'ul':
                // Nothing to do
                break;
            default:
                $params->document->paragraphClose();
                break;
        }

        // Remove current element from stack, if we created one
        if (!empty($options ['element'])) {
            $params->htmlStack->removeCurrent();
        }

        // Restore media selector
        if (!empty($options ['media_selector'])) {
            $params->import->setMedia ($media);
        }
    }
}
