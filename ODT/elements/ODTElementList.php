<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementList:
 * Class for handling the list element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementList extends ODTStateElement
{
    // List state data
    protected $continue_numbering;
    protected $in_list = false;
    protected $list_first_paragraph = true;
    protected $list_paragraph_pos = -1;

    /**
     * Constructor.
     */
    public function __construct($style_name=NULL, $continue=false) {
        parent::__construct();
        $this->setClass ('list');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
        $this->setContinueNumbering ($continue);
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('text:list');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $encoded = '<text:list text:style-name="'.$this->getStyleName().'"';
        if ($this->getContinueNumbering()) {
            $encoded .= ' text:continue-numbering="true" ';
        } else {
            if ($this->in_list === false) {
                $encoded .= ' text:continue-numbering="false" ';
            }
        }
        $encoded .= '>';
        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</text:list>';
    }

    /**
     * Are we in a paragraph or not?
     * As a list we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a list the previous element is our parent.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);

        // Check if this is a nested list
        while ($previous != NULL) {
            if ($previous->getClass() == 'list') {
                break;
            }
            $previous = $previous->getParent();
        }
        if ($previous != NULL) {
            // Yes, nested list.
            $this->in_list = true;
        }
    }

    /**
     * Set continue numbering to $value
     * 
     * @param bool $value
     */
    public function setContinueNumbering($value) {
        $this->continue_numbering = $value;
    }

    /**
     * Get continue numbering to $value
     * 
     * @return bool
     */
    public function getContinueNumbering() {
        return $this->continue_numbering;
    }

    /**
     * Set flag if the next paragraph will be the first in the list
     * 
     * @param boolean $value
     */
    public function setListFirstParagraph($value) {
        $this->list_first_paragraph = $value;
    }

    /**
     * Get flag if the next paragraph will be the first in the list
     * 
     * @return boolean
     */
    public function getListFirstParagraph() {
        return $this->list_first_paragraph;
    }

    /**
     * Set position of last opened paragraph in the list
     * 
     * @param integer $value
     */
    public function setListLastParagraphPosition($value) {
        $this->list_paragraph_pos = $value;
    }

    /**
     * Get position of last opened paragraph in the list
     * 
     * @return integer
     */
    public function getListLastParagraphPosition() {
        return $this->list_paragraph_pos;
    }
}
