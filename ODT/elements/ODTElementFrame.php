<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementFrame:
 * Class for handling the frame element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementFrame extends ODTStateElement implements iContainerAccess
{
    protected $container = NULL;
    protected $containerPos = NULL;
    protected $attributes = NULL;
    protected $own_max_width = NULL;
    protected $nameAttr = NULL;
    protected $name = NULL;
    protected $written = false;

    /**
     * Constructor.
     */
    public function __construct($style_name=NULL) {
        parent::__construct();
        $this->setClass ('frame');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
        $this->container = new ODTContainerElement($this);
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
    public function getOpeningTag (ODTInternalParams $params=NULL) {
        // Convert width to points
        $width = $this->getWidth();
        if ($width !== NULL) {
            $width = $params->units->toPoints($width);
            $this->setWidth($width);
        }

        $encoded =  '<draw:frame draw:style-name="'.$this->getStyleName().'" ';
        $encoded .= $this->getAttributes().'>';

        $this->written = true;

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
        $this->container->determineParent($previous);
        if ($this->isNested ()) {
            $this->containerPos = array();
            $this->getParent()->determinePositionInContainer($this->containerPos, $previous);
        }

        //$this->setParent($previous);
    }

    /**
     * Set frame attributes
     * 
     * @param array $value
     */
    public function setAttributes($value) {
        // Delete linebreaks and multiple whitespace
        $this->attributes = preg_replace( "/\r|\n/", "", $value);
        $this->attributes = preg_replace( "/\s+/", " ", $this->attributes);
        
        // Save name for later width rewriting
        $this->nameAttr = $this->getNameAttribute();
        $this->name = $this->getName();
    }

    /**
     * Get frame attributes
     * 
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Is this frame a nested frame (inserted into another table/frame)?
     * 
     * @return boolean
     */
    public function isNested () {
        return $this->container->isNested();
    }

    public function addNestedContainer (iContainerAccess $nested) {
        $this->container->addNestedContainer ($nested);
    }

    public function getNestedContainers () {
        return $this->container->getNestedContainers ();
    }

    public function determinePositionInContainer (array &$data, ODTStateElement $current) {
        // Position in frame doesn't mater for width calculation
        // So this is a dummy for now
        $data ['frame'] = true;
    }

    public function getMaxWidthOfNestedContainer (ODTInternalParams $params, array $data) {
        if ($this->own_max_width === NULL) {
            // We do not know our own width yet. Calculate it first.
            $this->own_max_width = $this->getMaxWidth($params);

            // Re-Write our width if frame already has been written to the document
            if ($this->written) {
                if (preg_match('/<draw:frame[^<]*'.$this->nameAttr.'[^>]*>/', $params->content, $matches) === 1) {
                    $frameTag = $matches [0];
                    $frameTag = preg_replace('/svg:width="[^"]*"/', 'svg:width="'.$this->own_max_width.'"', $frameTag);

                    // Replace old frame tag in document in
                    $params->content = str_replace ($matches [0], $frameTag, $params->content);
                }
            }
        }

        // Convert to points
        if ($this->own_max_width !== NULL) {
            $width = $params->units->getDigits ($params->units->toPoints($this->own_max_width));
        }

        return $width.'pt';
    }

    public function getMaxWidth (ODTInternalParams $params) {
        if ($this->own_max_width !== NULL) {
            return $this->own_max_width;
        }
        $frameStyle = $this->getStyle();

        // Get frame left margin
        $leftMargin = $frameStyle->getProperty('margin-left');
        if ($leftMargin == NULL) {
            $leftMarginPt = 0;
        } else {
            $leftMarginPt = $params->units->getDigits ($params->units->toPoints($leftMargin));
        }

        // Get frame right margin
        $rightMargin = $frameStyle->getProperty('margin-right');
        if ($rightMargin == NULL) {
            $rightMarginPt = 0;
        } else {
            $rightMarginPt = $params->units->getDigits ($params->units->toPoints($rightMargin));
        }

        // Get available max width
        if (!$this->isNested ()) {
            // Get max page width in points.
            $maxWidth = $params->document->getAbsWidthMindMargins ();
            $maxWidthPt = $params->units->getDigits ($params->units->toPoints($maxWidth.'cm'));
        } else {
            // If this frame is nested in another container we have to ask it's parent
            // for the allowed max width
            $maxWidth = $this->getParent()->getMaxWidthOfNestedContainer($params, $this->containerPos);
            $maxWidthPt = $params->units->getDigits ($params->units->toPoints($maxWidth));
        }

        // Get frame width
        $width = $this->getWidth();
        if ($width !== NULL) {
            if ($width [strlen($width)-1] != '%') {
                $widthPt = $params->units->getDigits ($params->units->toPoints($width));
            } else {
                $percentage = trim ($width, '%');
                $widthPt = ($percentage * $maxWidthPt)/100;
            }
        }

        // Calculate final width.
        // If no frame width is set or the frame width is greater than
        // the calculated max width then use the max width.
        $maxWidthPt = $maxWidthPt - $leftMarginPt - $rightMarginPt;
        if ($width == NULL || $widthPt > $maxWidthPt) {
            $width = $maxWidthPt - $leftMarginPt - $rightMarginPt;
        } else {
            $width = $widthPt;
        }
        $width = $width.'pt';

        return $width;
    }
    
    public function getWidth() {
        if ($this->attributes !== NULL) {
            if ( preg_match('/svg:width="[^"]+"/', $this->attributes, $matches) === 1 ) {
                $width = substr ($matches [0], 11);
                $width = trim ($width, '"');
                return $width;
            }
        }
        return NULL;
    }

    public function setWidth($width) {
        if ($this->attributes !== NULL) {
            if ( preg_match('/svg:width="[^"]+"/', $this->attributes, $matches) === 1 ) {
                $widthAttr = 'svg:width="'.$width.'"';
                $this->attributes = str_replace($matches [0], $widthAttr, $this->attributes);
                return;
            }
        }
        $this->attributes .= ' svg:width="'.$width.'"';
    }

    public function getNameAttribute() {
        if ($this->attributes !== NULL) {
            if ( preg_match('/draw:name="[^"]+"/', $this->attributes, $matches) === 1 ) {
                return $matches [0];
            }
        }
        return NULL;
    }

    public function getName() {
        if ($this->attributes !== NULL) {
            if ( preg_match('/draw:name="[^"]+"/', $this->attributes, $matches) === 1 ) {
                $name = substr ($matches [0], 10);
                $name = trim ($name, '"');
                return $name;
            }
        }
        return NULL;
    }

    /**
     * This function adjust the width of the frame.
     * There is not much to do except conversion of relative to absolute values
     * and calling the method for all nested elements.
     * (table has got more work to do, see ODTElementTable::adjustWidth)
     * 
     * @param ODTInternalParams $params      Common ODT params
     * @param boolean           $allowNested Allow to process call if this frame is nested
     */
    public function adjustWidth (ODTInternalParams $params, $allowNested=false) {
        if ($this->isNested () && !$allowNested) {
            // Do not do anything if this is a nested table.
            // Only if the function is called for the parent/root table
            // then the width of the nested tables will be calculated.
            return;
        }
        $matches = array ();

        $max_width = $this->getMaxWidth($params);
        //FIXME: convert % to points

        // Now adjust all nested containers too
        $nested = $this->getNestedContainers ();
        foreach ($nested as $container) {
            $container->adjustWidth ($params, true);
        }
    }
}
