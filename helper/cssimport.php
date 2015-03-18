<?php
/**
 * Helper class to read in a CSS style
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

class css_declaration {
    protected $property;
    protected $value;

    public function __construct($property, $value) {
        $this->property = $property;
        $this->value = $value;
    }

    public function getProperty () {
        return $this->property;
    }

    public function getValue () {
        return $this->value;
    }

    public function explode (&$decls) {
        if ( empty ($this->property) ) {
            return;
        }

        switch ($this->property) {
            case 'background':
                $this->explodeBackgroundShorthand ($decls);
            break;
            case 'font':
                $this->explodeFontShorthand ($decls);
            break;
            case 'padding':
                $this->explodePaddingShorthand ($decls);
            break;
            case 'margin':
                $this->explodeMarginShorthand ($decls);
            break;
            case 'border':
                $this->explodeBorderShorthand ($decls);
            break;

            //FIXME: Implement all the shorthands missing
            //case ...
        }
    }

    public function isShorthand () {
        switch ($this->property) {
            case 'background':
            case 'font':
            case 'padding':
            case 'margin':
            case 'border':
                return true;
            break;

            //FIXME: Implement all the shorthands missing
            //case ...
        }
        return false;
    }
    protected function explodeBackgroundShorthand (&$decls) {
        if ( $this->property == 'background' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('background-color', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('background-image', $values [1]);
            }
            if ( count($values) > 2 ) {
                $decls [] = new css_declaration ('background-repeat', $values [2]);
            }
            if ( count($values) > 3 ) {
                $decls [] = new css_declaration ('background-attachment', $values [3]);
            }
            if ( count($values) > 4 ) {
                $decls [] = new css_declaration ('background-position', $values [4]);
            }
        }
    }

    protected function explodeFontShorthand (&$decls) {
        if ( $this->property == 'font' ) {
            $values = preg_split ('/\s+/', $this->value);
            $index = 0;
            $font_style_set = false;
            $font_variant_set = false;
            $font_weight_set = false;
            foreach ($values as $value) {
                if ( $font_style_set === false ) {
                    switch ($value) {
                        case 'normal':
                        case 'italic':
                        case 'oblique':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-style', $value);
                        break;
                        default:
                            $decls [] = new css_declaration ('font-style', 'normal');
                        break;
                    }
                    $font_style_set = true;
                    continue;
                }
                if ( $font_variant_set === false ) {
                    switch ($value) {
                        case 'normal':
                        case 'small-caps':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-variant', $value);
                        break;
                        default:
                            $decls [] = new css_declaration ('font-variant', 'normal');
                        break;
                    }
                    $font_variant_set = true;
                    continue;
                }
                if ( $font_weight_set === false ) {
                    switch ($value) {
                        case 'normal':
                        case 'bold':
                        case 'bolder':
                        case 'lighter:':
                        case '100':
                        case '200':
                        case '300':
                        case '400':
                        case '500':
                        case '600':
                        case '700':
                        case '800':
                        case '900':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-weight', $value);
                        break;
                        default:
                            $decls [] = new css_declaration ('font-weight', 'normal');
                        break;
                    }
                    $font_weight_set = true;
                    continue;
                }
            }
        }
    }

    protected function explodePaddingShorthand (&$decls) {
        if ( $this->property == 'padding' ) {
            $values = preg_split ('/\s+/', $this->value);
            switch (count($values)) {
                case 4:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-bottom', $values [2]);
                    $decls [] = new css_declaration ('padding-left', $values [3]);
                break;
                case 3:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-left', $values [1]);
                    $decls [] = new css_declaration ('padding-bottom', $values [2]);
                break;
                case 2:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-bottom', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-left', $values [1]);
                break;
                case 1:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-bottom', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [0]);
                    $decls [] = new css_declaration ('padding-left', $values [0]);
                break;
            }
        }
    }

    protected function explodeMarginShorthand (&$decls) {
        if ( $this->property == 'margin' ) {
            $values = preg_split ('/\s+/', $this->value);
            switch (count($values)) {
                case 4:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-bottom', $values [2]);
                    $decls [] = new css_declaration ('margin-left', $values [3]);
                break;
                case 3:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-left', $values [1]);
                    $decls [] = new css_declaration ('margin-bottom', $values [2]);
                break;
                case 2:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-bottom', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-left', $values [1]);
                break;
                case 1:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-bottom', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [0]);
                    $decls [] = new css_declaration ('margin-left', $values [0]);
                break;
            }
        }
    }

    protected function explodeBorderShorthand (&$decls) {
        if ( $this->property == 'border' ) {
            $values = preg_split ('/\s+/', $this->value);
            $index = 0;
            $border_width_set = false;
            $border_style_set = false;
            $border_color_set = false;
            while ( $index < 3 ) {
                if ( $border_width_set === false ) {
                    switch ($values [$index]) {
                        case 'thin':
                        case 'medium':
                        case 'thick':
                            $decls [] = new css_declaration ('border-width', $values [$index]);
                        break;
                        default:
                            if ( strpos ($values [$index], 'px') !== false ) {
                                $decls [] = new css_declaration ('border-width', $values [$index]);
                            } else {
                                // There is no default value? So leave it unset.
                            }
                        break;
                    }
                    $border_width_set = true;
                    $index++;
                    continue;
                }
                if ( $border_style_set === false ) {
                    switch ($values [$index]) {
                        case 'none':
                        case 'dotted':
                        case 'dashed':
                        case 'solid':
                        case 'double':
                        case 'groove':
                        case 'ridge':
                        case 'inset':
                        case 'outset':
                            $decls [] = new css_declaration ('border-style', $values [$index]);
                        break;
                        default:
                            $decls [] = new css_declaration ('border-style', 'none');
                        break;
                    }
                    $border_style_set = true;
                    $index++;
                    continue;
                }
                if ( $border_color_set === false ) {
                    $decls [] = new css_declaration ('border-color', $values [$index]);
                    $border_color_set = true;
                    $index++;

                    // This is the last value.
                    break;
                }
            }
        }
    }
}
class css_rule {
    protected $selectors = array ();
    protected $declarations = array ();

    public function __construct($selector, $decls) {
        $exploded_decls = array ();

        $this->selectors = explode (' ', $selector);

        $decls = trim ($decls, '{}');

        // Parse declarations
        $pos = 0;
        $end = strlen ($decls);
        while ( $pos < $end ) {
            $colon = strpos ($decls, ':', $pos);
            if ( $colon === false ) {
                break;
            }
            $semi = strpos ($decls, ';', $colon + 1);
            if ( $semi === false ) {
                break;
            }
            
            $property = substr ($decls, $pos, $colon - $pos);
            $property = trim($property);

            $value = substr ($decls, $colon + 1, $semi - ($colon + 1));
            $value = trim ($value);
            $values = preg_split ('/\s+/', $value);       
            $value = '';
            foreach ($values as $part) {
                if ( $part != '!important' ) {
                    $value .= ' '.$part;
                }
            }
            $value = trim($value);

            // Create new declaration
            $declaration = new css_declaration ($property, $value);
            $this->declarations [] = $declaration;

            // Handle CSS shorthands, e.g. 'border'
            if ( $declaration->isShorthand () === true ) {
                $declaration->explode ($this->declarations);
            }

            $pos = $semi + 1;
        }
    }

    public function toString () {
        $returnString = '';
        foreach ($this->selectors as $selector) {
            $returnString .= $selector.' ';
        }
        $returnString .= "{\n";
        foreach ($this->declarations as $declaration) {
            $returnString .= '  '.$declaration->getProperty ().':'.$declaration->getValue ().";\n";
        }
        $returnString .= "}\n";
        return $returnString;
    }

    public function matches ($element, $classString) {
        $classes = array ();

        $matches = 0;
        $classes = explode (' ', $classString);

        foreach ($this->selectors as $selector) {
            foreach ($classes as $class) {
                if ( $selector [0] == '.' && $selector == '.'.$class ) {
                    $matches++;
                    break;
                } else {
                    if ( $selector == $element || $selector == $element.'.'.$class ) {
                        $matches++;
                        break;
                    }
                }
            }
        }

        // We only got a match if all selectors were matched
        if ( $matches == count($this->selectors) ) {
            // Return the number of matched selectors
            // This enables the caller to choose the most specific rule
            return $matches;
        }

        return false;
    }

    public function getProperty ($name) {
        foreach ($this->declarations as $declaration) {
            if ( $name == $declaration->getProperty () ) {
                return $declaration->getValue ();
            }
        }
        return NULL;
    }

    public function getProperties (&$values) {
        foreach ($this->declarations as $declaration) {
            $property = $declaration->getProperty ();
            $value = $declaration->getValue ();
            $values [$property] = $value;
        }
        return NULL;
    }
}

class helper_plugin_odt_cssimport extends DokuWiki_Plugin {
    protected $replacements = array();
    protected $raw;
    protected $rules = array ();

    function importFrom($filename) {
        // Try to read in the file content
        if ( empty($filename) ) {
            return false;
        }
        
        $handle = fopen($filename, "rb");
        if ( $handle === false ) {
            return false;
        }

        $contents = fread($handle, filesize($filename));
        fclose($handle);
        if ( $contents === false ) {
            return false;
        }

        // Delete all comments first
        $pos = 0;
        $max = strlen ($contents);
        $in_comment = false;
        while ( $pos < $max ) {
            if ( ($pos+1) < $max &&
                 $contents [$pos] == '/' &&
                 $contents [$pos+1] == '*' ) {
                $in_comment = true;

                $contents [$pos] = ' ';
                $contents [$pos+1] = ' ';
                $pos += 2;
                continue;
            }
            if ( ($pos+1) < $max &&
                 $contents [$pos] == '*' &&
                 $contents [$pos+1] == '/' &&
                 $in_comment === true ) {
                $in_comment = false;

                $contents [$pos] = ' ';
                $contents [$pos+1] = ' ';
                $pos += 2;
                continue;
            }
            if ( $in_comment === true ) {
                $contents [$pos] = ' ';
            }
            $pos++;
        }
        
        // Find all CSS rules
        $pos = 0;
        while ( $pos < $max ) {
            $bracket_open = strpos ($contents, '{', $pos);
            if ( $bracket_open === false ) {
                break;
            }
            $bracket_close = strpos ($contents, '}', $bracket_open);
            if ( $bracket_close === false ) {
                break;
            }

            // Our selectors area is all the part before the open bracket
            // and the last closing bracket.
            $selectors_area = substr ($contents, $pos, $bracket_open - $pos);
            $selectors = split (',', $selectors_area);

            $decls = substr ($contents, $bracket_open + 1, $bracket_close - $bracket_open);

            // Create a own, new rule for every selector
            foreach ( $selectors as $selector ) {
                $selector = trim ($selector);
                $this->rules [] = new css_rule ($selector, $decls);
            }
            
            $pos = $bracket_close + 1;
        }
        return true;
    }

    function loadReplacements($filename) {
        // Try to read in the file content
        if ( empty($filename) ) {
            return false;
        }

        $handle = fopen($filename, "rb");
        if ( $handle === false ) {
            return false;
        }

        $filesize = filesize($filename);
        $contents = fread($handle, $filesize);
        fclose($handle);
        if ( $contents === false ) {
            return false;
        }

        // Delete all comments first
        $contents = preg_replace ('/;.*/', ' ', $contents);

        // Find the start of the replacements section
        $rep_start = strpos ($contents, '[replacements]');
        if ( $rep_start === false ) {
            break;
        }
        $rep_start += strlen ('[replacements]');

        // Find the end of the replacements section
        // (The end is either the next section or the end of file)
        $rep_end = strpos ($contents, '[', $rep_start);
        if ( $rep_end === false ) {
            $rep_end = $filesize - 1;
        }

        // Find all replacment definitions
        $defs = substr ($contents, $rep_start, $rep_end - $rep_start);
        $defs_end = strlen ($defs);

        $def_pos = 0;
        while ( $def_pos < $defs_end ) {
            $linestart = strpos ($defs, "\n", $def_pos);
            if ( $linestart === false ) {
                break;
            }
            $linestart += strlen ("\n");

            $lineend = strpos ($defs, "\n", $linestart);
            if ( $lineend === false ) {
                $lineend = $def_end;
            }

            $equal_sign = strpos ($defs, '=', $linestart);
            if ( $equal_sign === false || $equal_sign > $lineend ) {
                $def_pos = $linestart;
                continue;
            }

            $quote_start = strpos ($defs, '"', $equal_sign + 1);
            if ( $quote_start === false || $quote_start > $lineend ) {
                $def_pos = $linestart;
                continue;
            }

            $quote_end = strpos ($defs, '"', $quote_start + 1);
            if ( $quote_end === false || $quote_start > $lineend) {
                $def_pos = $linestart;
                continue;
            }
            if ( $quote_end - $quote_start < 2 ) {
                $def_pos = $linestart;
                continue;
            }
                
            $replacement = substr ($defs, $linestart, $equal_sign - $linestart);
            $value = substr ($defs, $quote_start + 1, $quote_end - ($quote_start + 1));
            $replacement = trim($replacement);
            $value = trim($value);

            $this->replacements [$replacement] = $value;

            $def_pos = $lineend;
        }

        return true;
    }

    public function getRaw () {
        return $this->raw;
    }

    public function getReplacement ($name) {
        return $this->replacements [$name];
    }

    public function getPropertyForElement ($element, $classString, $name) {
        if ( empty ($name) === true ) {
            return NULL;
        }

        $value = NULL;
        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $classString);
            if ( $matched !== false ) {
                $current = $rule->getProperty ($name);
                if ( empty ($current) === false ) {
                    $value = $current;
                }
            }
        }

        return $value;
    }

    public function getProperty ($classString, $name) {
        if ( empty ($classString) === true || empty ($name) === true ) {
            return NULL;
        }

        $value = $this->getPropertyForElement (NULL, $classString, $name);
        return $value;
    }

    public function getPropertiesForElement (&$dest, $element, $classString) {
        if ( empty ($element) === true && empty ($classString) === true ) {
            return;
        }

        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $classString);
            if ( $matched !== false ) {
                $rule->getProperties ($dest);
            }
        }
    }

    public function adjustValueForODT ($value, $emValue = 0) {
        $values = preg_split ('/\s+/', $value);
        $value = '';
        foreach ($values as $part) {
            // Replace it if necessary
            $part = trim($part);
            $rep = $this->getReplacement($part);
            if ( empty ($rep) === false ) {
                $part = $rep;
            }
            $length = strlen ($part);

            // If it is a short color value (#xxx) then convert it to long value (#xxxxxx)
            // (ODT does not support the short form)
            if ( $part [0] == '#' && $length == 4 ) {
                $part = '#'.$part [1].$part [1].$part [2].$part [2].$part [3].$part [3];
            } else {
                // If it is a CSS color name, get it's real color value
                $odt_colors = plugin_load('helper', 'odt_csscolors');
                $color = $odt_colors->getColorValue ($part);
                if ( $color != '#000000' ) {
                    $part = $color;
                }
            }

            if ( $length > 2 && $part [$length-2] == 'e' && $part [$length-1] == 'm' ) {
                $number = substr ($part, 0, $length-2);
                if ( is_numeric ($number) === true && empty ($emValue) === false ) {
                    $part = ($number * $emValue).'pt';
                }
            }

            // Replace px with pt (px does not seem to be supported by ODT)
            if ( $length > 2 && $part [$length-2] == 'p' && $part [$length-1] == 'x' ) {
                $part [$length-1] = 't';
            }

            $value .= ' '.$part;
        }
        $value = trim($value);

        return $value;
    }

    public function rulesToString () {
        $returnString = '';
        foreach ($this->rules as $rule) {
            $returnString .= $rule->toString ();
        }
        return $returnString;
    }

    public function replaceURLPrefix ($URL, $replacement) {
        if ( empty ($URL) === false && empty ($replacement) === false ) {
            // Replace 'url(...)' with $replacement
            $URL = substr ($URL, 3);
            $URL = trim ($URL, '()');
            $URL = $replacement.$URL;
        }
        return $URL;
    }
}
?>
