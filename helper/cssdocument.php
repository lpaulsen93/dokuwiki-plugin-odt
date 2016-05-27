<?php
/**
 * Helper class to fake a document tree for CSS matching.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/helper/ecm_interface.php';

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class css_doc_element
 */
class css_doc_element extends DokuWiki_Plugin implements iElementCSSMatchable {
    public $doc = NULL;
    public $index = 0;

    public function iECSSM_getName() {
        return $this->doc->entries [$this->index]['element'];
    }

    public function iECSSM_getAttributes() {
        return $this->doc->entries [$this->index]['attributes_array'];
    }

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

    public function iECSSM_has_pseudo_class($class) {
        $result = array_search($class, 
            $this->doc->entries [$this->index]['pseudo_classes']);
        if ($result === false) {
            return false;
        }
        return true;
    }

    public function iECSSM_has_pseudo_element($element) {
        $result = array_search($element, 
            $this->doc->entries [$this->index]['pseudo_elements']);
        if ($result === false) {
            return false;
        }
        return true;
    }
}

/**
 * Class css_document
 */
class helper_plugin_odt_cssdocument extends DokuWiki_Plugin {
    public $size = 0;
    public $level = 0;
    public $entries = array ();

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

    public function open ($element, $attributes=NULL, $pseudo_classes=NULL, $pseudo_elements=NULL) {
        $this->entries [$this->size]['level'] = $this->level;
        $this->entries [$this->size]['state'] = 'open';
        $this->entries [$this->size]['element'] = $element;
        $this->entries [$this->size]['attributes'] = $attributes;
        $this->entries [$this->size]['pseudo_classes'] = explode(' ', $pseudo_classes);
        $this->entries [$this->size]['pseudo_elements'] = explode(' ', $pseudo_elements);
        
        // Build attribute array/parse attributes
        $this->entries [$this->size]['attributes_array'] =
            $this->get_attributes_array ($attributes);

        $this->size++;
        $this->level++;
    }

    public function close ($element) {
        $this->level--;
        $this->entries [$this->size]['level'] = $this->level;
        $this->entries [$this->size]['state'] = 'close';
        $this->entries [$this->size]['element'] = $element;
        $this->size++;
    }

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
    
    public function getEntry ($index) {
        if ($index >= $this->size ) {
            return NULL;
        }
        return $this->entries [$index];
    }

    public function getCurrentEntry () {
        if ($this->size == 0) {
            return NULL;
        }
        return $this->entries [$this->size-1];
    }

    public function getIndexLastOpened () {
        if ($this->size == 0) {
            return -1;
        }
        for ($index = $this->size-1 ; $index >= 0 ; $index++) {
            if ($this->entries [$index]['state'] == 'open') {
                return $index;
            }
        }
        return -1;
    }
    
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
}
