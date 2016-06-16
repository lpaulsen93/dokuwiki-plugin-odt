<?php

/**
 * ODTStateElement:
 * Base class for all elements which are added/used with class ODTState.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
abstract class ODTStateElement
{
    // General state information
    protected $clazz = NULL;
    protected $style_name = NULL;
    protected $count = 0;
    protected $parent_element = NULL;
    protected $style_obj = NULL;
    protected $htmlElement = NULL;

    // Temp pointer for various use! Can point to different things!
    protected $temp = NULL;

    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        // Empty for now.
        // All elements call the parent constructor so it might be
        // of use in the future...
    }

    /**
     * Set the class to $value.
     * 
     * @param string $value Class, e.g. 'paragraph'
     */
    public function setClass($value) {
        $this->clazz = $value;
    }

    /**
     * Get the class.
     * 
     * @return string Class.
     */
    public function getClass() {
        return $this->clazz;
    }

    /**
     * Set the element count to $value.
     * If e.g. the element is 'table', then the count specifies
     * that this element is table number '$value'.
     * 
     * @param string $value Count
     */
    public function setCount($value) {
        $this->count = $value;
    }

    /**
     * Get the element count.
     * 
     * @return integer Count.
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Set the style name.
     * 
     * @param string $value Style name, e.g. 'body'
     */
    public function setStyleName($value) {
        $this->style_name = $value;
    }

    /**
     * Get the style name.
     * 
     * @return string Style name.
     */
    public function getStyleName() {
        return $this->style_name;
    }

    /**
     * Set the style object.
     * 
     * @param ODTStyle $object
     */
    public function setStyle($object) {
        $this->style_obj = $object;
    }

    /**
     * Get the style object.
     * 
     * @return ODTStyle Style object.
     */
    public function getStyle() {
        return $this->style_obj;
    }

    /**
     * Set temporary data for various use.
     * 
     * @param mixed $value
     */
    public function setTemp($value) {
        $this->temp = $value;
    }

    /**
     * Get temporary data for various use.
     * 
     * @return mixed
     */
    public function getTemp() {
        return $this->temp;
    }

    /**
     * Set parent of this element.
     * 
     * @param ODTStateElement $parent_element
     */
    public function setParent(ODTStateElement $parent_element) {
        $this->parent_element = $parent_element;
    }
    
    /**
     * Get parent of this element.
     * 
     * @return ODTStateElement
     */
    public function getParent() {
        return $this->parent_element;
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    abstract public function getElementName ();

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    abstract public function getOpeningTag ();

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    abstract public function getClosingTag ();

    /**
     * Are we in a paragraph or not?
     * 
     * @return boolean
     */
    abstract public function getInParagraph();

    /**
     * Determine and set the parent for this element.
     * The search starts at element $previous.
     */
    abstract public function determineParent(ODTStateElement $previous);

    /**
     * Set the HTML element name pushed to the HTML stack for this ODT element.
     * 
     * @param string $value HTML element name e.g. 'u'
     */
    public function setHTMLElement($value) {
        $this->htmlElement = $value;
    }

    /**
     * Get the HTML element name pushed to the HTML stack for this ODT element.
     * 
     * @return string HTML element name.
     */
    public function getHTMLElement() {
        return $this->htmlElement;
    }
}
