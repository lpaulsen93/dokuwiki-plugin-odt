<?php
/**
 * Helper class to read in a CSS style
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class css_attribute_selector.
 * Simple storage class to save exactly one CSS attribute selector.
 */
class css_attribute_selector {
    protected $namespaze = NULL;
    protected $attribute = NULL;
    protected $operator = NULL;
    protected $value = NULL;

    public function __construct($attribute_string) {
        $attribute_string = trim ($attribute_string, '[] ');
        $found = strpos ($attribute_string, '|');
        if ($found !== false &&
            $attribute_string [$found+1] == '=') {
            $found = strpos ($attribute_string, '|', $found+1);
        }
        if ($found !== false) {
            if ($found > 0) {
                $this->namespaze = substr ($attribute_string, 0, $found);
            }
            $attribute_string = substr ($attribute_string, $found + 1);
        }
        $found = strpos ($attribute_string, '=');
        if ($found === false) {
            $this->attribute = $attribute_string;
        } else {
            if (ctype_alpha($attribute_string [$found-1])) {
                $this->attribute = substr($attribute_string, 0, $found);
                $this->operator = '=';
                $this->value = substr($attribute_string, $found + 1);
            } else {
                $this->attribute = substr($attribute_string, 0, $found - 1);
                $this->operator = $attribute_string [$found-1].$attribute_string [$found];
                $this->value = substr($attribute_string, $found + 1);
            }
            $this->value = trim ($this->value, '"');
        }
    }
    
    public function matches (array $attributes) {
        if ($this->operator == NULL) {
            // Attribute should be present
            return array_key_exists($this->attribute, $attributes);
        } else {
            switch ($this->operator) {
                case '=':
                    // Attribute should have exactly the value $this->value
                    if ($attributes [$this->attribute] == $this->value) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case '~=':
                    // Attribute value should contain the word $this->value
                    $words = preg_split ('/\s/', $attributes [$this->attribute]);
                    if (array_search($this->value, $words) !== false) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case '|=':
                    // Attribute value should contain the word $this->value
                    // or a word starting with $this->value.'-'
                    $with_hypen = $this->value.'-';
                    $length = strlen ($with_hypen);
                    if ($attributes [$this->attribute] == $this->value ||
                        strncmp($attributes [$this->attribute], $with_hypen, $length) == 0) {
                        return true;
                    }
                    break;

                case '^=':
                    // Attribute value should contain
                    // a word starting with $this->value
                    $length = strlen ($this->value);
                    if (strncmp($attributes [$this->attribute], $this->value, $length) == 0) {
                        return true;
                    }
                    break;

                case '$=':
                    // Attribute value should contain
                    // a word ending with $this->value
                    $length = -1 * strlen ($this->value);
                    if (substr($attributes [$this->attribute], $length) == $this->value) {
                        return true;
                    }
                    break;

                case '*=':
                    // Attribute value should include $this->value
                    if (strpos($attributes [$this->attribute], $this->value) !== false) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    public function toString () {
        $returnstring = '[';
        if (!empty($this->namespaze)) {
            $returnstring .= $this->namespaze.'|';
        }
        $returnstring .= $this->attribute.$this->operator.$this->value;
        $returnstring = ']';
        return $returnstring;
    }
}

/**
 * Class css_simple_selector
 */
class css_simple_selector {
    protected $type = NULL;
    protected $pseudo_element = NULL;
    protected $id = NULL;
    protected $classes = array();
    protected $pseudo_classes = array();
    protected $attributes = array();
    protected $specificity = 0;
    
    protected function isSpecialSign ($sign) {
        switch ($sign) {
            case '.':
            case '[':
            case '#':
            case ':':
                return true;
        }
        return false;
    }
    
    public function __construct($simple_selector_string) {
        $pos = 0;
        $max = strlen ($simple_selector_string);
        if ($max == 0) {
            $this->type = '*';
            return;
        }

        $a = 0;
        $b = 0;
        $c = 0;

        $content = '';
        $first_sign = '';
        $first = true;
        $pseudo_element = false;
        while ($pos < $max) {
            $sign = $simple_selector_string [$pos];
            if ($this->isSpecialSign ($sign)) {
                if ($pos == 0) {
                    $first_sign = $sign;
                } else {
                    // Found the end.
                    if (empty($first_sign)) {
                        // Element name/type
                        $this->type = $content;
                        if ($content != '*') {
                            $c++;
                        }
                    } else if ($first_sign == '.') {
                        // Class
                        $this->classes[] = $content;
                        $b++;
                    } else if ($first_sign == '#') {
                        // ID
                        $this->id = $content;
                        $a++;
                    } else if ($first_sign == ':') {
                        //if ($next_sign != ':') {
                        if (!$pseudo_element) {
                            // Pseudo class
                            $this->pseudo_classes[] = $content;
                            $b++;
                        } else {
                            // Pseudo element
                            $this->pseudo_element = $content;
                            $c++;
                        }
                    } else if ($first_sign == '[') {
                        $this->attributes [] = new css_attribute_selector($content);
                        $b++;
                    }
                    $first_sign = $sign;
                    $next_sign = $simple_selector_string [$pos+1];
                    if ($first_sign == ':' && $next_sign == ':') {
                        $pseudo_element = true;
                        $pos++;
                    } else {
                        $pseudo_element = false;
                    }
                    $content = '';
                }
            } else {
                $content .= $sign;
            }
            $pos++;
        }

        // If $content is not empty then parse it
        if (!empty($content)) {
            if (empty($first_sign)) {
                // Element name/type
                $this->type = $content;
                if ($content != '*') {
                    $c++;
                }
            } else if ($first_sign == '.') {
                // Class
                $this->classes[] = $content;
                $b++;
            } else if ($first_sign == '#') {
                // ID
                $this->id = $content;
                $a++;
            } else if ($first_sign == ':') {
                if ($next_sign != ':') {
                    // Pseudo class
                    $this->pseudo_classes[] = $content;
                    $b++;
                } else {
                    // Pseudo element
                    $this->pseudo_element = $content;
                    $c++;
                }
            } else if ($first_sign == '[') {
                $this->attributes [] = new css_attribute_selector($content);
                $b++;
            }
        }
        
        // Calculate specificity
        $this->specificity = $a * 100 + $b *10 + $c;
    }

    public function matches_entry (iElementCSSMatchable $element) {
        $element_attrs = $element->iECSSM_getAttributes();
        
        // Match type/element
        if (!empty($this->type) &&
            $this->type != '*' &&
            $this->type != $element->iECSSM_getName()) {
            return false;
        }
        
        // Match class(es)
        if (count($this->classes) > 0) {
            if (empty($element_attrs ['class'])) {
                return false;
            }
            $comp = explode (' ', $element_attrs ['class']);
            foreach ($this->classes as $search) {
                if (array_search($search, $comp) === false) {
                    return false;
                }
            }
        }

        // Match id
        if (!empty($this->id) &&
            $this->id != $element_attrs ['id']) {
            return false;
        }

        // Match attributes
        foreach ($this->attributes as $attr_sel) {
            if ($attr_sel->matches ($element_attrs) === false) {
                return false;
            }
        }

        // Match pseudo class(es)
        if (count($this->pseudo_classes) > 0) {
            foreach ($this->pseudo_classes as $search) {
                if ($element->iECSSM_has_pseudo_class($search) == false) {
                    return false;
                }
            }
        }

        // Match pseudo element
        if (!empty($this->pseudo_element)) {
            if ($element->iECSSM_has_pseudo_element($this->pseudo_element) == false) {
                return false;
            }
        }

        return true;
    }

    public function toString () {
        $returnstring = '';
        if (!empty($this->type)) {
            $returnstring .= $this->type;
        }
        if (!empty($this->id)) {
            $returnstring .= '#'.$this->id;
        }
        foreach ($this->classes as $class) {
            $returnstring .= '.'.$class;
        }
        foreach ($this->attributes as $attr_sel) {
            $returnstring .= $attr_sel->toString();
        }
        return $returnstring;
    }

    public function getSpecificity () {
        return $this->specificity;
    }    
}

/**
 * Class css_selector
 */
class css_selector {
    static protected $combinators = ' ,>+~';
    static protected $brackets = '[]';
    protected $selector_string = NULL;
    protected $selectors_parsed = array();
    protected $specificity = array();

    /**
     * @param $selector_string
     */
    public function __construct($selector_string) {
        $this->selector_string = trim($selector_string, ' ');
        
        $pos = 0;
        $max = strlen($this->selector_string);
        $current = '';
        $selector = array();
        $specificity = 0;
        $size = 0;
        $in_brackets = false;
        $separators = self::$combinators.self::$brackets;
        while ($pos < $max) {
            $sign = $this->selector_string [$pos];
            $result = strpos ($separators, $sign);
            if ($sign == '[') {
                $in_brackets = true;
            }
            if ($result === false || $in_brackets == true) {
                // No combinator
                $current .= $sign;
                $pos++;

                if ($sign == ']') {
                    $in_brackets = false;
                }
            } else {
                // Parse current
                $selector [$size]['selector'] = new css_simple_selector($current);
                $specificity += $selector [$size]['selector']->getSpecificity();
                $size++;
                $current = '';

                $combinator = $sign;
                $pos++;
                while ($pos < $max) {
                    $sign = $this->selector_string[$pos];
                    if (strpos (self::$combinators, $sign) === false) {
                        break;
                    }
                    $combinator .= $sign;
                    $pos++;
                }
                if (ctype_space($combinator)) {
                    $selector [$size]['combinator'] = ' ';
                    $size++;
                } else {
                    $combinator = trim ($combinator, ' ');
                    if ($combinator != ',') {
                        $selector [$size]['combinator'] = $combinator[0];
                        $size++;
                    } else {
                        $this->selectors_parsed [] = $selector;
                        $this->specificity [] = $specificity;
                        $selector = array();
                        $size = 0;
                        $specificity = 0;
                    }
                }
            }
        }
        if (!empty($current)) {
            $selector [$size]['selector'] = new css_simple_selector($current);
            $specificity += $selector [$size]['selector']->getSpecificity();
            $this->selectors_parsed [] = $selector;
            $this->specificity [] = $specificity;
        }
    }

    protected function selector_matches (array $selector, iElementCSSMatchable $element) {
        $combinator = '';
        $found = 0;
        $size = count($selector);
        if ($size == 0 ) {
            return false;
        }

        // First entry should be a selector
        if ($selector [$size-1]['selector'] == NULL) {
            // No! (Error)
            return false;
        }

        // Start comparison with the current element
        $simple = $selector [$size-1]['selector'];
        if ($simple->matches_entry ($element) == false) {
            // If the current open element does not match then there is no match
            return false;
        }
        if ($size == 1) {
            // We are finished already
            return true;
        }
        
        // Next entry should be a combinator
        if ($selector [$size-2]['combinator'] == NULL) {
            // No! (Error)
            return false;
        }
        $combinator = $selector [$size-2]['combinator'];
                
        $start_search = $element;
        for ($index = $size-3 ; $index >= 0 ; $index--) {
            // If we get here but start_search is already negative then there are
            // selectors left but no more subjects/element to match.
            if ($start_search < 0) {
                return false;
            }
            if (empty($selector [$index]['combinator'])) {
                $simple = $selector [$index]['selector'];
                switch ($combinator) {
                    case ' ':
                        // Find any parent, parent's parent... that matches our simple selector
                        do {
                            $parent = $start_search->iECSSM_getParent();
                            if ($parent === NULL) {
                                return false;
                            }
                            $start_search = $parent;
                            $is_match = $simple->matches_entry ($parent);
                            if ($is_match == true) {
                                // Found match. Stop this search.
                                break;
                            }
                        }while ($parent !== NULL);
                        
                        // Did we find anything?
                        if (!$is_match) {
                            // No.
                            return false;
                        }
                        $start_search = $parent;
                        break;

                    case '>':
                        // Check if we have a parent and if it matches our simple selector
                        $parent = $start_search->iECSSM_getParent();
                        if ($parent === NULL) {
                            return false;
                        }
                        if ($simple->matches_entry ($parent) == false) {
                            // No match.
                            return false;
                        }
                        $start_search = $parent;
                        break;

                    case '+':
                        // Immediate preceding sibling must match our simple selector
                        $sibling = $start_search->iECSSM_getPrecedingSibling();
                        if ($sibling === NULL) {
                            return false;
                        }
                        if ($simple->matches_entry ($sibling) == false) {
                            // No match.
                            return false;
                        }
                        $start_search = $sibling;
                        break;

                    case '~':
                        // One of the preceding siblings must match our simple selector
                        do {
                            $sibling = $start_search->iECSSM_getPrecedingSibling();
                            if ($sibling === NULL) {
                                return false;
                            }
                            $start_search = $sibling;
                            if ($simple->matches_entry ($sibling) == true) {
                                // Found match. Stop this search.
                                break;
                            }
                        }while ($sibling !== NULL);
                        
                        // Did we find anything?
                        if ($sibling === NULL) {
                            // No.
                            return false;
                        }
                        $start_search = $sibling;
                        break;

                    // We won't get the combinator ',' here cause that is
                    // handled at construction time by creating an array of selectors
                    //case ',':
                    //    break;
                }
            } else {
                $combinator = $selector [$index]['combinator'];
            }
        }
        
        // If we get here then everything matches!
        return true;
    }
    
    public function matches (iElementCSSMatchable $element, &$specificity) {
        $size = count ($this->selectors_parsed);
        $match = false;
        $specificity = 0;
        for ($index = 0 ; $index < $size ; $index++) {
            if ($this->selector_matches ($this->selectors_parsed [$index], $element) == true) {
                if ($this->specificity [$index] > $specificity) {
                    $specificity = $this->specificity [$index];
                }
                $match = true;
            }
        }
        return $match;
    }

    public function toString () {
        $returnstring = '';
        foreach ($this->selectors_parsed as $selector) {
            $size = count($selector);
            for ($index = 0 ; $index < $size ; $index++) {
                if ($selector [$index]['combinator'] !== NULL ) {
                    if ($selector [$index]['combinator'] == ' ') {
                        $returnstring .= ' ';
                    } else {
                        $returnstring .= ' '.$selector [$index]['combinator'].' ';
                    }
                } else {
                    $simple = $selector [$index]['selector'];
                    $returnstring .= $simple->toString();
                }
            }
        }
        return $returnstring;
    }
}

/**
 * Class css_rule_new
 */
class css_rule_new {
    protected $media = NULL;
    protected $selector = NULL;
    /** @var css_declaration[]  */
    protected $declarations = array ();

    /**
     * @param $selector
     * @param $decls
     * @param null $media
     */
    public function __construct($selector, $decls, $media = NULL) {

        $this->media = trim ($media);
        //print ("\nNew rule: ".$media."\n"); //Debuging

        // Create/parse selector
        $this->selector = new css_selector ($selector);

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

    /**
     * @return string
     */
    public function toString () {
        $returnString = '';
        $returnString .= "Media= \"".$this->media."\"\n";
        $returnString .= $this->selector->toString().' ';
        $returnString .= "{\n";
        foreach ($this->declarations as $declaration) {
            $returnString .= '  '.$declaration->getProperty ().':'.$declaration->getValue ().";\n";
        }
        $returnString .= "}\n";
        return $returnString;
    }

    /**
     * @param $element
     * @param $classString
     * @param null $media
     * @return bool|int
     */
    public function matches (iElementCSSMatchable $element, &$specificity, $media = NULL) {

        $media = trim ($media);
        if ( !empty($this->media) && $media != $this->media ) {
            // Wrong media
            //print ("\nNo-Match ".$this->media."==".$media); //Debuging
            return false;
        }

        // The rules does match if the selector does match
        $result = $this->selector->matches($element, $specificity);

        return $result;
    }

    /**
     * @param $name
     * @return null
     */
    public function getProperty ($name) {
        foreach ($this->declarations as $declaration) {
            if ( $name == $declaration->getProperty () ) {
                return $declaration->getValue ();
            }
        }
        return NULL;
    }

    /**
     * @param $values
     * @return null
     */
    public function getProperties (&$values) {
        foreach ($this->declarations as $declaration) {
            $property = $declaration->getProperty ();
            $value = $declaration->getValue ();
            $values [$property] = $value;
        }
        return NULL;
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->declarations as $declaration) {
            $declaration->adjustLengthValues ($callback);
        }
    }
}

/**
 * Class helper_plugin_odt_cssimport
 */
class helper_plugin_odt_cssimportnew extends DokuWiki_Plugin {
    protected $replacements = array();
    protected $raw;
    /** @var css_rule_new[]  */
    protected $rules = array ();
    protected $media = NULL;

    /**
     * @param $contents
     * @return bool
     */
    function importFromString($contents) {
        $this->deleteComments ($contents);
        return $this->importFromStringInternal ($contents);
    }

    /**
     * Delete comments in $contents. All comments are overwritten with spaces.
     * The '&' is required. DO NOT DELETE!!!
     * @param $contents
     */
    protected function deleteComments (&$contents) {
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
    }

    public function setMedia ($media) {
        $this->media = $media;
    }

    public function getMedia () {
        return $this->media;
    }

    /**
     * @param $contents
     * @param null $media
     * @return bool
     */
    protected function importFromStringInternal($contents, $media = NULL, &$processed = NULL) {
        // Find all CSS rules
        $pos = 0;
        $max = strlen ($contents);
        while ( $pos < $max ) {
            $bracket_open = strpos ($contents, '{', $pos);
            if ( $bracket_open === false ) {
                return false;
            }
            $bracket_close = strpos ($contents, '}', $pos);
            if ( $bracket_close === false ) {
                return false;
            }

            // If this is a nested call we might hit a closing } for the media section
            // which was the reason for this function call. In this case break and return.
            if ( $bracket_close < $bracket_open ) {
                $pos = $bracket_close + 1;
                break;
            }

            // Get the part before the open bracket and the last closing bracket
            // (or the start of the string).
            $before_open_bracket = substr ($contents, $pos, $bracket_open - $pos);

            // Is it a @media rule?
            $before_open_bracket = trim ($before_open_bracket);
            $mediapos = stripos($before_open_bracket, '@media');
            if ( $mediapos !== false ) {

                // Yes, decode content as normal rules with @media ... { ... }
                //$new_media = substr_replace ($before_open_bracket, NULL, $mediapos, strlen ('@media'));
                $new_media = substr ($before_open_bracket, $mediapos + strlen ('@media'));
                $contents_in_media = substr ($contents, $bracket_open + 1);

                $nested_processed = 0;
                $result = $this->importFromStringInternal ($contents_in_media, $new_media, $nested_processed);
                if ( $result !== true ) {
                    // Stop parsing on error.
                    return false;
                }
                unset ($new_media);
                $pos = $bracket_open + 1 + $nested_processed;
            } else {

                // No, decode rule the normal way selector { ... }
                $selectors = explode (',', $before_open_bracket);

                $decls = substr ($contents, $bracket_open + 1, $bracket_close - $bracket_open);

                // Create a own, new rule for every selector
                foreach ( $selectors as $selector ) {
                    $selector = trim ($selector);
                    $this->rules [] = new css_rule_new ($selector, $decls, $media);
                }

                $pos = $bracket_close + 1;
            }
        }
        if ( $processed !== NULL ) {
            $processed = $pos;
        }
        return true;
    }

    /**
     * @param $filename
     * @return bool|void
     */
    function importFromFile($filename) {
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

        return $this->importFromString ($contents);
    }

    /**
     * @return mixed
     */
    public function getRaw () {
        return $this->raw;
    }

    /**
     * @param $element
     * @param $classString
     * @param $name
     * @param null $media
     * @return null
     */
    public function getPropertyForElement ($name, iElementCSSMatchable $element) {
        if ( empty ($name) ) {
            return NULL;
        }

        $value = NULL;
        $highest = 0;
        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $specificity, $this->media);
            if ( $matched !== false ) {
                $current = $rule->getProperty ($name);

                // Only accept the property value if the current specificity of the matched
                // rule/selector is higher or equal than the highest one.
                if ( !empty ($current) && $specificity >= $highest) {
                    $highest = $specificity;
                    $value = $current;
                }
            }
        }

        return $value;
    }

    /**
     * @param $dest
     * @param $element
     * @param $classString
     * @param null $media
     */
    public function getPropertiesForElement (&$dest, iElementCSSMatchable $element) {
        if ($element == NULL) {
            return;
        }

        $highest = array();
        $temp = array();
        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $specificity, $this->media);
            if ( $matched !== false ) {
                $current = array();
                $rule->getProperties ($current);
                
                // Only accept a property value if the current specificity of the matched
                // rule/selector is higher or equal than the highest one.
                foreach ($current as $property => $value) {
                    if ($specificity >= $highest [$property]) {
                        $highest [$property] = $specificity;
                        $temp [$property] = $value;
                    }
                }
            }
        }

        // Add inline style properties if present (always have highest specificity):
        // Create rule with selector '*' (doesn't matter) and inline style declarations
        $attributes = $element->iECSSM_getAttributes();
        if (!empty($attributes ['style'])) {
            $rule = new css_rule ('*', $attributes ['style']);
            $rule->getProperties ($temp);
        }

        $dest = $temp;
    }

    /**
     * @return string
     */
    public function rulesToString () {
        $returnString = '';
        foreach ($this->rules as $rule) {
            $returnString .= $rule->toString ();
        }
        return $returnString;
    }

    /**
     * @param $URL
     * @param $replacement
     * @return string
     */
    public function replaceURLPrefix ($URL, $replacement) {
        if ( !empty ($URL) && !empty ($replacement) ) {
            // Replace 'url(...)' with $replacement
            $URL = substr ($URL, 3);
            $URL = trim ($URL, '()');
            $URL = $replacement.$URL;
        }
        return $URL;
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->rules as $rule) {
            $rule->adjustLengthValues ($callback);
        }
    }
}
