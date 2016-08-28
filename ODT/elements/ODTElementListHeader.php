<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementListHeader:
 * Class for handling the list header element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementListHeader extends ODTStateElement
{
    // List item state data
    protected $in_list_item = false;
    protected $list_item_level = 0;

    /**
     * Constructor.
     */
    public function __construct($level=0) {
        parent::__construct();
        $this->setClass ('list-item');
        $this->setListItemLevel ($level);
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('text:list-header');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        return '<text:list-header>';
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</text:list-header>';
    }

    /**
     * Are we in a paragraph or not?
     * As a list item we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a list item the list element is our parent.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        while ($previous != NULL) {
            if ($previous->getClass() == 'list') {
                break;
            }
            $previous = $previous->getParent();
        }
        $this->setParent($previous);
    }

    /**
     * Set the level for an list item
     * 
     * @param integer $value
     */
    public function setListItemLevel($value) {
        $this->list_item_level = $value;
    }

    /**
     * Get level of a list item
     * 
     * @return integer
     */
    public function getListItemLevel() {
        return $this->list_item_level;
    }

    /**
     * Return the list to which this list item belongs.
     * 
     * @return ODTStateElement
     */
    public function getList () {
        return $this->getParent();
    }
}
