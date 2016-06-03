<?php

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
}
