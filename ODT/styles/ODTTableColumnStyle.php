<?php
/**
 * ODTTableColumnStyle: class for ODT table column styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once 'ODTStyle.php';

ODTStyleStyle::register('ODTTableColumnStyle');

/**
 * The ODTTableColumnStyle class
 */
class ODTTableColumnStyle extends ODTStyleStyle
{
    static $table_column_fields = array(
        'column-width'               => array ('style:column-width',              'table-column',  true),
        'rel-column-width'           => array ('style:rel-column-width',          'table-column',  true),
        'use-optimal-column-width'   => array ('style:use-optimal-column-width',  'table-column',  true),
        'break-before'               => array ('fo:break-before',                 'table-column',  true),
        'break-after'                => array ('fo:break-after',                  'table-column',  true),
    );

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created.
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    public function importProperties($properties, $disabled) {
        $this->importPropertiesInternal(ODTStyleStyle::getStyleProperties (), $properties, $disabled);
        $this->importPropertiesInternal(self::$table_column_fields, $properties, $disabled);
        $this->setProperty('style-family', $this->getFamily());
    }

    /**
     * Check if a style is a common style.
     *
     * @return bool Is common style
     */
    public function mustBeCommonStyle() {
        return false;
    }

    /**
     * Get the style family of a style.
     *
     * @return string Style family
     */
    static public function getFamily() {
        return 'table-column';
    }

    /**
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setProperty($property, $value) {
        $style_fields = ODTStyleStyle::getStyleProperties ();
        if (array_key_exists ($property, $style_fields)) {
            $this->setPropertyInternal
                ($property, $style_fields [$property][0], $value, $style_fields [$property][1]);
            return;
        }
        if (array_key_exists ($property, self::$table_column_fields)) {
            $this->setPropertyInternal
                ($property, self::$table_column_fields [$property][0], $value, self::$table_column_fields [$property][1]);
            return;
        }
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $style = new ODTTableColumnStyle();
        $attrs = 0;
        
        $open = XMLUtil::getElementOpenTag('style:style', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(ODTStyleStyle::getStyleProperties (), $open);
        } else {
            $open = XMLUtil::getElementOpenTag('style:default-style', $xmlCode);
            if (!empty($open)) {
                $style->setDefault(true);
                $attrs += $style->importODTStyleInternal(ODTStyleStyle::getStyleProperties (), $open);
            }
        }

        $open = XMLUtil::getElementOpenTag('style:table-column-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(self::$table_column_fields, $open);
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    static public function getTableColumnProperties () {
        return self::$table_column_fields;
    }

    /**
     * This function creates a table column style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * width
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     *
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param null $style_name
     * @return ODTUnknownStyle or NULL
     */
    public static function createTableColumnStyle(array $properties, array $disabled_props = NULL){
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('TableColumn');
            $properties ['style-name'] = $style_name;
        }

        // Convert width to ODT format
        $table_co_width = $properties ['width'];
        if ( !empty ($table_co_width) ) {
            $length = strlen ($table_co_width);
            if ( $table_co_width [$length-1] != '%' ) {
                $properties ['column-width'] = $table_co_width;
            } else {
                // Columns have a specific syntax for relative width in %!
                // Change % to *.
                // Also mutiply with 10:
                // For some reason values for two columns like 10* and 90* do not give the result 10% and 90%.
                // But 100* and 900* do give the wanted reuslt! (weird)
                $table_co_width = trim ($table_co_width, '%');
                $properties ['rel-column-width'] = ($table_co_width*10).'*';
            }
        }

        // Create empty table column style.
        $object = new ODTTableColumnStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
    }
}

