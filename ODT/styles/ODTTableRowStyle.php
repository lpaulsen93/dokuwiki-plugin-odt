<?php
/**
 * ODTTableRowStyle: class for ODT table row styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 * @package    ODT\Styles\ODTTableRowStyle
 */

/** Include XMLUtil and ODTStyle */
require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once 'ODTStyle.php';

ODTStyleStyle::register('ODTTableRowStyle');

/**
 * The ODTTableRowStyle class.
 */
class ODTTableRowStyle extends ODTStyleStyle
{
    /** var array List of properties belonging to an ODT table. */
    static $table_row_fields = array(
        'row-height'               => array ('style:row-height',              'table-row',  true),
        'min-row-height'           => array ('style:min-row-height',          'table-row',  true),
        'use-optimal-row-height'   => array ('style:use-optimal-row-height',  'table-row',  true),
        'background-color'         => array ('fo:background-color',           'table-row',  true),
        'background-color'         => array ('fo:background-color',           'table-row',  true),
        'break-before'             => array ('fo:break-before',               'table-row',  true),
        'break-after'              => array ('fo:break-after',                'table-row',  true),
        'keep-together'            => array ('fo:keep-together',              'table-row',  true),

        // Fields for background-image
        // (see '<define name="style-background-image"> in relax-ng schema)'
        'repeat'                     => array ('style:repeat',                     'table-row-background-image',  true),
        'position'                   => array ('style:position',                   'table-row-background-image',  true),
        'style:filter-name'          => array ('style:filter-name',                'table-row-background-image',  true),
        'opacity'                    => array ('draw:opacity',                     'table-row-background-image',  true),
        'type'                       => array ('xlink:type',                       'table-row-background-image',  true),
        'href'                       => array ('xlink:href',                       'table-row-background-image',  true),
        'show'                       => array ('xlink:show',                       'table-row-background-image',  true),
        'actuate'                    => array ('xlink:actuate',                    'table-row-background-image',  true),
        'binary-data'                => array ('office:binary-data',               'table-row-background-image',  true),
        'base64Binary'               => array ('base64Binary',                     'table-row-background-image',  true),
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
     * @param    $properties    Properties to be imported
     * @param    $disabled      Properties to be ignored
     */
    public function importProperties($properties, $disabled) {
        $this->importPropertiesInternal(ODTStyleStyle::getStyleProperties (), $properties, $disabled);
        $this->importPropertiesInternal(self::$table_row_fields, $properties, $disabled);
        $this->setProperty('style-family', $this->getFamily());
    }

    /**
     * Check if a style is a common style.
     *
     * @return    bool    Is common style
     */
    public function mustBeCommonStyle() {
        return false;
    }

    /**
     * Get the style family of a style.
     *
     * @return    string    Style family
     */
    static public function getFamily() {
        return 'table-row';
    }

    /**
     * Set a property.
     * 
     * @param    $property    The name of the property to set
     * @param    $value       New value to set
     */
    public function setProperty($property, $value) {
        $style_fields = ODTStyleStyle::getStyleProperties ();
        if (array_key_exists ($property, $style_fields)) {
            $this->setPropertyInternal
                ($property, $style_fields [$property][0], $value, $style_fields [$property][1]);
            return;
        }
        if (array_key_exists ($property, self::$table_row_fields)) {
            $this->setPropertyInternal
                ($property, self::$table_row_fields [$property][0], $value, self::$table_row_fields [$property][1]);
            return;
        }
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param     $xmlCode    Style definition in ODT XML format
     * @return    ODTStyle    New specific style
     */
    static public function importODTStyle($xmlCode) {
        $style = new ODTTableRowStyle();
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

        $open = XMLUtil::getElementOpenTag('style:table-row-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(self::$table_row_fields, $open);
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    /**
     * Return an array listing the properties belonging to an ODT table row.
     *
     * @return    array    Properties
     */
    static public function getTableRowProperties () {
        return self::$table_row_fields;
    }

    /**
     * This function creates a table row style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * height, background-color
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author    LarsDW223
     * @param     array            $properties
     * @param     array|null       $disabled_props
     * @return    ODTTableRowStyle
     */
    public static function createTableRowStyle(array $properties, array $disabled_props = NULL){
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('TableRow');
            $properties ['style-name'] = $style_name;
        }

        // Create empty table row style.
        $object = new ODTTableRowStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
    }
}
