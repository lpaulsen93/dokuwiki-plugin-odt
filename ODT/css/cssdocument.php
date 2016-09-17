<?php
/**
 * Class to fake a document tree for CSS matching.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/** Include ecm_interface */
require_once DOKU_INC.'lib/plugins/odt/helper/ecm_interface.php';

/**
 * Class css_doc_element
 * 
 * @package    CSS\CSSDocElement
 */
class css_doc_element implements iElementCSSMatchable {
    /** var Reference to corresponding cssdocument */
    public $doc = NULL;
    /** var Index of this element in the corresponding cssdocument */
    public $index = 0;

    /**
     * Get the name of this element.
     * 
     * @return    string
     */
    public function iECSSM_getName() {
        return $this->doc->entries [$this->index]['element'];
    }

    /**
     * Get the attributes of this element.
     * 
     * @return    array
     */
    public function iECSSM_getAttributes() {
        return $this->doc->entries [$this->index]['attributes_array'];
    }

    /**
     * Get the parent of this element.
     * 
     * @return    css_doc_element
     */
    public function iECSSM_getParent() {
        $index = $this->doc->findParent($this->index);
        if ($index == -1 ) {
            return NULL;
        }
        $element = new css_doc_element();
        $element->doc = $this->doc;
        $element->index = $index;
        return $element;
    }

    /**
     * Get the preceding sibling of this element.
     * 
     * @return    css_doc_element
     */
    public function iECSSM_getPrecedingSibling() {
        $index = $this->doc->getPrecedingSibling($this->index);
        if ($index == -1 ) {
            return NULL;
        }
        $element = new css_doc_element();
        $element->doc = $this->doc;
        $element->index = $index;
        return $element;
    }

    /**
     * Does this element belong to pseudo class $class?
     * 
     * @param     string  $class
     * @return    boolean
     */
    public function iECSSM_has_pseudo_class($class) {
        if ($this->doc->entries [$this->index]['pseudo_classes'] == NULL) {
            return false;
        }
        $result = array_search($class, 
            $this->doc->entries [$this->index]['pseudo_classes']);
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * Does this element match the pseudo element $element?
     * 
     * @param     string  $element
     * @return    boolean
     */
    public function iECSSM_has_pseudo_element($element) {
        if ($this->doc->entries [$this->index]['pseudo_elements'] == NULL) {
            return false;
        }
        $result = array_search($element, 
            $this->doc->entries [$this->index]['pseudo_elements']);
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * Return the CSS properties assigned to this element.
     * (from extern via setProperties())
     * 
     * @return    array
     */
    public function getProperties () {
        return $this->doc->entries [$this->index]['properties'];
    }

    /**
     * Set/assign the CSS properties for this element.
     * 
     * @param     array $properties
     */
    public function setProperties (array &$properties) {
        $this->doc->entries [$this->index]['properties'] = $properties;
    }
}

/**
 * Class cssdocument.
 * 
 * @package    CSS\CSSDocument
 */
class cssdocument {
    /** var Current size, Index for next entry */
    public $size = 0;
    /** var Current nesting level */
    public $level = 0;
    /** var Array of entries, see open() */
    public $entries = array ();
    /** var Root index, see saveRootIndex() */
    protected $rootIndex = 0;
    /** var Root level, see saveRootIndex() */
    protected $rootLevel = 0;

    /**
     * Internal function to get the value of an attribute.
     * 
     * @param     string  $value Value of the attribute
     * @param     string  $input Code to parse
     * @param     integer $pos   Current position in $input
     * @param     integer $max   End of $input
     * @return    integer Position at which the attribute ends
     */
    protected function collect_attribute_value (&$value, $input, $pos, $max) {
        $value = '';
        $in_quotes = false;
        $quote = '';
        while ($pos < $max) {
            $sign = $input [$pos];
            $pos++;

            if ($in_quotes == false) {
                if ($sign == '"' || $sign == "'") {
                    $quote = $sign;
                    $in_quotes = true;
                }
            } else {
                if ($sign == $quote) {
                    break;
                }
                $value .= $sign;
            }
        }

        if ($in_quotes == false || $sign != $quote) {
            // No proper quotes, delete value
            $value = NULL;
        }
        
        return $pos;
    }

    /**
     * Internal function to parse $attributes for key="value" pairs
     * and store the result in an array.
     * 
     * @param     string  $attributes Code to parse
     * @return    array Array of attributes
     */
    protected function get_attributes_array ($attributes) {
        if ($attributes == NULL) {
            return NULL;
        }
        
        $result = array();
        $pos = 0;
        $max = strlen($attributes);
        while ($pos < $max) {
            $equal_sign = strpos ($attributes, '=', $pos);
            if ($equal_sign === false) {
                break;
            }
            $att_name = substr ($attributes, $pos, $equal_sign-$pos);
            $att_name = trim ($att_name, ' ');

            $att_end = $this->collect_attribute_value($att_value, $attributes, $equal_sign+1, $max);

            // Add a attribute to array
            $result [$att_name] = $att_value;
            $pos = $att_end + 1;
        }
        return $result;
    }

    /**
     * Save the current position as the root index of the document.
     * It is guaranteed that elements below the root index will not be
     * discarded from the cssdocument.
     */
    public function saveRootIndex () {
        $this->rootIndex = $this->getIndexLastOpened ();
        $this->rootLevel = $this->level-1;
    }

    /**
     * Shrinks/cuts the cssdocument down to its root index.
     */
    public function restoreToRoot () {
        for ($index = $this->size-1 ; $index > $this->rootIndex ; $index--) {
            $this->entries [$index] = NULL;
        }
        $this->size = $this->rootIndex + 1;
        $this->level = $this->rootLevel + 1;
    }

    /**
     * Get the current state of the cssdocument.
     * 
     * @param    array $state    Returned state information
     */
    public function getState (array &$state) {
        $state ['index'] = $this->size-1;
        $state ['level'] = $this->level;
    }

    /**
     * Shrinks/cuts the cssdocument down to the given $state.
     * ($state must be retrieved by calling getState())
     * 
     * @param    array $state    State information
     */
    public function restoreState (array $state) {
        for ($index = $this->size-1 ; $index > $state ['index'] ; $index--) {
            $this->entries [$index] = NULL;
        }
        $this->size = $state ['index'] + 1;
        $this->level = $state ['level'];
    }

    /**
     * Open a new element in the cssdocument.
     * 
     * @param    string $element         The element's name
     * @param    string $attributes      The element's attributes
     * @param    string $pseudo_classes  The element's pseudo classes
     * @param    string $pseudo_elements The element's pseudo elements
     */
    public function open ($element, $attributes=NULL, $pseudo_classes=NULL, $pseudo_elements=NULL) {
        $this->entries [$this->size]['level'] = $this->level;
        $this->entries [$this->size]['state'] = 'open';
        $this->entries [$this->size]['element'] = $element;
        $this->entries [$this->size]['attributes'] = $attributes;
        if (!empty($pseudo_classes)) {
            $this->entries [$this->size]['pseudo_classes'] = explode(' ', $pseudo_classes);
        }
        if (!empty($pseudo_elements)) {
            $this->entries [$this->size]['pseudo_elements'] = explode(' ', $pseudo_elements);
        }
        
        // Build attribute array/parse attributes
        if ($attributes != NULL) {
            $this->entries [$this->size]['attributes_array'] =
                $this->get_attributes_array ($attributes);
        }

        $this->size++;
        $this->level++;
    }

    /**
     * Close $element in the cssdocument.
     * 
     * @param    string $element         The element's name
     */
    public function close ($element) {
        $this->level--;
        $this->entries [$this->size]['level'] = $this->level;
        $this->entries [$this->size]['state'] = 'close';
        $this->entries [$this->size]['element'] = $element;
        $this->size++;
    }

    /**
     * Get the current element.
     * 
     * @return css_doc_element
     */
    public function getCurrentElement() {
        $index = $this->getIndexLastOpened ();
        if ($index == -1) {
            return NULL;
        }
        $element = new css_doc_element();
        $element->doc = $this;
        $element->index = $index;
        return $element;
    }
    
    /**
     * Get the entry of internal array $entries at $index.
     * 
     * @param  integer $index
     * @return array
     */
    public function getEntry ($index) {
        if ($index >= $this->size ) {
            return NULL;
        }
        return $this->entries [$index];
    }

    /**
     * Get the current entry of internal array $entries.
     * 
     * @return array
     */
    public function getCurrentEntry () {
        if ($this->size == 0) {
            return NULL;
        }
        return $this->entries [$this->size-1];
    }

    /**
     * Get the index of the 'open' entry of the latest opened element.
     * 
     * @return integer
     */
    public function getIndexLastOpened () {
        if ($this->size == 0) {
            return -1;
        }
        for ($index = $this->size-1 ; $index >= 0 ; $index--) {
            if ($this->entries [$index]['state'] == 'open') {
                return $index;
            }
        }
        return -1;
    }
    
    /**
     * Find the parent for the entry at index $start.
     * 
     * @param    integer $start    Starting point
     */
    public function findParent ($start) {
        if ($this->size == 0 || $start >= $this->size) {
            return -1;
        }
        $start_level = $this->entries [$start]['level'];
        if ($start_level == 0) {
            return -1;
        }
        for ($index = $start-1 ; $index >= 0 ; $index--) {
            if ($this->entries [$index]['state'] == 'open'
                &&
                $this->entries [$index]['level'] == $start_level-1) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * Find the preceding sibling for the entry at index $current.
     * 
     * @param    integer $current    Starting point
     */
    public function getPrecedingSibling ($current) {
        if ($this->size == 0 || $current >= $this->size || $current == 0) {
            return -1;
        }
        $current_level = $this->entries [$current]['level'];
        if ($this->entries [$current-1]['level'] == $current_level) {
            return ($current-1);
        }
        return -1;
    }
    
    /**
     * Dump the current elements/entries in this cssdocument.
     * Only for debugging purposes.
     */
    public function getDump () {
        $dump = '';
        $dump .= 'RootLevel: '.$this->rootLevel.', RootIndex: '.$this->rootIndex."\n";
        for ($index = 0 ; $index < $this->size ; $index++) {
            $element = $this->entries [$index];
            $dump .= str_repeat(' ', $element ['level'] * 2);
            if ($this->entries [$index]['state'] == 'open') {
                $dump .= '<'.$element ['element'];
                $dump .= ' '.$element ['attributes'].'>';
            } else {
                $dump .= '</'.$element ['element'].'>';
            }
            $dump .= ' (Level: '.$element ['level'].')';
            $dump .= "\n";
        }
        return $dump;
    }

    /**
     * Remove the current entry.
     */
    public function removeCurrent () {
        $index = $this->size-1;
        if ($index <= $this->rootIndex) {
            // Do not remove root elements!
            return;
        }
        $this->level = $this->entries [$index]['level'];
        $this->entries [$index] = NULL;
        $this->size--;
    }
}
