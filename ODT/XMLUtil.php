<?php
/**
 * XMLUtil: class with helper functions for simple XML handling
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

/**
 * The XMLUtil class
 */
class XMLUtil
{
    public static function isValidXMLName ($sign) {
        if (ctype_alnum($sign) || $sign == ':' || $sign == '-' || $sign == '_') {
            return true;
        }
        return false;
    }

    /**
     * Helper function which returns the opening $element tag
     * if found in $xml_code. Otherwise it returns NULL.
     *
     * @param  $element    The name of the element
     * @param  $xmlCode    The XML code to search through
     * @return string      Found opening tag or NULL
     */
    public static function getElementOpenTag ($element, $xmlCode) {
        $pattern = '/'.$element.'\s[^>]*>/';
        if (preg_match ($pattern, $xmlCode, $matches) === 1) {
            return $matches [0];
        }
        return NULL;
    }

    /**
     * Helper function to find the next element $element and return its
     * complete definition including opening and closing tag.
     *
     * THIS FUNCTION DOES NOT HANDLE ELEMENTS WHICH CAN BE NESTED IN THEMSELVES!!!
     * 
     * @param  $element    The name of the element
     * @param  $xmlCode    The XML code to search through
     * @return string      Found element or NULL
     */
    public static function getElement ($element, $xmlCode, &$endPos=NULL) {
        if(empty($element) || empty($xmlCode)) {
            return NULL;
        }
        $pos = 0;
        $max = strlen ($xmlCode);
        $elementLength = strlen ($element);

        // Search the opening tag first.
        while ($pos < $max) {
            $start = strpos ($xmlCode, '<'.$element, $pos);
            if ($start === false) {
                // Nothing found.
                return NULL;
            }

            $next = $xmlCode [$start+$elementLength+1];
            if ($next == '/' || $next == '>' || ctype_space($next)) {
                // Found it.
                break;
            }

            $pos = $start+$elementLength;
        }
        $pos = $start+$elementLength;

        // Search next '>'.
        $angle = strpos ($xmlCode, '>', $pos);
        if ($angle === false) {
            // Opening tag is not terminated.
            return NULL;
        }
        $pos = $angle + 1;

        // Is this already the end?
        if ($xmlCode [$angle-1] == '/') {
            // Yes.
            $endPos = $angle+1;
            return substr ($xmlCode, $start, $angle-$start+1);
        }

        // Now, search closing tag.
        // (Simple solution which expects there are no child elements
        //  with the same name. This means we assume the element can not
        //  be nested in itself!)
        $end = strpos ($xmlCode, '</'.$element.'>', $pos);
        if ($end === false) {
            return NULL;
        }
        $end += 3 + $elementLength;

        // Found closing tag.
        $endPos = $end;
        return substr ($xmlCode, $start, $end-$start);
    }

    /**
     * Helper function to find the next element $element and return its
     * content only without the opening and closing tag of $element itself.
     *
     * THIS FUNCTION DOES NOT HANDLE ELEMENTS WHICH CAN BE NESTED IN THEMSELVES!!!
     * 
     * @param  $element    The name of the element
     * @param  $xmlCode    The XML code to search through
     * @return string      Found element or NULL
     */
    public static function getElementContent ($element, $xmlCode, &$endPos=NULL) {
        if(empty($element) || empty($xmlCode)) {
            return NULL;
        }
        $pos = 0;
        $max = strlen ($xmlCode);
        $elementLength = strlen ($element);
        $contentStart = 0;
        $contentEnd = 0;

        // Search the opening tag first.
        while ($pos < $max) {
            $start = strpos ($xmlCode, '<'.$element, $pos);
            if ($start === false) {
                // Nothing found.
                return NULL;
            }

            $next = $xmlCode [$start+$elementLength+1];
            if ($next == '/' || $next == '>' || ctype_space($next)) {
                // Found it.
                break;
            }

            $pos = $start+$elementLength;
        }
        $pos = $start+$elementLength;

        // Search next '>'.
        $angle = strpos ($xmlCode, '>', $pos);
        if ($angle === false) {
            // Opening tag is not terminated.
            return NULL;
        }
        $pos = $angle + 1;

        // Is this already the end?
        if ($xmlCode [$angle-1] == '/') {
            // Yes. No content in this case!
            $endPos = $angle+1;
            return NULL;
        }
        $contentStart = $angle+1;

        // Now, search closing tag.
        // (Simple solution which expects there are no child elements
        //  with the same name. This means we assume the element can not
        //  be nested in itself!)
        $end = strpos ($xmlCode, '</'.$element.'>', $pos);
        if ($end === false) {
            return NULL;
        }
        $contentEnd = $end - 1;
        $end += 3 + $elementLength;

        // Found closing tag.
        $endPos = $end;
        if ($contentEnd <= $contentStart) {
            return NULL;
        }
        return substr ($xmlCode, $contentStart, $contentEnd-$contentStart+1);
    }

    /**
     * Helper function to find the next element and return its
     * content only without the opening and closing tag of $element itself.
     *
     * THIS FUNCTION DOES NOT HANDLE ELEMENTS WHICH CAN BE NESTED IN THEMSELVES!!!
     * 
     * @param  $element    On success $element carries the name of the found element
     * @param  $xmlCode    The XML code to search through
     * @return string      Found element or NULL
     */
    public static function getNextElementContent (&$element, $xmlCode, &$endPos=NULL) {
        if(empty($xmlCode)) {
            return NULL;
        }
        $pos = 0;
        $max = strlen ($xmlCode);
        $contentStart = 0;
        $contentEnd = 0;

        // Search the opening tag first.
        while ($pos < $max) {
            $start = strpos ($xmlCode, '<', $pos);
            if ($start === false) {
                // Nothing found.
                return NULL;
            }

            if (XMLUtil::isValidXMLName ($xmlCode [$start+1])) {
                // Extract element name.
                $read = $start+1;
                $found_element = '';
                while (XMLUtil::isValidXMLName ($xmlCode [$read])) {
                    $found_element .= $xmlCode [$read];
                    $read++;
                    if ($read >= $max) {
                        return NULL;
                    }
                }
                $elementLength = strlen ($found_element);

                $next = $xmlCode [$start+$elementLength+1];
                if ($next == '/' || $next == '>' || ctype_space($next)) {
                    // Found it.
                    break;
                }

                $pos = $start+$elementLength;
            } else {
                // Skip this one.
                $pos = $start+2;
            }
        }
        $pos = $start+$elementLength;

        // Search next '>'.
        $angle = strpos ($xmlCode, '>', $pos);
        if ($angle === false) {
            // Opening tag is not terminated.
            return NULL;
        }
        $pos = $angle + 1;

        // Is this already the end?
        if ($xmlCode [$angle-1] == '/') {
            // Yes. No content in this case!
            $endPos = $angle+1;
            $element = $found_element;
            return NULL;
        }
        $contentStart = $angle+1;

        // Now, search closing tag.
        // (Simple solution which expects there are no child elements
        //  with the same name. This means we assume the element can not
        //  be nested in itself!)
        $end = strpos ($xmlCode, '</'.$found_element.'>', $pos);
        if ($end === false) {
            return NULL;
        }
        $contentEnd = $end - 1;
        $end += 3 + $elementLength;

        // Found closing tag.
        $endPos = $end;
        if ($contentEnd <= $contentStart) {
            return NULL;
        }
        $element = $found_element;
        return substr ($xmlCode, $contentStart, $contentEnd-$contentStart+1);
    }

    /**
     * Helper function to find the next element and return its
     * complete definition including opening and closing tag.
     *
     * THIS FUNCTION DOES NOT HANDLE ELEMENTS WHICH CAN BE NESTED IN THEMSELVES!!!
     * 
     * @param  $element    On success $element carries the name of the found element
     * @param  $xmlCode    The XML code to search through
     * @return string      Found element or NULL
     */
    public static function getNextElement (&$element, $xmlCode, &$endPos=NULL) {
        if(empty($xmlCode)) {
            return NULL;
        }
        $pos = 0;
        $max = strlen ($xmlCode);

        // Search the opening tag first.
        while ($pos < $max) {
            $start = strpos ($xmlCode, '<', $pos);
            if ($start === false) {
                // Nothing found.
                return NULL;
            }

            if (XMLUtil::isValidXMLName ($xmlCode [$start+1])) {
                // Extract element name.
                $read = $start+1;
                $found_element = '';
                while (XMLUtil::isValidXMLName ($xmlCode [$read])) {
                    $found_element .= $xmlCode [$read];
                    $read++;
                    if ($read >= $max) {
                        return NULL;
                    }
                }
                $elementLength = strlen ($found_element);

                $next = $xmlCode [$start+$elementLength+1];
                if ($next == '/' || $next == '>' || ctype_space($next)) {
                    // Found it.
                    break;
                }

                $pos = $start+$elementLength;
            } else {
                // Skip this one.
                $pos = $start+2;
            }
        }
        $pos = $start+$elementLength;

        // Search next '>'.
        $angle = strpos ($xmlCode, '>', $pos);
        if ($angle === false) {
            // Opening tag is not terminated.
            return NULL;
        }
        $pos = $angle + 1;

        // Is this already the end?
        if ($xmlCode [$angle-1] == '/') {
            // Yes.
            $endPos = $angle+1;
            $element = $found_element;
            return substr ($xmlCode, $start, $angle-$start+1);
        }

        // Now, search closing tag.
        // (Simple solution which expects there are no child elements
        //  with the same name. This means we assume the element can not
        //  be nested in itself!)
        $end = strpos ($xmlCode, '</'.$found_element.'>', $pos);
        if ($end === false) {
            return NULL;
        }
        $end += 3 + $elementLength;

        // Found closing tag.
        $endPos = $end;
        $element = $found_element;
        return substr ($xmlCode, $start, $end-$start);
    }

    /**
     * Helper function to replace an XML element with a string.
     * 
     * @param  $element     Name of the element ot be replaced.
     * @param  $xmlCode     The XML code to search through
     * @param  $replacement The string which shall be inserted
     * @return string       $xmlCode with replaced element
     */
    public static function elementReplace ($element, $xmlCode, $replacement) {
        $start = strpos ($xmlCode, '<'.$element);
        $empty = false;
        if ($start === false) {
            $empty = strpos ($xmlCode, '<'.$element.'/>');
            if ($empty === false) {
                return $xmlCode;
            }
        }
        if ($empty !== false) {
            // Element has the form '<element/>'. Do a simple string replace.
            return str_replace('<'.$element.'/>', $replacement, $xmlCode);
        }
        $end = strpos ($xmlCode, '</'.$element.'>');
        if ($end === false) {
            // $xmlCode not well formed???
            return $xmlCode;
        }
        $end_length = strlen ('</'.$element.'>');
        return substr_replace ($xmlCode, $replacement, $start, $end-$start+$end_length);
    }

    /**
     * Helper function which returns the value of $attribute
     * if found in $xml_code. Otherwise it returns NULL.
     *
     * @param  $attribute    The name of the attribute
     * @param  $xmlCode      The XML code to search through
     * @return string        Found value or NULL
     */
    public static function getAttributeValue ($attribute, $xmlCode) {
        $pattern = '/\s'.$attribute.'="[^"]*"/';
        if (preg_match ($pattern, $xmlCode, $matches) === 1) {
            $value = substr($matches [0], strlen($attribute)+2);
            $value = trim($value, '"');
            return $value;
        }
        return NULL;
    }

    /**
     * Helper function which stores all attributes
     * in the array $attributes as name => value pairs.
     *
     * @param  $attributes    Array to store the attributes in
     * @param  $xmlCode       The XML code to search through
     * @return integer        Number of found attributes or 0
     */
    public static function getAttributes (&$attributes, $xmlCode) {
        $pattern = '/\s[-:_.a-zA-Z0-9]+="[^"]*"/';
        if (preg_match_all ($pattern, $xmlCode, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $equal_pos = strpos($match [0], '=');
                $name = substr($match [0], 0, $equal_pos);
                $name = trim($name);
                $value = substr($match [0], $equal_pos+1);
                $value = trim($value, '"');
                $attributes [$name] = $value;
            }
            return count($attributes);
        }
        return 0;
    }
}
