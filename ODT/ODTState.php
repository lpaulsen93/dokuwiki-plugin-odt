<?php

/**
 * ODTState: class for maintaining the ODT state.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTStateEntry
{
    protected $element = NULL;
    protected $clazz = NULL;
    protected $style_name = NULL;
    protected $in_list = false;
    protected $in_list_item = false;
    protected $list_interrupted = false;
    protected $list_interrupt_now = false;
    protected $in_paragraph = false;
    protected $in_frame = false;
    protected $in_header = false;
    protected $table_column_styles = array ();
    protected $table_style = NULL;
    protected $autocols = false;
    protected $maxcols = 0;
    protected $column = 0;
    protected $content = NULL;
    protected $cols = NULL;
    protected $temp_style_name = NULL;
    // Temp pointer for various use! Can point to different things!
    protected $temp = NULL;

    function __clone() {
        $this->element = NULL;
        $this->clazz = NULL;
        $this->temp_style = NULL;

        $this->table_column_styles = array ();
        $this->table_style = NULL;
        $this->autocols = false;
        $this->maxcols = 0;
        $this->column = 0;
        $this->content = NULL;
        $this->cols = NULL;

        $this->list_interrupted = false;
        $this->list_interrupt_now = false;
    }

    public function setElement($value) {
        $this->element = $value;
    }
    public function getElement() {
        return $this->element;
    }

    public function setClass($value) {
        $this->clazz = $value;
    }
    public function getClass() {
        return $this->clazz;
    }

    public function setStyleName($value) {
        $this->style_name = $value;
    }
    public function getStyleName() {
        return $this->style_name;
    }

    public function setTempStyleName($value) {
        $this->temp_style_name = $value;
    }
    public function getTempStyleName() {
        return $this->temp_style_name;
    }

    public function setInList($value) {
        $this->in_list = $value;
    }
    public function getInList() {
        return $this->in_list;
    }

    public function setListInterrupted($value) {
        $this->list_interrupted = $value;
    }
    public function getListInterrupted() {
        return $this->list_interrupted;
    }

    public function setListInterruptNow($value) {
        $this->list_interrupt_now = $value;
    }
    public function getListInterruptNow() {
        return $this->list_interrupt_now;
    }

    public function setInListItem($value) {
        $this->in_list_item = $value;
    }
    public function getInListItem() {
        return $this->in_list_item;
    }

    public function setInParagraph($value) {
        $this->in_paragraph = $value;
    }
    public function getInParagraph() {
        return $this->in_paragraph;
    }

    public function setInHeader($value) {
        $this->in_header = $value;
    }
    public function getInHeader() {
        return $this->in_header;
    }

    public function setInFrame($value) {
        $this->in_frame = $value;
    }
    public function getInFrame() {
        return $this->in_frame;
    }

    public function setTemp($value) {
        $this->temp = $value;
    }
    public function getTemp() {
        return $this->temp;
    }
}
/**
 * ODTState: class for maintaining the ODT state stack.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTState
{
    protected $stack = array();
    protected $index = 0;

    /**
     * Constructor. Set initial state.
     */
    public function __construct() {
        $this->stack [$this->index] = new ODTStateEntry;
        $this->stack [$this->index]->setElement('root');
        $this->stack [$this->index]->setClass('root');
    }

    public function setElement($value) {
        $this->stack [$this->index]->setElement($value);
    }
    public function getElement() {
        return $this->stack [$this->index]->getElement();
    }

    public function setClass($value) {
        $this->stack [$this->index]->setClass($value);
    }
    public function getClass() {
        return $this->stack [$this->index]->getClass();
    }

    public function setStyleName($value) {
        $this->stack [$this->index]->setStyleName($value);
    }
    public function getStyleName() {
        return $this->stack [$this->index]->getStyleName();
    }

    public function setTempStyleName($value) {
        $this->stack [$this->index]->setTempStyleName($value);
    }
    public function getTempStyleName() {
        return $this->stack [$this->index]->getTempStyleName();
    }

    public function setInList($value) {
        $this->stack [$this->index]->setInList($value);
    }
    public function getInList() {
        return $this->stack [$this->index]->getInList();
    }

    public function setListInterrupted($value) {
        $this->stack [$this->index]->setListInterrupted($value);
    }
    public function getListInterrupted() {
        return $this->stack [$this->index]->getListInterrupted();
    }

    public function setListInterruptNow($value) {
        $this->stack [$this->index]->setListInterruptNow($value);
    }
    public function getListInterruptNow() {
        return $this->stack [$this->index]->getListInterruptNow();
    }

    public function setInListItem($value) {
        $this->stack [$this->index]->setInListItem($value);
    }
    public function getInListItem() {
        return $this->stack [$this->index]->getInListItem();
    }

    public function setInParagraph($value) {
        $this->stack [$this->index]->setInParagraph($value);
    }
    public function getInParagraph() {
        return $this->stack [$this->index]->getInParagraph();
    }

    public function setInHeader($value) {
        $this->stack [$this->index]->setInHeader($value);
    }
    public function getInHeader() {
        return $this->stack [$this->index]->getInHeader();
    }

    public function setInFrame($value) {
        $this->stack [$this->index]->setInFrame($value);
    }
    public function getInFrame() {
        return $this->stack [$this->index]->getInFrame();
    }

    public function setTemp($value) {
        $this->stack [$this->index]->setTemp($value);
    }
    public function getTemp() {
        return $this->stack [$this->index]->getTemp();
    }

    public function enter($element, $clazz) {
        // We enter a new state by making a copy (clone) of the previous state.
        // The clone() function of ODTStateEntry needs to insure that all params
        // which SHALL NOT be inherited from the previous state are initialized.
        $this->index++;
        $this->stack [$this->index] = clone $this->stack[$this->index-1];
        $this->stack [$this->index]->setElement($element);
        $this->stack [$this->index]->setClass($clazz);
    }

    public function leave() {
        // We always will keep the initial state.
        // That means we do nothing if index is 0. This would be a fault anyway.
        if ($this->index > 0) {
            unset ($this->stack [$this->index]);
            $this->index--;
        }
    }

    public function reset() {
        // Throw away any states except the initial state.
        // Reset index to 0.
        for ($reset = 1 ; $reset <= $this->index ; $reset++) {
            unset ($this->stack [$reset]);
        }
        $this->index = 0;
    }

    public function findClosestWithClass($clazz) {
        for ($search = $this->index ; $search > 0 ; $search--) {
            if ($this->stack [$search]->getClass() == $clazz) {
                return $this->stack [$search];
            }
        }
        // Nothing found.
        return NULL;
    }

    public function toString () {
        $indent = '';
        $string = 'Stackdump:';
        for ($search = 0 ; $search <= $this->index ; $search++) {
            $string .= $indent . $this->stack [$search]->getElement().';';
            $string .= 'inListItem=';
            if (!$this->stack [$search]->getInList()) {
                $string .= 'false;'."\n";
            } else {
                $string .= 'true;'."\n";
            }
            $indent .= '    ';
        }
        return $string;
    }
}
