<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementParagraph:
 * Class for handling the paragraph element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementParagraph extends ODTStateElement
{
    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('paragraph');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('text:p');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        return '<text:p text:style-name="'.$this->getStyleName().'">';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</text:p>';
    }

    /**
     * Are we in a paragraph or not?
     * As a paragraph we are of course.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return true;
    }

    /**
     * Determine and set the parent for this element.
     * As a paragraph the previous element is our parent.
     * 
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }
}
