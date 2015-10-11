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
    protected $list_item_level = 0;
    protected $list_interrupted = false;
    protected $list_first_paragraph = true;

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

    /**
     * Clone-Function.
     * The function should initialize all variables that should not be
     * inherited from the previous state if a new state is entered.
     * See function enter().
     */
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

    /**
     * Set the element name to $value.
     * 
     * @param string $value Element name e.g. 'text:p'
     */
    public function setElement($value) {
        $this->element = $value;
    }

    /**
     * Get the element name.
     * 
     * @return string Element name.
     */
    public function getElement() {
        return $this->element;
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
     * Set flag if we are in a list or not.
     * 
     * @param boolean $value
     */
    public function setInList($value) {
        $this->in_list = $value;
    }

    /**
     * Get flag if we are in a list or not.
     * 
     * @return boolean
     */
    public function getInList() {
        return $this->in_list;
    }

    /**
     * Set flag if current list is interrupted (by a table) or not.
     * 
     * @param boolean $value
     */
    public function setListInterrupted($value) {
        $this->list_interrupted = $value;
    }

    /**
     * Get flag if current list is interrupted (by a table) or not.
     * 
     * @return boolean
     */
    public function getListInterrupted() {
        return $this->list_interrupted;
    }

    /**
     * Set flag if we are in a list item or not.
     * 
     * @param boolean $value
     */
    public function setInListItem($value) {
        $this->in_list_item = $value;
    }

    /**
     * Get flag if we are in a list item or not.
     * 
     * @return boolean
     */
    public function getInListItem() {
        return $this->in_list_item;
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
     * Set flag if we are in a paragraph or not.
     * 
     * @param boolean $value
     */
    public function setInParagraph($value) {
        $this->in_paragraph = $value;
    }

    /**
     * Get flag if we are in a paragraph or not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return $this->in_paragraph;
    }

    /**
     * Set flag if we are in a frame or not.
     * 
     * @param boolean $value
     */
    public function setInFrame($value) {
        $this->in_frame = $value;
    }

    /**
     * Get flag if we are in a frame or not.
     * 
     * @return boolean
     */
    public function getInFrame() {
        return $this->in_frame;
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
     * Set table column styles 
     * 
     * @param array $value
     */
    public function setTableColumnStyles($value) {
        $this->table_column_styles = $value;
    }

    /**
     * Get table column styles
     * 
     * @return array
     */
    public function getTableColumnStyles() {
        return $this->table_column_styles;
    }

    /**
     * Set table style name
     * 
     * @param string $value
     */
    public function setTableStyle($value) {
        $this->table_style = $value;
    }

    /**
     * Get table style name
     * 
     * @return string
     */
    public function getTableStyle() {
        return $this->table_style;
    }

    /**
     * Set flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @param boolean $value
     */
    public function setTableAutoColumns($value) {
        $this->table_autocols = $value;
    }

    /**
     * Get flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @return boolean
     */
    public function getTableAutoColumns() {
        return $this->table_autocols;
    }

    /**
     * Set maximal number of columns.
     * 
     * @param integer $value
     */
    public function setTableMaxColumns($value) {
        $this->table_maxcols = $value;
    }

    /**
     * Get maximal number of columns.
     * 
     * @return integer
     */
    public function getTableMaxColumns() {
        return $this->table_maxcols;
    }

    /**
     * Set current column.
     * 
     * @param integer $value
     */
    public function setTableCurrentColumn($value) {
        $this->table_curr_column = $value;
    }

    /**
     * Get current column.
     * 
     * @return integer
     */
    public function getTableCurrentColumn() {
        return $this->table_curr_column;
    }

    /**
     * Set column definitions content.
     * 
     * @param string $value
     */
    public function setTableColumnDefs($value) {
        $this->table_column_defs = $value;
    }

    /**
     * Get column definitions content.
     * 
     * @return string
     */
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
     * Constructor. Set initial 'root' state.
     */
    public function __construct() {
        $this->stack [$this->index] = new ODTStateEntry;
        $this->stack [$this->index]->setElement('root');
        $this->stack [$this->index]->setClass('root');
    }

    /**
     * Calls setElement for the current state.
     * See ODTStateEntry::setElement.
     */
    public function setElement($value) {
        $this->stack [$this->index]->setElement($value);
    }

    /**
     * Calls getElement for the current state.
     * See ODTStateEntry::getElement.
     */
    public function getElement() {
        return $this->stack [$this->index]->getElement();
    }

    /**
     * Calls setClass for the current state.
     * See ODTStateEntry::setClass.
     */
    public function setClass($value) {
        $this->stack [$this->index]->setClass($value);
    }

    /**
     * Calls getClass for the current state.
     * See ODTStateEntry::getClass.
     */
    public function getClass() {
        return $this->stack [$this->index]->getClass();
    }

    /**
     * Calls setStyleName for the current state.
     * See ODTStateEntry::setStyleName.
     */
    public function setStyleName($value) {
        $this->stack [$this->index]->setStyleName($value);
    }

    /**
     * Calls getStyleName for the current state.
     * See ODTStateEntry::getStyleName.
     */
    public function getStyleName() {
        return $this->stack [$this->index]->getStyleName();
    }

    /**
     * Calls setInList for the current state.
     * See ODTStateEntry::setInList.
     */
    public function setInList($value) {
        $this->stack [$this->index]->setInList($value);
    }

    /**
     * Calls getInList for the current state.
     * See ODTStateEntry::getInList.
     */
    public function getInList() {
        return $this->stack [$this->index]->getInList();
    }

    /**
     * Calls setListInterrupted for the current state.
     * See ODTStateEntry::setListInterrupted.
     */
    public function setListInterrupted($value) {
        $this->stack [$this->index]->setListInterrupted($value);
    }

    /**
     * Calls getListInterrupted for the current state.
     * See ODTStateEntry::getListInterrupted.
     */
    public function getListInterrupted() {
        return $this->stack [$this->index]->getListInterrupted();
    }

    /**
     * Calls setInListItem for the current state.
     * See ODTStateEntry::setInListItem.
     */
    public function setInListItem($value) {
        $this->stack [$this->index]->setInListItem($value);
    }

    /**
     * Calls getInListItem for the current state.
     * See ODTStateEntry::getInListItem.
     */
    public function getInListItem() {
        return $this->stack [$this->index]->getInListItem();
    }

    /**
     * Calls setListItemLevel for the current state.
     * See ODTStateEntry::setListItemLevel.
     */
    public function setListItemLevel($value) {
        $this->stack [$this->index]->setListItemLevel($value);
    }

    /**
     * Calls getListItemLevel for the current state.
     * See ODTStateEntry::getListItemLevel.
     */
    public function getListItemLevel() {
        return $this->stack [$this->index]->getListItemLevel();
    }

    /**
     * Calls setListFirstParagraph for the current state.
     * See ODTStateEntry::setListFirstParagraph.
     */
    public function setListFirstParagraph($value) {
        $this->stack [$this->index]->setListFirstParagraph($value);
    }

    /**
     * Calls getListFirstParagraph for the current state.
     * See ODTStateEntry::getListFirstParagraph.
     */
    public function getListFirstParagraph() {
        return $this->stack [$this->index]->getListFirstParagraph();
    }

    /**
     * Calls setInParagraph for the current state.
     * See ODTStateEntry::setInParagraph.
     */
    public function setInParagraph($value) {
        $this->stack [$this->index]->setInParagraph($value);
    }

    /**
     * Calls getInParagraph for the current state.
     * See ODTStateEntry::getInParagraph.
     */
    public function getInParagraph() {
        return $this->stack [$this->index]->getInParagraph();
    }

    /**
     * Calls setInFrame for the current state.
     * See ODTStateEntry::setInFrame.
     */
    public function setInFrame($value) {
        $this->stack [$this->index]->setInFrame($value);
    }

    /**
     * Calls getInFrame for the current state.
     * See ODTStateEntry::getInFrame.
     */
    public function getInFrame() {
        return $this->stack [$this->index]->getInFrame();
    }

    /**
     * Calls setTemp for the current state.
     * See ODTStateEntry::setTemp.
     */
    public function setTemp($value) {
        $this->stack [$this->index]->setTemp($value);
    }

    /**
     * Calls getTemp for the current state.
     * See ODTStateEntry::getTemp.
     */
    public function getTemp() {
        return $this->stack [$this->index]->getTemp();
    }

    /**
     * Calls setTableColumnStyles for the current state.
     * See ODTStateEntry::setTableColumnStyles.
     */
    public function setTableColumnStyles($value) {
        $this->stack [$this->index]->setTableColumnStyles($value);
    }

    /**
     * Calls getTableColumnStyles for the current state.
     * See ODTStateEntry::getTableColumnStyles.
     */
    public function getTableColumnStyles() {
        return $this->stack [$this->index]->getTableColumnStyles();
    }

    /**
     * Calls setTableStyle for the current state.
     * See ODTStateEntry::setTableStyle.
     */
    public function setTableStyle($value) {
        $this->stack [$this->index]->setTableStyle($value);
    }

    /**
     * Calls getTableStyle for the current state.
     * See ODTStateEntry::getTableStyle.
     */
    public function getTableStyle() {
        return $this->stack [$this->index]->getTableStyle();
    }

    /**
     * Calls setTableAutoColumns for the current state.
     * See ODTStateEntry::setTableAutoColumns.
     */
    public function setTableAutoColumns($value) {
        $this->stack [$this->index]->setTableAutoColumns($value);
    }

    /**
     * Calls getTableAutoColumns for the current state.
     * See ODTStateEntry::getTableAutoColumns.
     */
    public function getTableAutoColumns() {
        return $this->stack [$this->index]->getTableAutoColumns();
    }

    /**
     * Calls setTableMaxColumns for the current state.
     * See ODTStateEntry::setTableMaxColumns.
     */
    public function setTableMaxColumns($value) {
        $this->stack [$this->index]->setTableMaxColumns($value);
    }

    /**
     * Calls getTableMaxColumns for the current state.
     * See ODTStateEntry::getTableMaxColumns.
     */
    public function getTableMaxColumns() {
        return $this->stack [$this->index]->getTableMaxColumns();
    }

    /**
     * Calls setTableCurrentColumn for the current state.
     * See ODTStateEntry::setTableCurrentColumn.
     */
    public function setTableCurrentColumn($value) {
        $this->stack [$this->index]->setTableCurrentColumn($value);
    }

    /**
     * Calls getTableCurrentColumn for the current state.
     * See ODTStateEntry::getTableCurrentColumn.
     */
    public function getTableCurrentColumns() {
        return $this->stack [$this->index]->getTableCurrentColumn();
    }

    /**
     * Calls setTableColumnDefs for the current state.
     * See ODTStateEntry::setTableColumnDefs.
     */
    public function setTableColumnDefs($value) {
        $this->stack [$this->index]->setTableColumnDefs($value);
    }

    /**
     * Calls getTableColumnDefs for the current state.
     * See ODTStateEntry::getTableColumnDefs.
     */
    public function getTableColumnDefs() {
        return $this->stack [$this->index]->getTableColumnDefs();
    }

    /**
     * Enter a new state with element name $element and class $clazz.
     * E.g. 'text:p' and 'paragraph'.
     * 
     * @param string $element
     * @param string $clazz
     */
    public function enter($element, $clazz) {
        // We enter a new state by making a copy (clone) of the previous state.
        // The clone() function of ODTStateEntry needs to insure that all params
        // which SHALL NOT be inherited from the previous state are initialized.
        $this->index++;
        $this->stack [$this->index] = clone $this->stack[$this->index-1];
        $this->stack [$this->index]->setElement($element);
        $this->stack [$this->index]->setClass($clazz);
    }

    /**
     * Leave current state. All data of the curent state is thrown away.
     */
    public function leave() {
        // We always will keep the initial state.
        // That means we do nothing if index is 0. This would be a fault anyway.
        if ($this->index > 0) {
            unset ($this->stack [$this->index]);
            $this->index--;
        }
    }

    /**
     * Reset the state stack/go back to the initial state.
     * All states except the root state will be discarded.
     */
    public function reset() {
        // Throw away any states except the initial state.
        // Reset index to 0.
        for ($reset = 1 ; $reset <= $this->index ; $reset++) {
            unset ($this->stack [$reset]);
        }
        $this->index = 0;
    }

    /**
     * Find the closest state with class $clazz.
     *
     * @param string $clazz
     * @return ODTStateEntry|NULL
     */
    public function findClosestWithClass($clazz) {
        for ($search = $this->index ; $search > 0 ; $search--) {
            if ($this->stack [$search]->getClass() == $clazz) {
                return $this->stack [$search];
            }
        }
        // Nothing found.
        return NULL;
    }

    /**
     * toString() function. Only for creating debug dumps.
     * 
     * @return string
     */
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

    /**
     * Find the closest state with class $clazz.
     *
     * @param string $clazz
     * @return ODTStateEntry|NULL
     */
    public function countClass($clazz) {
        $count = 0;
        for ($search = $this->index ; $search > 0 ; $search--) {
            if ($this->stack [$search]->getClass() == $clazz) {
                $count++;
            }
        }
        return $count;
    }
}
