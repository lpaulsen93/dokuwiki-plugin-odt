<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * Interface iContainerAccess
 *
 * To prevent clashes with other interfaces function names all functions
 * are prefixed with iCA_.
 * 
 * @package ODT\iContainerAccess
 */
interface iContainerAccess
{
    public function isNested ();
    public function addNestedContainer (iContainerAccess $nested);
    public function getNestedContainers ();
    public function determinePositionInContainer (array &$data, ODTStateElement $current);
    public function getMaxWidthOfNestedContainer (ODTInternalParams $params, array $data);
}

/**
 * ODTContainerElement:
 * Class for extra code to support container elements (frame and table).
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTContainerElement
{
    // Container specific state data
    protected $owner = NULL;
    protected $is_nested = false;
    protected $nestedContainers = array();

    /**
     * Constructor.
     */
    public function __construct(ODTStateElement $owner) {
        $this->owner = $owner;
    }

    /**
     * Determine and set the parent for this element.
     * The parent is the previous element.
     * 
     * If the container is nested in another table or frame,
     * then the surrounding table or frame is the parent!
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $container = $previous;
        while ($container != NULL) {
            if ($container->getClass() == 'table-cell') {
                $cell = $container;
            }
            if ($container->getClass() == 'table') {
                break;
            }
            if ($container->getClass() == 'frame') {
                break;
            }
            $container = $container->getParent();
        }
        if ($container == NULL) {
            $this->owner->setParent($previous);
        } else {
            $this->owner->setParent($container);
            $container->addNestedContainer ($this->owner);
            $this->is_nested = true;
        }
    }

    /**
     * Is this container nested in another container
     * (inserted into another table or frame)?
     * 
     * @return boolean
     */
    public function isNested () {
        return $this->is_nested;
    }

    public function addNestedContainer (iContainerAccess $nested) {
        $this->nestedContainers [] = $nested;
    }

    public function getNestedContainers () {
        return $this->nestedContainers;
    }
}
