<?php
/**
 * ODTStyleStyle: class for ODT style styles.
 * (Elements style:style and style:default-style)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_PLUGIN.'odt/ODT/styles/ODTStyle.php';
//require_once 'ODTTextStyle.php';
//require_once 'ODTParagraphStyle.php';
//require_once 'ODTTableStyle.php';
//require_once 'ODTTableRowStyle.php';
//require_once 'ODTTableColumnStyle.php';
//require_once 'ODTTableCellStyle.php';
//require_once DOKU_PLUGIN.'odt/ODT/styles/ODTParagraphStyle.php';
//require_once DOKU_PLUGIN.'odt/ODT/styles/ODTTableStyle.php';
//require_once DOKU_PLUGIN.'odt/ODT/styles/ODTTableRowStyle.php';
//require_once DOKU_PLUGIN.'odt/ODT/styles/ODTTableCellStyle.php';

/**
 * The ODTStyleStyle class
 */
abstract class ODTStyleStyle extends ODTStyle
{
    // Style properties/attributes common to each
    // style:style and style:default-style element
    static $style_fields = array(
        'style-name'                       => array ('style:name',                         'style', false),
        'style-display-name'               => array ('style:display-name',                 'style', false),
        'style-parent'                     => array ('style:parent-style-name',            'style', false),
        'style-class'                      => array ('style:class',                        'style', true),
        'style-family'                     => array ('style:family',                       'style', true),
        'style-next'                       => array ('style:next-style-name',              'style', true),
        'style-list-level'                 => array ('style:list-level',                   'style', true),
        'style-list-style-name'            => array ('style:list-style-name',              'style', true),
        'style-master-page-name'           => array ('style:master-page-name',             'style', true),
        'style-auto-update'                => array ('style:auto-update',                  'style', true),
        'style-data-style-name'            => array ('style:data-style-name',              'style', true),
        'style-percentage-data-style-name' => array ('style:percentage-data-style-name',   'style', true),
        'style-default-outline-level'      => array ('style:default-outline-level',        'style', true),
        );
    static $get_family_callbacks = NULL;
    static $import_odt_callbacks = NULL;
    protected $is_default = false;

    /**
     * Constructor.
     */
    public function __construct() {
        if (self::$get_family_callbacks === NULL)
            self::$get_family_callbacks = array();
        if (self::$import_odt_callbacks === NULL)
            self::$import_odt_callbacks = array();
    }

    static public function register ($classname) {
        self::$get_family_callbacks [] = array($classname, 'getFamily');
        self::$import_odt_callbacks [] = array($classname, 'importODTStyle');
    }

    /**
     * Get the element name for the ODT XML encoding of the style.
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    public function getElementName() {
        if ($this->isDefault() == true) {
            return 'style:default-style';
        }
        return 'style:style';
    }
        
    /**
     * Mark style as default style or not.
     *
     * @param  $is_default
     */
    public function setDefault($is_default) {
        $this->is_default = $is_default;
    }

    /**
     * Is this style a default style?
     *
     * @return boolean Is this a default style?
     */
    public function isDefault() {
        return $this->is_default;
    }
    
    /**
     * Encode current style values in a string and return it.
     *
     * @return string ODT XML encoded style
     */
    public function toString() {
        //FIXME: Handling for background-image-section
        $style = '';
        $text = '';
        $paragraph = '';
        $table = '';
        $table_column = '';
        $table_row = '';
        $table_cell = '';
        $tab_stop = '';
        $image = '';
        foreach ($this->properties as $property => $items) {
            switch ($items ['section']) {
                case 'style':
                    $style .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'text':
                    $text .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'paragraph':
                    $paragraph .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'table':
                    $table .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'table-column':
                    $table_column .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'table-row':
                    $table_row .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'table-cell':
                    $table_cell .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'tab-stop':
                    $tab_stop .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
                case 'table-cell-background-image':
                    $image .= $items ['odt_property'].'="'.$items ['value'].'" ';
                    break;
            }
        }

        // Build style.
        $element = $this->getElementName();
        $style  = '<'.$element.' '.$style.'>'."\n";
        if ( !empty($paragraph) ) {
            if ( empty($tab_stop) ) {
                $style .= '    <style:paragraph-properties '.$paragraph.'/>'."\n";
            } else {
                $style .= '    <style:paragraph-properties '.$paragraph.'>'."\n";
                $style .= '        <style:tab-stops><style:tab-stop '.$tab_stop.'/></style:tab-stops>'."\n";
                $style .= '    </style:paragraph-properties>'."\n";
            }
        }
        if ( !empty($text) ) {
            $style .= '    <style:text-properties '.$text.'/>'."\n";
        }
        if ( !empty($table) ) {
            $style .= '    <style:table-properties '.$table.'/>'."\n";
        }
        if ( !empty($table_column) ) {
            $style .= '    <style:table-column-properties '.$table_column.'/>'."\n";
        }
        if ( !empty($table_row) ) {
            $style .= '    <style:table-row-properties '.$table_row.'/>'."\n";
        }
        if ( !empty($table_cell) ) {
            if (empty($image)) {
                $style .= '    <style:table-cell-properties '.$table_cell.'/>'."\n";
            } else {
                $style .= '    <style:table-cell-properties '.$table_cell.'>'."\n";
                $style .='         <style:background-image '.$image.'/>'."\n";
                $style .= '    </style:table-cell-properties>';
            }
        }
        $style .= '</'.$element.'>'."\n";
        return $style;
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $matches = array();
        if (preg_match ('/style:family="[^"]+"/', $xmlCode, $matches) !== 1) {
            return NULL;
        }
        $family = substr ($matches [0], strlen('style:family='));
        $family = trim ($family, '"<>');

        for ($index = 0 ; $index < count(self::$get_family_callbacks) ; $index++ ) {
            $curr_family = call_user_func(self::$get_family_callbacks [$index]);
            if ($curr_family == $family) {
                return call_user_func(self::$import_odt_callbacks [$index], $xmlCode);
            }
        }

        // Unknown/not implemented style family.
        // Return NULL, in this case ODTStyle will create a generic unknown style.
        return NULL;
    }

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created. Only those properties
     * will be accepted that are mentioned in the fields array.
     *
     * @param  $style      ODTStyle object for storing the properties
     * @param  $fields     Properties accepted by the object/class
     * @param  $properties Properties to be imported
     * @param  $disabled   Properties to be ignored
     */
    protected function importPropertiesInternal(array $fields, $properties, $disabled, &$dest=NULL) {
        parent::importPropertiesInternal($fields, $properties, $disabled, $dest);
    }

    /**
     * The function deletes all properties that do not belong to the styles section,
     * e.g. text properties or paragraph properties.
     */
    public function clearLayoutProperties() {
        foreach ($this->properties as $property => $items) {
            switch ($items ['section']) {
                case 'style':
                    // Keep it.
                    break;
                default:
                    // Delete everything that does not belong to the styles section.
                    $this->properties [$property] = NULL;
                    unset ($this->properties [$property]);
                    break;
            }
        }
    }

    static public function getStyleProperties () {
        return self::$style_fields;
    }
}
