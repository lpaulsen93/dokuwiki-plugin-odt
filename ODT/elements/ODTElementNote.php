<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementNote:
 * Class for handling the text note element (e.g. for footnotes).
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementNote extends ODTStateElement
{
    protected $value_type = 'string';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setClass ('text-note');
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('text:note');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        // Intentionally return an empty string!
        return '';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        // Intentionally return an empty string!
        return '';
    }

    /**
     * Are we in a paragraph or not?
     * As a text note we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table cell our parent is the table element.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }

    /**
     * Return the table to which this column belongs.
     * 
     * @return ODTStateElement
     */
    public function getTable () {
        return $this->getParent();
    }
}
