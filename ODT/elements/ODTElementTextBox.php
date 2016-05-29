<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTextBox:
 * Class for handling the text box element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTextBox extends ODTStateElement
{
    protected $attributes = NULL;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setClass ('text-box');
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('draw:text-box');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $encoded =  '<draw:text-box '.$this->getAttributes().'>';
        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</draw:text-box>';
    }

    /**
     * Are we in a paragraph or not?
     * As a text box we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a text box the previous element is our parent.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }

    /**
     * Set text box attributes
     * 
     * @param array $value
     */
    public function setAttributes($value) {
        $this->attributes = $value;
    }

    /**
     * Get text box attributes
     * 
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }
}
