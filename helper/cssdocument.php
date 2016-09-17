<?php
/**
 * Helper class to fake a document tree for CSS matching.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/helper/ecm_interface.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/css/cssdocument.php';

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class css_document
 * 
 * @package helper\cssdocument
 */
class helper_plugin_odt_cssdocument extends DokuWiki_Plugin {
    protected $internal = NULL;

    public function __construct() {
        $this->internal = new cssdocument();
    }

    public function open ($element, $attributes=NULL, $pseudo_classes=NULL, $pseudo_elements=NULL) {
        $this->internal->open ($element, $attributes, $pseudo_classes, $pseudo_elements);
    }

    public function close ($element) {
        $this->internal->close ($element);
    }

    public function getCurrentElement() {
        return $this->internal->getCurrentElement ();
    }
    
    public function getEntry ($index) {
        return $this->internal->getEntry ($index);
    }

    public function getCurrentEntry () {
        return $this->internal->getCurrentEntry ();
    }

    public function getIndexLastOpened () {
        return $this->internal->getIndexLastOpened ();
    }
    
    public function findParent ($start) {
        return $this->internal->findParent ($start);
    }

    public function getPrecedingSibling ($current) {
        return $this->internal->getPrecedingSibling ($current);
    }

    public function getDump () {
        return $this->internal->getDump ($current);
    }
}
