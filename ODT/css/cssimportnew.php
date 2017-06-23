<?php
/**
 * Class for importing and using CSS (new version).
 * Partly uses code from the old version, e.g. css_declaration.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/**
 * Class css_attribute_selector.
 * Simple storage class to save exactly one CSS attribute selector.
 * 
 * @package CSS\CSSAttributeSelector
 */
class css_attribute_selector {
    /** var The namespace to which this attribute selector belongs */
    protected $namespaze = NULL;
    /** var The attribute name */
    protected $attribute = NULL;
    /** var The attribute selector operator */
    protected $operator = NULL;
    /** var The attribute selector value */
    protected $value = NULL;

    /**
     * Construct the selector from $attribute_string.
     * 
     * @param    string $attribute_string String containing the selector
     */
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
    
    /**
     * The function checks if this atrribute selector matches the
     * attributes given in $attributes as key - value pairs.
     * 
     * @param    string $attributes String containing the selector
     * @return   boolean
     */
    public function matches (array $attributes=NULL) {
        if ($this->operator == NULL) {
            // Attribute should be present
            return isset($attributes) && array_key_exists($this->attribute, $attributes);
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

    /**
     * The function returns a string representation of this attribute
     * selector (only for debugging purpose).
     * 
     * @return   string
     */
    public function toString () {
        $returnstring = '[';
        if (!empty($this->namespaze)) {
            $returnstring .= $this->namespaze.'|';
        }
        $returnstring .= $this->attribute.$this->operator.$this->value;
        $returnstring .= ']';
        return $returnstring;
    }
}

/**
 * Class css_simple_selector
 * Simple storage class to save a simple CSS selector.
 * 
 * @package CSS\CSSSimpleSelector
 */
class css_simple_selector {
    /** var Element name/Type of this simple selector */
    protected $type = NULL;
    /** var Pseudo element which this selector matches */
    protected $pseudo_element = NULL;
    /** var Id which this selector matches */
    protected $id = NULL;
    /** var Classes which this selector matches */
    protected $classes = array();
    /** var Pseudo classes which this selector matches */
    protected $pseudo_classes = array();
    /** var Attributes which this selector matches */
    protected $attributes = array();
    /** var Specificity of this selector */
    protected $specificity = 0;
    
    /**
     * Internal function that checks if $sign is a sign that
     * separates/identifies the different parts of an simple selector.
     * 
     * @param character $sign
     */
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
    
    /**
     * Construct the simple selector from $simple_selector_string.
     * 
     * @param    string $simple_selector_string String containing the selector
     */
    public function __construct($simple_selector_string) {
        $pos = 0;
        $simple_selector_string = trim ($simple_selector_string);
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

    /**
     * The functions checks wheter this simple selector matches the given
     * $element or not. $element must support the interface iElementCSSMatchable
     * to enable this class to do the CSS selector matching.
     * 
     * @param    iElementCSSMatchable $element Element to check
     * @return   boolean
     */
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

    /**
     * The function returns a string representation of this simple
     * selector (only for debugging purpose).
     * 
     * @return   string
     */
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

    /**
     * Return the specificity of this simple selector.
     * 
     * @return   integer
     */
    public function getSpecificity () {
        return $this->specificity;
    }    
}

/**
 * Class css_selector.
 * Storage class to save a complete CSS selector.
 * The class can also store multiple selectors, e.g. like 'h1 , h2, h3 {...}'
 * 
 * @package CSS\CSSSelector
 */
class css_selector {
    /** var Known combinators */
    static protected $combinators = ' ,>+~';
    /** var Brackets */
    static protected $brackets = '[]';
    /** var String from which this selector was created */
    protected $selector_string = NULL;
    /** var Array with parsed selector(s) */
    protected $selectors_parsed = array();
    /** var Specificity of this selector */
    protected $specificity = array();

    /**
     * Construct the selector from $selector_string.
     * 
     * @param    string $selector_string String containing the selector
     */
    public function __construct($selector_string) {
        $selector_string = str_replace("\n", '', $selector_string);
        $this->selector_string = trim($selector_string);
        
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

    /**
     * The function checks if the combined simple selectors in $selector
     * match $element or not. $element must support the interface iElementCSSMatchable
     * to enable this class to do the CSS selector matching.
     * 
     * @param    array                $selector Internal selector array
     * @param    iElementCSSMatchable $element  Element to check
     * @return   boolean
     */
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
    
    /**
     * The functions checks wheter any selector stored in this object
     * match the given $element or not. $element must support the interface
     * iElementCSSMatchable to enable this class to do the CSS selector matching.
     * 
     * @param    iElementCSSMatchable $element     Element to check
     * @param    integer              $specificity Specificity of matching selector
     * @return   boolean
     */
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

    /**
     * The function returns a string representation of this
     * selector (only for debugging purpose).
     * 
     * @return   string
     */
    public function toString () {
        $returnstring = '';
        $max = count($this->selectors_parsed);
        $index_parsed = 0;
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
                    if ($index < $size-1) {
                        $returnstring .= ' ';
                    }
                }
            }
            $index_parsed++;
            if ($index_parsed < $max) {
                $returnstring .= ',';
            }
        }
        return $returnstring;
    }
}

/**
 * Class css_rule_new.
 * 
 * @package CSS\CSSRuleNew
 */
class css_rule_new {
    /** @var Media selector to which this rule belongs */
    protected $media = NULL;
    /** @var Selector string from which this rule was created */
    protected $selector = NULL;
    /** @var Array of css_declaration objects */
    protected $declarations = array ();

    /**
     * Construct rule from strings $selector and $decls.
     * 
     * @param    string      $selector String containing the selector
     * @param    string      $decls    String containing the declarations
     * @param    string|null $media    String containing the media selector
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
     * The function returns a string representation of this
     * rule (only for debugging purpose).
     * 
     * @return   string
     */
    public function toString () {
        $returnString = '';
        $returnString .= "Media= \"".$this->media."\"\n";
        $returnString .= $this->selector->toString().' ';
        $returnString .= "{\n";
        foreach ($this->declarations as $declaration) {
            $returnString .= $declaration->getProperty ().':'.$declaration->getValue ().";\n";
        }
        $returnString .= "}\n";
        return $returnString;
    }

    /**
     * The functions checks wheter this rule matches the given $element
     * or not. $element must support the interface iElementCSSMatchable
     * to enable this class to do the CSS selector matching.
     * 
     * @param    iElementCSSMatchable $element     Element to check
     * @param    integer              $specificity Specificity of matching selector
     * @param    string               $media       Media selector to match
     * @return   boolean
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
     * The function returns the value of property $name or null if a
     * property with that name does not exist in this rule.
     * 
     * @param    string $name    The property name
     * @return string|null
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
     * The function stores all properties of this rule in the array
     * $values as key - value pairs, e.g. $values ['color'] = 'red';
     * 
     * @param    array $values    Array for property storage
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
     * The function calls $callback for each property stored in this
     * rule containing a length value. The return value of $callback
     * is saved as the new property value.
     * 
     * @param    callable $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->declarations as $declaration) {
            $declaration->adjustLengthValues ($callback, $this);
        }
    }

    /**
     * The function calls $callback for each property stored in this
     * rule containing a URL reference. The return value of $callback
     * is saved as the new property value.
     * 
     * @param    callable $callback
     */
    public function replaceURLPrefixes ($callback) {
        foreach ($this->declarations as $declaration) {
            $declaration->replaceURLPrefixes ($callback);
        }
    }
}

/**
 * Class cssimportnew
 * 
 * @package CSS\CSSImportNew
 */
class cssimportnew {
    /** var Imported raw CSS code */
    protected $raw;
    /** @var Array of css_rule_new  */
    protected $rules = array ();
    /** @var Actually set media selector */    
    protected $media = NULL;

    /**
     * Import CSS code from string $contents.
     * Returns true on success or false if any error occured during CSS parsing.
     * 
     * @param    string      $contents
     * @return boolean
     */
    function importFromString($contents) {
        $this->deleteComments ($contents);
        return $this->importFromStringInternal ($contents);
    }

    /**
     * Delete comments in $contents. All comments are overwritten with spaces.
     * The '&' is required. DO NOT DELETE!!!
     * 
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

    /**
     * Set the media selector to use for CSS matching to $media.
     * 
     * @param    string      $media
     */
    public function setMedia ($media) {
        $this->media = $media;
    }

    /**
     * Return the actually set media selector.
     * 
     * @return    string
     */
    public function getMedia () {
        return $this->media;
    }

    /**
     * Internal function that imports CSS code from string $contents.
     * (The function is calling itself recursively)
     * 
     * @param    string      $contents
     * @param    string|null $media     Actually valid media selector
     * @param    integer     $processed Position to which $contents were parsed
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
                // The selector is stored in $before_open_bracket
                $decls = substr ($contents, $bracket_open + 1, $bracket_close - $bracket_open);
                $this->rules [] = new css_rule_new ($before_open_bracket, $decls, $media);

                $pos = $bracket_close + 1;
            }
        }
        if ( $processed !== NULL ) {
            $processed = $pos;
        }
        return true;
    }

    /**
     * Import CSS code from file filename.
     * Returns true on success or false if any error occured during CSS parsing.
     * 
     * @param    string      $filename
     * @return boolean
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
     * Return the original CSS code that was imported.
     * 
     * @return string
     */
    public function getRaw () {
        return $this->raw;
    }

    /**
     * Get the value of CSS property for element $element.
     * If $element is not matched by any rule or the rule(s) matching
     * do not contain the property $name then null is returned.
     * 
     * @param    string               $name    Name of queried property
     * @param    iElementCSSMatchable $element Element to match
     * @return string|null
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
     * Get all properties for element $element and store them in $dest.
     * Properties are stored as key -value pairs, e.g. $dest ['color'] = 'red';
     * If $element is not matched by any rule then array $dest will be
     * empty (if it was empty before the call!).
     * 
     * @param    array                $dest    Property storage
     * @param    iElementCSSMatchable $element Element to match
     * @param    ODTUnits             $units   ODTUnits object for conversion
     * @param    boolean              $inherit Enable/disable inheritance
     * @return string|null
     */
    public function getPropertiesForElement (&$dest, iElementCSSMatchable $element, ODTUnits $units, $inherit=true) {
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

        if ($inherit) {
            // Now calculate absolute values and inherit values from parents
            $this->calculateAndInherit ($temp, $element, $units);
            unset($temp ['calculated']);
        }

        $dest = $temp;
    }

    /**
     * Get the value of CSS property for element $parent. If $parent has
     * no match for the property with name $key then return the value of
     * the property for $parent's parents.
     * 
     * @param    string               $key    Name of queried property
     * @param    iElementCSSMatchable $parent Element to match
     * @return string|null
     */
    protected function getParentsValue($key, iElementCSSMatchable $parent) {
        $properties = $parent->getProperties ();
        if ($properties [$key] != NULL) {
            return $properties [$key];
        }
        
        $parentsParent = $parent->iECSSM_getParent();
        if ($parentsParent != NULL) {
            return $this->getParentsValue($key, $parentsParent);
        }

        return NULL;
    }

    /**
     * The function calculates the absolute values for the relative
     * property values of element $element and store them in $properties.
     * 
     * @param    array                $properties Property storage
     * @param    iElementCSSMatchable $element    Element to match
     * @param    ODTUnits             $units   ODTUnits object for conversion
     */
    protected function calculate (array &$properties, iElementCSSMatchable $element, ODTUnits $units) {
        if ($properties ['calculated'] == '1') {
            // Already done
            return;
        }

        $properties ['calculated'] = '1';
        $parent = $element->iECSSM_getParent();

        // First get absolute font-size in points for
        // conversion of relative units
        if ($parent != NULL) {
            $font_size = $this->getParentsValue('font-size', $parent);
        }
        if ($font_size != NULL) {
            // Use the parents value
            // (It is assumed that the value is already calculated to an absolute
            //  value. That's why the loops in calculateAndInherit() must run backwards
            $base_font_size_in_pt = $units->getDigits($font_size);
        } else {
            // If there is no parent value use global setting
            $base_font_size_in_pt = $units->getPixelPerEm ().'px';
            $base_font_size_in_pt = $units->toPoints($base_font_size_in_pt, 'y');
            $base_font_size_in_pt = $units->getDigits($base_font_size_in_pt);
        }

        // Do we have font-size or line-height set?
        if ($properties ['font-size'] != NULL || $properties ['line-height'] != NULL) {
            if ($properties ['font-size'] != NULL) {
                $font_size_unit = $units->stripDigits($properties ['font-size']);
                $font_size_digits = $units->getDigits($properties ['font-size']);
                if ($font_size_unit == '%' || $font_size_unit == 'em') {
                    $base_font_size_in_pt = $units->getAbsoluteValue ($properties ['font-size'], $base_font_size_in_pt);
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

        // Calculate all other absolute values
        // (NOT 'width' as it depends on the encapsulating element,
        //  and not 'font-size' and 'line-height' => already done above
        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'width':
                case 'font-size':
                case 'line-height':
                    // Do nothing.
                break;
                case 'margin':
                case 'margin-left':
                case 'margin-right':
                case 'margin-top':
                case 'margin-bottom':
                    // Do nothing.
                    // We do not know the size of the surrounding element.
                break;
                default:
                    // Convert '%' or 'em' value based on determined font-size
                    $unit = $units->stripDigits($value);
                    if ($unit == '%' || $unit == 'em') {
                        $value = $units->getAbsoluteValue ($value, $base_font_size_in_pt);
                        $properties [$key] = $value.'pt';
                    }
                break;
            }
        }

        $element->setProperties($properties);
    }

    /**
     * The function inherits all properties of the $parents into array
     * $dest. $parents is an array of elements (iElementCSSMatchable).
     * 
     * @param    array $dest    Property storage
     * @param    array $parents Parents to inherit from
     */
    protected function inherit (array &$dest, array $parents) {
        // Inherit properties of all parents
        // (MUST be done backwards!)
        $max = count ($parents);
        foreach ($parents as $parent) {
            $properties = $parent->getProperties ();
            foreach ($properties as $key => $value) {
                if ($dest [$key] == 'inherit') {
                    $dest [$key] = $value;
                } else {
                    if (strncmp($key, 'background', strlen('background')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'border', strlen('border')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'padding', strlen('padding')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'margin', strlen('margin')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'outline', strlen('outline')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'counter', strlen('counter')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'page-break', strlen('page-break')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'cue', strlen('cue')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strncmp($key, 'pause', strlen('pause')) == 0) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strpos($key, 'width') !== false) {
                        // The property may not be inherited
                        continue;
                    }
                    if (strpos($key, 'height') !== false) {
                        // The property may not be inherited
                        continue;
                    }
                    switch ($key) {
                        case 'text-decoration':
                        case 'text-shadow':
                        case 'display':
                        case 'table-layout':
                        case 'vertical-align':
                        case 'visibility':
                        case 'position':
                        case 'top':
                        case 'right':
                        case 'bottom':
                        case 'left':
                        case 'float':
                        case 'clear':
                        case 'z-index':
                        case 'unicode-bidi':
                        case 'overflow':
                        case 'clip':
                        case 'visibility':
                        case 'content':
                        case 'marker-offset':
                        case 'play-during':
                            // The property may not be inherited
                        break;
                        default:
                            if ($dest [$key] == NULL || $dest [$key] == 'inherit') {
                                $dest [$key] = $value;
                            }
                        break;
                    }
                }
            }
        }
    }

    /**
     * Main function performing calculation and inheritance for element
     * $element. Properties are stored in $dest.
     * 
     * @param    array $dest    Property storage
     * @param    array $element Element to match
     * @param    ODTUnits             $units   ODTUnits object for conversion
     */
    protected function calculateAndInherit (array &$dest, iElementCSSMatchable $element, ODTUnits $units) {
        $parents = array();
        $parent = $element->iECSSM_getParent();
        while ($parent != NULL) {
            $parents [] = $parent;
            $parent = $parent->iECSSM_getParent();
        }

        // Determine properties of all parents if not done yet
        // and calculate absolute values
        // (MUST be done backwards!)
        $max = count ($parents);
        for ($index = $max-1 ; $index >= 0 ; $index--) {
            $properties = $parents [$index]->getProperties ();
            if ($properties == NULL) {
                $properties = array();
                $this->getPropertiesForElement ($properties, $parents [$index], $units, false);
                $parents [$index]->setProperties ($properties);
            }
            if ($properties ['calculated'] == NULL) {
                $this->calculate($properties, $parents [$index], $units);
            }
        }

        // Calculate our own absolute values
        $this->calculate($dest, $element, $units);

        // Inherit values from our parents
        $this->inherit($dest, $parents);
    }

    /**
     * Return a string representation of all imported rules.
     * (String can be large)
     * 
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
     * The function strips the 'url(...)' part from an URL reference
     * and puts a $replacement path in front of the rest.
     * 
     * @param    string $URL         Original URL reference
     * @param    string $replacement Replacement path to set
     * @return string
     */
    public static function replaceURLPrefix ($URL, $replacement) {
        if ( !empty ($URL) && !empty ($replacement) ) {
            // Replace 'url(...)' with $replacement
            $URL = substr ($URL, 3);
            $URL = trim ($URL, '()');
            $URL = $replacement.$URL;
        }
        return $URL;
    }

    /**
     * The function calls $callback for each imported property
     * containing a length value. The return value of $callback
     * is saved as the new property value.
     * 
     * @param    callable $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->rules as $rule) {
            $rule->adjustLengthValues ($callback);
        }
    }

    /**
     * The function calls $callback for each property imported
     * containing a URL reference. The return value of $callback
     * is saved as the new property value.
     * 
     * @param    callable $callback
     */
    public function replaceURLPrefixes ($callback) {
        foreach ($this->rules as $rule) {
            $rule->replaceURLPrefixes ($callback);
        }
    }
}
