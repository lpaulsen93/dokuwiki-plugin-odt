<?php

require_once DOKU_PLUGIN . 'odt/ODT/css/csscolors.php';

/**
 * ODTUtility:
 * Class containing some internal utility functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
class ODTUtility
{
    /**
     * Replace local links with bookmark references or text
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
     * @param string $src
     * @param  $width
     * @param  $height
     * @return array
     */
    public static function getImageSizeString($src, $width = NULL, $height = NULL){
        list($width_file, $height_file) = self::getImageSize($src);
        if ($width_file != 0) {
            $width  = $width_file;
            $height = $height_file;
        } else {
            // convert from pixel to centimeters
            if ($width) $width = (($width/96.0)*2.54);
            if ($height) $height = (($height/96.0)*2.54);
        }

        if ($width && $height) {
            // Don't be wider than the page
            if ($width >= 17){ // FIXME : this assumes A4 page format with 2cm margins
                $width = $width.'cm"  style:rel-width="100%';
                $height = $height.'cm"  style:rel-height="scale';
            } else {
                $width = $width.'cm';
                $height = $height.'cm';
            }
        } else {
            // external image and unable to download, fallback
            if ($width) {
                $width = $width."cm";
            } else {
                $width = '" svg:rel-width="100%';
            }
            if ($height) {
                $height = $height."cm";
            } else {
                $height = '" svg:rel-height="100%';
            }
        }
        return array($width, $height);
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
     * @param  array  $properties Array with property value pairs
     * @param  ODTUnits $units Units object to use for conversion
     */
    public static function adjustValuesForODT (&$properties, ODTUnits $units) {
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

        // First do simple adjustments per property
        foreach ($properties as $property => $value) {
            $properties [$property] = ODTUtility::adjustValueForODT ($property, $value, $units);
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
     * @param  string  $property The property name
     * @param  string  $value    The value
     * @param  integer $emValue  Factor for conversion from 'em' to 'pt'
     * @return string  Converted value
     */
    public static function adjustValueForODT ($property, $value, ODTUnits $units) {
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
                //$number = substr ($part, 0, $length-2);
                //if ( is_numeric ($number) && !empty ($emValue) ) {
                //    $part = ($number * $emValue).'pt';
                //}
            }

            // Some values can have '"' in it. These need to be converted to '&apos;'
            // e.g. 'font-family' tp specify that '"Courier New"' is one font name not two
            $part = str_replace('"', '&apos;', $part);

            $value .= ' '.$part;
        }
        $value = trim($value);
        $value = trim($value, '"');

        return $value;
    }

    /**
     * This function processes the CSS style declarations in $style and saves them in $properties
     * as key - value pairs, e.g. $properties ['color'] = 'red'. It also adjusts the values
     * for the ODT format and changes URLs to local paths if required, using $baseURL).
     *
     * @author LarsDW223
     * @param array $properties
     * @param $style The CSS style e.g. 'color:red;'
     * @param null $baseURL
     */
    public static function getCSSStylePropertiesForODT(&$properties, $style, $baseURL = NULL, ODTUnits $units){
        // Create rule with selector '*' (doesn't matter) and declarations as set in $style
        $rule = new css_rule ('*', $style);
        $rule->getProperties ($properties);
        //foreach ($properties as $property => $value) {
        //    $properties [$property] = self::adjustValueForODT ($property, $value, $units);
        //}
        self::adjustValuesForODT ($properties, $units);

        if ( !empty ($properties ['background-image']) ) {
            if ( !empty ($baseURL) ) {
                // Replace 'url(...)' with $baseURL
                $properties ['background-image'] = cssimportnew::replaceURLPrefix ($properties ['background-image'], $baseURL);
            }
        }
    }

    public static function openHTMLElement (ODTInternalParams $params, array &$dest, $element, $attributes) {
        // Push/create our element to import on the stack
        $params->htmlStack->open($element, $attributes);
        $toMatch = $params->htmlStack->getCurrentElement();
        $params->import->getPropertiesForElement($dest, $toMatch, $params->units);

        // Adjust values for ODT
        ODTUtility::adjustValuesForODT($dest, $params->units);
    }

    public static function closeHTMLElement (ODTInternalParams $params, $element) {
        $params->htmlStack->close($element);
    }
}
