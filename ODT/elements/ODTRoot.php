<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementRoot:
 * Root-Element to make things easier. Always needs to be on top of ODTState.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementRoot extends ODTStateElement
{
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setClass ('root');
        $this->setCount (1);
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        // Dummy.
        return 'root';
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        // Dummy.
        return '';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        // Dummy.
        return '';
    }

    /**
     * Set parent dummy function for preventing that anyone
     * accidentally sets a parent for the root.
     * 
     * @param ODTStateElement $parent_element
     */
    public function setParent(ODTStateElement $parent_element) {
        // Intentionally do nothing!
    }

    /**
     * Are we in a paragraph or not?
     * This is the root - so we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * Nothing to do for the root.
     */
    public function determineParent(ODTStateElement $previous) {
        // Intentionally do nothing!
    }
}
