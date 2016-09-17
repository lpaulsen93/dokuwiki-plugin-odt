<?php
/**
 * ODTTableStyle: class for ODT table styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 * @package    ODT\Styles\ODTTableStyle
 */

/** Include XMLUtil and ODTStyle */
require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once 'ODTStyle.php';

ODTStyleStyle::register('ODTTableStyle');

/**
 * The ODTTableStyle class.
 */
class ODTTableStyle extends ODTStyleStyle
{
    /** var array List of properties belonging to an ODT table. */
    static $table_fields = array(
        'width'                      => array ('style:width',                      'table',  true),
        'rel-width'                  => array ('style:rel-width',                  'table',  true),
        'align'                      => array ('table:align',                      'table',  true),
        'margin-left'                => array ('fo:margin-left',                   'table',  true),
        'margin-right'               => array ('fo:margin-right',                  'table',  true),
        'margin-top'                 => array ('fo:margin-top',                    'table',  true),
        'margin-bottom'              => array ('fo:margin-bottom',                 'table',  true),
        'margin'                     => array ('fo:margin',                        'table',  true),
        'page-number'                => array ('style:page-number',                'table',  true),
        'break-before'               => array ('fo:break-before',                  'table',  true),
        'break-after'                => array ('fo:break-after',                   'table',  true),
        'background-color'           => array ('fo:background-color',              'table',  true),
        'shadow'                     => array ('style:shadow',                     'table',  true),
        'keep-with-next'             => array ('fo:keep-with-next',                'table',  true),
        'may-break-between-rows'     => array ('style:may-break-between-rows',     'table',  true),
        'border-model'               => array ('table:border-model',               'table',  true),
        'writing-mode'               => array ('style:writing-mode',               'table',  true),
        'display'                    => array ('table:display',                    'table',  true),

        // Fields for background-image
        // (see '<define name="style-background-image"> in relax-ng schema)'
        'repeat'                     => array ('style:repeat',                     'table-background-image',  true),
        'position'                   => array ('style:position',                   'table-background-image',  true),
        'style:filter-name'          => array ('style:filter-name',                'table-background-image',  true),
        'opacity'                    => array ('draw:opacity',                     'table-background-image',  true),
        'type'                       => array ('xlink:type',                       'table-background-image',  true),
        'href'                       => array ('xlink:href',                       'table-background-image',  true),
        'show'                       => array ('xlink:show',                       'table-background-image',  true),
        'actuate'                    => array ('xlink:actuate',                    'table-background-image',  true),
        'binary-data'                => array ('office:binary-data',               'table-background-image',  true),
        'base64Binary'               => array ('base64Binary',                     'table-background-image',  true),
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
        $this->importPropertiesInternal(self::$table_fields, $properties, $disabled);
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
        return 'table';
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
        if (array_key_exists ($property, self::$table_fields)) {
            $this->setPropertyInternal
                ($property, self::$table_fields [$property][0], $value, self::$table_fields [$property][1]);
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
        $style = new ODTTableStyle();
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

        $open = XMLUtil::getElementOpenTag('style:table-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(self::$table_fields, $open);
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    /**
     * Return an array listing the properties belonging to an ODT table.
     *
     * @return    array    Properties
     */
    static public function getTableProperties () {
        return self::$table_fields;
    }

    /**
     * This function creates a table table style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * width, border-collapse, background-color
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author    LarsDW223
     * @param     array              $properties     Properties for the table style
     * @param     array|null         $disabled_props Ignored properties.
     * @param     int                $max_width_cm   Max. allowed table width.
     * @return    ODTTableStyle|NULL
     */
    public static function createTableTableStyle(array $properties, array $disabled_props = NULL, $max_width_cm = 17){
        // If we want to change the table width we must set table:align to something else
        // than "margins". Otherwise the width will not be changed.
        if (empty($properties ['align'])) {
            $properties ['align'] = 'center';
        }
        if ($properties ['margin-left'] == '0') {
            unset($properties ['margin-left']);
        }
        if ($properties ['margin-right'] == '0') {
            unset($properties ['margin-right']);
        }

        // If no width specified always set 100%
        if (empty ($properties ['width'])) {
            $properties ['width'] = '100%';
        }
        
        // If relative width set, then move value to property 'rel-width'!
        if ( $properties ['width'] [strlen($properties ['width'])-1] == '%' ) {
            $properties ['rel-width'] = $properties ['width'];
            unset($properties ['width']);
        }
        
        // Convert property 'border-model' to ODT
        if ( !empty ($properties ['border-model']) ) {
            if ( $properties ['border-model'] == 'collapse' ) {
                $properties ['border-model'] = 'collapsing';
            } else {
                $properties ['border-model'] = 'separating';
            }
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Table');
            $properties ['style-name'] = $style_name;
        }

        // Create empty table style.
        $object = new ODTTableStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
    }
}

