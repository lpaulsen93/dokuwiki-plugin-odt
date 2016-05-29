<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementSpan:
 * Class for handling the span element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementSpan extends ODTStateElement
{
    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('span');
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
        return ('text:span');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        return '<text:span text:style-name="'.$this->getStyleName().'">';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</text:span>';
    }

    /**
     * Are we in a paragraph or not?
     * As a span we ask our parent.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        $parent = $this->getParent();
        if ($parent != NULL) {
            return $parent->getInParagraph();
        }
        return NULL;
    }

    /**
     * Determine and set the parent for this element.
     * As a span the previous element is our parent.
     * 
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }
}
