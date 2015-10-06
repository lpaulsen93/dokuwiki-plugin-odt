<?php

/**
 * ODTState: class for maintaining the ODT state.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTStateEntry
{
    // General state information
    protected $element = NULL;
    protected $clazz = NULL;
    protected $style_name = NULL;

    // List state data
    protected $in_list = false;
    protected $in_list_item = false;
    protected $list_interrupted = false;

    // Paragraph or frame entered?
    protected $in_paragraph = false;
    protected $in_frame = false;

    // Table state data
    protected $table_column_styles = array ();
    protected $table_style = NULL;
    protected $table_autocols = false;
    protected $table_maxcols = 0;
    protected $table_curr_column = 0;
    protected $table_column_defs = NULL;

    // Temp pointer for various use! Can point to different things!
    protected $temp = NULL;

    function __clone() {
        $this->element = NULL;
        $this->clazz = NULL;
        $this->temp_style = NULL;

        $this->table_column_styles = array ();
        $this->table_style = NULL;
        $this->table_autocols = false;
        $this->table_maxcols = 0;
        $this->table_curr_column = 0;
        $this->table_column_defs = NULL;

        $this->list_interrupted = false;
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

    public function setTableColumnStyles($value) {
        $this->table_column_styles = $value;
    }
    public function getTableColumnStyles() {
        return $this->table_column_styles;
    }

    public function setTableStyle($value) {
        $this->table_style = $value;
    }
    public function getTableStyle() {
        return $this->table_style;
    }

    public function setTableAutoColumns($value) {
        $this->table_autocols = $value;
    }
    public function getTableAutoColumns() {
        return $this->table_autocols;
    }

    public function setTableMaxColumns($value) {
        $this->table_maxcols = $value;
    }
    public function getTableMaxColumns() {
        return $this->table_maxcols;
    }

    public function setTableCurrentColumn($value) {
        $this->table_curr_column = $value;
    }
    public function getTableCurrentColumn() {
        return $this->table_curr_column;
    }

    public function setTableColumnDefs($value) {
        $this->table_column_defs = $value;
    }
    public function getTableColumnDefs() {
        return $this->table_column_defs;
    }
}
/**
 * ODTState: class for maintaining the ODT state stack.
 *
 * In general this is a setter/getter class for ODT states.
 * The intention is to get rid of some global state variables.
 * Especially the global error-prone $in_paragraph which easily causes
 * a document to become invalid if once set wrong. Now each state/element
 * can set their own instance of $in_paragraph which hopefully makes it use
 * a bit safer. E.g. for a new table-cell or list-item it can be set to false
 * because they allow creation of a new paragraph. On leave() we throw the
 * current state variables away and are safe back from where we came from.
 * So we also don't need to worry about correct re-initialization of global
 * variables anymore.
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

    public function setTableColumnStyles($value) {
        $this->stack [$this->index]->setTableColumnStyles($value);
    }
    public function getTableColumnStyles() {
        return $this->stack [$this->index]->getTableColumnStyles();
    }

    public function setTableStyle($value) {
        $this->stack [$this->index]->setTableStyle($value);
    }
    public function getTableStyle() {
        return $this->stack [$this->index]->getTableStyle();
    }

    public function setTableAutoColumns($value) {
        $this->stack [$this->index]->setTableAutoColumns($value);
    }
    public function getTableAutoColumns() {
        return $this->stack [$this->index]->getTableAutoColumns();
    }

    public function setTableMaxColumns($value) {
        $this->stack [$this->index]->setTableMaxColumns($value);
    }
    public function getTableMaxColumns() {
        return $this->stack [$this->index]->getTableMaxColumns();
    }

    public function setTableCurrentColumn($value) {
        $this->stack [$this->index]->setTableCurrentColumn($value);
    }
    public function getTableCurrentColumns() {
        return $this->stack [$this->index]->getTableCurrentColumn();
    }

    public function setTableColumnDefs($value) {
        $this->stack [$this->index]->setTableColumnDefs($value);
    }
    public function getTableColumnDefs() {
        return $this->stack [$this->index]->getTableColumnDefs();
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
