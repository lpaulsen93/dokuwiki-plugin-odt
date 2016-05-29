<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementFrame:
 * Class for handling the frame element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementFrame extends ODTStateElement
{
    protected $attributes = NULL;

    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('frame');
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
        return ('draw:frame');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $encoded =  '<draw:frame draw:style-name="'.$this->getStyleName().'" ';
        $encoded .= $this->getAttributes().'>';
        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag () {
        return '</draw:frame>';
    }

    /**
     * Are we in a paragraph or not?
     * As a frame we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a frame the previous element is our parent.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }

    /**
     * Set frame attributes
     * 
     * @param array $value
     */
    public function setAttributes($value) {
        $this->attributes = $value;
    }

    /**
     * Get frame attributes
     * 
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }
}
